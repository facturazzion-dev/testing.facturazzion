@extends('layouts/contentLayoutMaster')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')}}">
@endsection

{{-- Content --}}
@section('content')
    
    <!-- <div class="page-header clearfix">
        
    </div> -->
    <div class="card">
        <div class="card-header bg-white">
            <h4 class="float-left">{{ $title }}</h4>
            @if($user->hasAccess(['taxes.write']) || $orgRole=='admin')
                <div class="pull-right">
                    <a href="{{ $type.'/create' }}" class="btn btn-primary ">
                        <i class="fa fa-plus-circle"></i> {{ trans('tax.create') }}</a>
                    
                </div>
            @endif
        </div>
        <div class="card-body">
            <div class="">
                <table id="data" class="table table-striped table-bordered fed-tax-list-table">
                    <thead>
                    <tr>
                        <th class="cell-fit"></th>
                        <th>ID</th>
                        <th>{{ trans('tax.name') }}</th>
                        <th>{{ trans('tax.tax') }}</th>
                        <th>{{ trans('tax.tax_type') }}</th>
                        <th>{{ trans('tax.percentage') }}</th>
                        <th>{{ trans('tax.factor_type') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('user.local_tax.index')

@endsection

@section('vendor-script')
<script src="{{asset('vendors/js/extensions/moment.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/datatables.buttons.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/dataTables.bootstrap5.min.js')}}"></script>
<script src="{{ asset(mix('vendors/js/extensions/sweetalert2.all.min.js')) }}"></script>
@endsection

{{-- Scripts --}}
@section('page-script')
<script>
    $(function () {
        'use strict';
        var dtFedTaxesTable = $('.fed-tax-list-table'),
            dtLocalTaxesTable = $('.local-tax-list-table'),
            bookmarkAction = {
                favoriteMessage: ['Agregar como impuesto constante', 'Quitar como impuesto constante'],
                activeClass: ['', 'checked'],
                iconName: ['toggle-left', 'toggle-right'],
                init: function (id, isFavorite) {
                    return (
                        '<div class="form-check form-switch"> ' +
                            '<input type="checkbox" class="form-check-input favorite_toggle" name="favorite_toggle" data-id="' +
                            id +
                            '" data-is-favorite="' +
                            isFavorite + '" ' +
                            this.activeClass[isFavorite] +
                            '/>' +
                            '<label class="form-check-label" for="favorite_toggle"></label> ' +
                        '</div>'
                    // '<a class="me-1 favorite" data-id="' +
                    // id +
                    // '" data-is-favorite="' +
                    // isFavorite +
                    // '" data-bs-toggle="tooltip" data-bs-placement="top" title="' +
                    // this.favoriteMessage[isFavorite] +
                    // '">' +
                    // feather.icons[this.iconName[isFavorite]].toSvg({ class: 'font-medium-5 bookmark-icon ' + this.activeClass[isFavorite] }) +
                    // '</a>'
                    );
                }
            };

        if ($('body').attr('data-framework') === 'laravel') {
            var assetPath = $('body').attr('data-asset-path'),
            basePath = assetPath + 'tax/';
        }
            
        // datatable
        if (dtFedTaxesTable.length) {
            var dtFedTaxes = dtFedTaxesTable.DataTable({
                "language": {
                    "url":"//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
                },
                "dom":"<'row'<'col-md-4'f><'col-md-4'><'col-md-4'l>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                "order": [],
                "columns":[
                    {"data":""},
                    {"data":"id"},
                    {"data":"name"},
                    {"data":"tax"},
                    {"data":"tax_type"},
                    {"data":"percentage"},
                    {"data":"factor_type"},
                ],
                columnDefs: [
                    {
                        // Actions
                        targets: 0,
                        orderable: false,
                        render: function (data, type, full, meta) {
                            var $id = full['id'],
                            $edit = "{{ url('tax') }}/" + $id + '/edit',
                            $show = "{{ url('tax') }}/" + $id + '/show',
                            $delete = "{{ url('tax') }}/" + $id + '/delete';

                            return (
                                '<div class="d-flex align-items-center col-actions">' +
                                '<div class="dropdown">' +
                                '<a class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">' +
                                feather.icons['plus-circle'].toSvg({ class: 'font-medium-2 text-primary' }) +
                                '</a>' +
                                '<div class="dropdown-menu">' +
                                '<a href="' +
                                $edit +
                                '" class="dropdown-item">' +
                                feather.icons['edit'].toSvg({ class: 'font-small-4 me-50' }) +
                                'Editar</a>' +
                                '<a href="' +
                                $show +
                                '" class="dropdown-item">' +
                                feather.icons['eye'].toSvg({ class: 'font-small-4 me-50' }) +
                                'Ver detalle</a>' +
                                '<a href="' +
                                $delete +
                                '" class="dropdown-item">' +
                                feather.icons['trash'].toSvg({ class: 'font-small-4 me-50' }) +
                                'Borrar</a>' +
                                '</div>' +
                                '</div>' +
                                '</div>'
                            );
                        }
                    },
                    {
                        targets: 1,
                        visible: false
                    },
                    {
                        // Actions
                        targets: -1,
                        title: 'Impuestos constantes',
                        width: '80px',
                        orderable: false,
                        render: function (data, type, full, meta) {
                            var $taxId = full['id'],
                            $isFavorite = full['favorite'];

                            return (
                            '<div class="d-flex align-items-center col-actions">' +
                            bookmarkAction.init($taxId, $isFavorite) +
                            '</div>'
                            )
                        }
                    }
                ],
                "ajax": "{{ url($type) }}" + ((typeof $('#data').attr('data-id') != "undefined") ? "/" + $('#id').val() + "/" + $('#data').attr('data-id') : "/data")
            });
        }

        if (dtLocalTaxesTable.length) {
            var dtLocalTaxes = dtLocalTaxesTable.DataTable({
                "language": {
                    "url":"//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
                },
                "dom":"<'row'<'col-md-4'f><'col-md-4'><'col-md-4'l>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                "processing": true,
                "serverSide": true,
                // "lengthChange": false,
                "order": [],
                "columns":[
                    {"data":""},
                    {"data":"id"},
                    {"data":"name"},
                    {"data":"tax_type"},
                    {"data":"percentage"},
                    {"data":"factor_type"},
                ],
                columnDefs: [
                    {
                        // Actions
                        targets: 0,
                        orderable: false,
                        render: function (data, type, full, meta) {
                            var $id = full['id'],
                            $edit = "{{ url('tax') }}/" + $id + '/edit',
                            $show = "{{ url('tax') }}/" + $id + '/show',
                            $delete = "{{ url('tax') }}/" + $id + '/delete';

                            return (
                                '<div class="d-flex align-items-center col-actions">' +
                                '<div class="dropdown">' +
                                '<a class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">' +
                                feather.icons['plus-circle'].toSvg({ class: 'font-medium-2 text-primary' }) +
                                '</a>' +
                                '<div class="dropdown-menu dropdown-menu-end">' +
                                '<a href="' +
                                $edit +
                                '" class="dropdown-item">' +
                                feather.icons['edit'].toSvg({ class: 'font-small-4 me-50' }) +
                                'Editar</a>' +
                                '<a href="' +
                                $show +
                                '" class="dropdown-item">' +
                                feather.icons['eye'].toSvg({ class: 'font-small-4 me-50' }) +
                                'Ver detalle</a>' +
                                '<a href="' +
                                $delete +
                                '" class="dropdown-item">' +
                                feather.icons['trash'].toSvg({ class: 'font-small-4 me-50' }) +
                                'Borrar</a>' +
                                '</div>' +
                                '</div>' +
                                '</div>'
                            );
                        }
                    },
                    {
                        targets: 1,
                        visible: false
                    },
                    {
                        // Actions
                        targets: -1,
                        title: 'Impuestos constantes',
                        width: '80px',
                        orderable: false,
                        render: function (data, type, full, meta) {
                            var $taxId = full['id'],
                            $isFavorite = full['favorite'];

                            return (
                            '<div class="d-flex align-items-center col-actions">' +
                            bookmarkAction.init($taxId, $isFavorite) +
                            '</div>'
                            )
                        }
                    }
                ],
                "ajax": "{{ url($type) }}" + ((typeof $('#data2').attr('data-id') != "undefined") ? "/" + $('#id').val() + "/" + $('#data2').attr('data-id') : "/local_data")
            });
        }

        $(document).on('change', '.favorite_toggle', function (e) {
            e.preventDefault();
            var $this = $(this),
                taxId = $this.data('id'),
                isFavorite = $this.data('is-favorite'),
                setFavorite = basePath + taxId + '/toggle-favorite',
                message = isFavorite ? 'Se removerá de impuesto constante.' : 'Al seleccionarlo como impuesto constante se mostrará como predeterminado al momento de crear una nueva factura.';

            Swal.fire({
                title: 'Impuesto Constante',
                text: message,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Si, estoy de acuerdo.',
                cancelButtonText: 'Cancelar',
                customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-outline-danger ms-1'
                },
                buttonsStyling: false,
                showLoaderOnConfirm: true,
                backdrop: true,                
                allowOutsideClick: () => !Swal.isLoading(),
                
            })
            .then((result) => {
                if (result.isConfirmed) {    
                    // Swal.showLoading();
                    Swal.fire({
                        title: 'Impuesto Constante',
                        text: 'Actualizando el impuesto...',
                        icon: 'info',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        timerProgressBar: true,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });

                    fetch(setFavorite)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(response.statusText)
                        }
                        Swal.fire({
                            icon: 'success',
                            title: '¡Listo!',
                            // text: `El cliente '${company.name}' ha sido actualizado.`,
                            text: `El impuesto ha sido actualizado.`,
                            customClass: {
                                confirmButton: 'btn btn-success'
                            }
                        });
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Algo salió mal: ${error}`);
                        $this.prop('checked',!$this.prop('checked'));
                    })
                } else {
                    $this.prop('checked',!$this.prop('checked'));
                }
                Swal.hideLoading();
            })
        });
    });
</script>

@endsection
