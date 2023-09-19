<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Device;
use App\Models\Clinic;
use App\Models\Diagnosis;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Dashboard according to role
     *
     * @param Request $request
     * @return void
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $roles = $user->roles;

        $startDate = $request->startDate ? Carbon::parse($request->startDate) : Carbon::parse('1970-01-01');
        $endDate = $request->endDate ? Carbon::parse($request->endDate) : Carbon::parse();

        if ($roles->isNotEmpty()) {
            $highestPriorityRole = $roles->sortBy('priority')->first(); // Retrieve the role with the lowest priority value
        
            // Do something with the highest priority role
            $roleName = $highestPriorityRole->name;
            $rolePriority = $highestPriorityRole->priority;

            switch ($roleName) {
                case 'patient':
                    return $this->userDashboard($roleName, $user, $startDate, $endDate);
                case 'doctor':
                    return $this->userDashboard($roleName, $user, $startDate, $endDate);
                case 'operator':
                    return $this->userDashboard($roleName, $user, $startDate, $endDate);
                case 'manager':
                    return $this->adminDashboard($roleName, $startDate, $endDate);
                case 'admin':
                    return $this->adminDashboard($roleName, $startDate, $endDate);
                case 'superadmin':
                    return $this->adminDashboard($roleName, $startDate, $endDate);
                default:
                    return apiResponse(false, trans('messages.dashboard.invalidrole'), (object)[], 403);
            }
        } else {
            apiResponse(false, trans('messages.dashboard.noroles'), (object)[], 403);
        }
    }

    /**
     * Admin dashboard for superadmin, admin, manager
     *
     * @param [type] $roleName
     * @param [type] $startDate
     * @param [type] $endDate
     * @return void
     */
    // public function adminDashboard($roleName, $startDate, $endDate)
    // {
    //     $dashboardData = [];
    //     $totalDeviceCount = Device::where('status', 1)->whereBetween('created_at', [$startDate, $endDate])->count();

    //     $totalClinicCount = Clinic::where('status', 1)->whereBetween('created_at', [$startDate, $endDate])->count();

    //     $diagnoses = Diagnosis::where('status', 1)->whereBetween('created_at', [$startDate, $endDate])->get();
    //     $totalDiagnosisCount = count($diagnoses);

    //     $totalDoctor = User::whereHas('roles', function ($query) {
    //                         $query->where('name', 'doctor');
    //                     })
    //                     ->whereBetween('created_at', [$startDate, $endDate])
    //                     ->count();
    //     $totalPatient = User::whereHas('roles', function ($query) {
    //                         $query->where('name', 'patient');
    //                     })
    //                     ->whereBetween('created_at', [$startDate, $endDate])
    //                     ->count();
    //     $totalOperator = User::whereHas('roles', function ($query) {
    //                         $query->where('name', 'operator');
    //                     })
    //                     ->whereBetween('created_at', [$startDate, $endDate])
    //                     ->count();
    //     $dashboardData = [
    //         'totalDevices' => $totalDeviceCount,
    //         'totalDiagnoses' => $totalDiagnosisCount,
    //         'totalDoctors' => $totalDoctor,
    //         'totalPatients' => $totalPatient,
    //         'totalOperators' => $totalOperator
    //     ];
    //     $roleName !== 'manager' ? $dashboardData['totalClinics'] = $totalClinicCount : null;
    //     // $diagnosesDataCount = $this->diagnosesPositiveNegativeCount($diagnoses);
    //     // $dashboardData = array_merge($dashboardData, $diagnosesDataCount);

    //     return apiResponse(true, $roleName . ' ' . trans('messages.dashboard.returned'), ['card' => $dashboardData]);
    // }

    public function adminDashboard($roleName, $startDate, $endDate)
    {
        $dashboardData = [];

        // Total Devices count
        $totalDeviceCount = Device::where('status', 1)->whereBetween('created_at', [$startDate, $endDate])->count();

        $devicesChartData = Device::whereBetween('created_at', [$startDate, $endDate])
                                    ->selectRaw('date(created_at) as date, count(*) as count')
                                    ->groupBy('date')
                                    ->get();

        // Total Clinics count (only if the role is not 'manager')
        $totalClinicCount = ($roleName !== 'manager')
            ? Clinic::where('status', 1)->whereBetween('created_at', [$startDate, $endDate])->count()
            : null;

        $clinicsChartData = Clinic::whereBetween('created_at', [$startDate, $endDate])
                                    ->selectRaw('date(created_at) as date, count(*) as count')
                                    ->groupBy('date')
                                    ->get();

        // Total Diagnoses count and grouped data for the chart
        $diagnoses = Diagnosis::where('status', 1)->whereBetween('created_at', [$startDate, $endDate])->get();
        $totalDiagnosisCount = count($diagnoses);
        $diagnosesChartData = $diagnoses->groupBy(function ($item) {
            return $item->created_at->format('d-m-Y');
        })->map(function ($groupedDiagnoses) {
            return $groupedDiagnoses->count();
        });

        // Total Doctors count and grouped data for the chart
        $totalDoctor = User::whereHas('roles', function ($query) {
            $query->where('name', 'doctor');
        })->whereBetween('created_at', [$startDate, $endDate])->count();
        $doctorsChartData = User::whereHas('roles', function ($query) {
            $query->where('name', 'doctor');
        })->whereBetween('created_at', [$startDate, $endDate])
        ->selectRaw('date(created_at) as date, count(*) as count')
        ->groupBy('date')
        ->get();

        // Total Patients count and grouped data for the chart
        $totalPatient = User::whereHas('roles', function ($query) {
            $query->where('name', 'patient');
        })->whereBetween('created_at', [$startDate, $endDate])->count();
        $patientsChartData = User::whereHas('roles', function ($query) {
            $query->where('name', 'patient');
        })->whereBetween('created_at', [$startDate, $endDate])
        ->selectRaw('date(created_at) as date, count(*) as count')
        ->groupBy('date')
        ->get();

        // Total Operators count and grouped data for the chart
        $totalOperator = User::whereHas('roles', function ($query) {
            $query->where('name', 'operator');
        })->whereBetween('created_at', [$startDate, $endDate])->count();
        $operatorsChartData = User::whereHas('roles', function ($query) {
            $query->where('name', 'operator');
        })->whereBetween('created_at', [$startDate, $endDate])
        ->selectRaw('date(created_at) as date, count(*) as count')
        ->groupBy('date')
        ->get();

        $dashboardData = [
            'totalDevices' => $totalDeviceCount,
            'totalDiagnoses' => $totalDiagnosisCount,
            'totalDoctors' => $totalDoctor,
            'totalPatients' => $totalPatient,
            'totalOperators' => $totalOperator,
        ];

        // Add totalClinics data only if the role is not 'manager'
        if ($totalClinicCount !== null) {
            $dashboardData['totalClinics'] = $totalClinicCount;
        }

        // Prepare data for the chart
        $chartData = [
            // 'clinics' => [
            //     'labels' => $clinicsChartData->pluck('date'),
            //     'data' => $clinicsChartData->pluck('count'),
            // ],
            'devices' => [
                'labels' => $devicesChartData->pluck('date'),
                'data' => $devicesChartData->pluck('count'),
            ],
            'doctors' => [
                'labels' => $doctorsChartData->pluck('date'),
                'data' => $doctorsChartData->pluck('count'),
            ],
            'patients' => [
                'labels' => $patientsChartData->pluck('date'),
                'data' => $patientsChartData->pluck('count'),
            ],
            'operators' => [
                'labels' => $operatorsChartData->pluck('date'),
                'data' => $operatorsChartData->pluck('count'),
            ],
            'diagnoses' => [
                'labels' => $diagnosesChartData->keys(),
                'data' => $diagnosesChartData->values(),
            ]
        ];
        if ($roleName !== 'manager') {
            $chartData['clinics'] = [
                'labels' => $clinicsChartData->pluck('date'),
                'data' => $clinicsChartData->pluck('count'),
            ];
        }

        return apiResponse(true, $roleName . ' ' . trans('messages.dashboard.returned'), ['card' => $dashboardData, 'chart' => $chartData]);
    }

    /**
     * Dashboard for only doctor, patient and operator
     *
     * @param [type] $roleName
     * @param [type] $user
     * @param [type] $startDate
     * @param [type] $endDate
     * @return void
     */
    public function userDashboard($roleName, $user, $startDate, $endDate)
    {
        $roleConditions = [
            'patient' => function ($query) use ($user) {
                $query->where('patient_id', $user->id);
            },
            'doctor' => function ($query) use ($user) {
                $query->where('doctor_id', $user->id);
            },
            'operator' => function ($query) use ($user) {
                $query->where('operator_id', $user->id);
            },
        ];
        $diagnoses = Diagnosis::where('status', 1)
                                ->when(isset($roleConditions[$roleName]), $roleConditions[$roleName])
                                ->whereBetween('created_at', [$startDate, $endDate])
                                ->get();
        $totalDiagnosisCount = count($diagnoses);
        $totalUniquePatients = Diagnosis::where('status', 1)
                                ->where(function ($query) use ($roleName, $user) {
                                    if ($roleName === 'doctor') {
                                        $query->where('doctor_id', $user->id);
                                    } elseif ($roleName === 'operator') {
                                        $query->where('operator_id', $user->id);
                                    }
                                })
                                ->whereBetween('created_at', [$startDate, $endDate])
                                ->select(DB::raw('COUNT(DISTINCT patient_id) as totalUniquePatients'))
                                ->value('totalUniquePatients');
        
        $diagnosesDataCount = $this->diagnosesPositiveNegativeCount($diagnoses);
        $dashboardData = [
            'totalDiagnoses' => $totalDiagnosisCount,
        ];
        $roleName !== 'patient' ? $dashboardData['totalPatients'] = $totalUniquePatients : null;
        $dashboardData = array_merge($dashboardData, $diagnosesDataCount);
        return apiResponse(true, $roleName . ' ' . trans('messages.dashboard.returned'), $dashboardData);
    }

    /**
     * Count total postitive and negative count of diagnosis
     *
     * @param [type] $diagnoses
     * @return void
     */
    public function diagnosesPositiveNegativeCount($diagnoses)
    {
        $positiveCount = 0;
        $negativeCount = 0;
        foreach ($diagnoses as $diagnosis) {
            $reports = json_decode($diagnosis['report']);
            if(count($reports)){
                foreach ($reports as $report) {
                    $referenceValue = $report->reference_value;
                    $result = $report->result;
                    // Extract the min and max values from the reference value range
                    $referenceRange = explode('-', $referenceValue);
                    $minValue = isset($referenceRange[0]) ? intval($referenceRange[0]) : 0;
                    $maxValue = isset($referenceRange[1]) ? intval($referenceRange[1]) : 0;
                    
                    // Compare the result with the reference range
                    if($minValue !== 0 && $maxValue !== 0){
                        if ($result >= $minValue && $result <= $maxValue) {
                            $positiveCount++;
                        } else {
                            $negativeCount++;
                        }
                    }
                }
            }
        }
        return [
            'totalPositiveCount' => $positiveCount,
            'totalNegativeCount' => $negativeCount
        ];
    }
}
