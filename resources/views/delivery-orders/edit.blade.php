<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">Edit / Dispatch Delivery Order</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="update-delivery-order-form" method="PUT">
        <div class="row">
            <div class="col-md-12">
                <x-forms.select fieldId="dispatcher_id" :fieldLabel="'Assign Dispatcher (Employee)'" fieldName="dispatcher_id" search="true">
                    <option value="">-- Select Employee --</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" @if($deliveryOrder->dispatcher_id == $employee->id) selected @endif>
                            {{ $employee->name }}
                        </option>
                    @endforeach
                </x-forms.select>
            </div>
            
            <div class="col-md-12 mt-3">
                <x-forms.select fieldId="status" :fieldLabel="'Status'" fieldName="status" search="false">
                    <option value="pending" @if($deliveryOrder->status == 'pending') selected @endif>Pending</option>
                    <option value="dispatched" @if($deliveryOrder->status == 'dispatched') selected @endif>Dispatched</option>
                    <option value="delivered" @if($deliveryOrder->status == 'delivered') selected @endif>Delivered</option>
                    <option value="cancelled" @if($deliveryOrder->status == 'cancelled') selected @endif>Cancelled</option>
                </x-forms.select>
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">Cancel</x-forms.button-cancel>
    <x-forms.button-primary id="save-delivery-order" icon="check">Save Changes</x-forms.button-primary>
</div>

<script>
    $(function() {
        $('#dispatcher_id, #status').selectpicker();

        $('#save-delivery-order').click(function() {
            var url = "{{ route('delivery-orders.update', $deliveryOrder->id) }}";
            $.easyAjax({
                url: url,
                container: '#update-delivery-order-form',
                type: "POST",
                blockUI: true,
                data: $('#update-delivery-order-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        $(MODAL_LG).modal('hide');
                        if (typeof table !== 'undefined') {
                            table.draw();
                        } else {
                            window.location.reload();
                        }
                    }
                }
            });
        });
    });
</script>
