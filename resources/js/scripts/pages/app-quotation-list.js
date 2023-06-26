$(function () {
  'use strict';

  var dtQuotationTable = $('.quotation-list-table'),
    sendQuotationModal = $('#sendQuotationModal');

  if ($('body').attr('data-framework') === 'laravel') {
    var assetPath = $('body').attr('data-asset-path'),
      basePath = assetPath + 'quotation/',
      quotationCreate = basePath + 'create',
      quotationDraft = basePath + 'draft_quotations',
      quotationData = basePath + 'data';
  }

  // datatable
  if (dtQuotationTable.length) {
    var dtQuotation = dtQuotationTable.DataTable({
      ajax: quotationData, // JSON file to add data
      autoWidth: false,
      columns: [
        // columns according to JSON
        { data: 'responsive_id' },
        { data: 'quotation_id' },
        { data: 'issued_date' },
        { data: 'sat_name' },
        { data: 'total' },
        { data: 'quotation_status' },
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
          // Quotation ID
          targets: 1,
          width: '46px',
          render: function (data, type, full, meta) {
            var $quotationId = full['quotation_id'],
              $quotationNumber = full['quotation_number'],
              $quotationPreview = basePath + $quotationId + '/show';
            // Creates full output for row
            var $rowOutput = '<a class="fw-bold" href="' +
              $quotationPreview +
              '"> ' + $quotationNumber + '</a>';
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
          // Total Quotation Amount
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
            var $quotationId = full['quotation_id'],
              $companyEmail = full['company_email'],
              $quotationPreview = basePath + $quotationId + '/show',
              $quotationPdf = basePath + $quotationId + '/download_pdf',
              $quotationReuse = basePath + $quotationId + '/reuse',
              $quotationEdit = basePath + $quotationId + '/edit',
              $quotationToInvoice = basePath + $quotationId + '/convert_to_invoice',
              $quotationToSaleOrder = basePath + $quotationId + '/convert_to_saleorder',
              $quotationDelete = basePath + $quotationId + '/delete';

            return (
              '<div class="d-flex align-items-center col-actions">' +
              '<a class="me-1" href="' +
              $quotationPreview +
              '" data-bs-toggle="tooltip" data-bs-placement="top" title="Ver cotización">' +
              feather.icons['eye'].toSvg({ class: 'font-medium-2 text-body' }) +
              '</a>' +
              '<a class="me-1 send_" data-id="' +
              $quotationId +
              '" data-email="' +
              $companyEmail +
              '" data-bs-toggle="tooltip" data-bs-placement="top" title="Enviar cotización">' +
              feather.icons['send'].toSvg({ class: 'font-medium-2 text-body' }) +
              '</a>' +
              '<a class="me-1" href="' +
              $quotationReuse +
              '" data-bs-toggle="tooltip" data-bs-placement="top" title="Reutilizar cotización">' +
              feather.icons['copy'].toSvg({ class: 'font-medium-2 text-body' }) +
              '</a>' +
              '<a class="me-1" href="' +
              $quotationPdf +
              '" data-bs-toggle="tooltip" data-bs-placement="top" title="Descargar cotización">' +
              feather.icons['download'].toSvg({ class: 'font-medium-2 text-body' }) +
              '</a>' +
              '<a class="me-1" href="' +
              $quotationToInvoice +
              '" data-bs-toggle="tooltip" data-bs-placement="top" title="Convertir a Factura">' +
              feather.icons['zap'].toSvg({ class: 'font-medium-2 text-body' }) +
              '</a>' +
              '<a class="me-251" href="' +
              $quotationToSaleOrder +
              '" data-bs-toggle="tooltip" data-bs-placement="top" title="Convertir a Nota de Venta">' +
              feather.icons['file-text'].toSvg({ class: 'font-medium-2 text-body' }) +
              '</a>' +
              '<div class="dropdown">' +
              '<a class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">' +
              feather.icons['more-vertical'].toSvg({ class: 'font-medium-2 text-body' }) +
              '</a>' +
              '<div class="dropdown-menu dropdown-menu-end">' +
              '<a href="' +
              $quotationEdit +
              '" class="dropdown-item">' +
              feather.icons['edit'].toSvg({ class: 'font-small-4 me-50' }) +
              'Editar</a>' +
              '<a href="' +
              $quotationDelete +
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
        if (data["quotation_status"] == 'Cancelled') {
          $(row).addClass('table-secondary');
        }
      },
      dom:
        '<"row d-flex justify-content-between align-items-center m-1"' +
        '<"col-lg-6 d-flex align-items-center"<"dt-action-buttons text-xl-end text-lg-start text-lg-end text-start "B>>' +
        '<"col-lg-6 d-flex align-items-center justify-content-lg-end flex-lg-nowrap flex-wrap pe-lg-1 p-0"f<"quotation_status ms-sm-2">>' +
        '>t' +
        '<"d-flex justify-content-between mx-2 row"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      language: {
        search: 'Buscar',
        searchPlaceholder: 'Buscar Cotización',
        paginate: {
          // remove previous & next text from pagination
          previous: '&nbsp;',
          next: '&nbsp;'
        }
      },
      // Buttons with Dropdown
      buttons: [
        {
          text: 'Nueva Cotización',
          className: 'btn btn-primary btn-add-record ms-2',
          action: function (e, dt, button, config) {
            window.location = quotationCreate;
          }
        },
        {
          text: 'Borradores',
          className: 'btn btn-outline-secondary ms-2',
          action: function (e, dt, button, config) {
            window.location = quotationDraft;
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

    // For action: send-quotation
    dtQuotation.on('click', '.send_', function (e) {
      e.preventDefault();
      sendQuotationModal.modal('show', $(this));
    });

    // grab the data-id & data-email value from the button that was clicked once modal is shown
    sendQuotationModal.on('show.bs.modal', function (event) {
      var quotationId = $(event.relatedTarget).data('id'),
        companyEmail = $(event.relatedTarget).data('email'),
        iframePdfPath = basePath + quotationId + '/print_quot',
        downloadPdfPath = basePath + quotationId + '/download_pdf';

      $('#modalSendQuotationId').val(quotationId);
      $('#modalSendQuotationEmailTo').val(companyEmail);
      $('#modalSendQuotationIframePdf').attr('src', iframePdfPath);
      $('#modalSendQuotationDownloadPdf').attr('href', downloadPdfPath);
    });

    // clear modal on hidden
    sendQuotationModal.on("hidden.bs.modal", function () {
      $('#modalSendQuotationId').val("");
      $('#modalSendQuotationEmailTo').val("");
      $('#modalSendQuotationIframePdf').attr('src', "");
      $('#modalSendQuotationDownloadPdf').attr('href', "");
    });

  }
});
