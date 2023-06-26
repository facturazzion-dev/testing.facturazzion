$(function () {
  'use strict';

  var dtInvoiceTable = $('.invoice-list-table'),
    sendInvoiceModal = $('#sendInvoiceModal');

  if ($('body').attr('data-framework') === 'laravel') {
    var assetPath = $('body').attr('data-asset-path'),
      basePath = assetPath + 'invoice/',
      repPath = assetPath + 'invoices_payment_log/',
      invoiceCreate = basePath + 'create',
      invoiceDraft = basePath + 'draft_invoices',
      invoiceData = basePath + 'data';
  }

  // datatable
  if (dtInvoiceTable.length) {
    var dtInvoice = dtInvoiceTable.DataTable({
      language: {
        url: "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
      },
      ajax: invoiceData, // JSON file to add data
      autoWidth: false,
      columns: [
        // columns according to JSON
        { data: '' },
        { data: 'invoice_number' },
        { data: 'issued_date' },
        { data: 'invoice_pay_method' },
        { data: 'sat_name' },
        { data: 'total' },
        { data: 'balance' },
        { data: 'invoice_status' },
        { data: 'invoice_id' },
      ],
      order: [[8, 'desc']],
      columnDefs: [
        {
          // Invoice ID
          targets: 1,
          width: '46px',
          render: function (data, type, full, meta) {
            var $invoiceId = full['invoice_id'],
              $invoiceNumber = full['invoice_number'],
              $invoicePreview = basePath + $invoiceId + '/show';
            // Creates full output for row
            var $rowOutput = '<a class="fw-bold" href="' +
              $invoicePreview +
              '"> ' + $invoiceNumber + '</a>';
            return $rowOutput;
          }
        },
        {
          // Due Date
          targets: 2,
          width: '130px',
          render: function (data, type, full, meta) {
            var $dueDate = new Date(full['due_date']);
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
          // Invoice status
          targets: 3,
          width: '42px',
          render: function (data, type, full, meta) {
            var $paymentMethod = full['invoice_pay_method'],
              roleObj = {
                PUE: { class: 'bg-light-success', icon: 'x-circle' },
                PPD: { class: 'bg-light-warning', icon: 'check-circle' }
              };
            return (
              '<span class="badge rounded-pill ' +
              roleObj[$paymentMethod].class +
              '">' +
              $paymentMethod +
              '</span>'
            );
          }
        },
        {
          // Client name and Rfc
          targets: 4,
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
          // Total Invoice Amount
          targets: 5,
          width: '73px',
          render: function (data, type, full, meta) {
            var $total = full['total'];
            return '<span class="d-none">' + $total + '</span>$' + $total;
          }
        },
        {
          // Client Balance/Status
          targets: 6,
          width: '98px',
          render: function (data, type, full, meta) {
            var $balance = full['balance'];
            if ($balance === 0) {
              var $badge_class = 'badge-light-success';
              return '<span class="badge rounded-pill ' + $badge_class + '" text-capitalized> Pagada </span>';
            } else {
              return '<span class="d-none">' + $balance + '</span>$' + $balance;
            }
          }
        },
        {
          targets: 7,
          visible: false
        },
        {
          targets: 8,
          visible: false
        },
        {
          // Actions
          targets: 0,
          orderable: false,
          render: function (data, type, full, meta) {
            var payOption = '',
              $invoiceId = full['invoice_id'],
              $paymentMethod = full['invoice_pay_method'],
              $balance = full['balance'],
              $companyEmail = full['company_email'],
              $invoicePreview = basePath + $invoiceId + '/show',
              $invoicePdf = basePath + $invoiceId + '/download_pdf',
              $invoiceXml = basePath + $invoiceId + '/download_xml',
              $invoiceReuse = basePath + $invoiceId + '/reuse',
              $invoiceCancel = basePath + $invoiceId + '/delete',
              $invoicePay = repPath + 'pay_invoice/' + $invoiceId;

            if($paymentMethod == 'PPD' && $balance > 0) {
              payOption = '<a href="' + $invoicePay + '" class="dropdown-item">' + feather.icons['dollar-sign'].toSvg({ class: 'font-small-4 me-50' }) + 'Registrar Pago (REP)</a>';
            }

            return (
              '<div class="d-flex align-items-center col-actions">' +
              '<div class="dropdown">' +
              '<a class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">' +
              feather.icons['plus-circle'].toSvg({ class: 'font-medium-2 text-primary' }) +
              '</a>' +
              '<div class="dropdown-menu dropdown-menu-end">' +
              payOption +
              '<a href="' +
              $invoicePreview +
              '" class="dropdown-item">' +
              feather.icons['eye'].toSvg({ class: 'font-small-4 me-50' }) +
              'Ver Factura</a>' +
              '<a class="dropdown-item send_" data-id="' +
              $invoiceId +
              '" data-email="' +
              $companyEmail +
              '">' +
              feather.icons['send'].toSvg({ class: 'font-small-4 me-50' }) +
              'Enviar Factura</a>' +
              '<a href="' +
              $invoicePdf +
              '" class="dropdown-item">' +
              feather.icons['file-text'].toSvg({ class: 'font-small-4 me-50' }) +
              'Descargar PDF</a>' +
              '<a href="' +
              $invoiceXml +
              '" class="dropdown-item">' +
              feather.icons['code'].toSvg({ class: 'font-small-4 me-50' }) +
              'Descargar XML</a>' +
              '<a href="' +
              $invoiceReuse +
              '" class="dropdown-item">' +
              feather.icons['copy'].toSvg({ class: 'font-small-4 me-50' }) +
              'Reutilizar</a>' +
              '<a href="' +
              $invoiceCancel +
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
        if (data["invoice_status"] == 'Cancelled') {
          $(row).addClass('table-secondary');
        }
      },
      dom:
        '<"row d-flex justify-content-between align-items-center m-1"' +
        '<"col-lg-6 d-flex align-items-center"<"dt-action-buttons text-xl-end text-lg-start text-lg-end text-start "B>>' +
        '<"col-lg-6 d-flex align-items-center justify-content-lg-end flex-lg-nowrap flex-wrap pe-lg-1 p-0"f<"invoice_status ms-sm-2">>' +
        '>t' +
        '<"d-flex justify-content-between mx-2 row"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      language: {
        search: 'Buscar',
        searchPlaceholder: 'Buscar Factura',
        paginate: {
          // remove previous & next text from pagination
          previous: '&nbsp;',
          next: '&nbsp;'
        }
      },
      // Buttons with Dropdown
      buttons: [
        {
          text: 'Nueva Factura',
          className: 'btn btn-primary btn-add-record ms-2',
          action: function (e, dt, button, config) {
            window.location = invoiceCreate;
          }
        },
        {
          text: 'Borradores',
          className: 'btn btn-outline-secondary ms-2',
          action: function (e, dt, button, config) {
            window.location = invoiceDraft;
          }
        }
      ],
      initComplete: function () {
        $(document).find('[data-bs-toggle="tooltip"]').tooltip();
      },
      drawCallback: function () {
        $(document).find('[data-bs-toggle="tooltip"]').tooltip();
      }
    });

    // For action: send-invoice
    dtInvoice.on('click', '.send_', function (e) {
      e.preventDefault();
      sendInvoiceModal.modal('show', $(this));
    });

    // grab the data-id & data-email value from the button that was clicked once modal is shown
    sendInvoiceModal.on('show.bs.modal', function (event) {
      var invoiceId = $(event.relatedTarget).data('id'),
        companyEmail = $(event.relatedTarget).data('email'),
        iframePdfPath = basePath + invoiceId + '/print_quot',
        downloadPdfPath = basePath + invoiceId + '/download_pdf',
        downloadXmlPath = basePath + invoiceId + '/download_xml';

      $('#modalSendInvoiceId').val(invoiceId);
      $('#modalSendInvoiceEmailTo').val(companyEmail);
      $('#modalSendInvoiceIframePdf').attr('src', iframePdfPath);
      $('#modalSendInvoiceDownloadPdf').attr('href', downloadPdfPath);
      $('#modalSendInvoiceDownloadXml').attr('href', downloadXmlPath);
    });

    // clear modal on hidden
    sendInvoiceModal.on("hidden.bs.modal", function () {
      $('#modalSendInvoiceId').val("");
      $('#modalSendInvoiceEmailTo').val("");
      $('#modalSendInvoiceIframePdf').attr('src', "");
      $('#modalSendInvoiceDownloadPdf').attr('href', "");
      $('#modalSendInvoiceDownloadXml').attr('href', "");
    });
  }
});