<div class="row">
    <div class="col-sm-12">
        <x-form id="save-segment-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    Segment Details
                </h4>

                <div class="row p-20">
                    <!-- Left Column: Fields -->
                    <div class="col-md-9 border-right-grey">
                        <div class="row">
                            <!-- Segment Name -->
                            <div class="col-md-12">
                                <x-forms.text fieldId="name" fieldLabel="Segment Name" fieldName="name"
                                    fieldRequired="true" fieldPlaceholder="e.g. Inactive Clients in USA"></x-forms.text>
                            </div>

                            <!-- Target Sources Checkboxes -->
                            <div class="col-md-12 mt-3 mb-4">
                                <x-forms.label fieldId="sources" fieldLabel="Target Sources" fieldRequired="true"></x-forms.label>
                                <div class="d-flex flex-wrap mt-1">
                                    <div class="custom-control custom-checkbox mr-4">
                                        <input type="checkbox" class="custom-control-input source-checkbox" id="source-clients" name="sources[]" value="clients">
                                        <label class="custom-control-label f-14 cursor-pointer" for="source-clients">Clients</label>
                                    </div>
                                    <div class="custom-control custom-checkbox mr-4">
                                        <input type="checkbox" class="custom-control-input source-checkbox" id="source-leads" name="sources[]" value="leads">
                                        <label class="custom-control-label f-14 cursor-pointer" for="source-leads">Leads</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input source-checkbox" id="source-contacts" name="sources[]" value="contacts">
                                        <label class="custom-control-label f-14 cursor-pointer" for="source-contacts">Client Contacts</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 1. CLIENT FILTERS PANEL -->
                        <div class="card border-grey mb-4 bg-light filter-source-card" id="client-filters-card" style="display: none;">
                            <div class="card-header bg-white border-bottom-grey font-weight-bold f-14 py-2 px-3">
                                <i class="fa fa-user mr-1 text-info"></i> Client Filters
                            </div>
                            <div class="card-body p-3">
                                <div class="row">
                                    <!-- Status -->
                                    <div class="col-md-4">
                                        <div class="form-group c-inv-select">
                                            <x-forms.label fieldId="client-status" fieldLabel="Account Status"></x-forms.label>
                                            <select class="form-control select-picker" name="criteria[clients][status][]" id="client-status" multiple data-actions-box="true" data-container="body">
                                                <option value="active">Active</option>
                                                <option value="deactive">Deactive</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Category -->
                                    <div class="col-md-4">
                                        <div class="form-group c-inv-select">
                                            <x-forms.label fieldId="client-category" fieldLabel="Client Category"></x-forms.label>
                                            <select class="form-control select-picker" name="criteria[clients][category_ids][]" id="client-category" multiple data-actions-box="true" data-live-search="true" data-container="body">
                                                @foreach($clientCategories as $cat)
                                                    <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Sub Category -->
                                    <div class="col-md-4">
                                        <div class="form-group c-inv-select">
                                            <x-forms.label fieldId="client-subcategory" fieldLabel="Client Sub-Category"></x-forms.label>
                                            <select class="form-control select-picker" name="criteria[clients][sub_category_ids][]" id="client-subcategory" multiple data-actions-box="true" data-live-search="true" data-container="body">
                                                @foreach($clientSubCategories as $subcat)
                                                    <option value="{{ $subcat->id }}">{{ $subcat->category_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Country -->
                                    <div class="col-md-4 mt-2">
                                        <div class="form-group c-inv-select">
                                            <x-forms.label fieldId="client-country" fieldLabel="Country"></x-forms.label>
                                            <select class="form-control select-picker" name="criteria[clients][country_ids][]" id="client-country" multiple data-actions-box="true" data-live-search="true" data-container="body">
                                                @foreach($countries as $country)
                                                    <option value="{{ $country->id }}">{{ $country->nicename }} ({{ $country->iso }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- City -->
                                    <div class="col-md-4 mt-2">
                                        <x-forms.text fieldId="client-city" fieldLabel="City" fieldName="criteria[clients][city]" fieldPlaceholder="e.g. New York"></x-forms.text>
                                    </div>

                                    <!-- State -->
                                    <div class="col-md-4 mt-2">
                                        <x-forms.text fieldId="client-state" fieldLabel="State" fieldName="criteria[clients][state]" fieldPlaceholder="e.g. NY"></x-forms.text>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. LEAD FILTERS PANEL -->
                        <div class="card border-grey mb-4 bg-light filter-source-card" id="lead-filters-card" style="display: none;">
                            <div class="card-header bg-white border-bottom-grey font-weight-bold f-14 py-2 px-3">
                                <i class="fa fa-bullhorn mr-1 text-warning"></i> Lead Filters
                            </div>
                            <div class="card-body p-3">
                                <div class="row">
                                    <!-- Lead Status -->
                                    <div class="col-md-4">
                                        <div class="form-group c-inv-select">
                                            <x-forms.label fieldId="lead-status" fieldLabel="Lead Status"></x-forms.label>
                                            <select class="form-control select-picker" name="criteria[leads][status_ids][]" id="lead-status" multiple data-actions-box="true" data-live-search="true" data-container="body">
                                                @foreach($leadStatuses as $status)
                                                    <option value="{{ $status->id }}">{{ $status->type }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Lead Source -->
                                    <div class="col-md-4">
                                        <div class="form-group c-inv-select">
                                            <x-forms.label fieldId="lead-source" fieldLabel="Lead Source"></x-forms.label>
                                            <select class="form-control select-picker" name="criteria[leads][source_ids][]" id="lead-source" multiple data-actions-box="true" data-live-search="true" data-container="body">
                                                @foreach($leadSources as $source)
                                                    <option value="{{ $source->id }}">{{ $source->type }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Lead Category -->
                                    <div class="col-md-4">
                                        <div class="form-group c-inv-select">
                                            <x-forms.label fieldId="lead-category" fieldLabel="Lead Category"></x-forms.label>
                                            <select class="form-control select-picker" name="criteria[leads][category_ids][]" id="lead-category" multiple data-actions-box="true" data-live-search="true" data-container="body">
                                                @foreach($leadCategories as $cat)
                                                    <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Country -->
                                    <div class="col-md-4 mt-2">
                                        <x-forms.text fieldId="lead-country" fieldLabel="Country" fieldName="criteria[leads][country]" fieldPlaceholder="e.g. United States"></x-forms.text>
                                    </div>

                                    <!-- City -->
                                    <div class="col-md-4 mt-2">
                                        <x-forms.text fieldId="lead-city" fieldLabel="City" fieldName="criteria[leads][city]" fieldPlaceholder="e.g. Los Angeles"></x-forms.text>
                                    </div>

                                    <!-- State -->
                                    <div class="col-md-4 mt-2">
                                        <x-forms.text fieldId="lead-state" fieldLabel="State" fieldName="criteria[leads][state]" fieldPlaceholder="e.g. California"></x-forms.text>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. CLIENT CONTACT FILTERS PANEL -->
                        <div class="card border-grey mb-4 bg-light filter-source-card" id="contact-filters-card" style="display: none;">
                            <div class="card-header bg-white border-bottom-grey font-weight-bold f-14 py-2 px-3">
                                <i class="fa fa-address-book mr-1 text-success"></i> Client Contact Filters
                            </div>
                            <div class="card-body p-3">
                                <div class="row">
                                    <!-- Parent Client Status -->
                                    <div class="col-md-6">
                                        <div class="form-group c-inv-select">
                                            <x-forms.label fieldId="contact-parent-status" fieldLabel="Parent Client Status"></x-forms.label>
                                            <select class="form-control select-picker" name="criteria[contacts][parent_client_status][]" id="contact-parent-status" multiple data-actions-box="true" data-container="body">
                                                <option value="active">Active</option>
                                                <option value="deactive">Deactive</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Designations -->
                                    <div class="col-md-6">
                                        <div class="form-group c-inv-select">
                                            <x-forms.label fieldId="contact-designations" fieldLabel="Designation / Title"></x-forms.label>
                                            <select class="form-control select-picker" name="criteria[contacts][designations][]" id="contact-designations" multiple data-actions-box="true" data-live-search="true" data-container="body">
                                                @foreach($designations as $desig)
                                                    <option value="{{ $desig }}">{{ $desig }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Live Estimate and Preview Widget -->
                    <div class="col-md-3">
                        <div class="card border-grey bg-light sticky-top" style="top: 20px; z-index: 10;">
                            <div class="card-body p-3">
                                <h5 class="f-14 font-weight-bold text-darkest-grey mb-3">Live Estimation</h5>
                                <button type="button" class="btn btn-outline-primary btn-block mb-3" id="estimate-btn">
                                    <i class="fa fa-calculator mr-1"></i> Estimate Recipients
                                </button>
                                <div id="estimate-preview-container">
                                    <div class="text-center py-3 text-muted border border-dashed rounded bg-white">
                                        <i class="fa fa-calculator mb-1 f-16"></i>
                                        <p class="mb-0 f-11">Click estimate button to verify segment size.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-segment-btn" class="mr-3" icon="check">
                        @lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('crm-email-segments.index')" class="border-0">
                        @lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Toggle card panels on target source checkmark change
        function toggleCards() {
            if ($('#source-clients').is(':checked')) {
                $('#client-filters-card').slideDown();
            } else {
                $('#client-filters-card').slideUp();
            }

            if ($('#source-leads').is(':checked')) {
                $('#lead-filters-card').slideDown();
            } else {
                $('#lead-filters-card').slideUp();
            }

            if ($('#source-contacts').is(':checked')) {
                $('#contact-filters-card').slideDown();
            } else {
                $('#contact-filters-card').slideUp();
            }
        }

        $('.source-checkbox').change(function() {
            toggleCards();
        });

        // Trigger estimation via AJAX
        $('#estimate-btn').click(function() {
            var token = "{{ csrf_token() }}";
            var data = $('#save-segment-data-form').serialize();
            
            $('#estimate-preview-container').html('<div class="text-center py-4"><i class="fa fa-spinner fa-spin f-20 text-primary"></i><span class="d-block f-11 text-muted mt-2">Analyzing segment...</span></div>');

            $.ajax({
                url: "{{ route('crm-email-segments.estimate') }}",
                type: "POST",
                data: data,
                success: function(response) {
                    if (response.status === 'success') {
                        $('#estimate-preview-container').html(response.html);
                    } else {
                        $('#estimate-preview-container').html('<div class="text-danger p-2 text-center f-12">Estimation failed</div>');
                    }
                },
                error: function(err) {
                    $('#estimate-preview-container').html('<div class="text-danger p-2 text-center f-12">An error occurred</div>');
                }
            });
        });

        // Save Segment Submission
        $('#save-segment-btn').click(function() {
            const url = "{{ route('crm-email-segments.store') }}";

            $.easyAjax({
                url: url,
                container: '#save-segment-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-segment-btn",
                data: $('#save-segment-data-form').serialize(),
                redirect: true
            });
        });

        init(RIGHT_MODAL);
    });
</script>
