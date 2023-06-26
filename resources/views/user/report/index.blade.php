@extends('layouts/contentLayoutMaster')
@section('vendor-style')
<link rel="stylesheet" href="{{asset('vendors/css/pickers/flatpickr/flatpickr.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')}}">
@endsection
@section('page-style')
<link rel="stylesheet" href="{{asset('css/base/plugins/forms/pickers/form-flat-pickr.css')}}">
@endsection

@section('content')
    {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id'=>'form']) !!}
    <div class="card">
        <div class="card-header">
            <h4 class="float-left">Descargar facturas por fecha</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-4 d-none">
                    <div class="form-group {{ $errors->has('report_type') ? 'has-error' : '' }}">
                        {!! Form::label('report_type', "Tipo:", array('class' => 'control-label')) !!}
                        <div class="controls">
                            {!! Form::select('report_type', $report_type, null, array('id'=>'report_type', 'class' => 'form-control select2')) !!}
                            <span class="help-block">{{ $errors->first('report_type', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div id="start_date" class="form-group {{ $errors->has('start_date') ? 'has-error' : '' }}">
                        {!! Form::label('start_date', "Desde:", array('class' => 'control-label')) !!}
                        <div class="controls" style="position: relative">
                            {!! Form::text('start_date', $start_date, array('class' => 'form-control flatpickr-basic')) !!}
                            <span class="help-block">{{ $errors->first('start_date', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div id="end_date" class="form-group {{ $errors->has('end_date') ? 'has-error' : '' }}">
                        {!! Form::label('end_date', "Hasta:", array('class' => 'control-label')) !!}
                        <div class="controls" style="position: relative">
                            {!! Form::text('end_date', $end_date, array('class' => 'form-control flatpickr-basic')) !!}
                            <span class="help-block">{{ $errors->first('end_date', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 d-flex">
                    <button type="submit" class="btn btn-primary align-self-end">Descargar Excel</button>
                </div> 
            </div>   
        </div>        
    </div>
    
    {!! Form::close() !!}

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="float-left">Descargar facturas por mes</h4>
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

    <!-- MODAL DE ENVIAR REPORTE-->
<div class="modal fade" id="cfdi_success" data-keyboard="false" data-backdrop="static" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div id="cfdi_header" class="modal-header content">
        
      </div>
      <div class="modal-body">
        
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
<script src="{{ asset('vendors/js/pickers/flatpickr/flatpickr.min.js') }}"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>
@endsection

@section('page-script')
<script>
    $(function () {
    'use strict';

    var dtInvoiceTable = $('#data'),
        sendReportModal = $('#sendReportModal'),
        basicPickr = $('.flatpickr-basic');

    if (basicPickr.length) {
        basicPickr.flatpickr({
            locale: 'es',
            defaultDate: 'today',
            maxDate: 'today',
            altInput: true,
            altFormat: "F j, Y",
            dateFormat: "d/m/Y",
        });
    }

    if (dtInvoiceTable.length) {
        var dtInvoice = dtInvoiceTable.DataTable({
            "pageLength": 25,
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
            "ajax": "{{ url($type) }}" + ((typeof $('#data').attr('data-id') != "undefined") ? "/" + $('#id').val() + "/" + $('#data').attr('data-id') : "/data")
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

    

    function SelectAll(id)
    {
        document.getElementById(id).focus();
        document.getElementById(id).select();
    }    
});
</script>
@stop
