@extends('layouts/contentLayoutMaster')
@section('vendor-style')
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')}}">
@endsection
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="float-left">Resumen de Facturas por mes</h4>
            </div>
            <div class="card-body">
                <div class="">
                    <table id="data" class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th class="cell-fit"></th>
                            <th>{{ trans('table.id') }}</th>
                            <th>{{ trans('invoice.year') }}</th>
                            <th>{{ trans('invoice.month') }}</th>
                            <th>{{ trans('invoice.total') }}</th>
                                                            
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>    

@include('content._partials._modals.modal-wait-dialog')
@include('content._partials._modals.modal-send-report')
@endsection

@section('vendor-script')
<script src="{{asset('vendors/js/extensions/moment.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/datatables.buttons.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/dataTables.bootstrap5.min.js')}}"></script>
@endsection

@section('page-script')
<script>
    $(function () {
    'use strict';

    var dtInvoiceTable = $('#data'),
        sendReportModal = $('#sendReportModal');

    if (dtInvoiceTable.length) {
        var dtInvoice = dtInvoiceTable.DataTable({
            "pageLength": 5,
            "language": {
                "url":"//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
            },
            "dom":"<'row'<'col-md-4'><'col-md-4'><'col-md-4'>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            order: [
                [1, "desc"],
                
            ],
            "columns":[
                {"data": "actions", "sortable": false},
                {"data": "year_month", "sortable": true,"visible": false},
                {"data": "year", "sortable": false},
                {"data": "month", "sortable": false},
                {"data": "total", "sortable": false},
            ],
            columnDefs: [
                {
                    // Actions
                    targets: 0,
                    orderable: false,
                    render: function (data, type, full, meta) {
                        var $yearMonth = full['year_month'],
                        $downloadPdf = "{{ url('report') }}/" + $yearMonth + '/download_pdf',
                        $downloadExcel = "{{ url('report') }}/" + $yearMonth + '/download_xls';

                        return (
                            '<div class="d-flex align-items-center col-actions">' +
                            '<div class="dropdown">' +
                            '<a class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">' +
                            feather.icons['plus-circle'].toSvg({ class: 'font-medium-2 text-primary' }) +
                            '</a>' +
                            '<div class="dropdown-menu">' +
                            '<a class="dropdown-item send_" data-id="' +
                            $yearMonth +
                            '" data-email="">' +
                            feather.icons['send'].toSvg({ class: 'font-small-4 me-50' }) +
                            'Enviar</a>' +
                            '<a href="' +
                            $downloadPdf +
                            '" class="dropdown-item">' +
                            feather.icons['file-text'].toSvg({ class: 'font-small-4 me-50' }) +
                            'Descargar PDF</a>' +
                            '<a href="' +
                            $downloadExcel +
                            '" class="dropdown-item">' +
                            feather.icons['download'].toSvg({ class: 'font-small-4 me-50' }) +
                            'Descargar Excel</a>' +
                            '</div>' +
                            '</div>' +
                            '</div>'
                        );
                    }
                }
            ],
            "ajax": "{{ url('report') }}" + ((typeof $('#data').attr('data-id') != "undefined") ? "/" + $('#id').val() + "/" + $('#data').attr('data-id') : "/data")
        });

        // For action: send-invoice
        dtInvoice.on('click', '.send_', function (e) {
        e.preventDefault();
        sendReportModal.modal('show', $(this));
        });

        // grab the data-id & data-email value from the button that was clicked once modal is shown
        sendReportModal.on('show.bs.modal', function (event) {
        var yearMonth = $(event.relatedTarget).data('id'),
            companyEmail = $(event.relatedTarget).data('email'),
            downloadPdfPath = basePath + yearMonth + '/download_pdf',
            downloadXlsPath = basePath + yearMonth + '/download_xls';

        $('#modalSendReportYearMonth').val(yearMonth);
        $('#modalSendReportEmailTo').val(companyEmail);
        $('#modalSendReportDownloadPdf').attr('href', downloadPdfPath);
        $('#modalSendReportDownloadXls').attr('href', downloadXlsPath);
        });

        // clear modal on hidden
        sendReportModal.on("hidden.bs.modal", function () {
        $('#modalSendReportYearMonth').val("");
        $('#modalSendReportEmailTo').val("");
        $('#modalSendReportDownloadPdf').attr('href', "");
        $('#modalSendReportDownloadXls').attr('href', "");
        });
    }        
    });
</script>
@stop
