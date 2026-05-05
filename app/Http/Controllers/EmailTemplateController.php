<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailTemplateController extends AccountBaseController
{
    public function store(Request $request)
    {
        $request->validate([
            'event_name' => 'required',
            'subject' => 'required',
            'content' => 'required'
        ]);

        \App\Models\EmailTemplate::create([
            'event_name' => $request->event_name,
            'subject' => $request->subject,
            'content' => trim_editor($request->content),
            'is_active' => true
        ]);

        return \App\Helper\Reply::success('Template created successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'subject' => 'required',
            'content' => 'required'
        ]);

        $template = \App\Models\EmailTemplate::findOrFail($id);
        $template->subject = $request->subject;
        $template->content = trim_editor($request->content);
        $template->save();

        return \App\Helper\Reply::success('Template updated successfully.');
    }
}
