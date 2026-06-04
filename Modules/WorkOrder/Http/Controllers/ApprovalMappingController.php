<?php

namespace Modules\WorkOrder\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\WorkOrder\Entities\ApprovalMapping;

class ApprovalMappingController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('workorder::modules.workOrder.approvalMappings');
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('admin', user_roles()) && user()->permission('manage_approval_mappings') == 'none');
            return $next($request);
        });
    }

    public function index()
    {
        $this->mappings  = ApprovalMapping::with(['creator', 'approver'])
            ->where('company_id', company()->id)
            ->orderBy('created_at', 'desc')
            ->get();
        $this->employees = User::allEmployees();

        if (request()->ajax()) {
            $html = view('workorder::approval-mappings.ajax.index', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html]);
        }

        return view('workorder::approval-mappings.index', $this->data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'creator_id'  => 'required|exists:users,id',
            'approver_id' => 'required|exists:users,id|different:creator_id',
        ]);

        ApprovalMapping::updateOrCreate(
            ['company_id' => company()->id, 'creator_id' => $request->creator_id],
            [
                'approver_id'     => $request->approver_id,
                'is_active'       => 1,
                'added_by'        => user()->id,
                'last_updated_by' => user()->id,
            ]
        );

        return Reply::successWithData(__('messages.recordSaved'), [
            'redirectUrl' => route('approval-mappings.index'),
        ]);
    }

    public function destroy($id)
    {
        $mapping = ApprovalMapping::where('company_id', company()->id)->findOrFail($id);
        $mapping->delete();
        return Reply::success(__('messages.recordDeleted'));
    }
}
