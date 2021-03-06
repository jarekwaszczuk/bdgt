<?php

namespace App\Http\Controllers;

use App\Reports\ReportFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Request;

class ReportController extends Controller
{
    /**
     * Show the default report to the user.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->show('spending');
    }

    /**
     * Show an individual report to the user.
     *
     * @param $type
     * @return \Illuminate\Http\Response
     */
    public function show($type)
    {
        $report = (object)[
            'name' => ReportFactory::generate($type)->name(),
            'url' => '/reports/ajax/' . $type,
        ];

        return view('report.show')->with('report', $report)->with('type', $type);
    }

    /**
     * Retrieve report data based on type
     * @param  string $type
     * @return \Illuminate\Http\Response
     */
    public function ajax($type)
    {
        return response()->json(
            ReportFactory::generate($type)
                           ->forDateRange(new Carbon(Request::get('startDate')), new Carbon(Request::get('endDate')))
        );
    }
}
