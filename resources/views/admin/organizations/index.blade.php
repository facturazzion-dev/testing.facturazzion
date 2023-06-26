@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="page-header clearfix">
        <div class="pull-right">
            <a href="{{ $type.'/create' }}" class="btn btn-primary m-b-10">
                <i class="fa fa-plus-circle"></i> {{ trans('organizations.create') }}</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white">
            <h4 class="float-left">
                <i class="material-icons">flag</i>
                {{ $title }}
            </h4>                                
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="data" class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>Razón Social</th>                        
                        <th>RFC</th>
                        <th>{{ trans('organizations.email') }}</th>
                        <th>{{ trans('organizations.phone_number') }}</th>
                        <th>Facturas realizadas</th>
                        <th>Fecha primer factura</th>
                        <th>Fecha ultima factura</th>
                        <th class="noExport">{{ trans('table.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@stop

{{-- Scripts --}}
@section('scripts')
    <!-- Scripts -->
    @if(isset($type))
        <script type="text/javascript">
            var oTable;
            $(document).ready(function () {
                oTable = $('#data').DataTable({
                    "language": {
                        "url":"//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json",
                        "searchPlaceholder": "Buscar",
                    },
                    "processing": false,
                    "serverSide": false,
                    "order": [],
                        "columns":[
                            {"data":"sat_name"},
                            {"data":"sat_rfc"},
                            {"data":"email"},
                            {"data":"phone_number"},
                            {"data":"invoices"},
                            {"data":"first_invoice_date"},
                            {"data":"last_invoice_date"},
                            {"data":"actions"},
                        ],
                    "ajax": "{{ url($type) }}" + ((typeof $('#data').attr('data-id') != "undefined") ? "/" + $('#id').val() + "/" + $('#data').attr('data-id') : "/data"),
                    dom: 'Bfrtip',
                    pageLength: 15,
                    stateSave: false,
                    lengthMenu: [[10,25,50,100, -1],[10,25,50,100, "All"]],
                    buttons: [
                        {
                            extend: 'pageLength'
                        },
                        {
                            extend: 'collection',
                            text: 'Export',
                            buttons: [
                                {
                                    extend: 'copy',
                                    exportOptions: {
                                        columns: "thead th:not(.noExport)"
                                    }
                                },
                                {
                                    extend: 'csv',                                    
                                },
                                {
                                    extend: 'excel',
                                    exportOptions: {
                                        columns: "thead th:not(.noExport)"
                                    }
                                },
                                {
                                    extend: 'pdf',
                                    exportOptions: {
                                        columns: "thead th:not(.noExport)"
                                    }
                                },
                                {
                                    extend: 'print',
                                    exportOptions: {
                                        columns: "thead th:not(.noExport)"
                                    }
                                }
                            ]
                        }
                    ]
                });
                
            });
        </script>
    @endif
@stop
