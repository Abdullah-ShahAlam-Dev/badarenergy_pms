@php
$addClientCategoryPermission = user()->permission('manage_client_category');
$addClientSubCategoryPermission = user()->permission('manage_client_subcategory');
$addClientNotePermission = user()->permission('add_client_note');
$addPermission = user()->permission('add_clients');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-client-data-form">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.employees.accountDetails')</h4>

                @if (isset($lead->id)) <input type="hidden" name="lead"
                        value="{{ $lead->id }}"> @endif

                <div class="row p-20">
                    <div class="col-lg-9">
                        <div class="row">
                            <div class="col-md-4">
                                <x-forms.select fieldId="salutation" fieldName="salutation"
                                    :fieldLabel="__('modules.client.salutation')">
                                    <option value="">--</option>
                                    @foreach ($salutations as $salutation)
                                        <option value="{{ $salutation }}">@lang('app.'.$salutation)</option>
                                    @endforeach
                                </x-forms.select>
                            </div>
                            <div class="col-md-4">
                                <x-forms.text fieldId="name" :fieldLabel="__('modules.client.clientName')" fieldName="name"
                                    fieldRequired="true" :fieldPlaceholder="__('placeholders.name')"
                                    :fieldValue="$lead->client_name ?? ''"></x-forms.text>
                            </div>
                            <div class="col-md-4">
                                <x-forms.email fieldId="email" :fieldLabel="__('app.email')" fieldName="email"
                                    :popover="__('modules.client.emailNote')" :fieldPlaceholder="__('placeholders.email')"
                                    :fieldValue="$lead->client_email ?? ''">
                                </x-forms.email>
                            </div>
                            <div class="col-md-4">
                                <x-forms.label class="mt-3" fieldId="password" :fieldLabel="__('app.password')"
                                    :popover="__('messages.requiredForLogin')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <input type="password" name="password" id="password" class="form-control height-35 f-14">
                                    <x-slot name="preappend">
                                        <button type="button" data-toggle="tooltip"
                                            data-original-title="@lang('app.viewPassword')"
                                            class="btn btn-outline-secondary border-grey height-35 toggle-password"><i
                                                class="fa fa-eye"></i></button>
                                    </x-slot>
                                    <x-slot name="append">
                                        <button id="random_password" type="button" data-toggle="tooltip"
                                            data-original-title="@lang('modules.client.generateRandomPassword')"
                                            class="btn btn-outline-secondary border-grey height-35"><i
                                                class="fa fa-random"></i></button>
                                    </x-slot>
                                </x-forms.input-group>
                                <small class="form-text text-muted">@lang('placeholders.password')</small>
                            </div>
                            <div class="col-md-4">
                                <x-forms.select fieldId="country" :fieldLabel="__('app.country')" fieldName="country"
                                    search="true">
                                    @foreach ($countries as $item)
                                    <option data-tokens="{{ $item->iso3 }}" data-phonecode = "{{$item->phonecode}}"
                                        data-content="<span class='flag-icon flag-icon-{{ strtolower($item->iso) }} flag-icon-squared'></span> {{ $item->nicename }}"
                                        value="{{ $item->id }}">{{ $item->nicename }}</option>
                                @endforeach
                                </x-forms.select>
                            </div>
                            <div class="col-md-4">
                                <x-forms.label class="my-3" fieldId="mobile"
                                    :fieldLabel="__('app.mobile')"></x-forms.label>
                                <x-forms.input-group style="margin-top:-4px">


                                    <x-forms.select fieldId="country_phonecode" fieldName="country_phonecode"
                                        search="true">

                                        @foreach ($countries as $item)
                                            <option data-tokens="{{ $item->name }}"
                                                    data-content="{{$item->flagSpanCountryCode()}}"
                                                    value="{{ $item->phonecode }}">{{ $item->phonecode }}
                                            </option>
                                        @endforeach
                                    </x-forms.select>
                                    <input type="tel" class="form-control height-35 f-14" placeholder="@lang('placeholders.mobile')"
                                        name="mobile" id="mobile">
                                </x-forms.input-group>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <x-forms.file allowedFileExtensions="png jpg jpeg svg" class="mr-0 mr-lg-2 mr-md-2 cropper"
                            :fieldLabel="__('modules.profile.profilePicture')" fieldName="image" fieldId="image"
                            fieldHeight="119" :popover="__('messages.fileFormat.ImageFile')" />
                    </div>

                    <div class="col-md-3">
                        <x-forms.select fieldId="gender" :fieldLabel="__('modules.employees.gender')"
                            fieldName="gender">
                            <option value="male">@lang('app.male')</option>
                            <option value="female">@lang('app.female')</option>
                            <option value="others">@lang('app.others')</option>
                        </x-forms.select>
                    </div>

                    <div class="col-md-3">
                        <x-forms.select fieldId="locale" :fieldLabel="__('modules.accountSettings.changeLanguage')"
                            fieldName="locale" search="true">
                            @foreach ($languages as $language)
                                <option {{ user()->locale == $language->language_code ? 'selected' : '' }}
                                data-content="<span class='flag-icon flag-icon-{{ ($language->flag_code == 'en') ? 'gb' : strtolower($language->flag_code) }} flag-icon-squared'></span> {{ $language->language_name }}"
                                value="{{ $language->language_code }}">{{ $language->language_name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class="mt-3" fieldId="category"
                            :fieldLabel="__('modules.client.clientCategory')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="category_id" id="category_id"
                                data-live-search="true">
                                <option value="">--</option>
                                @foreach ($categories as $category)
                                    <option @if (isset($lead) && $lead->category_id == $category->id) selected @endif value="{{ $category->id }}">
                                  {{ $category->category_name }}</option>
                                @endforeach
                            </select>

                            @if ($addClientCategoryPermission == 'all')
                                <x-slot name="append">
                                    <button id="addClientCategory" type="button"
                                        class="btn btn-outline-secondary border-grey"
                                        data-toggle="tooltip" data-original-title="{{ __('app.add').' '.__('modules.client.clientCategory') }}">
                                        @lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class="mt-3" fieldId="sub_category_id"
                            :fieldLabel="__('modules.client.clientSubCategory')"></x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="sub_category_id" id="sub_category_id"
                                data-live-search="true">
                                <option value="">--</option>
                            </select>

                            @if ($addClientSubCategoryPermission == 'all')
                                <x-slot name="append">
                                    <button id="addClientSubCategory" type="button"
                                        class="btn btn-outline-secondary border-grey"
                                        data-toggle="tooltip" data-original-title="{{ __('app.add').' '.__('modules.client.clientSubCategory') }}"
                                        >@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group my-3">
                            <label class="f-14 text-dark-grey mb-12 w-100 mt-3"
                                for="usr">@lang('modules.client.clientCanLogin')</label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="login-yes" :fieldLabel="__('app.yes')" fieldName="login"
                                    fieldValue="enable">
                                </x-forms.radio>
                                <x-forms.radio fieldId="login-no" :fieldLabel="__('app.no')" fieldValue="disable"
                                    fieldName="login" checked="true"></x-forms.radio>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group my-3">
                            <label class="f-14 text-dark-grey mb-12 w-100 mt-3"
                                for="usr">@lang('modules.emailSettings.emailNotifications')</label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="notification-yes" :fieldLabel="__('app.yes')" fieldValue="yes"
                                    fieldName="sendMail" checked="true">
                                </x-forms.radio>
                                <x-forms.radio fieldId="notification-no" :fieldLabel="__('app.no')" fieldValue="no"
                                    fieldName="sendMail">
                                </x-forms.radio>
                            </div>
                        </div>
                    </div>


                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-top-grey">
                    Dealer Credit Details</h4>
                <div class="row p-20">
                    <div class="col-md-3">
                        <x-forms.text class="mb-3 mt-3 mt-lg-0 mt-md-0" fieldId="dealer_code" fieldName="dealer_code"
                            fieldLabel="Dealer Code" fieldRequired="true"
                            fieldPlaceholder="e.g. DLR-10001" :fieldValue="$lead->dealer_code ?? ''">
                        </x-forms.text>
                    </div>
                    <div class="col-md-3">
                        <x-forms.select fieldId="dealer_category" fieldLabel="Dealer Category" fieldName="dealer_category" fieldRequired="true">
                            <option value="distributor" {{ (isset($lead) && $lead->dealer_category == 'distributor') ? 'selected' : '' }}>Distributor</option>
                            <option value="dealer" {{ (!isset($lead) || $lead->dealer_category == 'dealer') ? 'selected' : '' }}>Dealer</option>
                            <option value="end_customer" {{ (isset($lead) && $lead->dealer_category == 'end_customer') ? 'selected' : '' }}>End Customer</option>
                        </x-forms.select>
                    </div>
                    <div class="col-md-3">
                        <x-forms.select fieldId="dealer_tier" fieldLabel="Dealer Tier" fieldName="dealer_tier" fieldRequired="true">
                            <option value="Tier A" {{ (isset($lead) && $lead->dealer_tier == 'Tier A') ? 'selected' : '' }}>Tier A</option>
                            <option value="Tier B" {{ (isset($lead) && $lead->dealer_tier == 'Tier B') ? 'selected' : '' }}>Tier B</option>
                            <option value="Tier C" {{ (!isset($lead) || $lead->dealer_tier == 'Tier C') ? 'selected' : '' }}>Tier C</option>
                        </x-forms.select>
                    </div>
                    <div class="col-md-3">
                        <x-forms.select fieldId="salesperson_id" fieldLabel="Assigned Salesperson" fieldName="salesperson_id" fieldRequired="true">
                            <option value="">--</option>
                            @if(isset($employees))
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            @endif
                        </x-forms.select>
                    </div>
                </div>
                <div class="row px-20 pb-20">
                    <div class="col-md-3">
                        <x-forms.number class="mb-3 mt-3 mt-lg-0 mt-md-0" fieldId="credit_limit" fieldLabel="Credit Limit (PKR)" fieldName="credit_limit"
                            fieldPlaceholder="e.g. 50000" :fieldValue="$lead->credit_limit ?? ''" min="0" step="0.01">
                        </x-forms.number>
                    </div>
                    <div class="col-md-3">
                        <x-forms.number class="mb-3 mt-3 mt-lg-0 mt-md-0" fieldId="credit_days" fieldLabel="Credit Days" fieldName="credit_days"
                            fieldPlaceholder="e.g. 30" :fieldValue="$lead->credit_days ?? ''" min="0">
                        </x-forms.number>
                    </div>
                    <div class="col-md-3">
                        <x-forms.select fieldId="area" fieldLabel="Area" fieldName="area" fieldRequired="true" search="true">
                            <option value="">-- Select City First --</option>
                        </x-forms.select>
                    </div>
                    <div class="col-md-3 d-none" id="custom_area_container">
                        <x-forms.text class="mb-3 mt-3 mt-lg-0 mt-md-0" fieldId="custom_area" fieldName="custom_area"
                            fieldLabel="Custom Area Name" fieldPlaceholder="Enter area name">
                        </x-forms.text>
                    </div>
                    <div class="col-md-3">
                        <x-forms.text class="mb-3 mt-3 mt-lg-0 mt-md-0" fieldId="ntn_number" fieldName="ntn_number"
                            fieldLabel="NTN Number" fieldPlaceholder="Optional">
                        </x-forms.text>
                    </div>
                </div>
                <div class="row px-20 pb-20">
                    <div class="col-md-3">
                        <x-forms.text class="mb-3 mt-3 mt-lg-0 mt-md-0" fieldId="strn_number" fieldName="strn_number"
                            fieldLabel="STRN Number" fieldPlaceholder="Optional">
                        </x-forms.text>
                    </div>
                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-top-grey">
                    @lang('modules.client.companyDetails')</h4>
                <div class="row p-20">
                    <div class="col-md-4">
                        <x-forms.text class="mb-3 mt-3 mt-lg-0 mt-md-0" fieldId="company_name"
                            :fieldLabel="__('modules.client.companyName')" fieldName="company_name"
                            :fieldPlaceholder="__('placeholders.company')" :fieldValue="$lead->company_name ?? ''">
                        </x-forms.text>
                    </div>
                    <div class="col-md-4">
                        <x-forms.text class="mb-3 mt-3 mt-lg-0 mt-md-0" fieldId="website"
                            :fieldLabel="__('modules.client.website')" fieldName="website"
                            fieldPlaceholder="e.g. https://www.spacex.com/" :fieldValue="$lead->website ?? ''">
                        </x-forms.text>
                    </div>
                    <div class="col-md-4">
                        <x-forms.text class="mb-3 mt-3 mt-lg-0 mt-md-0" fieldId="gst_number"
                            :fieldLabel="__('app.gstNumber')" fieldName="gst_number"
                            fieldPlaceholder="e.g. 18AABCU960XXXXX" :fieldValue="$lead->gst_number ?? ''">
                        </x-forms.text>
                    </div>

                    <div class="col-md-3">
                        <x-forms.text fieldId="office" :fieldLabel="__('modules.client.officePhoneNumber')"
                            fieldName="office" fieldPlaceholder="e.g. +19876543" :fieldValue="$lead->office ?? ''">
                        </x-forms.text>
                    </div>
                    <div class="col-md-3">
                        <x-forms.select fieldId="city" fieldLabel="City" fieldName="city" fieldRequired="true" search="true">
                            <option value="">--</option>
                            <option value="Karachi">Karachi</option>
                            <option value="Lahore">Lahore</option>
                            <option value="Islamabad">Islamabad</option>
                            <option value="Rawalpindi">Rawalpindi</option>
                            <option value="Peshawar">Peshawar</option>
                            <option value="Quetta">Quetta</option>
                            <option value="Faisalabad">Faisalabad</option>
                            <option value="Hyderabad">Hyderabad</option>
                            <option value="Multan">Multan</option>
                            <option value="Gujranwala">Gujranwala</option>
                            <option value="Sialkot">Sialkot</option>
                            <option value="Abbottabad">Abbottabad</option>
                            <option value="Sukkur">Sukkur</option>
                            <option value="Other">Other (Write In)</option>
                        </x-forms.select>
                    </div>
                    <div class="col-md-3 d-none" id="custom_city_container">
                        <x-forms.text class="mb-3 mt-3 mt-lg-0 mt-md-0" fieldId="custom_city" fieldName="custom_city"
                            fieldLabel="Custom City Name" fieldPlaceholder="Enter city name">
                        </x-forms.text>
                    </div>
                    <div class="col-md-3">
                        <x-forms.text fieldId="state" :fieldLabel="__('modules.stripeCustomerAddress.state')"
                            fieldName="state" fieldPlaceholder="e.g. California" :fieldValue="$lead->state ?? ''">
                        </x-forms.text>
                    </div>
                    <div class="col-md-3">
                        <x-forms.text fieldId="postalCode" :fieldLabel="__('modules.stripeCustomerAddress.postalCode')"
                            fieldName="postal_code" fieldPlaceholder="e.g. 90250"
                            :fieldValue="$lead->postal_code ?? ''">
                        </x-forms.text>
                    </div>

                    @if ($addPermission == 'all')
                        <div class="col-lg-6 col-md-6">
                            <x-forms.select fieldId="added_by" :fieldLabel="__('app.added').' '.__('app.by')"
                                fieldName="added_by">
                                <option value="">--</option>
                                @foreach ($employees as $item)
                                    <x-user-option :user="$item" :selected="user()->id == $item->id" />
                                @endforeach
                            </x-forms.select>
                        </div>
                    @endif
                    <div class="col-md-12">
                    </div>
                    <div class="col-md-6">
                        <div class="form-group my-3">
                            <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2"
                                :fieldLabel="__('modules.accountSettings.companyAddress')" fieldName="address"
                                fieldId="address" fieldPlaceholder="e.g. Rocket Road"
                                :fieldValue="$lead->address ?? ''">
                            </x-forms.textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group my-3">
                            <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.shippingAddress')"
                                fieldName="shipping_address" fieldId="shipping_address"
                                fieldPlaceholder="e.g. Rocket Road" :fieldValue="$lead->shipping_address ?? ''">
                            </x-forms.textarea>
                        </div>
                    </div>

                    @if (function_exists('sms_setting') && sms_setting()->telegram_status)
                        <div class="col-md-4">
                            <x-forms.number fieldName="telegram_user_id" fieldId="telegram_user_id"
                                fieldLabel="<i class='fab fa-telegram'></i> {{ __('sms::modules.telegramUserId') }}"
                                :popover="__('sms::modules.userIdInfo')" />
                        </div>
                    @endif

                    @if ($addClientNotePermission == 'all' || $addClientNotePermission == 'added' || $addClientNotePermission == 'both')
                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.label class="my-3" fieldId="note" :fieldLabel="__('app.note')">
                            </x-forms.label>
                            <div id="note"></div>
                            <textarea name="note" id="note-text" class="d-none"></textarea>
                        </div>
                    </div>
                    @endif

                    <div class="col-lg-12">
                        <x-forms.file allowedFileExtensions="png jpg jpeg svg" class="mr-0 mr-lg-2 mr-md-2"
                                               :fieldLabel="__('modules.contracts.companyLogo')" fieldName="company_logo"
                                               :fieldValue="(company()->logo_url)" fieldId="company_logo" :popover="__('messages.fileFormat.ImageFile')"/>
                    </div>

                    <input type ="hidden" name="add_more" value="false" id="add_more" />

                </div>

                <x-forms.custom-field :fields="$fields"></x-forms.custom-field>

                <x-form-actions>
                    <x-forms.button-primary id="save-client-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-secondary class="mr-3" id="save-more-client-form" icon="check-double">@lang('app.saveAddMore')
                    </x-forms.button-secondary>
                    <x-forms.button-cancel :link="route('clients.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script src="{{ asset('vendor/jquery/dropzone.min.js') }}"></script>
<script>
    (function() {
        var $body = $('body');
        var add_client_note_permission = "{{ $addClientNotePermission }}";
        var namespace = '.clientsCreate';

        $('.custom-date-picker').each(function(ind, el) {
            datepicker(el, { position: 'bl', ...datepickerConfig });
        });

        if (add_client_note_permission == 'all' || add_client_note_permission == 'added' || add_client_note_permission == 'both') {
            if (typeof quillImageLoad === 'function') {
                quillImageLoad('#note');
            }
        }

        init(RIGHT_MODAL);

        $body.off(namespace);

        $body.on('change' + namespace, '#country', function() {
            var phonecode = $(this).find(':selected').data('phonecode');
            $('#country_phonecode').val(phonecode).selectpicker('refresh');
        });

        $body.on('change' + namespace, '#category_id', function() {
            var categoryId = $(this).val();
            var url = "{{ route('get_client_sub_categories', ':id') }}".replace(':id', categoryId);

            $.easyAjax({
                url: url,
                type: "GET",
                success: function(response) {
                    if (response.status == 'success') {
                        var options = [];
                        $.each(response.data, function(index, value) {
                            options.push('<option value="' + value.id + '">' + value.category_name + '</option>');
                        });
                        $('#sub_category_id').html('<option value="">--</option>' + options.join('')).selectpicker('refresh');
                    }
                }
            });
        });

        var areaMap = {
            'Karachi': ['Saddar', 'DHA', 'Orangi Town', 'Bismillah Market', 'Nagan Chorangi', 'Johar', 'Clifton', 'Gulshan-e-Iqbal', 'North Nazimabad', 'Federal B Area', 'Korangi', 'Malir', 'Other'],
            'Lahore': ['Johar Town', 'DHA', 'Gulberg', 'Model Town', 'Cantt', 'Samanabad', 'Iqbal Town', 'Shadman', 'Other'],
            'Islamabad': ['F-6', 'F-7', 'F-8', 'G-9', 'G-11', 'I-8', 'DHA', 'Bahria Town', 'Other'],
            'Rawalpindi': ['Saddar', 'Satellite Town', 'DHA', 'Bahria Town', 'Other'],
            'Peshawar': ['Hayatabad', 'University Road', 'Saddar', 'Warsak Road', 'Other'],
            'Quetta': ['Cantt', 'Jinnah Road', 'Shahbaz Town', 'Satellite Town', 'Double Road', 'Other'],
            'Faisalabad': ['Peoples Colony', 'Kohinoor City', 'Madina Town', 'D-Ground', 'Ghulam Muhammad Abad', 'Other'],
            'Hyderabad': ['Latifabad', 'Qasimabad', 'Saddar', 'Gari Khata', 'Other'],
            'Multan': ['Cantt', 'Gulgasht Colony', 'Bosan Road', 'Shah Rukn-e-Alam', 'Other'],
            'Gujranwala': ['Satellite Town', 'People\'s Colony', 'Cantt', 'Other'],
            'Sialkot': ['Cantt', 'Shahabpura', 'Model Town', 'Other'],
            'Abbottabad': ['Cantt', 'Jinnahabad', 'Mandian', 'Other'],
            'Sukkur': ['Military Road', 'Barrage Road', 'Shalimar', 'Other']
        };

        function populateAreas(city, selectedArea) {
            var $areaSelect = $('#area');
            $areaSelect.empty();

            if (!city || city === '') {
                $areaSelect.append('<option value="">-- Select City First --</option>');
                $areaSelect.selectpicker('refresh');
                $('#custom_area_container').addClass('d-none');
                $('#custom_area').val('');
                return;
            }

            var areas = areaMap[city];
            if (areas) {
                $.each(areas, function(index, val) {
                    var selected = (val === selectedArea) ? 'selected' : '';
                    $areaSelect.append('<option value="' + val + '" ' + selected + '>' + val + '</option>');
                });
                $('#custom_area_container').addClass('d-none');
                $('#custom_area').removeAttr('required').val('');
            } else {
                $areaSelect.append('<option value="Other" selected>Other (Write In)</option>');
                $('#custom_area_container').removeClass('d-none');
                if (selectedArea) {
                    $('#custom_area').val(selectedArea);
                }
                $('#custom_area').attr('required', true);
            }
            $areaSelect.selectpicker('refresh');
        }

        $body.on('change' + namespace, '#city', function() {
            var city = $(this).val();
            if (city === 'Other') {
                $('#custom_city_container').removeClass('d-none');
                $('#custom_city').attr('required', true).val('');
                populateAreas('', '');
            } else {
                $('#custom_city_container').addClass('d-none');
                $('#custom_city').removeAttr('required').val('');
                populateAreas(city, '');
            }
        });

        $body.on('change' + namespace, '#area', function() {
            if ($(this).val() === 'Other') {
                $('#custom_area_container').removeClass('d-none');
                $('#custom_area').attr('required', true).val('');
            } else {
                $('#custom_area_container').addClass('d-none');
                $('#custom_area').removeAttr('required').val('');
            }
        });

        $body.on('input' + namespace, '#custom_city', function() {
            var val = $(this).val();
            $('#city option[value="Other"]').val(val);
        });

        $body.on('input' + namespace, '#custom_area', function() {
            var val = $(this).val();
            $('#area option[value="Other"]').val(val);
        });

        // Initialize loaded city/area (if converted from lead)
        var predefinedCities = Object.keys(areaMap);
        var initialCity = "{{ $lead->city ?? '' }}";
        var initialArea = "{{ $lead->area ?? '' }}";

        if (initialCity && initialCity !== '') {
            if (predefinedCities.indexOf(initialCity) === -1) {
                $('#city').append('<option value="' + initialCity + '" selected>' + initialCity + '</option>');
                $('#city').val(initialCity).selectpicker('refresh');
                $('#custom_city_container').removeClass('d-none');
                $('#custom_city').val(initialCity);
                populateAreas(initialCity, initialArea);
            } else {
                $('#city').val(initialCity).selectpicker('refresh');
                populateAreas(initialCity, initialArea);
            }
        }

        function saveClient(data, url, buttonSelector) {
            if (add_client_note_permission == 'all' || add_client_note_permission == 'added' || add_client_note_permission == 'both') {
                var noteElement = document.getElementById('note');
                if (noteElement && noteElement.children[0]) {
                    document.getElementById('note-text').value = noteElement.children[0].innerHTML;
                }
            }

            $.easyAjax({
                url: url,
                container: '#save-client-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: buttonSelector,
                file: true,
                data: data,
                success: function(response) {
                    if (response.status == 'success') {
                        if ($(MODAL_XL).hasClass('show')) {
                            $(MODAL_XL).modal('hide');
                            window.location.reload();
                        } else if (typeof response.redirectUrl !== 'undefined') {
                            window.location.href = response.redirectUrl;
                        } else if (response.add_more == true) {
                            var $rightModalContent = $(RIGHT_MODAL_CONTENT);
                            if ($rightModalContent.length && $.trim($rightModalContent.html()).length) {
                                $rightModalContent.html(response.html.html);
                                $('#add_more').val(false);
                            } else {
                                $('.content-wrapper').html(response.html.html);
                                init('.content-wrapper');
                                $('#add_more').val(false);
                            }
                        }

                        if (typeof showTable === 'function') {
                            showTable();
                        }
                    }
                }
            });
        }

        $body.on('click' + namespace, '#save-more-client-form', function() {
            $('#add_more').val(true);
            saveClient($('#save-client-data-form').serialize(), "{{ route('clients.store') }}", "#save-more-client-form");
        });

        $body.on('click' + namespace, '#save-client-form', function() {
            saveClient($('#save-client-data-form').serialize(), "{{ route('clients.store') }}", "#save-client-form");
        });

        $body.on('click' + namespace, '#random_password', function() {
            $('#password').val(Math.random().toString(36).substr(2, 8));
        });

        $body.on('click' + namespace, '#addClientCategory', function() {
            $.ajaxModal(MODAL_LG, "{{ route('clientCategory.create') }}");
        });

        $body.on('click' + namespace, '#addClientSubCategory', function() {
            $.ajaxModal(MODAL_LG, "{{ route('clientSubCategory.create') }}");
        });

        window.checkboxChange = function(parentClass, id) {
            var checkedData = '';
            $('.' + parentClass).find("input[type='checkbox']:checked").each(function() {
                checkedData = (checkedData !== '') ? checkedData + ', ' + $(this).val() : $(this).val();
            });
            $('#' + id).val(checkedData);
        };

        document.addEventListener("turbo:before-cache", function cleanup() {
            $body.off(namespace);
            if (typeof destroy_editor === 'function') {
                destroy_editor('#note');
            }
            document.removeEventListener("turbo:before-cache", cleanup);
        }, { once: true });
    })();
</script>
