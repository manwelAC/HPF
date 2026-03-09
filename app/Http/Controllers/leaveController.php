<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Auth;
use DB;
use Storage;
use Yajra\DataTables\DataTables;
use DateTime;
use DateInterval;
use DatePeriod;

class leaveController extends Controller
{
    public function leave_management(){
        $leave_types = DB::connection("intra_payroll")->table("tbl_leave_types")
            ->orderBy("leave_name")
            ->get();
        $role_id = Auth::user()->role_id;
        $user_id = Auth::id();
        // dd(Auth::user()->access);
        if(Auth::user()->access["leave_management"]["user_type"] != "employee"){
            $emp_list = DB::connection("intra_payroll")->table("tbl_employee")
            ->where('is_active', 1);
            
            $dept_id = [];
            if($role_id === 4){
                
                if($user_id === 4){ 
                    $dept_id = ['2','4']; //operation dept thelma add sales
                    
                }elseif($user_id === 7){ 
                    $dept_id = ['4']; //sales dept mark
                }
                $emp_list = $emp_list->whereIn('department', $dept_id);
                
            }
            $emp_list = $emp_list->orderBy("last_name")
            ->orderBy("first_name")
            ->orderBy("middle_name")
            ->get();
        
        }else{
            $emp_list = DB::connection("intra_payroll")->table("tbl_employee")
            ->where('is_active', 1)
            ->where("id",Auth::user()->company["linked_employee"]["id"])
            ->orderBy("last_name")
            ->orderBy("first_name")
            ->orderBy("middle_name")
            ->get();
         
            }
    
      
        return view("leave.index")
            ->with("leave_type", $leave_types)
            ->with("emp_list", $emp_list)
            ;
    }
    public function store_leave_type(Request $request){
        
        if($request->id == "new"){
            $tbl = DB::connection("intra_payroll")->table("tbl_leave_types")
            ->where("leave_name", "like", $request->leave_name)
            ->first();
            if($tbl != null) {
                return json_encode("Leave Name Already Taken");
            }else{
               $insert_array = array(
                "leave_type" => $request->leave_type,
                "leave_name" => $request->leave_name,
                "is_with_credits" => $request->require,
                "date_created" => date("Y-m-d H:i:s"),
                "user_id" => Auth::user()->id
                );
            }
        }else{
            $tbl = DB::connection("intra_payroll")->table("tbl_leave_types")
            ->where("leave_name", "like", $request->leave_name)
            ->where("id", "!=", $request->id)
            ->first();
            if($tbl != null) {
                return json_encode("Leave Name Already Taken");
            }else{
               $insert_array = array(
                "leave_type" => $request->leave_type,
                "leave_name" => $request->leave_name,
                "is_with_credits" => $request->require,
                "user_id" => Auth::user()->id
                );
            }
        }
        DB::beginTransaction();
        try {
            if($request->id == "new"){
                DB::connection("intra_payroll")->table("tbl_leave_types")
                ->insert($insert_array);
            }else{
                DB::connection("intra_payroll")->table("tbl_leave_types")
                ->where("id", $request->id)
                ->update($insert_array);
            }
         
            DB::commit();
            return json_encode("Success");
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        }
    }
    public function leave_type_tbl(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];
        $types =array(
            "VL" => "Vacation Leave",
            "SL" => "Sick Leave",
            "OL" => "Special Leave",
            "LWP" => "Leave Without Pay"
        );
        $data = DB::connection("intra_payroll")->table("tbl_leave_types")
            ->orderBy("date_updated")
            ->get();
        $data = collect($data);
        return Datatables::of($data)
        ->addColumn('leave_type', function($row) use ($types){
            if(isset($types[$row->leave_type])){
                return $types[$row->leave_type];
            }else{
                return $types["OL"];
            }
            
        })
       
        ->addColumn('is_with_credits', function($row){
            if($row->is_with_credits ==1){
                return "<a class='btn btn-success btn-sm'>YES</a>";
            }else{
                return "<a class='btn btn-warning btn-sm'>NO</a>";
            }
        })
        ->addColumn('action', function($row) use ($page_permission){
            $btn = "";
            if(preg_match("/U/i", $page_permission)){
                // add delete in leave
                $type = "type";
                $btn .= "<a 
                class='btn btn-sm btn-success'
                data-id='".$row->id."' 
                data-type='".$row->leave_type."' 
                data-name='".$row->leave_name."' 
                data-require='".$row->is_with_credits."' 
                data-toggle='modal' 
                data-target='#leave_table_modal'
                >
                Edit
                </a>";
                // add delete in leave
                $btn .= " <button 
                class='btn btn-sm btn-danger'
                onclick='delete_leave(" . $row->id . ", \"" . $type . "\")'
                >
                Delete
                </button>";
            }
          
            return $btn;
        })
        ->rawColumns(['action', 'is_with_credits'])
        ->make(true);
    }
    function search_multi_array($array, $key, $value) {
        foreach ($array as $subarray) {
            if (isset($subarray[$key]) && $subarray[$key] == $value) {
                return $subarray;
            }
        }
        return null;
    }
    public function leave_credit_tbl(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];
        $employee = json_decode(json_encode(
            DB::connection("intra_payroll")->table("tbl_employee")
                ->get()
        ), true);
        $leave_type = json_decode(json_encode(
            DB::connection("intra_payroll")->table("tbl_leave_types")
                ->get()
        ), true);
        $types =array(
            "VL" => "Vacation Leave",
            "SL" => "Sick Leave",
            "OL" => "Special Leave"
        );
        if(Auth::user()->access[$request->page]["user_type"] != "employee"){
            $data = DB::connection("intra_payroll")->table("tbl_leave_credits")
            ->orderBy("date_updated")
            ->get();
        }else{
            $data = DB::connection("intra_payroll")->table("tbl_leave_credits")
            ->where("emp_id", Auth::user()->company["linked_employee"]["id"])
            ->orderBy("date_updated")
            ->get();
            }
    
        
        $data = collect($data);
        return Datatables::of($data)
        ->addColumn('emp_name', function($row) use ($employee){
            $data = $this->search_multi_array($employee, "id", $row->emp_id);
            if($data != null && count($data)>0){
                return "(".$data["emp_code"] . ") ".$data["last_name"].", ".$data["first_name"]." ".$data["middle_name"]." ".$data["ext_name"];
            }else{
                return "";
            }
            
        })
       
        ->addColumn('leave_type', function($row) use ($types,$leave_type){
            $data = $this->search_multi_array($leave_type, "id", $row->leave_id);
            if($data != null && count($data)>0){
            return $types[$data["leave_type"]];
            }else{
                return "";
            }
        })
        ->addColumn('leave_name', function($row) use ($types,$leave_type){
            $data = $this->search_multi_array($leave_type, "id", $row->leave_id);
            if($data != null && count($data)>0){
            return $data["leave_name"];
            }else{
                return "";
            }
        })
       
        ->addColumn('credit', function($row){
          return $row->leave_count;
        })
        ->addColumn('balance', function($row){
            $target_year = date("Y");
            
            $leave_used = DB::connection("intra_payroll")->table("tbl_leave_used")
                ->where("leave_source_id", $row->leave_id)
                ->where("emp_id", $row->emp_id)
                ->where("leave_status", 'APPROVED')
                ->sum("leave_count");
            
            $balance = $row->leave_count - $leave_used;
            return max(0, $balance);
        })
        ->addColumn('action', function($row) use ($page_permission, $request){
            $btn = "";
            if(preg_match("/U/i", $page_permission)){
                if(Auth::user()->access[$request->page]["user_type"] != "employee"){
                    // add delete in leave
                    $type = "credit";
                    $btn .= "<a 
                    class='btn btn-sm btn-success'
                    data-id='".$row->id."'
                    data-emp_id='".$row->emp_id."'
                    data-leave_id='".$row->leave_id."'
                    data-leave_count='".$row->leave_count."'
                    data-toggle='modal' 
                    data-target='#leave_credit_modal'
                    >
                    Edit
                    </a>";
                    // add delete in leave
                    $btn .= " <button 
                    class='btn btn-sm btn-danger'
                    onclick='delete_leave(" . $row->id . ", \"" . $type . "\")'
                    >
                    Delete
                    </button>";
                }
               
            }
          
            return $btn;
        })
        ->rawColumns(['action'])
        ->make(true);
    }
    public function file_leave_tbl(Request $request)
    {
        $user = Auth::user();
        $page_permission = $user->access[$request->page]["access"];
        $role_id = $user->role_id;
        $user_id = $user->id;
        // Department mapping for role 4
        $deptMap = [
            4 => [2, 4], // Thelma: can view dept 2 and 4
            7 => [4],    // Mark: dept 4
        ];
        // Build leave query with joins
        $leaveQuery = DB::connection("intra_payroll")
            ->table("tbl_leave_used as lu")
            ->join("tbl_employee as e", "e.id", "=", "lu.emp_id")
            ->join("tbl_leave_types as lt", "lt.id", "=", "lu.leave_source_id")
            ->select(
                "lu.*",
                DB::raw("CONCAT(e.last_name, ', ', e.first_name, ' ', e.middle_name) as emp_name"),
                "lt.leave_name"
            )
            ->orderBy("lu.date_updated", "desc");
        // Department restriction
        if ($role_id === 4 && isset($deptMap[$user_id])) {
            $leaveQuery->whereIn("e.department", $deptMap[$user_id]);
        }
        // Restrict if user is an employee
        if ($user->access[$request->page]["user_type"] === "employee") {
            if($role_id === 7){ //first approver (josephine, mary anne, morfe)
                $leaveQuery->where(function($q){
                    $q->whereIn("lu.leave_status", ["FILED","APPROVED"]);
                })
                ->orWhere("e.user_id", Auth::user()->id);
            }elseif($role_id === 8){ //2nd approver (paul)
                $leaveQuery->where(function($q){
                    $q->whereIn("lu.leave_status", ["1st_Approved","APPROVED"]);
                })
                ->orWhere("e.user_id", Auth::user()->id);
            }else{
                $leaveQuery->where("lu.emp_id", $user->company["linked_employee"]["id"]);
            }
            
        }
        $data = $leaveQuery->get();
        return Datatables::of($data)
            // ✅ Explicitly add columns
            ->addColumn('emp_name', fn($row) => $row->emp_name)
            ->addColumn('date_filed', fn($row) => date("Y-m-d", strtotime($row->date_created)))
            ->addColumn('leave_type', fn($row) => $row->leave_name)
            //  Other columns
            ->addColumn('dates', fn($row) =>
                "<label class='badge badge-info mb-1 mr-1'>FROM</label>{$row->leave_date_from}
                <br><label class='badge badge-info mr-1'>TO</label>{$row->leave_date_to}"
            )
            ->addColumn('is_half_day', fn($row) => $row->half_day == "1" ? "YES" : "NO")
            ->addColumn('rejoin_duty_on', fn($row) => $row->rejoin_duty_on)
            ->addColumn('leave_count', fn($row) => $row->leave_count)
            ->addColumn('leave_status', function($row) {
                $status = $row->leave_status;
                $role_id = Auth::user()->role_id;
                if ($status === "FILED") {
                    $status = "<span class='badge badge-warning'>WAITING FOR 1ST APPROVAL</span>";
                } elseif ($status === "1st_Approved") {
                    $status = "<span class='badge badge-info'>FOR FINAL APPROVAL</span>";
                } elseif ($status === "APPROVED") {
                    $status = "<span class='badge badge-success'>APPROVED</span>";
                }
                return $status;
            })
            ->addColumn('action', function ($row) use ($page_permission, $request, $user) {
                $btn = "";
                if (preg_match("/U/i", $page_permission)) {
                    if ($user->access[$request->page]["user_type"] != "employee") {
                        $btn .= "<a class='btn btn-sm btn-success mr-1'
                            data-id='{$row->id}'
                            data-emp_id='{$row->emp_id}'
                            data-leave_id='{$row->leave_source_id}'
                            data-leave_from='{$row->leave_date_from}'
                            data-leave_to='{$row->leave_date_to}'
                            data-rejoin_duty='{$row->rejoin_duty_on}'
                            data-reason='{$row->reason}'
                            data-leave_status='{$row->leave_status}'
                            data-toggle='modal'
                            data-target='#leave_file_modal'
                            data-half_day='{$row->half_day}'>
                            Edit</a>";
                            $btn .= "<button class='btn btn-sm btn-danger' onclick='delete_file_leave({$row->id})'>
                                Delete
                            </button>";
                    }else{
                        $class_disable = '';
                        if($row->leave_status === 'APPROVED'){
                            $class_disable = 'disabled';
                        }
                        $btn .= "<a class='btn btn-sm btn-success mr-1 $class_disable'
                        data-id='{$row->id}'
                        data-emp_id='{$row->emp_id}'
                        data-leave_id='{$row->leave_source_id}'
                        data-leave_from='{$row->leave_date_from}'
                        data-leave_to='{$row->leave_date_to}'
                        data-rejoin_duty='{$row->rejoin_duty_on}'
                        data-reason='{$row->reason}'
                        data-leave_status='{$row->leave_status}'
                        data-toggle='modal'
                        data-target='#leave_file_modal'
                        data-half_day='{$row->half_day}'>
                        Edit</a>";
                        $btn .= "<button class='btn btn-sm btn-danger' onclick='delete_file_leave({$row->id})'$class_disable>
                            Delete
                        </button>"; 
                    }
                   
                }
                return $btn;
            })
            ->rawColumns(['action', 'dates','leave_status'])
            ->make(true);
    }
    public function file_leave_tbl_old(Request $request){
        $page_permission = Auth::user()->access[$request->page]["access"];
        $employee = json_decode(json_encode(
            DB::connection("intra_payroll")->table("tbl_employee")
                ->get()
        ), true);
        $leave_type = json_decode(json_encode(
            DB::connection("intra_payroll")->table("tbl_leave_types")
                ->get()
        ), true);
        
        if(Auth::user()->access[$request->page]["user_type"] != "employee"){
            $data = DB::connection("intra_payroll")->table("tbl_leave_used")
            ->orderBy("date_updated", "desc")
            ->get();
        }else{
        
            $data = DB::connection("intra_payroll")->table("tbl_leave_used")
            ->where("emp_id", Auth::user()->company["linked_employee"]["id"])
            ->orderBy("date_updated", "desc")
            ->get();
            }
    
        $data = collect($data);
        return Datatables::of($data)
        ->addColumn('emp_name', function($row) use ($employee){
            $data = $this->search_multi_array($employee, "id", $row->emp_id);
            if(count($data)>0){
                return $data["last_name"].", ".$data["first_name"]." ".$data["middle_name"]." ".$data["ext_name"];
            }else{
                return "";
            }
        })
        ->addColumn('date_filed', function($row){
            return date("Y-m-d", strtotime($row->date_created));
          })
        ->addColumn('leave_type', function($row) use ($leave_type){
            $data = $this->search_multi_array($leave_type, "id", $row->leave_source_id);
            if(count($data)>0){
            return $data["leave_name"];
            }else{
                return "";
            }
        })
        ->addColumn('dates', function($row){
            $btn = "<label class='badge badge-info mb-1 mr-1'>FROM</label>".$row->leave_date_from;
            $btn .= "<br>" . "<label class='badge badge-info mr-1'>TO</label>".$row->leave_date_to;
            return $btn;
          })
          ->addColumn('is_half_day', function($row){
            if($row->half_day == "1"){
                return "YES";
            }else{
                return "NO";
            }
          })
        ->addColumn('rejoin_duty_on', function($row){
            return $row->rejoin_duty_on;
          })
        ->addColumn('leave_count', function($row){
          return $row->leave_count;
        })
        ->addColumn('action', function($row) use ($page_permission, $request){
            $btn = "";
            if(preg_match("/U/i", $page_permission)){
                if(Auth::user()->access[$request->page]["user_type"] != "employee"){
                    $btn .= "<a 
                    class='btn btn-sm btn-success mr-1'
                    data-id = '".$row->id."'
                    data-emp_id = '".$row->emp_id."'
                    data-leave_id = '".$row->leave_source_id."'
                    data-leave_from = '".$row->leave_date_from."'                        
                    data-leave_to = '".$row->leave_date_to."'    
                    data-rejoin_duty = '".$row->rejoin_duty_on."'                        
                    data-reason ='".$row->reason."'
                    data-leave_status = '".$row->leave_status."'
                    data-toggle='modal' 
                    data-target='#leave_file_modal'
                    data-half_day='".$row->half_day."'
                    >
                    Edit
                    </a>";
                }
                
                $btn .= "<button 
                class='btn btn-sm btn-danger'
                onclick='delete_file_leave(".$row->id.")'
                >
                Delete
                </button>";
                
            }
          
            $today = strtotime(date("Y-m-d"));
            $date_from = strtotime($row->leave_date_from);
            // show action btn
            // if($today > $date_from){
            //     $btn ="";
            // }
            return $btn;
        })
        ->rawColumns(['action', 'dates'])
        ->make(true);
    }
    public function delete_filed_leave(Request $request){
        DB::beginTransaction();
            try {
                DB::connection("intra_payroll")->table("tbl_leave_used")
                    ->where("id", $request->id)
                    ->delete();
                DB::commit();
                return json_encode("Deleted");
            } catch (\Throwable $th) {
                DB::rollback();
                return json_encode($th->getMessage());
            }
    }
    // add delete in leave
    public function delete_leave(Request $request){
        $type = $request->type;
        $tbl = "";
        if($type == "credit"){
            $tbl = "tbl_leave_credits";
        }else{
            $tbl = "tbl_leave_types";
        }
        DB::beginTransaction();
            try {
                DB::connection("intra_payroll")->table($tbl)
                    ->where("id", $request->id)
                    ->delete();
                DB::commit();
                return json_encode("Deleted");
            } catch (\Throwable $th) {
                DB::rollback();
                return json_encode($th->getMessage());
            }
    }
    public function store_leave_credit(Request $request){
        DB::beginTransaction();
        if(isset($request->emp_id)){
            try {
                $all_emp = 0;
                foreach($request->emp_id as $emp){
                    if($emp == "all"){
                        $all_emp = 1;
                    }
                }
                if($all_emp == 1){
                    if(Auth::user()->access["leave_management"]["user_type"] != "employee"){
                        $emp_list = DB::connection("intra_payroll")->table("tbl_employee")
                        ->select("id")
                        ->where('is_active', 1)
                        ->orderBy("last_name")
                        ->orderBy("first_name")
                        ->orderBy("middle_name")
                        ->get();
            
                    
                    }else{
                        $emp_list = DB::connection("intra_payroll")->table("tbl_employee")
                        ->select("id")
                        ->where('is_active', 1)
                        ->where("id",Auth::user()->company["linked_employee"]["id"])
                        ->orderBy("last_name")
                        ->orderBy("first_name")
                        ->orderBy("middle_name")
                        ->get();
                     
                        }
    
                    
                        $request->emp_id = json_decode(json_encode($emp_list), true);
                }
              
                foreach($request->emp_id as $emp){
                    if($all_emp == 1){
                        $emp_id =  $emp["id"];
                    }else{
                        $emp_id = $emp;
                    }
                  
                    $tbl = DB::connection("intra_payroll")->table("tbl_leave_credits")
                        ->where("emp_id", "like", $emp_id)
                        ->where("leave_id", $request->leave_type)
                        ->first();
                    if($tbl != null){
                        if($request->id != "new"){
                            $new_leave_credit = $request->leave_credit; //update leave credit
                        }else{
                            $new_leave_credit = $tbl->leave_count + $request->leave_credit; //update leave credit
                        }
                        
                        
                        $insert_array = array(
                            "emp_id" => $emp_id,
                            "leave_id" => $request->leave_type,
                            "leave_count" => $new_leave_credit, //update leave credit
                            "user_id" => Auth::user()->id,
                            "year_given" => date("Y-m-d")  // fix leave credit
                            );
                            DB::connection("intra_payroll")->table("tbl_leave_credits")
                            ->where("id", $tbl->id)
                            ->update($insert_array);
                    }else{
                        $insert_array = array(
                            "emp_id" => $emp_id,
                            "leave_id" => $request->leave_type,
                            "leave_count" => $request->leave_credit,
                            "date_created" => date("Y-m-d H:i:s"),
                            "user_id" => Auth::user()->id,
                            "year_given" => date("Y-m-d")  // fix leave credit
                            );
                    
                            DB::connection("intra_payroll")->table("tbl_leave_credits")
                            ->insert($insert_array);
                    }
                }
                DB::commit();
                return json_encode("Success");
            } catch (\Throwable $th) {
                DB::rollback();
                return json_encode($th->getMessage());
            }
        }else{
            return json_encode("No Employee Selected");
        }
    }
    public function leave_employee_list(Request $request){ //pogi
        $role_id = Auth::user()->role_id;
        $user_id = Auth::id();
        if(Auth::user()->access[$request->page]["user_type"] != "employee"){
          $employee_list = DB::connection("intra_payroll")->table("tbl_employee")
            ->where("is_active", 1);
            $dept_id = [];
            if($role_id === 4){
                
                if($user_id === 4){ 
                    $dept_id = ['2','4']; //operation dept thelma add sales
                }elseif($user_id === 7){ 
                    $dept_id = ['4']; //sales dept mark
                }
                $employee_list = $employee_list->whereIn('department', $dept_id);
                
            }
            $employee_list = $employee_list->get();
            return json_encode($employee_list);
        }else{
            //EMPLOYEE
            $employee_list = DB::connection("intra_payroll")->table("tbl_employee")
                ->where("is_active", 1);
            if($role_id === 2){
                $employee_list = $employee_list->where("user_id", Auth::user()->id);
            }
            $employee_list = $employee_list->get();
            return json_encode($employee_list); 
        }
    }
    public function get_leave_balance(Request $request){
        $target_year = date("Y");
        $chk_leave_type = DB::connection("intra_payroll")->table("tbl_leave_types")
        ->where("id", $request->file_leave_type)
        ->where("is_with_credits", "1")
        ->first();
        if($chk_leave_type != null){
            $is_with_credits = 1;
            $leave_credits = DB::connection("intra_payroll")->table("tbl_leave_credits")
            ->where("leave_id", $request->file_leave_type)
           // ->where("year_given", $target_year)
            ->where("emp_id", $request->file_emp_name)
            ->sum("leave_count");
            $leave_used = DB::connection("intra_payroll")->table("tbl_leave_used")
            //    ->where("leave_year", $target_year)
                ->where("leave_source_id", $request->file_leave_type)
                ->where("emp_id", $request->file_emp_name)
                ->where("leave_status", 'APPROVED')
                ->sum("leave_count");
             $leave_balance = $leave_credits - $leave_used;
        }else{
           $leave_balance = "not required";
        }
        return json_encode($leave_balance);
    }
    public function store_filed_leave(Request $request){
        $is_with_credits = 0;
        $target_year  = date("Y", strtotime($request->file_from));
        $half_day = $request->half_day;
        $file_from = new DateTime($request->file_from);
        $file_to = new DateTime($request->file_to);
        $interval = $file_from->diff($file_to);
        $days = $interval->format('%d') + 1;
        
        if($half_day == "1"){
            $days = $days - 0.5;
        }
        if($request->id == "new"){
            $chk_if_already_filed = DB::connection("intra_payroll")->table("tbl_leave_used")
            ->where("leave_source_id", $request->file_leave_type)
            ->where("emp_id", $request->file_emp_name)
            ->whereBetween("leave_date_from",[$request->file_from, $request->file_to])
            ->orWhere("leave_source_id", $request->file_leave_type)
            ->where("emp_id", $request->file_emp_name)
            ->whereBetween("leave_date_from",[$request->file_from, $request->file_to])
            ->get();
            if(count($chk_if_already_filed)>0){
                return json_encode("Filling Date conflict");
            }
            $chk_leave_type = DB::connection("intra_payroll")->table("tbl_leave_types")
            ->where("id", $request->file_leave_type)
            ->where("is_with_credits", "1")
            ->first();
            if($chk_leave_type != null){
                $is_with_credits = 1;
            }
        if($is_with_credits == 1){
            
            $leave_used = DB::connection("intra_payroll")->table("tbl_leave_used")
               // ->where("leave_year", $target_year)
                ->where("leave_source_id", $request->file_leave_type)
                ->where("emp_id", $request->file_emp_name)
                ->sum("leave_count");
            $leave_credits = DB::connection("intra_payroll")->table("tbl_leave_credits")
                ->where("leave_id", $request->file_leave_type)
               // ->where("year_given", $target_year)
                ->where("emp_id", $request->file_emp_name)
                ->sum("leave_count");
            $leave_balance = $leave_credits - $leave_used;
            if($leave_balance >= $days){
                $ins_data = array(
                    "emp_id" => $request->file_emp_name,
                    "leave_source_id" => $request->file_leave_type,
                    "leave_year" => $target_year,
                    "leave_date_from" => $request->file_from,
                    "leave_date_to" => $request->file_to,
                    "rejoin_duty_on" => $request->rejoin_duty, 
                    "leave_status" => $request->leave_status,
                    "reason" => $request->file_reason,
                    "half_day" => $half_day,
                    "leave_count" => $days,
                    "date_created" => date("Y-m-d H:i:s"),
                    "user_id" => Auth::user()->id
                );
            }else{
                return json_encode("Leave Credits is not enough");
            }
        }else{
            $ins_data = array(
                "emp_id" => $request->file_emp_name,
                "leave_source_id" => $request->file_leave_type,
                "leave_year" => $target_year,
                "leave_date_from" => $request->file_from,
                "leave_date_to" => $request->file_to,
                "rejoin_duty_on" => $request->rejoin_duty, 
                "leave_status" => $request->leave_status,
                "leave_count" => $days,
                "half_day" => $half_day,
                "reason" => $request->file_reason,
                "date_created" => date("Y-m-d H:i:s"),
                "user_id" => Auth::user()->id
            );
        }
        }else{
            $ins_data = array(
                "emp_id" => $request->file_emp_name,
                "leave_source_id" => $request->file_leave_type,
                "leave_year" => $target_year,
                "leave_date_from" => $request->file_from,
                "leave_date_to" => $request->file_to,
                "rejoin_duty_on" => $request->rejoin_duty, 
                "leave_status" => $request->leave_status,
                "reason" => $request->file_reason,
                "leave_count" => $days,
                "half_day" => $half_day,
                "user_id" => Auth::user()->id
            );
        }
       
        
            DB::beginTransaction();
            try {
                
                if($request->id == "new"){
                    DB::connection("intra_payroll")->table("tbl_leave_used")
                    ->insert($ins_data);
                }else{
                    DB::connection("intra_payroll")->table("tbl_leave_used")
                    ->where('id', $request->id)
                    ->update($ins_data);
                }
                
                    DB::commit();
                return json_encode("Filling Success");
            } catch (\Throwable $th) {
                DB::rollback();
                return json_encode($th->getMessage());
            }
       
    }
    public function get_leaves(Request $request, $start_date){
        $date_from = $request->month_view;
        $date_from_year = date("Y", strtotime($date_from));
        $date_from_month = date("m", strtotime($date_from));
        $date_from_day = date("d", strtotime($date_from));
        if($date_from_day > 1){
            $date_from = date("Y-m-01", strtotime($date_from . ' +1 month'));
        }
        $date_to = date("Y-m-t", strtotime($date_from));
        $data_days = array();
        $cur_day = $date_from;
    
        $leave_dates = array();
    
        $tbl_leave_used = DB::connection("intra_payroll")->table("tbl_leave_used")
            ->where("leave_status", "APPROVED")
            ->where("leave_year", $date_from_year)
            ->get();
    
        foreach($tbl_leave_used as $leave_used){
            $begin = new DateTime($leave_used->leave_date_from);
            $leave_to_date = date("Y-m-d", strtotime($leave_used->leave_date_to .' +1 day'));
    
            $emp_name = "";
            $tbl_employees = DB::connection("intra_payroll")->table("tbl_employee")
                ->select("id", "first_name", "last_name")
                ->where("is_active", 1)
                ->where("id", $leave_used->emp_id)
                ->first();
            if($tbl_employees){
                $emp_name = $tbl_employees->last_name . ', ' . $tbl_employees->first_name;
            }
    
            $tbl_leave_source = DB::connection("intra_payroll")->table("tbl_leave_credits")
                ->where("leave_id", $leave_used->leave_source_id)
                ->first();
    
            $tbl_leave_type = DB::connection("intra_payroll")->table("tbl_leave_types")
                ->select("leave_type")
                ->where("id", $tbl_leave_source->leave_id)
                ->first();
    
            $end = new DateTime($leave_to_date);
            $interval = new DateInterval('P1D'); // 1 day interval
            $daterange = new DatePeriod($begin, $interval, $end);
    
            $leave_detail = $tbl_leave_type->leave_type . ' - ' . $emp_name;
            
            foreach($daterange as $date_leave){
                $leave_dates[$date_leave->format("Y-m-d")][] = [
                    'title' => $leave_detail,
                    'start' => $date_leave->format("Y-m-d"),
                    'color' => "#159ca1",
                    'extendedProps' => [
                        'name' => $emp_name,
                        'type' => $tbl_leave_type->leave_type
                    ]
                ];
            }
        }
    
        // Ensure multiple events for the same date
        $data_days = [];
        foreach($leave_dates as $date => $events) {
            foreach($events as $event) {
                $data_days[] = $event;
            }
        }
    
        return response()->json($data_days);
    }  
}
