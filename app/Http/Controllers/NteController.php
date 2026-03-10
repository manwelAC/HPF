<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use Carbon\Carbon;

class NTEController extends Controller
{
    public function index()
    {
        return view("nte_management.nte");
    }

    // Load NTE list for DataTable
    public function list(Request $request)
        {
            $user = Auth::user();
            $user_type = $user->access['nte_management']['user_type'] ?? null;

            $ntes = DB::table('tbl_nte as n')
                ->leftJoin('tbl_employee as e', 'n.employee_id', '=', 'e.id')
                ->leftJoin('tbl_incident_report as ir', 'n.ir_id', '=', 'ir.id')
                ->select(
                    'n.id',
                    'n.case_number',
                    'n.date_served',
                    'n.due_date',
                    'n.status',
                    'ir.case_number as ir_case_number',
                    DB::raw("CONCAT(e.first_name, ' ', e.last_name) as employee_name")
                );

            if($user_type == 'employee'){
                $employee = DB::table('tbl_employee')->where('user_id', $user->id)->first();
                if($employee){
                    $ntes = $ntes->where('n.employee_id', $employee->id);
                } else {
                    return response()->json(['data' => []]);
                }
            }

            $ntes = $ntes->orderBy('n.date_created', 'desc')->get();

            $count = 1;
            foreach($ntes as $nte){
                $nte->DT_RowIndex = $count++;
                $nte->action = $this->actionButtons($nte->id, $nte->status);
            }

            return response()->json(['data' => $ntes]);
        }

    // Store new NTE
public function store(Request $request)
{
    try {
        // Generate case number
        if($request->ir_id){
            // NTE from IR — derive case number from parent IR
            $ir = DB::table('tbl_incident_report')->where('id', $request->ir_id)->first();
            $base = substr($ir->case_number, 3); // remove "IR-"
            $nte_case_number = "NTE-" . $base;

            // Check if NTE already exists for this employee from this IR
            $existing = DB::table('tbl_nte')
                ->where('ir_id', $request->ir_id)
                ->where('employee_id', $request->employee_id)
                ->first();

            if($existing){
                return response()->json([
                    'success' => false,
                    'message' => 'NTE already exists for this employee from this IR!'
                ]);
            }
        } else {
            // Standalone NTE — generate case number independently
            $today = Carbon::now()->format('Ymd');
            $count = DB::table('tbl_nte')
                ->whereDate('date_created', Carbon::today())
                ->count();
            $sequence = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            $nte_case_number = "NTE-{$today}-{$sequence}";
        }

        DB::table('tbl_nte')->insert([
            'case_number'   => $nte_case_number,
            'ir_id'         => $request->ir_id ?? null,
            'employee_id'   => $request->employee_id,
            'case_details'  => $request->case_details,
            'remarks'       => $request->remarks,
            'date_served'   => $request->date_served,
            'due_date'      => $request->due_date,
            'resolution'    => $request->resolution,
            'status'        => 'pending',
            'date_created'  => Carbon::now(),
            'user_id_added' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "NTE {$nte_case_number} created successfully!"
        ]);

    } catch(\Exception $e){
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong: ' . $e->getMessage()
        ]);
    }
}

    // View NTE details
    public function view($id)
    {
        $nte = DB::table('tbl_nte as n')
            ->leftJoin('tbl_employee as e', 'n.employee_id', '=', 'e.id')
            ->leftJoin('tbl_incident_report as ir', 'n.ir_id', '=', 'ir.id')
            ->where('n.id', $id)
            ->select(
                'n.*',
                'ir.case_number as ir_case_number',
                DB::raw("CONCAT(e.first_name, ' ', e.last_name) as employee_name")
            )
            ->first();

        return response()->json([
            'success' => true,
            'data'    => $nte
        ]);
    }

    // Update NTE
    public function update(Request $request, $id)
    {
        try {
            DB::table('tbl_nte')->where('id', $id)->update([
                'case_details' => $request->case_details,
                'remarks'      => $request->remarks,
                'date_served'  => $request->date_served,
                'due_date'     => $request->due_date,
                'resolution'   => $request->resolution,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'NTE updated successfully!'
            ]);

        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ]);
        }
    }

    // Employee reply to NTE
    public function reply(Request $request, $id)
    {
        try {
            DB::table('tbl_nte')->where('id', $id)->update([
                'employee_reply' => $request->employee_reply,
                'reply_date'     => Carbon::now(),
                'status'         => 'replied',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Your explanation has been submitted successfully!'
            ]);

        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ]);
        }
    }

    // Delete NTE
    public function delete($id)
    {
        try {
            DB::table('tbl_nte')->where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'NTE deleted successfully!'
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
        $access = $user->access['nte_management']['access'] ?? '';
        $user_type = $user->access['nte_management']['user_type'] ?? '';

        $buttons = '';

        // View button — always visible if has R access
        if(preg_match("/R/i", $access)){
            $buttons .= '<button class="btn btn-info btn-sm btn_view_nte" data-id="'.$id.'">
                            <i class="fa fa-eye"></i>
                        </button> ';
        }

        // Edit & Delete — only for HR, only if still pending
        if($user_type == 'hr'){
            if(preg_match("/U/i", $access) && $status == 'pending'){
                $buttons .= '<button class="btn btn-warning btn-sm btn_edit_nte" data-id="'.$id.'">
                                <i class="fa fa-edit"></i>
                            </button> ';
            }

            if(preg_match("/D/i", $access) && $status == 'pending'){
                $buttons .= '<button class="btn btn-danger btn-sm btn_delete_nte" data-id="'.$id.'">
                                <i class="fa fa-trash"></i>
                            </button>';
            }
        }

        return $buttons;
    }

}