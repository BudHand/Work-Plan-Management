<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChartController extends Controller
{
    public function showChart_1(Request $request)
    {
        $selectedYear = $request->input('year');
        $selectedStatus = $request->input('status');
        $selectedName = $request->input('code_workplans');

        $vw_graphic_weight_monthly = DB::table('vw_graphic_weight_monthly')
            ->leftJoin('projects', 'vw_graphic_weight_monthly.id_projects', '=', 'projects.id');

        if ($selectedName) {
            $vw_graphic_weight_monthly->where('id_projects', $selectedName);
        }

        if ($selectedYear) {
            $vw_graphic_weight_monthly->where('year', $selectedYear);
        }

        if ($selectedStatus) {
            $vw_graphic_weight_monthly->where('status', $selectedStatus);
        }

        $data = $vw_graphic_weight_monthly->get();

        $chartData = [
            'categories' => ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'],
            'dataSets' => [
                [
                    'nama' => 'Planed',
                    'data' => [
                        $data->sum('plan_jan'),
                        $data->sum('plan_feb'),
                        $data->sum('plan_mar'),
                        $data->sum('plan_apr'),
                        $data->sum('plan_may'),
                        $data->sum('plan_jun'),
                        $data->sum('plan_jul'),
                        $data->sum('plan_aug'),
                        $data->sum('plan_sep'),
                        $data->sum('plan_oct'),
                        $data->sum('plan_nov'),
                        $data->sum('plan_dec'),
                    ],
                ],
                [
                    'nama' => 'Actual',
                    'data' => [
                        $data->sum('actual_jan'),
                        $data->sum('actual_feb'),
                        $data->sum('actual_mar'),
                        $data->sum('actual_apr'),
                        $data->sum('actual_may'),
                        $data->sum('actual_jun'),
                        $data->sum('actual_jul'),
                        $data->sum('actual_aug'),
                        $data->sum('actual_sep'),
                        $data->sum('actual_oct'),
                        $data->sum('actual_nov'),
                        $data->sum('actual_dec'),
                    ],
                ],
            ],
        ];

        return response()->json($chartData);
    }

    public function showChart_2(Request $request)
    {
        $selectedYear = $request->input('year');
        $selectedStatus = $request->input('status');
        $selectedName = $request->input('code_workplans');

        $vw_graphic_weight_cumulative = DB::table('vw_graphic_weight_cumulative')
            ->leftJoin('projects', 'vw_graphic_weight_cumulative.id_projects', '=', 'projects.id');

        if ($selectedName) {
            $vw_graphic_weight_cumulative->where('id_projects', $selectedName);
        }

        if ($selectedYear) {
            $vw_graphic_weight_cumulative->where('year', $selectedYear);
        }

        if ($selectedStatus) {
            $vw_graphic_weight_cumulative->where('status', $selectedStatus);
        }

        $data = $vw_graphic_weight_cumulative->get();

        $chartData = [
            'categories' => ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'],
            'dataSets' => [
                [
                    'nama' => 'Planed',
                    'data' => [
                        $data->sum('jan_plan_cumulative'),
                        $data->sum('feb_plan_cumulative'),
                        $data->sum('mar_plan_cumulative'),
                        $data->sum('apr_plan_cumulative'),
                        $data->sum('may_plan_cumulative'),
                        $data->sum('jun_plan_cumulative'),
                        $data->sum('jul_plan_cumulative'),
                        $data->sum('aug_plan_cumulative'),
                        $data->sum('sep_plan_cumulative'),
                        $data->sum('oct_plan_cumulative'),
                        $data->sum('nov_plan_cumulative'),
                        $data->sum('dec_plan_cumulative'),
                    ],
                ],
                [
                    'nama' => 'Actual',
                    'data' => [
                        $data->sum('jan_realization_cumulative'),
                        $data->sum('feb_realization_cumulative'),
                        $data->sum('mar_realization_cumulative'),
                        $data->sum('apr_realization_cumulative'),
                        $data->sum('may_realization_cumulative'),
                        $data->sum('jun_realization_cumulative'),
                        $data->sum('jul_realization_cumulative'),
                        $data->sum('aug_realization_cumulative'),
                        $data->sum('sep_realization_cumulative'),
                        $data->sum('oct_realization_cumulative'),
                        $data->sum('nov_realization_cumulative'),
                        $data->sum('dec_realization_cumulative'),
                    ],
                ],
            ],
        ];

        return response()->json($chartData);
    }
}