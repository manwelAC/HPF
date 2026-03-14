<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use Carbon\Carbon;

class dashboardController extends Controller
{
    public function dashboard()
    {
        $tbl_employee = count(DB::connection("intra_payroll")->table("tbl_employee")->where("is_active", 1)->get());
        $department = count(DB::connection("intra_payroll")->table("tbl_employee")->select("department")->where("is_active", 1)->groupBy('department')->get());

        $files = count(DB::connection("intra_payroll")->table("tbl_file")->get());
        $loans = count(DB::connection("intra_payroll")->table("tbl_loan_file")->where("is_done", 0)->get());
        $branches = count(DB::connection("intra_payroll")->table("tbl_employee")->select("branch_id")->where("is_active", 1)->groupBy('branch_id')->get());
        $leave_total = 0;
        $payroll_processing = 0;
        $payroll_done = 0;
        $logs = 0;
        $todayBirthdays = collect();
        $upcomingBirthdays = collect();

        if (Auth::user()->access["dashboard"]['user_type'] == "employee") {
            $leave_count = DB::connection("intra_payroll")->table("tbl_leave_used")->where("emp_id", Auth::user()->company["linked_employee"]["id"])->where("leave_status", "APPROVED")->where("leave_year", date("Y"))->sum("leave_count");
            $leave_total = DB::connection("intra_payroll")->table("tbl_leave_credits")->where("emp_id", Auth::user()->company["linked_employee"]["id"])->where("year_given", date("Y"))->sum("leave_count");
            $payroll_processing = count(DB::connection("intra_payroll")->table("tbl_payroll")->where("employee", "LIKE", "%|" . Auth::user()->company["linked_employee"]["id"] . "|%")->where("payroll_status", "!=", "CLOSED")->get());
            $payroll_done = count(DB::connection("intra_payroll")->table("tbl_payroll")->where("employee", "LIKE", "%|" . Auth::user()->company["linked_employee"]["id"] . "|%")->where("payroll_status", "CLOSED")->get());
            $logs = count(DB::connection("intra_payroll")->table("tbl_raw_logs")->where("biometric_id", Auth::user()->company["linked_employee"]["bio_id"])->where("logs", "LIKE", date("Y-m-d") . "%")->get());
        } else {
            $leave_count = count(DB::connection("intra_payroll")->table("tbl_leave_used")->whereRaw("'" . date("Y-m-d") . "' BETWEEN leave_date_from and leave_date_to and leave_status = 'APPROVED'")->get());
        }

        $today = Carbon::today();

        $allEmployees = DB::connection("intra_payroll")
            ->table("tbl_employee")
            ->select('id', 'first_name', 'middle_name', 'last_name', 'ext_name', 'date_of_birth', 'profile_picture', 'department')
            ->where('is_active', 1)
            ->whereNotNull('date_of_birth')
            ->get();

        $todayBirthdays    = collect();
        $upcomingBirthdays = collect();

        foreach ($allEmployees as $emp) {
            try {
                $dob = Carbon::createFromFormat('Y-m-d', $emp->date_of_birth);
            } catch (\Exception $e) {
                try {
                    $dob = Carbon::createFromFormat('m/d/Y', $emp->date_of_birth);
                } catch (\Exception $e2) {
                    continue;
                }
            }

            try {
                $birthdayThisYear = Carbon::create($today->year, $dob->month, $dob->day);
            } catch (\Exception $e) {
                $birthdayThisYear = Carbon::create($today->year, 3, 1);
            }

            $diffDays = $today->diffInDays($birthdayThisYear, false);

            if ((int)$diffDays === 0) {
                $emp->days_until = 0;
                $emp->full_name  = $this->formatEmployeeName($emp);
                $emp->age        = $dob->diffInYears($today);
                $todayBirthdays->push($emp);
            } elseif ($diffDays > 0 && $diffDays <= 7) {
                $emp->days_until = (int)$diffDays;
                $emp->full_name  = $this->formatEmployeeName($emp);
                $emp->age        = $dob->diffInYears($today) + 1;
                $upcomingBirthdays->push($emp);
            }
        }

        $upcomingBirthdays = $upcomingBirthdays->sortBy('days_until')->values();

        // Get Anniversary Data
        $allEmployeesForAnniversary = DB::connection("intra_payroll")
            ->table("tbl_employee")
            ->select('id', 'first_name', 'middle_name', 'last_name', 'ext_name', 'start_date', 'profile_picture', 'department')
            ->whereNotNull('start_date')
            ->where('start_date', '!=', '')
            ->get();

        $todayAnniversaries = collect();
        $upcomingAnniversaries = collect();

        foreach ($allEmployeesForAnniversary as $emp) {
            try {
                $startDate = Carbon::parse($emp->start_date);
            } catch (\Exception $e) {
                continue;
            }

            // Only show if 1 or more years
            $yearsInCompany = $startDate->diffInYears($today);
            if ($yearsInCompany < 1) {
                continue;
            }

            // Calculate anniversary date this year
            try {
                $anniversaryThisYear = Carbon::create($today->year, $startDate->month, $startDate->day);
            } catch (\Exception $e) {
                $anniversaryThisYear = Carbon::create($today->year, 1, 1);
            }

            // Check if anniversary is today or upcoming within 7 days
            $diffDays = $today->diffInDays($anniversaryThisYear);
            
            // If difference is 0, anniversary is today
            if ((int)$diffDays === 0) {
                $emp->days_until = 0;
                $emp->full_name = $this->formatEmployeeName($emp);
                $emp->years_service = $yearsInCompany;
                $todayAnniversaries->push($emp);
            } 
            // If anniversary hasn't happened yet but is within 7 days
            elseif ($diffDays > 0 && $diffDays <= 7) {
                $emp->days_until = (int)$diffDays;
                $emp->full_name = $this->formatEmployeeName($emp);
                $emp->years_service = $yearsInCompany;
                $upcomingAnniversaries->push($emp);
            }
            // Check if anniversary was in the past 7 days (previous year) - also show upcoming
            elseif ($diffDays < 0 && $diffDays >= -7) {
                // Anniversary already passed this year
                continue;
            }
        }

        $upcomingAnniversaries = $upcomingAnniversaries->sortBy('days_until')->values();

        // Get Regularization Data - Probationary employees reaching 1 month
        $regularizationEmployees = collect();
        $allEmployeesForRegularization = DB::connection("intra_payroll")
            ->table("tbl_employee")
            ->select('id', 'first_name', 'middle_name', 'last_name', 'ext_name', 'start_date', 'employment_status', 'profile_picture', 'department')
            ->where('employment_status', 'Probationary')
            ->whereNotNull('start_date')
            ->where('start_date', '!=', '')
            ->where('is_active', 1)
            ->get();

        foreach ($allEmployeesForRegularization as $emp) {
            try {
                $startDate = Carbon::parse($emp->start_date);
            } catch (\Exception $e) {
                continue;
            }

            // Check if exactly 1 month has passed
            $oneMonthAgo = $today->copy()->subMonth();
            $oneMonthFromNow = $today->copy()->addMonth();

            // If start_date was between 1 month ago and today, or if 1 month from start date is today/soon
            if ($startDate->copy()->addMonth()->format('Y-m-d') === $today->format('Y-m-d')) {
                // Exactly 1 month today
                $emp->full_name = $this->formatEmployeeName($emp);
                $emp->days_for_regularization = 0;
                $regularizationEmployees->push($emp);
            } elseif ($startDate->copy()->addMonth()->isFuture() && $startDate->copy()->addMonth()->diffInDays($today) <= 7 && $startDate->copy()->addMonth()->diffInDays($today) >= 0) {
                // Within next 7 days
                $emp->full_name = $this->formatEmployeeName($emp);
                $emp->days_for_regularization = $today->diffInDays($startDate->copy()->addMonth());
                $regularizationEmployees->push($emp);
            }
        }

        $regularizationEmployees = $regularizationEmployees->sortBy('days_for_regularization')->values();

        return view("dashboard.index")
            ->with("tbl_employee", $tbl_employee)
            ->with("department", $department)
            ->with("leave_count", $leave_count)
            ->with("leave_total", $leave_total)
            ->with("payroll_done", $payroll_done)
            ->with("payroll_processing", $payroll_processing)
            ->with("logs", $logs)
            ->with("files", $files)
            ->with("loans", $loans)
            ->with("branches", $branches)
            ->with("todayBirthdays", $todayBirthdays)
            ->with("upcomingBirthdays", $upcomingBirthdays)
            ->with("todayAnniversaries", $todayAnniversaries)
            ->with("upcomingAnniversaries", $upcomingAnniversaries)
            ->with("regularizationEmployees", $regularizationEmployees);
    }

    private function formatEmployeeName($emp): string
    {
        $first  = trim($emp->first_name ?? '');
        $middle = trim($emp->middle_name ?? '');
        $last   = trim($emp->last_name ?? '');
        $ext    = trim($emp->ext_name ?? '');

        $firstPart = $middle !== '' ? "{$first} {$middle}" : $first;
        $fullName  = "{$last}, {$firstPart}";

        if ($ext !== '') {
            $fullName .= " {$ext}";
        }

        return $fullName;
    }

    public function branch_per_emp()
    {
        $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")
            ->select("tbl_branch.branch as name", DB::raw("COUNT(tbl_employee.id) as y"))
            ->join("tbl_branch", "tbl_branch.id", "=", "branch_id")
            ->where("tbl_employee.is_active", 1)
            ->groupBy("branch_id")
            ->get();

        return json_encode($tbl_employee);
    }

    public function count_mwe()
    {
        $tbl_employee = DB::connection("intra_payroll")->table("tbl_employee")
            ->select(DB::raw("IF(is_mwe = 1, 'MWE', 'NON-MWE') as name"), DB::raw("COUNT(tbl_employee.id) as y"))
            ->where("tbl_employee.is_active", 1)
            ->groupBy("is_mwe")
            ->get();

        return json_encode($tbl_employee);
    }

    public function mark_employee_regular(Request $request)
    {
        try {
            DB::connection("intra_payroll")->table("tbl_employee")
                ->where("id", $request->emp_id)
                ->update([
                    "employment_status" => "Regular"
                ]);

            return json_encode("success");
        } catch (\Throwable $th) {
            return json_encode($th->getMessage());
        }
    }
}