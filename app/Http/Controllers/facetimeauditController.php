<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use Storage;
use DateTime;
use DateTimeZone;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Hash;

class facetimeauditController extends Controller
{
    public function face_and_time_audit(){
        
        if(Auth::user()->access["face_and_time_audit"]["user_type"] != "employee"){
            $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")
            ->where("is_active",1)
            ->orderBy("last_name","asc")
            ->get();

       
        }else{
             $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")
            ->where("is_active",1)
            ->where("id",Auth::user()->company["linked_employee"]["id"])
            ->orderBy("last_name","asc")
            ->get();

        }
        return view("face_time_audit.index")
        ->with("tbl_employee", $tbl_employee)
        ;

    }

    public function load_face_time_audit_tbl(Request $request)
    {
        // Start by querying the tbl_face_time_audit table for the given emp_id
        $logs = DB::connection("intra_payroll")->table("tbl_face_time_audit")
            ->where("emp_id", $request->emp_id);

        // Apply a date range filter if provided
        if ($request->filled('date_range')) {
            [$start_date, $end_date] = explode(' - ', $request->date_range);
            $logs->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
        }

        // Get the total count of records for pagination
        $total = $logs->count();

        // Apply pagination: Default 50 items per page, based on the current page
        $logs = $logs->orderBy("created_at", "DESC")
            ->skip(($request->page - 1) * 20)  // Skip records based on the current page
            ->take(20)  // Limit results to 50 per page
            ->get();

        $array = collect($logs);

        // Return data with pagination information
        return response()->json([
            'total_images' => $total,
            'images' => $array->map(function($row) {
                return [
                    'image' => $row->image,  // You might want to modify this to use `asset_with_env`
                    'state' => $this->getStateButton($row->state),  // Get formatted state
                    'created_at' => (new DateTime($row->created_at, new DateTimeZone('UTC')))->format('Y-m-d H:i:s')
                ];
            })
        ]);
    }

    // Helper function to format the state as a button
    private function getStateButton($state)
    {
        switch ($state) {
            case "AM_IN":
                return "<div class='btn btn-success btn-sm'>AM IN</div>";
            case "AM_OUT":
                return "<div class='btn btn-warning btn-sm'>AM OUT</div>";
            case "PM_IN":
                return "<div class='btn btn-success btn-sm'>PM IN</div>";
            case "PM_OUT":
                return "<div class='btn btn-warning btn-sm'>PM OUT</div>";
            case "OT_IN":
                return "<div class='btn btn-success btn-sm'>OT IN</div>";
            case "OT_OUT":
                return "<div class='btn btn-warning btn-sm'>OT OUT</div>";
            case "FLEX_IN":
                return "<div class='btn btn-warning btn-sm'>START TIME</div>";
            case "FLEX_OUT":
                return "<div class='btn btn-warning btn-sm'>END TIME</div>";
            default:
                return "<div class='btn btn-dark btn-sm'>UNKNOWN</div>";
        }
    }

}
