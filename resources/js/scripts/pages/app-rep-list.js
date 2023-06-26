$(function () {
  'use strict';

  var dtRepTable = $('.rep-list-table'),
    sendRepModal = $('#sendRepModal');

  if ($('body').attr('data-framework') === 'laravel') {
    var assetPath = $('body').attr('data-asset-path'),
      basePath = assetPath + 'invoices_payment_log/',
      repCreate = basePath + 'create',
      repData = basePath + 'data';
  }

  // datatable
  if (dtRepTable.length) {
    var dtRep = dtRepTable.DataTable({
      ajax: repData, // JSON file to add data
      autoWidth: false,
      columns: [
        // columns according to JSON
        { data: '' },
        { data: 'id' },
        { data: 'issued_date' },
        { data: 'sat_name' },
        { data: 'total' },
        { data: 'status' }
      ],
      columnDefs: [
        {
          // Rep ID
          targets: 1,
          width: '46px',
          render: function (data, type, full, meta) {
            var $repId = full['id'],
              $repNumber = full['payment_number'],
              $repPreview = basePath + $repId + '/show';
            // Creates full output for row
            var $rowOutput = '<a class="fw-bold" href="' +
              $repPreview +
              '"> ' + $repNumber + '</a>';
            return $rowOutput;
          }
        },
        {
          // Due Date
          targets: 2,
          width: '130px',
          render: function (data, type, full, meta) {
            var $issuedDate = new Date(full['issued_date']);
            // Creates full output for row
            var $rowOutput =
              '<span class="d-none">' +
              moment($issuedDate).format('YYYYMMDD') +
              '</span>' +
              moment($issuedDate).format('DD MMM YYYY');
            $issuedDate;
            return $rowOutput;
          }
        },
        {
          // Client name and Rfc
          targets: 3,
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
          // Total Rep Amount
          targets: 4,
          width: '73px',
          render: function (data, type, full, meta) {
            var $total = full['total'],
              $currency = full['currency'];
            return '<span class="d-none">' + $total + '</span>$' + $total + ' ' + $currency;
          }
        },        
        {
          // status
          targets: 5,
          width: '42px',
          render: function (data, type, full, meta) {
            var $status = full['status'],
              roleObj = {
                Activa: { class: 'bg-light-success', icon: 'x-circle' },
                Cancelada: { class: 'bg-light-danger', icon: 'check-circle' }
              };
            return (
              '<span class="badge rounded-pill ' +
              roleObj[$status].class +
              '">' +
              $status +
              '</span>'
            );
          }
        },
        {
          // Actions
          targets: 0,
          orderable: false,
          render: function (data, type, full, meta) {
            var $repId = full['id'],
              $companyEmail = full['company_email'],
              $repPreview = basePath + $repId + '/show',
              $repPdf = basePath + $repId + '/download_pdf',
              $repXml = basePath + $repId + '/download_xml',
              $repCancel = basePath + $repId + '/delete';

            return (
              '<div class="d-flex align-items-center col-actions">' +
              '<div class="dropdown">' +
              '<a class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">' +
              feather.icons['plus-circle'].toSvg({ class: 'font-medium-2 text-primary' }) +
              '</a>' +
              '<div class="dropdown-menu dropdown-menu-end">' +
              '<a href="' +
              $repPreview +
              '" class="dropdown-item">' +
              feather.icons['eye'].toSvg({ class: 'font-small-4 me-50' }) +
              'Ver Recibo de Pago</a>' +
              '<a class="dropdown-item send_" data-id="' +
              $repId +
              '" data-email="' +
              $companyEmail +
              '">' +
              feather.icons['send'].toSvg({ class: 'font-small-4 me-50' }) +
              'Enviar Recibo de Pago</a>' +
              '<a href="' +
              $repPdf +
              '" class="dropdown-item">' +
              feather.icons['file-text'].toSvg({ class: 'font-small-4 me-50' }) +
              'Descargar PDF</a>' +
              '<a href="' +
              $repXml +
              '" class="dropdown-item">' +
              feather.icons['code'].toSvg({ class: 'font-small-4 me-50' }) +
              'Descargar XML</a>' +
              '<a href="' +
              $repCancel +
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
        if (data["rep_status"] == 'Cancelled') {
          $(row).addClass('table-secondary');
        }
      },
      dom:
        '<"row d-flex justify-content-between align-items-center m-1"' +
        '<"col-lg-6 d-flex align-items-center"<"dt-action-buttons text-xl-end text-lg-start text-lg-end text-start "B>>' +
        '<"col-lg-6 d-flex align-items-center justify-content-lg-end flex-lg-nowrap flex-wrap pe-lg-1 p-0"f<"rep_status ms-sm-2">>' +
        '>t' +
        '<"d-flex justify-content-between mx-2 row"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      language: {
        search: 'Buscar',
        searchPlaceholder: 'Buscar Pago REP',
        paginate: {
          // remove previous & next text from pagination
          previous: '&nbsp;',
          next: '&nbsp;'
        }
      },
      // Buttons with Dropdown
      buttons: [
        {
          text: 'Nuevo Pago REP',
          className: 'btn btn-primary btn-add-record ms-2',
          action: function (e, dt, button, config) {
            window.location = repCreate;
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

    // For action: send-rep
    dtRep.on('click', '.send_', function (e) {
      e.preventDefault();
      sendRepModal.modal('show', $(this));
    });

    // grab the data-id & data-email value from the button that was clicked once modal is shown
    sendRepModal.on('show.bs.modal', function (event) {
      var repId = $(event.relatedTarget).data('id'),
        companyEmail = $(event.relatedTarget).data('email'),
        iframePdfPath = basePath + repId + '/print_quot',
        downloadPdfPath = basePath + repId + '/download_pdf',
        downloadXmlPath = basePath + repId + '/download_xml';

      $('#modalSendRepId').val(repId);
      $('#modalSendRepEmailTo').val(companyEmail);
      $('#modalSendRepIframePdf').attr('src', iframePdfPath);
      $('#modalSendRepDownloadPdf').attr('href', downloadPdfPath);
      $('#modalSendRepDownloadXml').attr('href', downloadXmlPath);
    });

    // clear modal on hidden
    sendRepModal.on("hidden.bs.modal", function () {
      $('#modalSendRepId').val("");
      $('#modalSendRepEmailTo').val("");
      $('#modalSendRepIframePdf').attr('src', "");
      $('#modalSendRepDownloadPdf').attr('href', "");
      $('#modalSendRepDownloadXml').attr('href', "");
    });
  }
});
