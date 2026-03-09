<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use Carbon\Carbon;

class IncidentReportController extends Controller
{
    public function index()
    {
        return view("incident_report.incident_report");
    }

    // Load IR list for DataTable
    public function list(Request $request)
    {
        $user = Auth::user();
        $user_type = $user->access[request()->route()->getAction('as')]['user_type'] ?? null;

        $irs = DB::table('tbl_incident_report as ir')
            ->leftJoin('tbl_employee as e', 'ir.reported_by', '=', 'e.id')
            ->select(
                'ir.id',
                'ir.case_number',
                'ir.incident_date',
                'ir.location',
                'ir.status',
                'ir.report_datetime',
                DB::raw("CONCAT(e.first_name, ' ', e.last_name) as reported_by_name")
            );

        // If employee, only show IRs where they are involved
        if($user_type == 'employee'){
            $irs = $irs->whereExists(function($query) use ($user){
                $query->select(DB::raw(1))
                    ->from('tbl_ir_involved')
                    ->whereRaw('tbl_ir_involved.ir_id = ir.id')
                    ->where('tbl_ir_involved.employee_id', $user->employee_id);
            });
        }

        $irs = $irs->orderBy('ir.date_created', 'desc')->get();
        $count = 1; // add this to fix DT_RowIndex issue when using whereExists with pagination. separate count variable.
        // Attach involved employees per IR
        foreach($irs as $ir){
            $ir->DT_RowIndex = $count++; // Fixing DT_ROW Issue

            $involved = DB::table('tbl_ir_involved as inv')
                ->join('tbl_employee as e', 'inv.employee_id', '=', 'e.id')
                ->where('inv.ir_id', $ir->id)
                ->select(DB::raw("CONCAT(e.first_name, ' ', e.last_name) as name"))
                ->get()
                ->pluck('name')
                ->toArray();

            $ir->names_involved = implode(', ', $involved);

            // Action buttons
            $ir->action = $this->actionButtons($ir->id, $ir->status);
        }

        return response()->json(['data' => $irs]);
    }

    // Search employees for Select2
    public function searchEmployee(Request $request)
    {
        $search = $request->search;

        $employees = DB::table('tbl_employee')
            ->where('is_active', 1)
            ->where(function($query) use ($search){
                $query->where('first_name', 'like', "%$search%")
                      ->orWhere('last_name', 'like', "%$search%")
                      ->orWhere('emp_code', 'like', "%$search%");
            })
            ->select(
                'id',
                DB::raw("CONCAT(first_name, ' ', last_name) as text"),
                'position_id as position'
            )
            ->limit(10)
            ->get();

        return response()->json($employees);
    }

    // Store new IR
    public function store(Request $request)
    {
        try {
            // Generate case number
            $today = Carbon::now()->format('Ymd');
            $count = DB::table('tbl_incident_report')
                ->whereDate('date_created', Carbon::today())
                ->count();
            $sequence = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            $case_number = "IR-{$today}-{$sequence}";

            // Insert IR
            $ir_id = DB::table('tbl_incident_report')->insertGetId([
                'case_number'           => $case_number,
                'reported_by'           => $request->reported_by,
                'complainant_position'  => $request->complainant_position,
                'report_datetime'       => $request->report_datetime,
                'incident'              => $request->incident,
                'incident_date'         => $request->incident_date,
                'location'              => $request->location,
                'witnesses'             => $request->witnesses,
                'status'                => 'pending',
                'date_created'          => Carbon::now(),
                'user_id_added'         => Auth::id(),
            ]);

            // Insert involved employees
            $involved = [];
            foreach($request->names_involved as $employee_id){
                $involved[] = [
                    'ir_id'       => $ir_id,
                    'employee_id' => $employee_id,
                    'date_created' => Carbon::now(),
                ];
            }
            DB::table('tbl_ir_involved')->insert($involved);

            return response()->json([
                'success' => true,
                'message' => "Incident Report {$case_number} filed successfully!"
            ]);

        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ]);
        }
    }

    // View IR details
    public function view($id)
    {
        $ir = DB::table('tbl_incident_report as ir')
            ->leftJoin('tbl_employee as e', 'ir.reported_by', '=', 'e.id')
            ->where('ir.id', $id)
            ->select(
                'ir.*',
                DB::raw("CONCAT(e.first_name, ' ', e.last_name) as reported_by_name")
            )
            ->first();

        $involved = DB::table('tbl_ir_involved as inv')
            ->join('tbl_employee as e', 'inv.employee_id', '=', 'e.id')
            ->where('inv.ir_id', $id)
            ->select(
                'e.id',
                DB::raw("CONCAT(e.first_name, ' ', e.last_name) as name")
            )
            ->get();

        $ir->involved = $involved;

        return response()->json([
            'success' => true,
            'data'    => $ir
        ]);
    }

    // Delete IR function
    public function delete($id)
    {
        try {
            DB::table('tbl_ir_involved')->where('ir_id', $id)->delete();
            DB::table('tbl_incident_report')->where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Incident Report deleted successfully!'
            ]);
        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ]);
        }
    }

    // Update IR Function
    public function update(Request $request, $id)
    {
        try {
            DB::table('tbl_incident_report')->where('id', $id)->update([
                'reported_by'          => $request->reported_by,
                'complainant_position' => $request->complainant_position,
                'report_datetime'      => $request->report_datetime,
                'incident'             => $request->incident,
                'incident_date'        => $request->incident_date,
                'location'             => $request->location,
                'witnesses'            => $request->witnesses,
            ]);

            // Re-sync involved employees
            DB::table('tbl_ir_involved')->where('ir_id', $id)->delete();
            $involved = [];
            foreach($request->names_involved as $employee_id){
                $involved[] = [
                    'ir_id'        => $id,
                    'employee_id'  => $employee_id,
                    'date_created' => Carbon::now(),
                ];
            }
            DB::table('tbl_ir_involved')->insert($involved);

            return response()->json([
                'success' => true,
                'message' => 'Incident Report updated successfully!'
            ]);

        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ]);
        }
    }

    // Generate action buttons
    private function actionButtons($id, $status)
    {
        $user = Auth::user();
        $access = $user->access['incident_report']['access'] ?? '';

        $buttons = '';

        // View button — always visible if has R access
        if(preg_match("/R/i", $access)){
            $buttons .= '<button class="btn btn-info btn-sm btn_view_ir" data-id="'.$id.'">
                            <i class="fa fa-eye"></i>
                         </button> ';
        }

        // Edit & Delete — only for HR, only if still pending
        if(preg_match("/U/i", $access) && $status == 'pending'){
            $buttons .= '<button class="btn btn-warning btn-sm btn_edit_ir" data-id="'.$id.'">
                            <i class="fa fa-edit"></i>
                         </button> ';
        }

        if(preg_match("/D/i", $access) && $status == 'pending'){
            $buttons .= '<button class="btn btn-danger btn-sm btn_delete_ir" data-id="'.$id.'">
                            <i class="fa fa-trash"></i>
                         </button>';
        }

        return $buttons;
    }

        public function review($id)
    {
        try {
            DB::table('tbl_incident_report')->where('id', $id)->update([
                'status' => 'reviewed'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Incident Report marked as reviewed!'
            ]);

        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ]);
        }
    }

}