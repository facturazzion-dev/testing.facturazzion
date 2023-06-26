@extends('layouts/contentLayoutMaster')

@section('title', $title)

@section('vendor-style')
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/extensions/dataTables.checkboxes.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/responsive.bootstrap5.min.css')}}">
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('css/base/pages/app-invoice-list.css')}}">
@endsection

@section('content')
<section class="invoice-list-wrapper">
  <div class="card">
    <div class="card-datatable">
      <table class="saleorder-list-table table">
        <thead>
          <tr>
            <th></th>
            <th>#</th>
            <th>Cliente</th>
            <th>Total</th>
            <th class="text-truncate">Fecha de Emisión</th>
            <th>Status</th>
            <th class="cell-fit">Opciones</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</section>
@include('content/_partials/_modals/modal-send-saleorder')
@include('content._partials._modals.modal-wait-dialog')
@endsection

@section('vendor-script')
<script src="{{asset('vendors/js/extensions/moment.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/datatables.buttons.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/dataTables.bootstrap5.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/datatables.checkboxes.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/dataTables.responsive.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/responsive.bootstrap5.js')}}"></script>
@endsection

@section('page-script')
<script>
  $(function () {
  'use strict';

  var dtSaleorderTable = $('.saleorder-list-table'),
    sendSaleorderModal = $('#sendSaleorderModal');

  if ($('body').attr('data-framework') === 'laravel') {
    var assetPath = $('body').attr('data-asset-path'),
      basePath = assetPath + 'sales_order/',
      saleorderCreate = basePath + 'create',
      saleorderDraft = basePath + 'draft_saleorders',
      saleorderData = basePath + 'data';
  }

  // datatable
  if (dtSaleorderTable.length) {
    var dtSaleorder = dtSaleorderTable.DataTable({
      ajax: saleorderData, // JSON file to add data
      autoWidth: false,
      columns: [
        // columns according to JSON
        { data: 'responsive_id' },
        { data: 'saleorder_id' },
        { data: 'issued_date' },
        { data: 'sat_name' },
        { data: 'total' },
        { data: 'saleorder_status' },
        { data: '' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          responsivePriority: 2,
          targets: 0
        },
        {
          // Saleorder ID
          targets: 1,
          width: '46px',
          render: function (data, type, full, meta) {
            var $saleorderId = full['saleorder_id'],
              $saleorderNumber = full['sale_number'],
              $saleorderPreview = basePath + $saleorderId + '/show';
            // Creates full output for row
            var $rowOutput = '<a class="fw-bold" href="' +
              $saleorderPreview +
              '"> ' + $saleorderNumber + '</a>';
            return $rowOutput;
          }
        },
        {
          // Client name and Rfc
          targets: 2,
          responsivePriority: 4,
          width: '270px',
          render: function (data, type, full, meta) {
            var $satName = full['sat_name'],
              $satRfc = full['sat_rfc'],
              $image = full['avatar'],
              $state = 'secondary',
              $initials = $satName.match(/\b\w/g) || [];
            $initials = (($initials.shift() || '') + ($initials.pop() || '')).toUpperCase();
            if ($image) {
              // For Avatar image
              var $output =
                '<img  src="' + assetPath + 'images/avatars/' + $image + '" alt="Avatar" width="32" height="32">';
            } else {
              // For Avatar badge
              $output = '<div class="avatar-content">' + $initials + '</div>';
            }
            // Creates full output for row
            var colorClass = $image === '' ? ' bg-light-' + $state + ' ' : ' ';

            var $rowOutput =
              '<div class="d-flex justify-content-left align-items-center">' +
              '<div class="avatar-wrapper">' +
              '<div class="avatar' +
              colorClass +
              'me-50">' +
              $output +
              '</div>' +
              '</div>' +
              '<div class="d-flex flex-column">' +
              '<h6 class="sat-name text-truncate mb-0">' +
              $satName +
              '</h6>' +
              '<small class="text-truncate text-muted">' +
              $satRfc +
              '</small>' +
              '</div>' +
              '</div>';
            return $rowOutput;
          }
        },
        {
          // Total Saleorder Amount
          targets: 3,
          width: '73px',
          render: function (data, type, full, meta) {
            var $total = full['total'];
            return '<span class="d-none">' + $total + '</span>$' + $total;
          }
        },
        {
          // Due Date
          targets: 4,
          width: '130px',
          render: function (data, type, full, meta) {
            var $dueDate = new Date(full['issued_date']);
            // Creates full output for row
            var $rowOutput =
              '<span class="d-none">' +
              moment($dueDate).format('YYYYMMDD') +
              '</span>' +
              moment($dueDate).format('DD MMM YYYY');
            $dueDate;
            return $rowOutput;
          }
        },
        {
          targets: 5,
          visible: false
        },
        {
          // Actions
          targets: -1,
          title: 'Opciones',
          width: '180px',
          orderable: false,
          render: function (data, type, full, meta) {
            var $saleorderId = full['saleorder_id'],
              $companyEmail = full['company_email'],
              $saleorderPreview = basePath + $saleorderId + '/show',
              $saleorderPdf = basePath + $saleorderId + '/download_pdf',
              $saleorderReuse = basePath + $saleorderId + '/reuse',
              $saleorderEdit = basePath + $saleorderId + '/edit',
              $saleorderToInvoice = basePath + $saleorderId + '/convert_to_invoice',
              $saleorderDelete = basePath + $saleorderId + '/delete';

            return (
              '<div class="d-flex align-items-center col-actions">' +
              '<a class="me-1" href="' +
              $saleorderPreview +
              '" data-bs-toggle="tooltip" data-bs-placement="top" title="Ver nota de venta">' +
              feather.icons['eye'].toSvg({ class: 'font-medium-2 text-body' }) +
              '</a>' +
              '<a class="me-1 send_" data-id="' +
              $saleorderId +
              '" data-email="' +
              $companyEmail +
              '" data-bs-toggle="tooltip" data-bs-placement="top" title="Enviar nota de venta">' +
              feather.icons['send'].toSvg({ class: 'font-medium-2 text-body' }) +
              '</a>' +
              '<a class="me-1" href="' +
              $saleorderReuse +
              '" data-bs-toggle="tooltip" data-bs-placement="top" title="Reutilizar nota de venta">' +
              feather.icons['copy'].toSvg({ class: 'font-medium-2 text-body' }) +
              '</a>' +
              '<a class="me-1" href="' +
              $saleorderPdf +
              '" data-bs-toggle="tooltip" data-bs-placement="top" title="Descargar nota de venta">' +
              feather.icons['download'].toSvg({ class: 'font-medium-2 text-body' }) +
              '</a>' +
              '<a class="me-1" href="' +
              $saleorderToInvoice +
              '" data-bs-toggle="tooltip" data-bs-placement="top" title="Convertir a Factura">' +
              feather.icons['zap'].toSvg({ class: 'font-medium-2 text-body' }) +
              '</a>' +
              '<div class="dropdown">' +
              '<a class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">' +
              feather.icons['more-vertical'].toSvg({ class: 'font-medium-2 text-body' }) +
              '</a>' +
              '<div class="dropdown-menu dropdown-menu-end">' +
              '<a href="' +
              $saleorderEdit +
              '" class="dropdown-item">' +
              feather.icons['edit'].toSvg({ class: 'font-small-4 me-50' }) +
              'Editar</a>' +
              '<a href="' +
              $saleorderDelete +
              '" class="dropdown-item">' +
              feather.icons['x-circle'].toSvg({ class: 'font-small-4 me-50' }) +
              'Cancelar</a>' +
              '</div>' +
              '</div>' +
              '</div>'
            );
          }
        }
      ],
      createdRow: function (row, data) {
        if (data["saleorder_status"] == 'Cancelled') {
          $(row).addClass('table-secondary');
        }
      },
      dom:
        '<"row d-flex justify-content-between align-items-center m-1"' +
        '<"col-lg-6 d-flex align-items-center"<"dt-action-buttons text-xl-end text-lg-start text-lg-end text-start "B>>' +
        '<"col-lg-6 d-flex align-items-center justify-content-lg-end flex-lg-nowrap flex-wrap pe-lg-1 p-0"f<"saleorder_status ms-sm-2">>' +
        '>t' +
        '<"d-flex justify-content-between mx-2 row"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      language: {
        search: 'Buscar',
        searchPlaceholder: 'Buscar Nota de Venta',
        paginate: {
          // remove previous & next text from pagination
          previous: '&nbsp;',
          next: '&nbsp;'
        }
      },
      // Buttons with Dropdown
      buttons: [
        {
          text: 'Nueva Nota de Venta',
          className: 'btn btn-primary btn-add-record ms-2',
          action: function (e, dt, button, config) {
            window.location = saleorderCreate;
          }
        },
        {
          text: 'Borradores',
          className: 'btn btn-outline-secondary ms-2',
          action: function (e, dt, button, config) {
            window.location = saleorderDraft;
          }
        }
      ],
      // For responsive popup
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Detalle de ' + data['sat_name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            var data = $.map(columns, function (col, i) {
              return col.columnIndex !== 2 // ? Do not show row in modal popup if title is blank (for check box)
                ? '<tr data-dt-row="' +
                col.rowIdx +
                '" data-dt-column="' +
                col.columnIndex +
                '">' +
                '<td>' +
                col.title +
                ':' +
                '</td> ' +
                '<td>' +
                col.data +
                '</td>' +
                '</tr>'
                : '';
            }).join('');
            return data ? $('<table class="table"/>').append('<tbody>' + data + '</tbody>') : false;
          }
        }
      },
      initComplete: function () {
        $(document).find('[data-bs-toggle="tooltip"]').tooltip();
      },
      drawCallback: function () {
        $(document).find('[data-bs-toggle="tooltip"]').tooltip();
      }
    });

    // For action: send-saleorder
    dtSaleorder.on('click', '.send_', function (e) {
      e.preventDefault();
      sendSaleorderModal.modal('show', $(this));
    });

    // grab the data-id & data-email value from the button that was clicked once modal is shown
    sendSaleorderModal.on('show.bs.modal', function (event) {
      var saleorderId = $(event.relatedTarget).data('id'),
        companyEmail = $(event.relatedTarget).data('email'),
        iframePdfPath = basePath + saleorderId + '/print_quot',
        downloadPdfPath = basePath + saleorderId + '/download_pdf';

      $(this).find('.hidden-document-id').val(saleorderId);
      $('#modalSendSaleorderEmailTo').val(companyEmail);
      $('#modalSendSaleorderIframePdf').attr('src', iframePdfPath);
      $('#modalSendSaleorderDownloadPdf').attr('href', downloadPdfPath);
    });

    // clear modal on hidden
    sendSaleorderModal.on("hidden.bs.modal", function () {
      $(this).find('.hidden-document-id').val("");
      $('#modalSendSaleorderEmailTo').val("");
      $('#modalSendSaleorderIframePdf').attr('src', "");
      $('#modalSendSaleorderDownloadPdf').attr('href', "");
    });

  }
});

</script>
@endsection