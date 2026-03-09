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
        return view("nte.nte");
    }

    // Load NTE list for DataTable
    public function list(Request $request)
    {
        $user = Auth::user();
        $user_type = $user->role_type ?? null;

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

        // If employee, only show their own NTEs
        if($user_type == 'employee'){
            $ntes = $ntes->where('n.employee_id', $user->employee_id);
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
            // Get parent IR to derive case number
            $ir = DB::table('tbl_incident_report')->where('id', $request->ir_id)->first();

            // Derive NTE case number from IR case number
            // IR-20260903-0001 → NTE-20260903-0001
            $nte_case_number = str_replace('IR-', 'NTE-', $ir->case_number) . '-' . str_pad(
                DB::table('tbl_nte')->where('ir_id', $request->ir_id)->count() + 1, 
                2, '0', STR_PAD_LEFT
            );

            // Actually derive it cleanly
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

            DB::table('tbl_nte')->insert([
                'case_number'   => $nte_case_number,
                'ir_id'         => $request->ir_id,
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
        $access = $user->access['nte']['access'] ?? '';

        $buttons = '';

        if(preg_match("/R/i", $access)){
            $buttons .= '<button class="btn btn-info btn-sm btn_view_nte" data-id="'.$id.'">
                            <i class="fa fa-eye"></i>
                         </button> ';
        }

        // Only HR can edit, only if still pending
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

        return $buttons;
    }
}