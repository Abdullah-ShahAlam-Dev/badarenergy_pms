<style>
    .provider-card {
        border: 1px solid #e8eef3;
        border-radius: 8px;
        padding: 16px;
        cursor: pointer;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }
    .provider-card:hover {
        border-color: #1d82f5;
        background-color: #f8fafc;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .provider-card.active {
        border-color: #1d82f5;
        background-color: #f0f7ff;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .provider-card .custom-control-input:checked ~ .provider-card {
        border-color: #1d82f5;
        background-color: #f0f7ff;
    }
    .provider-icon {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 20px;
    }
    .bg-meta-soft {
        background-color: #e7f3ff;
        color: #1877f2;
    }
    .bg-twilio-soft {
        background-color: #fce8e6;
        color: #f22f46;
    }
    .whatsapp-preview-container {
        background-color: #efeae2;
        background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');
        background-repeat: repeat;
        border-radius: 12px;
        padding: 18px;
        min-height: 180px;
        border: 1px solid #e8eef3;
    }
    .whatsapp-bubble {
        background-color: #ffffff;
        padding: 12px 14px;
        border-radius: 8px;
        box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
        max-width: 90%;
        position: relative;
        border-top-left-radius: 0 !important;
    }
    .whatsapp-bubble-tail {
        position: absolute;
        left: -8px;
        top: 0;
        width: 0;
        height: 0;
        border-top: 0px solid transparent;
        border-bottom: 10px solid transparent;
        border-right: 8px solid #fff;
    }
    .settings-section-card {
        background: #ffffff;
        border: 1px solid #e8eef3;
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px 0 rgba(0,0,0,0.02);
    }
    .settings-section-title {
        font-size: 15px;
        font-weight: 600;
        color: #1d2939;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
    }
    .settings-section-title i {
        color: #667085;
        font-size: 16px;
    }
    
    /* Layer tabs and navigation always on top to prevent overlapping click issues */
    .ntfcn-tab-content-right {
        margin-top: 0 !important;
        position: relative;
        z-index: 1;
    }
    #tabs, .s-b-n-header, .tabs, .nav-item {
        position: relative;
        z-index: 9999 !important;
    }
</style>

{{-- Left Column: Guided Form Configuration --}}
<div class="col-xl-8 col-lg-12 col-md-12 ntfcn-tab-content-left p-4" id="whatsapp-settings-section">
    
    {{-- Header Section --}}
    <div class="row mb-4">
        <div class="col-sm-12">
            <h3 class="f-18 font-weight-bold text-dark-grey mb-1">WhatsApp Notification Settings</h3>
            <p class="text-muted f-13">Send automatic WhatsApp messages to employees for task updates and overdue reminders.</p>
        </div>
    </div>

    {{-- Card 1: Enable & Provider Selection --}}
    <div class="settings-section-card">
        <div class="row">
            {{-- Enable Module --}}
            <div class="col-lg-12 mb-3">
                <div class="d-flex align-items-center">
                    <x-forms.checkbox :fieldLabel="'Enable WhatsApp Notifications'" fieldName="whatsapp_status"
                        fieldId="whatsapp_status" fieldValue="active" fieldRequired="true"
                        :checked="$whatsappSetting->status=='active'"
                        :popover="'Turn on automatic WhatsApp messaging for task notifications.'" />
                </div>
            </div>
        </div>

        {{-- Provider Selector Wrapper --}}
        <div class="whatsapp_details @if($whatsappSetting->status=='inactive') d-none @endif mt-3">
            <label class="f-14 text-dark-grey mb-2">
                WhatsApp Provider 
                <i class="fa fa-info-circle text-lightest" data-toggle="tooltip" data-original-title="Select your WhatsApp service provider. Meta Cloud API is official and recommended."></i>
            </label>
            
            <div class="row">
                {{-- Meta Cloud Radio Card --}}
                <div class="col-md-6 mb-3">
                    <label class="w-100 cursor-pointer mb-0">
                        <input type="radio" name="provider" value="meta_cloud" class="d-none provider-radio" @if($whatsappSetting->provider == 'meta_cloud') checked @endif>
                        <div class="provider-card d-flex align-items-center @if($whatsappSetting->provider == 'meta_cloud') active @endif" id="provider_card_meta">
                            <div class="provider-icon bg-meta-soft mr-3">
                                <i class="fab fa-facebook-f"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 f-14 text-dark-grey font-weight-bold">Meta Cloud API</h5>
                                <p class="mb-0 text-muted f-11">Official Cloud API (Recommended)</p>
                            </div>
                            @if($whatsappSetting->provider == 'meta_cloud')
                                <i class="fa fa-check-circle text-primary position-absolute" style="top: 12px; right: 12px;"></i>
                            @endif
                        </div>
                    </label>
                </div>

                {{-- Twilio WhatsApp Radio Card --}}
                <div class="col-md-6 mb-3">
                    <label class="w-100 cursor-pointer mb-0">
                        <input type="radio" name="provider" value="twilio" class="d-none provider-radio" @if($whatsappSetting->provider == 'twilio') checked @endif>
                        <div class="provider-card d-flex align-items-center @if($whatsappSetting->provider == 'twilio') active @endif" id="provider_card_twilio">
                            <div class="provider-icon bg-twilio-soft mr-3">
                                <i class="fas fa-comments"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 f-14 text-dark-grey font-weight-bold">Twilio WhatsApp</h5>
                                <p class="mb-0 text-muted f-11">Twilio Messaging API Gateway</p>
                            </div>
                            @if($whatsappSetting->provider == 'twilio')
                                <i class="fa fa-check-circle text-primary position-absolute" style="top: 12px; right: 12px;"></i>
                            @endif
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>

    {{-- Card 2: Dynamic Connection Setup --}}
    <div class="settings-section-card whatsapp_details @if($whatsappSetting->status=='inactive') d-none @endif">
        <div class="settings-section-title">
            <i class="fa fa-plug mr-2"></i> Connection Setup
        </div>

        {{-- Meta Cloud Fields --}}
        <div class="meta-fields" @if($whatsappSetting->provider != 'meta_cloud') style="display:none" @endif>
            <div class="row">
                <div class="col-lg-6 col-md-12">
                    <x-forms.text 
                        :fieldLabel="'Access Token'"
                        :fieldPlaceholder="'EAABsbCS1...'"
                        fieldName="credentials[access_token]"
                        fieldId="meta_access_token"
                        :fieldValue="$whatsappSetting->credentials['access_token'] ?? ''"
                        :fieldRequired="true"
                        :popover="'Access Token generated from your Meta for Developers Portal with whatsapp_business_messaging permission.'"
                        :fieldHelp="'Required to authenticate with Meta API (starts with EAAB)'" />
                </div>
                <div class="col-lg-6 col-md-12">
                    <x-forms.text 
                        :fieldLabel="'Phone Number ID'"
                        :fieldPlaceholder="'100234567890123'"
                        fieldName="credentials[phone_number_id]"
                        fieldId="meta_phone_number_id"
                        :fieldValue="$whatsappSetting->credentials['phone_number_id'] ?? ''"
                        :fieldRequired="true"
                        :popover="'Unique Phone Number ID assigned by Meta for your WhatsApp Business phone number.'"
                        :fieldHelp="'15-digit numeric ID found in WhatsApp Cloud API dashboard'" />
                </div>
                <div class="col-lg-12">
                    <x-forms.text 
                        :fieldLabel="'Business Account ID (Optional)'"
                        :fieldPlaceholder="'1029384756'"
                        fieldName="credentials[business_account_id]"
                        fieldId="meta_business_account_id"
                        :fieldValue="$whatsappSetting->credentials['business_account_id'] ?? ''"
                        :fieldRequired="false"
                        :popover="'Your Meta Business Account ID. Useful for account asset sync.'"
                        :fieldHelp="'Optional - Found under Business Settings in Meta Business Manager'" />
                </div>
            </div>
        </div>

        {{-- Twilio Fields --}}
        <div class="twilio-fields" @if($whatsappSetting->provider != 'twilio') style="display:none" @endif>
            <div class="row">
                <div class="col-lg-6 col-md-12">
                    <x-forms.text 
                        :fieldLabel="'Account SID'"
                        :fieldPlaceholder="'ACxxxxxxxx'"
                        fieldName="credentials[sid]"
                        fieldId="twilio_sid"
                        :fieldValue="$whatsappSetting->credentials['sid'] ?? ''"
                        :fieldRequired="true"
                        :popover="'Your unique Twilio Account Identifier found in your Twilio Console.'"
                        :fieldHelp="'Starts with AC followed by 32 characters'" />
                </div>
                <div class="col-lg-6 col-md-12">
                    <x-forms.text 
                        :fieldLabel="'Auth Token'"
                        :fieldPlaceholder="'your_auth_token'"
                        fieldName="credentials[auth_token]"
                        fieldId="twilio_auth_token"
                        :fieldValue="$whatsappSetting->credentials['auth_token'] ?? ''"
                        :fieldRequired="true"
                        :popover="'Your secret Twilio Authentication Token, available in your Twilio Console.'"
                        :fieldHelp="'Keep this token secure'" />
                </div>
                <div class="col-lg-12">
                    <x-forms.text 
                        :fieldLabel="'Sender Number'"
                        :fieldPlaceholder="'+14155238886'"
                        fieldName="credentials[sender]"
                        fieldId="twilio_sender"
                        :fieldValue="$whatsappSetting->credentials['sender'] ?? ''"
                        :fieldRequired="true"
                        :popover="'Your WhatsApp-enabled phone number registered with Twilio (or Twilio Sandbox number).'"
                        :fieldHelp="'Must be formatted with country code (e.g., +14155238886)'" />
                </div>
            </div>
        </div>

        {{-- Connection Testing & Status Badge --}}
        <div class="border-top-grey pt-3 mt-3 d-flex align-items-center justify-content-between">
            <div>
                <button type="button" id="test-whatsapp-connection" class="btn btn-outline-secondary btn-sm">
                    <i class="fa fa-plug mr-1"></i> Test Connection
                </button>
            </div>
            <div>
                @php
                    $credentials = $whatsappSetting->credentials ?? [];
                    $isConnected = false;
                    if ($whatsappSetting->status == 'active') {
                        if ($whatsappSetting->provider == 'meta_cloud') {
                            $isConnected = !empty($credentials['access_token']) && !empty($credentials['phone_number_id']);
                        } elseif ($whatsappSetting->provider == 'twilio') {
                            $isConnected = !empty($credentials['sid']) && !empty($credentials['auth_token']) && !empty($credentials['sender']);
                        }
                    }
                @endphp
                <span class="f-13 font-weight-bold" id="connection-status-badge" data-toggle="tooltip" data-original-title="Verifies your WhatsApp API credentials.">
                    @if($isConnected)
                        <span class="text-success"><i class="fa fa-circle mr-1"></i> Connected</span>
                    @else
                        <span class="text-danger"><i class="fa fa-circle mr-1"></i> Not Connected</span>
                    @endif
                </span>
            </div>
        </div>
    </div>

    {{-- Card 3: Automation Rules & Routing --}}
    <div class="settings-section-card whatsapp_details @if($whatsappSetting->status=='inactive') d-none @endif">
        <div class="settings-section-title">
            <i class="fa fa-cog mr-2"></i> Automation Rules & Recipients
        </div>

        <div class="row">
            {{-- Left Sub-section: Rules --}}
            <div class="col-md-6 mb-3 mb-md-0 border-right-grey">
                <label class="f-13 font-weight-bold text-dark-grey mb-2">
                    When should WhatsApp messages be sent?
                    <i class="fa fa-info-circle text-lightest" data-toggle="tooltip" data-original-title="Select events that trigger automatic WhatsApp messages."></i>
                </label>
                
                <div class="mt-2">
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="rule_overdue" name="template_active" value="1" @if($whatsappTemplate->is_active ?? true) checked @endif>
                        <label class="custom-control-label pt-1 cursor-pointer f-13 text-dark-grey" for="rule_overdue">When task becomes overdue</label>
                    </div>

                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="rule_assigned" name="credentials[automation_rules][]" value="task_assigned" @if(in_array('task_assigned', $whatsappSetting->credentials['automation_rules'] ?? [])) checked @endif>
                        <label class="custom-control-label pt-1 cursor-pointer f-13 text-dark-grey" for="rule_assigned">When task is assigned</label>
                    </div>

                    <div class="custom-control custom-checkbox mb-2" style="opacity: 0.65;">
                        <input type="checkbox" class="custom-control-input" id="rule_updated" disabled>
                        <label class="custom-control-label pt-1 cursor-not-allowed f-13 text-muted" for="rule_updated">
                            When task is updated 
                            <span class="badge badge-light border text-muted py-0.5 px-1 ml-1" style="font-size: 10px;">Future Scope</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Right Sub-section: Recipient Source --}}
            <div class="col-md-6 pl-md-4">
                <label class="f-13 font-weight-bold text-dark-grey mb-2">
                    Employee Recipient Source
                    <i class="fa fa-info-circle text-lightest" data-toggle="tooltip" data-original-title="System will send WhatsApp messages to the mobile number saved in the employee profile."></i>
                </label>

                <div class="mt-2">
                    <div class="custom-control custom-radio mb-2">
                        <input type="radio" id="recipient_source_mobile" name="recipient_source" class="custom-control-input" checked>
                        <label class="custom-control-label pt-1 cursor-pointer f-13 text-dark-grey" for="recipient_source_mobile">Use Employee Profile Mobile Number</label>
                    </div>

                    <div class="pl-4 border-left-grey ml-2 mt-2">
                        <div class="custom-control custom-radio mb-2">
                            <input type="radio" id="fallback_email" name="credentials[fallback_option]" value="email_fallback" class="custom-control-input" @if(($whatsappSetting->credentials['fallback_option'] ?? 'email_fallback') == 'email_fallback') checked @endif>
                            <label class="custom-control-label pt-1 cursor-pointer f-13 text-dark-grey" for="fallback_email">Send email fallback</label>
                        </div>
                        <div class="custom-control custom-radio mb-1">
                            <input type="radio" id="fallback_skip" name="credentials[fallback_option]" value="skip" class="custom-control-input" @if(($whatsappSetting->credentials['fallback_option'] ?? 'email_fallback') == 'skip') checked @endif>
                            <label class="custom-control-label pt-1 cursor-pointer f-13 text-dark-grey" for="fallback_skip">Skip if mobile number is missing</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Card 4: Template Mapping & Variables --}}
    <div class="settings-section-card whatsapp_details @if($whatsappSetting->status=='inactive') d-none @endif">
        <div class="settings-section-title">
            <i class="fa fa-file-alt mr-2"></i> Message Template Setup
        </div>

        <div class="row">
            {{-- Template Name --}}
            <div class="col-md-6 mb-3">
                <x-forms.select fieldId="template_id" :fieldLabel="'Template Name'"
                    fieldName="template_id" :popover="'Pre-approved WhatsApp template from Meta Business Manager.'">
                    <option value="task_overdue_alert" @if(($whatsappTemplate->template_id ?? 'task_overdue_alert') == 'task_overdue_alert') selected @endif>
                        task_overdue_alert (Alert Template)
                    </option>
                </x-forms.select>
            </div>

            {{-- Language --}}
            <div class="col-md-6 mb-3">
                <x-forms.select fieldId="language_code" :fieldLabel="'Language'"
                    fieldName="language_code" :popover="'Select the language code of the pre-approved template.'">
                    <option value="en" @if(($whatsappTemplate->language_code ?? 'en') == 'en') selected @endif>English (en)</option>
                    <option value="ur" @if(($whatsappTemplate->language_code ?? 'en') == 'ur') selected @endif>Urdu (ur)</option>
                    <option value="es" @if(($whatsappTemplate->language_code ?? 'en') == 'es') selected @endif>Spanish (es)</option>
                    <option value="ar" @if(($whatsappTemplate->language_code ?? 'en') == 'ar') selected @endif>Arabic (ar)</option>
                    <option value="fr" @if(($whatsappTemplate->language_code ?? 'en') == 'fr') selected @endif>French (fr)</option>
                    <option value="de" @if(($whatsappTemplate->language_code ?? 'en') == 'de') selected @endif>German (de)</option>
                    <option value="hi" @if(($whatsappTemplate->language_code ?? 'en') == 'hi') selected @endif>Hindi (hi)</option>
                </x-forms.select>
            </div>

            {{-- Hidden mapping collector --}}
            <input type="hidden" name="parameter_mappings" id="parameter_mappings_hidden" value="">

            {{-- Variables mapping --}}
            <div class="col-12 mt-3">
                <label class="f-13 font-weight-bold text-dark-grey mb-2">
                    Template Variables Mapping
                    <i class="fa fa-info-circle text-lightest" data-toggle="tooltip" data-original-title="These values will be automatically inserted into your WhatsApp message."></i>
                </label>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm f-13">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 35%">WhatsApp Variable</th>
                                <th>Maps To</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $mappings = $whatsappTemplate->parameter_mappings ?? ['{employee_name}', '{task_name}', '{due_date}', '{project_name}'];
                            @endphp
                            <tr>
                                <td class="align-middle font-weight-bold pl-3"><code>@{{1}}</code></td>
                                <td>
                                    <select class="form-control height-35 f-13 variable-mapping-select" id="mapping_1">
                                        <option value="{employee_name}" @if(($mappings[0] ?? '') == '{employee_name}') selected @endif>Employee Name</option>
                                        <option value="{task_name}" @if(($mappings[0] ?? '') == '{task_name}') selected @endif>Task Name</option>
                                        <option value="{due_date}" @if(($mappings[0] ?? '') == '{due_date}') selected @endif>Due Date</option>
                                        <option value="{project_name}" @if(($mappings[0] ?? '') == '{project_name}') selected @endif>Project Name</option>
                                        <option value="{company_name}" @if(($mappings[0] ?? '') == '{company_name}') selected @endif>Company Name</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="align-middle font-weight-bold pl-3"><code>@{{2}}</code></td>
                                <td>
                                    <select class="form-control height-35 f-13 variable-mapping-select" id="mapping_2">
                                        <option value="{employee_name}" @if(($mappings[1] ?? '') == '{employee_name}') selected @endif>Employee Name</option>
                                        <option value="{task_name}" @if(($mappings[1] ?? '') == '{task_name}') selected @endif>Task Name</option>
                                        <option value="{due_date}" @if(($mappings[1] ?? '') == '{due_date}') selected @endif>Due Date</option>
                                        <option value="{project_name}" @if(($mappings[1] ?? '') == '{project_name}') selected @endif>Project Name</option>
                                        <option value="{company_name}" @if(($mappings[1] ?? '') == '{company_name}') selected @endif>Company Name</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="align-middle font-weight-bold pl-3"><code>@{{3}}</code></td>
                                <td>
                                    <select class="form-control height-35 f-13 variable-mapping-select" id="mapping_3">
                                        <option value="{employee_name}" @if(($mappings[2] ?? '') == '{employee_name}') selected @endif>Employee Name</option>
                                        <option value="{task_name}" @if(($mappings[2] ?? '') == '{task_name}') selected @endif>Task Name</option>
                                        <option value="{due_date}" @if(($mappings[2] ?? '') == '{due_date}') selected @endif>Due Date</option>
                                        <option value="{project_name}" @if(($mappings[2] ?? '') == '{project_name}') selected @endif>Project Name</option>
                                        <option value="{company_name}" @if(($mappings[2] ?? '') == '{company_name}') selected @endif>Company Name</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="align-middle font-weight-bold pl-3"><code>@{{4}}</code></td>
                                <td>
                                    <select class="form-control height-35 f-13 variable-mapping-select" id="mapping_4">
                                        <option value="{employee_name}" @if(($mappings[3] ?? '') == '{employee_name}') selected @endif>Employee Name</option>
                                        <option value="{task_name}" @if(($mappings[3] ?? '') == '{task_name}') selected @endif>Task Name</option>
                                        <option value="{due_date}" @if(($mappings[3] ?? '') == '{due_date}') selected @endif>Due Date</option>
                                        <option value="{project_name}" @if(($mappings[3] ?? '') == '{project_name}') selected @endif>Project Name</option>
                                        <option value="{company_name}" @if(($mappings[3] ?? '') == '{company_name}') selected @endif>Company Name</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Right Column: Live Preview & Delivery Logs --}}
<div class="col-xl-4 col-lg-12 col-md-12 ntfcn-tab-content-right border-left-grey p-4">
    
    {{-- Section: Live Preview --}}
    <h4 class="f-15 text-capitalize f-w-600 text-dark-grey mb-3">
        <i class="fab fa-whatsapp text-success mr-1"></i> Live Preview Panel
    </h4>
    
    <div class="whatsapp-preview-container mb-3 d-flex align-items-start justify-content-start">
        <div class="whatsapp-bubble w-100">
            <div class="whatsapp-bubble-tail"></div>
            <div class="whatsapp-bubble-body text-dark f-13" id="whatsapp-live-preview-text" style="white-space: pre-wrap; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                {{-- Live updated by JS --}}
            </div>
            <div class="text-right text-muted f-10 mt-2">
                <span id="whatsapp-preview-time">12:00 PM</span>
                <span class="text-success ml-1" style="font-size: 11px;"><i class="fa fa-check-double"></i></span>
            </div>
        </div>
    </div>
    
    <div class="mb-4">
        <button type="button" id="refresh-preview-btn" class="btn btn-light btn-sm btn-block border" data-toggle="tooltip" data-original-title="Preview shows final message format before sending.">
            <i class="fa fa-sync mr-1"></i> Refresh Preview
        </button>
    </div>

    {{-- Section: Delivery Logs --}}
    <h4 class="f-15 text-capitalize f-w-600 text-dark-grey mt-4 mb-3">
        <i class="fa fa-history mr-1"></i> Delivery Logs
    </h4>
    
    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-sm table-hover border f-12 mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Recipient</th>
                    <th>Event</th>
                    <th>Status</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($whatsappLogs as $log)
                    <tr>
                        <td class="font-weight-medium text-dark-grey" title="{{ $log->recipient }}">{{ Str::limit($log->recipient, 12) }}</td>
                        <td class="text-muted" title="{{ $log->event_name }}">{{ Str::limit(ucfirst(str_replace('_', ' ', $log->event_name)), 15) }}</td>
                        <td>
                            @if($log->status == 'sent')
                                <span class="badge badge-success text-success bg-light border-success py-1" style="border: 1px solid #c3e6cb;"><i class="fa fa-circle mr-1 f-8"></i> Sent</span>
                            @elseif($log->status == 'pending')
                                <span class="badge badge-warning text-warning bg-light border-warning py-1" style="border: 1px solid #ffeeba;"><i class="fa fa-circle mr-1 f-8"></i> Pending</span>
                            @else
                                <span class="badge badge-danger text-danger bg-light border-danger py-1" style="border: 1px solid #f5c6cb;"><i class="fa fa-circle mr-1 f-8"></i> Failed</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $log->created_at ? $log->created_at->diffForHumans() : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4 bg-light">No delivery logs yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Sticky Settings Action Footer --}}
<div class="w-100 border-top-grey set-btns">
    <x-setting-form-actions>
        <x-forms.button-primary id="save-whatsapp-form" class="mr-3" icon="check">
            @lang('app.save')
        </x-forms.button-primary>

        <x-forms.button-secondary id="send-whatsapp-test" icon="paper-plane" data-toggle="tooltip" data-original-title="Sends a template-mapped test message only to the admin phone number.">
            Send Test Message
        </x-forms.button-secondary>
    </x-setting-form-actions>
</div>

<script>
    $(document).ready(function () {
        
        // Dynamic compiler for variable mappings JSON string
        function compileParameterMappings() {
            var mappings = [];
            mappings.push($('#mapping_1').val());
            mappings.push($('#mapping_2').val());
            mappings.push($('#mapping_3').val());
            mappings.push($('#mapping_4').val());
            $('#parameter_mappings_hidden').val(JSON.stringify(mappings));
        }

        // Live preview generation mapping values
        function updateLivePreview() {
            var m1 = $('#mapping_1').val();
            var m2 = $('#mapping_2').val();
            var m3 = $('#mapping_3').val();
            var m4 = $('#mapping_4').val();

            var sampleVals = {
                '{employee_name}': 'Ali',
                '{task_name}': 'Landing Page Design',
                '{due_date}': '22 May 2026',
                '{project_name}': 'Website Redesign',
                '{company_name}': 'Acme Corp'
            };

            var val1 = sampleVals[m1] || 'Ali';
            var val2 = sampleVals[m2] || 'Landing Page Design';
            var val3 = sampleVals[m3] || '22 May 2026';
            var val4 = sampleVals[m4] || 'Website Redesign';

            var text = "Hello " + val1 + ",\n\nYour task \"" + val2 + "\" is now overdue.\nDue Date: " + val3;
            if (m4 && m4 !== '') {
                text += "\nProject: " + val4;
            }

            $('#whatsapp-live-preview-text').text(text);
            
            // Set current time in WhatsApp bubble
            var now = new Date();
            var timeString = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            $('#whatsapp-preview-time').text(timeString);
        }

        // Update parameters & preview on mapping change
        $('.variable-mapping-select').on('change', function() {
            compileParameterMappings();
            updateLivePreview();
        });

        // Initialize variables mappings & preview on load
        compileParameterMappings();
        updateLivePreview();

        // Refresh Preview Button Click
        $('#refresh-preview-btn').on('click', function(e) {
            e.preventDefault();
            updateLivePreview();
            
            // Subtle flash animation on preview
            $('.whatsapp-bubble').css('opacity', 0.5);
            setTimeout(function() {
                $('.whatsapp-bubble').css('opacity', 1);
            }, 150);
        });

        // Toggle details visibility on Status checkbox change
        $(document).on('change', '#whatsapp_status', function () {
            if ($(this).is(':checked')) {
                $('.whatsapp_details').removeClass('d-none');
            } else {
                $('.whatsapp_details').addClass('d-none');
            }
        });

        // Provider Radio Card selector functionality
        $(document).on('change', '.provider-radio', function () {
            var val = $(this).val();
            $('.provider-card').removeClass('active');
            $('.provider-card').find('.fa-check-circle').remove();

            if (val === 'meta_cloud') {
                $('#provider_card_meta').addClass('active').append('<i class="fa fa-check-circle text-primary position-absolute" style="top: 12px; right: 12px;"></i>');
                $('.meta-fields').show();
                $('.twilio-fields').hide();
            } else {
                $('#provider_card_twilio').addClass('active').append('<i class="fa fa-check-circle text-primary position-absolute" style="top: 12px; right: 12px;"></i>');
                $('.meta-fields').hide();
                $('.twilio-fields').show();
            }
        });

        // Save Form settings action
        $('body').on('click', '#save-whatsapp-form', function () {
            compileParameterMappings();
            var url = "{{ route('whatsapp-settings.update', $whatsappSetting->id) }}";

            $.easyAjax({
                url: url,
                type: "POST",
                container: "#editSettings",
                blockUI: true,
                data: $('#editSettings').serialize(),
                success: function (response) {
                    if (response.status === 'success') {
                        window.location.reload();
                    }
                }
            });
        });

        // Test Credentials connection
        $('body').on('click', '#test-whatsapp-connection', function () {
            var url = "{{ route('whatsapp-settings.test-connection') }}";
            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Testing...');

            $.easyAjax({
                url: url,
                type: "GET",
                success: function (response) {
                    $btn.prop('disabled', false).html('<i class="fa fa-plug mr-1"></i> Test Connection');
                    if (response.status === 'success') {
                        $('#connection-status-badge').html('<span class="text-success"><i class="fa fa-circle mr-1"></i> Connected</span>');
                    } else {
                        $('#connection-status-badge').html('<span class="text-danger"><i class="fa fa-circle mr-1"></i> Not Connected</span>');
                    }
                },
                error: function () {
                    $btn.prop('disabled', false).html('<i class="fa fa-plug mr-1"></i> Test Connection');
                    $('#connection-status-badge').html('<span class="text-danger"><i class="fa fa-circle mr-1"></i> Not Connected</span>');
                }
            });
        });

        // Send Test Message action
        $('body').on('click', '#send-whatsapp-test', function () {
            var url = "{{ route('whatsapp-settings.send-test') }}";
            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Sending...');

            $.easyAjax({
                url: url,
                type: "GET",
                success: function () {
                    $btn.prop('disabled', false).html('<i class="fa fa-paper-plane mr-1"></i> Send Test Message');
                },
                error: function () {
                    $btn.prop('disabled', false).html('<i class="fa fa-paper-plane mr-1"></i> Send Test Message');
                }
            });
        });

        init('#whatsapp-settings-section');
    });
</script>
