<!-- Datatables Libraries handled by app.blade.php -->

{!! $dataTable->scripts() !!}

<script>
    $('.table-responsive').on('show.bs.dropdown', function () {
        $('.table-responsive').css( "overflow", "inherit" );
    });

    $('.table-responsive').on('hide.bs.dropdown', function () {
        $('.table-responsive').css( "overflow", "auto" );
    })
</script>

@include('sections.daterange_js')