<!-- Datatables Libraries handled by app.blade.php -->

@if(isset($dataTable))
    {!! $dataTable->scripts() !!}
@endif

<script>
    // Suppress native DataTables alerts for aborted XHR requests on Turbo navigations
    $.fn.dataTable.ext.errMode = 'none';
    $('.table-responsive').on('error.dt', function(e, settings, techNote, message) {
        console.warn('DataTables Async Warning (Expected during Turbo navigation):', message);
    });
    $('.table-responsive').on('show.bs.dropdown', function () {
        $('.table-responsive').css( "overflow", "inherit" );
    });

    $('.table-responsive').on('hide.bs.dropdown', function () {
        $('.table-responsive').css( "overflow", "auto" );
    })
</script>

@include('sections.daterange_js')