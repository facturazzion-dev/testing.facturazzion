@extends('layouts/contentLayoutMaster')

{{-- Web site Title --}}
@section('title')
{{ $title }}
@stop

@section('vendor-style')
  {{-- Page Css files --}}
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap5.min.css')) }}">
@endsection

{{-- Content --}}
@section('content')
    <div class="card">
        <div class="card-datatable table-responsive">
            <table class="company-list-table table">
                <thead>
                <tr>
                    <th>{{ trans('company.sat_rfc') }} / {{ trans('company.sat_name') }}</th>
                    <th>{{ trans('company.company_name') }}</th>
                    <th>{{ trans('company.phone') }}</th>
                    <th>{{ trans('company.sat_rfc') }}</th>
                    <th>{{ trans('company.sat_name') }}</th>
                    <th>{{ trans('company.company_name') }}</th>
                    <th>{{ trans('company.email') }}</th>
                    <th class="cell-fit">Opciones</th>
                </tr>
                </thead>
            </table>
            </div>
    </div>
@endsection

@section('vendor-script')
<script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.bootstrap5.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap5.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/datatables.buttons.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/jszip.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/pdfmake.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/vfs_fonts.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/buttons.html5.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/extensions/sweetalert2.all.min.js')) }}"></script>
@endsection

@section('page-script')
<script>
    $(function () {
  'use strict';

  var dtCompanyTable = $('.company-list-table'),
    bookmarkAction = {
      favoriteMessage: ['Marcar como Favorito', 'Quitar de Favorito'],
      activeClass: ['text-body', 'text-warning'],
      init: function (id, isFavorite) {
        return (
          '<a class="me-1 favorite" data-id="' +
          id +
          '" data-is-favorite="' +
          isFavorite +
          '" data-bs-toggle="tooltip" data-bs-placement="top" title="' +
          this.favoriteMessage[isFavorite] +
          '">' +
          feather.icons['star'].toSvg({ class: 'font-medium-2 bookmark-icon ' + this.activeClass[isFavorite] }) +
          '</a>'
        );
      }
    };

  if ($('body').attr('data-framework') === 'laravel') {
    var assetPath = $('body').attr('data-asset-path'),
      basePath = assetPath + 'company/',
      companyCreate = basePath + 'create',
      companyData = basePath + 'data';
  }

  // datatable
  if (dtCompanyTable.length) {
    var dtCompany = dtCompanyTable.DataTable({
      ajax: companyData, // JSON file to add data
      autoWidth: false,
      columns: [
        // columns according to JSON
        { data: 'sat_rfc_name' },
        { data: 'name_email' },
        { data: 'phone' },
        { data: 'sat_rfc' },
        { data: 'sat_name' },
        { data: 'name' },
        { data: 'email' },
        { data: '' }
      ],
      pageLength: 25,
      columnDefs: [
        {
          // rfc and sat_name
          targets: 0,
          render: function (data, type, full, meta) {
            var $rfc = full['sat_rfc'],
              $name = full['sat_name'],
              $companyView = basePath + full['id'] + '/show';

            var $row_output =
              '<div class="d-flex justify-content-left align-items-center">' +
              '<div class="d-flex flex-column">' +
              '<a href="' +
              $companyView +
              '" class="user_name text-truncate text-body"><span class="fw-bolder">' +
              $rfc +
              '</span></a>' +
              '<small class="emp_post text-muted">' +
              ($name.length > 50 ? `${$name.substr(0, 40)}...` : $name) +
              '</small>' +
              '</div>' +
              '</div>'
            return $row_output
          }
        },
        {
          // name and email
          targets: 1,
          render: function (data, type, full, meta) {
            var $name = full['name'],
              $email = full['email'];

            var $row_output =
              '<div class="d-flex justify-content-left align-items-center">' +
              '<div class="d-flex flex-column">' +
              '<span class="user_name text-truncate text-body"><span class="fw-bolder">' +
              ($name.length > 50 ? `${$name.substr(0, 40)}...` : $name) +
              '</span></span>' +
              '<small class="emp_post text-muted">' +
              $email +
              '</small>' +
              '</div>' +
              '</div>'
            return $row_output
          }
        },
        {
            targets: 3,
            visible: false
          },
          {
            targets: 4,
            visible: false
          },
          {
            targets: 5,
            visible: false
          },
          {
            targets: 6,
            visible: false
          },
        {
          // Actions
          targets: -1,
          title: 'Opciones',
          width: '80px',
          orderable: false,
          render: function (data, type, full, meta) {
            var $companyId = full['id'],
              $isFavorite = full['favorite'],
              $companyEdit = basePath + $companyId + '/edit',
              $companyDelete = basePath + $companyId + '/delete';


            return (
              '<div class="d-flex align-items-center col-actions">' +
              bookmarkAction.init($companyId, $isFavorite) +
              '<a class="me-1" href="' +
              $companyEdit +
              '" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar cliente">' +
              feather.icons['edit'].toSvg({ class: 'font-medium-2 text-body' }) +
              '</a>' +
              '<a class="me-25" href="' +
              $companyDelete +
              '" data-bs-toggle="tooltip" data-bs-placement="top" title="Borrar cliente">' +
              feather.icons['trash'].toSvg({ class: 'font-medium-2 text-body' }) +
              '</a>' +
              '</div>'
            )
          }
        }
      ],
      dom:
        '<"d-flex justify-content-between align-items-center mx-2 row mt-75"' +
        '<"col-sm-12 col-lg-4 d-flex justify-content-center justify-content-lg-start" <"me-1"f>>' +
        '<"col-sm-12 col-lg-8 ps-xl-75 ps-0"<"dt-action-buttons d-flex align-items-center justify-content-center justify-content-lg-end flex-lg-nowrap flex-wrap"B>>' +
        '>t' +
        '<"d-flex justify-content-between mx-2 row mb-1"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',        
      language: {
        url: '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json',
        searchPlaceholder: 'Buscar Cliente',
        paginate: {
          // remove previous & next text from pagination
          previous: '&nbsp;',
          next: '&nbsp;'
        }
      },
      // Buttons with Dropdown
      buttons: [
        {
          extend: 'collection',
          className: 'btn btn-outline-secondary dropdown-toggle me-2',
          text: feather.icons['external-link'].toSvg({ class: 'font-small-4 me-50' }) + 'Exportar',
          buttons: [
            {
              extend: 'csv',
              text: feather.icons['file-text'].toSvg({ class: 'font-small-4 me-50' }) + 'Csv',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6] }
            },
            {
              extend: 'excel',
              text: feather.icons['file'].toSvg({ class: 'font-small-4 me-50' }) + 'Excel',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6] }
            },
            {
              extend: 'pdf',
              text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 me-50' }) + 'Pdf',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6] }
            },
            {
              extend: 'copy',
              text: feather.icons['copy'].toSvg({ class: 'font-small-4 me-50' }) + 'Copiar',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6] }
            }
          ],
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
            $(node).parent().removeClass('btn-group');
            setTimeout(function () {
              $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex mt-50');
            }, 50);
          }
        },
        {
          text: feather.icons['plus'].toSvg({ class: 'font-small-4 me-50' }) + 'Crear Cliente',
          className: 'btn btn-primary btn-add-record ms-2',
          action: function (e, dt, button, config) {
            window.location = companyCreate;
          }
        },
      ],
      initComplete: function () {
        $(document).find('[data-bs-toggle="tooltip"]').tooltip();
      },
      drawCallback: function () {
        $(document).find('[data-bs-toggle="tooltip"]').tooltip();
      }
    });

    dtCompany.on('click', '.favorite', function (e) {
      e.preventDefault();
      var $this = $(this),
        companyId = $this.data('id'),
        isFavorite = $this.data('is-favorite'),
        setFavorite = basePath + companyId + '/toggle-favorite',
        message = isFavorite ? 'Se removerá este cliente de favorito.' : 'Al seleccionarlo como favorito se mostrará como predeterminado al momento de crear una nueva factura.';

      Swal.fire({
        title: 'Cliente favorito',
        text: message,
        footer: '<span>* Recuerda que solo se permite un cliente favorito.</span>',
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
        preConfirm: () => {
          return fetch(setFavorite)
            .then(response => {
              if (!response.ok) {
                throw new Error(response.statusText)
              }
              return response.json()
            })
            .catch(error => {
              Swal.showValidationMessage(
                `Algo salió mal: ${error}`
              )
            })
        },
        allowOutsideClick: () => !Swal.isLoading(),
      }).then((result) => {
        if (result.isConfirmed) {
          var { company } = result.value,
            activeBookmarkList = $('.company-list-table').find('.bookmark-icon.text-warning');

          for (var i = 0; i < activeBookmarkList.length; i++) {
            var activeBookmark = activeBookmarkList[i].parentElement,
              companyId = activeBookmark.dataset.id;
            $(activeBookmark).replaceWith(bookmarkAction.init(companyId, 0));
          }
          $this.replaceWith(bookmarkAction.init(companyId, 1));

          Swal.fire({
            icon: 'success',
            title: '¡Listo!',
            text: `El cliente '${company.name}' ha sido actualizado.`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
          dtCompany.draw();
        }
      })
    });
  }
});

</script>
@endsection