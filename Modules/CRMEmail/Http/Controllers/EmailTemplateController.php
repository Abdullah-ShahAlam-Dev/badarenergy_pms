<?php

namespace Modules\CRMEmail\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Modules\CRMEmail\DataTables\EmailTemplateDataTable;
use Modules\CRMEmail\Entities\EmailMarketingTemplate;
use Modules\CRMEmail\Http\Requests\StoreEmailTemplate;
use Modules\CRMEmail\Http\Requests\UpdateEmailTemplate;
use Modules\CRMEmail\Services\TemplateVariableService;
use Illuminate\Http\Request;

class EmailTemplateController extends AccountBaseController
{
    private $variableService;

    public function __construct(TemplateVariableService $variableService)
    {
        parent::__construct();
        $this->pageTitle = 'Email Templates';
        $this->variableService = $variableService;

        $this->middleware(function ($request, $next) {
            $viewPermission = user()->permission('view_crm_email');
            abort_403(!in_array('admin', user_roles()) && $viewPermission == 'none');
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @param EmailTemplateDataTable $dataTable
     * @return mixed
     */
    public function index(EmailTemplateDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_crm_email');
        abort_403(!in_array('admin', user_roles()) && $viewPermission == 'none');

        return $dataTable->render('crmemail::templates.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $addPermission = user()->permission('add_crm_email');
        abort_403(!in_array('admin', user_roles()) && $addPermission == 'none');

        $this->variables = $this->variableService->getAvailableVariables();

        if (request()->ajax()) {
            $html = view('crmemail::templates.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'Create Template']);
        }

        return view('crmemail::templates.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreEmailTemplate $request
     * @return array
     */
    public function store(StoreEmailTemplate $request)
    {
        $addPermission = user()->permission('add_crm_email');
        abort_403(!in_array('admin', user_roles()) && $addPermission == 'none');

        $template = new EmailMarketingTemplate();
        $template->company_id = company()->id;
        $template->title = $request->title;
        $template->subject = $request->subject;
        $template->from_name = $request->from_name;
        $template->from_email = $request->from_email;
        $template->content = $request->content;
        $template->status = $request->status;
        $template->added_by = user()->id;
        $template->save();

        return Reply::successWithData('Email template created successfully!', [
            'redirectUrl' => route('crm-email-templates.index')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->template = EmailMarketingTemplate::findOrFail($id);

        $editPermission = user()->permission('edit_crm_email');
        abort_403(!in_array('admin', user_roles()) && !($editPermission == 'all' || ($editPermission == 'added' && $this->template->added_by == user()->id)));

        $this->variables = $this->variableService->getAvailableVariables();

        if (request()->ajax()) {
            $html = view('crmemail::templates.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'Edit Template']);
        }

        return view('crmemail::templates.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateEmailTemplate $request
     * @param int $id
     * @return array
     */
    public function update(UpdateEmailTemplate $request, $id)
    {
        $template = EmailMarketingTemplate::findOrFail($id);

        $editPermission = user()->permission('edit_crm_email');
        abort_403(!in_array('admin', user_roles()) && !($editPermission == 'all' || ($editPermission == 'added' && $template->added_by == user()->id)));

        $template->title = $request->title;
        $template->subject = $request->subject;
        $template->from_name = $request->from_name;
        $template->from_email = $request->from_email;
        $template->content = $request->content;
        $template->status = $request->status;
        $template->last_updated_by = user()->id;
        $template->save();

        return Reply::successWithData('Email template updated successfully!', [
            'redirectUrl' => route('crm-email-templates.index')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return array
     */
    public function destroy($id)
    {
        $template = EmailMarketingTemplate::findOrFail($id);

        $deletePermission = user()->permission('delete_crm_email');
        abort_403(!in_array('admin', user_roles()) && !($deletePermission == 'all' || ($deletePermission == 'added' && $template->added_by == user()->id)));

        $template->delete();

        return Reply::success('Email template deleted successfully!');
    }

    /**
     * Duplicate/clone the specified template.
     *
     * @param int $id
     * @return array
     */
    public function duplicate($id)
    {
        $template = EmailMarketingTemplate::findOrFail($id);

        $addPermission = user()->permission('add_crm_email');
        abort_403(!in_array('admin', user_roles()) && $addPermission == 'none');

        $newTemplate = new EmailMarketingTemplate();
        $newTemplate->company_id = company()->id;
        $newTemplate->title = $template->title . ' - Copy';
        $newTemplate->subject = $template->subject;
        $newTemplate->from_name = $template->from_name;
        $newTemplate->from_email = $template->from_email;
        $newTemplate->content = $template->content;
        $newTemplate->status = $template->status;
        $newTemplate->added_by = user()->id;
        $newTemplate->save();

        return Reply::success('Email template duplicated successfully!');
    }

    /**
     * Render HTML preview modal using sample data.
     *
     * @param int $id
     * @return array
     */
    public function preview($id)
    {
        $template = EmailMarketingTemplate::findOrFail($id);

        $viewPermission = user()->permission('view_crm_email');
        abort_403(!in_array('admin', user_roles()) && $viewPermission == 'none');

        $sampleData = [
            'name' => 'John Doe',
            'company' => 'Example Corp',
            'email' => 'johndoe@example.com',
            'city' => 'New York',
            'country' => 'United States',
            'designation' => 'Lead Manager',
        ];

        $this->renderedSubject = $this->variableService->render($template->subject, $sampleData);
        $this->renderedBody = $this->variableService->render($template->content, $sampleData);
        $this->template = $template;

        if (request()->ajax()) {
            return view('crmemail::templates.ajax.modal', $this->data)->render();
        }

        return view('crmemail::templates.ajax.modal', $this->data);
    }
}
