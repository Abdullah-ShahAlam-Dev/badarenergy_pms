<?php

namespace Modules\GatePass\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\GatePass\Entities\GatePassRequest;
use Modules\GatePass\Entities\GatePassItem;

class GatePassReportController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Gate Pass Reports';
    }

    public function index(Request $request)
    {
        abort_403(user()->permission('view_gate_pass_reports') == 'none');

        $this->startDate = $request->startDate ? Carbon::parse($request->startDate)->toDateString() : now()->startOfMonth()->toDateString();
        $this->endDate = $request->endDate ? Carbon::parse($request->endDate)->toDateString() : now()->endOfMonth()->toDateString();

        $query = GatePassRequest::with(['user', 'department', 'items'])
            ->whereBetween('request_date', [$this->startDate, $this->endDate]);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        $this->reports = $query->orderBy('request_date', 'desc')->get();

        if (request()->ajax()) {
            $html = view('gatepass::reports.ajax.index', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html]);
        }

        return view('gatepass::reports.index', $this->data);
    }
}
