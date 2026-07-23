<div>
    @if ($order->customer_type === 'distributor' || ($order->distributor_id && !$order->client_id))
        <small>@lang("modules.invoices.billedTo") (Distributor):</small>
        @if ($order->distributor)
            @if ($order->distributor->name)
                <div>{{ mb_ucwords($order->distributor->name) }}</div>
            @endif
            @if ($order->distributor->company_name)
                <div>{{ mb_ucwords($order->distributor->company_name) }}</div>
            @endif
            @if ($order->distributor->email)
                <div>{{ $order->distributor->email }}</div>
            @endif
            @if ($order->distributor->phone)
                <div>{{ $order->distributor->phone }}</div>
            @endif
            @if ($order->distributor->address)
                <div class="mb-3">
                    <div>@lang('app.address') :</div>
                    <div>{!! nl2br(e($order->distributor->address)) !!}</div>
                </div>
            @endif
            @if ($order->distributor->shipping_address)
                <div>
                    <div>@lang('app.shippingAddress') :</div>
                    <div>{!! nl2br(e($order->distributor->shipping_address)) !!}</div>
                </div>
            @endif
            @if (!is_null($order->distributor->tax_number))
                <div>@lang('app.gstIn'): {{ $order->distributor->tax_number }}</div>
            @endif
        @else
            <div>Distributor #{{ $order->distributor_id }}</div>
        @endif

    @elseif ($order->customer_type === 'end_to_end')
        <small>@lang("modules.invoices.billedTo") (End-to-End Customer):</small>
        @if ($order->custom_customer_name)
            <div>{{ mb_ucwords($order->custom_customer_name) }}</div>
        @else
            <div>End to End Customer</div>
        @endif
        @if ($order->custom_customer_number)
            <div>Contact: {{ $order->custom_customer_number }}</div>
        @endif
        @if ($order->custom_customer_address)
            <div class="mb-3">
                <div>@lang('app.address') :</div>
                <div>{!! nl2br(e($order->custom_customer_address)) !!}</div>
            </div>
        @endif

    @elseif ($order->customer_type === 'care_of' || ($order->care_of_id && !$order->client_id))
        <small>@lang("modules.invoices.billedTo") (Care of):</small>
        @if ($order->careOf)
            <div>{{ mb_ucwords($order->careOf->name) }}</div>
            @if ($order->careOf->email)
                <div>{{ $order->careOf->email }}</div>
            @endif
            @if ($order->careOf->mobile)
                <div>{{ $order->careOf->mobile }}</div>
            @endif
        @else
            <div>Employee #{{ $order->care_of_id }}</div>
        @endif

    @elseif ($order->client && $order->clientDetails)
        <small>@lang("modules.invoices.billedTo"):</small>
        @if ($order->client->name && $invoiceSetting->show_client_name == 'yes')
            <div>{{ mb_ucwords($order->client->name) }}</div>
        @endif

        @if ($order->client->email && $invoiceSetting->show_client_email == 'yes')
            <div>{{ $order->client->email }}</div>
        @endif

        @if ($order->client->mobile && $invoiceSetting->show_client_phone == 'yes')
            <div>{{ $order->client->mobile }}</div>
        @endif

        @if ($order->clientDetails->company_name && $invoiceSetting->show_client_company_name == 'yes')
            <div>{{ mb_ucwords($order->clientDetails->company_name) }}</div>
        @endif

        @if ($order->clientDetails->address && $invoiceSetting->show_client_company_address == 'yes')
            <div class="mb-3">
                <div>@lang('app.address') :</div>
                <div>{!! nl2br(e($order->clientDetails->address)) !!}</div>
            </div>
        @endif

        @if ($order->show_shipping_address === 'yes' && $order->clientDetails->shipping_address && $invoiceSetting->show_client_company_address == 'yes')
            <div>
                <div>@lang('app.shippingAddress') :</div>
                <div>{!! nl2br(e($order->clientDetails->shipping_address)) !!}</div>
            </div>
        @endif
        @if ($invoiceSetting->show_gst == 'yes' && !is_null($order->clientDetails->gst_number))
            <div> @lang('app.gstIn'): {{ $order->clientDetails->gst_number }} </div>
        @endif
    @else
        <small>@lang("modules.invoices.billedTo"):</small>
        <div>{{ $order->customer_name_display }}</div>
    @endif
</div>
