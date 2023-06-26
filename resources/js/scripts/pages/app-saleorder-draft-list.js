$(function () {
    'use strict';
  
    var dtSaleorderTable = $('.saleorder-list-table');
  
    if ($('body').attr('data-framework') === 'laravel') {
      var assetPath = $('body').attr('data-asset-path'),
        basePath = assetPath + 'sales_order/',
        saleorderCreate = basePath + 'create',
        saleorderData = basePath + 'draft_salesorder_list';
    }
  
    // datatable
    if (dtSaleorderTable.length) {
      dtSaleorderTable.DataTable({
        ajax: saleorderData, // JSON file to add data
        autoWidth: false,
        columns: [
          // columns according to JSON
          { data: 'responsive_id' },
          { data: 'saleorder_id' },
          { data: 'issued_date' },
          { data: 'sat_name' },
          { data: 'total' },
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
                $saleorderPreview = basePath + $saleorderId + '/edit';
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
            // Issued Date
            targets: 4,
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
            // Actions
            targets: -1,
            title: 'Opciones',
            width: '180px',
            orderable: false,
            render: function (data, type, full, meta) {
              var $saleorderId = full['saleorder_id'],
                $companyEmail = full['company_email'],
                $saleorderEdit = basePath + $saleorderId + '/edit',
                $saleorderDelete = basePath + $saleorderId + '/delete';
  
              return (
                '<div class="d-flex align-items-center col-actions">' +
                '<a class="me-1" href="' +
                $saleorderEdit +
                '" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar nota de venta">' +
                feather.icons['edit'].toSvg({ class: 'font-medium-2 text-body' }) +
                '</a>' +
                '<a class="me-1" href="' +
                $saleorderDelete +
                '" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar nota de venta">' +
                feather.icons['trash'].toSvg({ class: 'font-medium-2 text-body' }) +
                '</a>' +
                '</div>'
              );
            }
          }
        ],        
        dom:
          '<"row d-flex justify-content-between align-items-center m-1"' +
          '<"col-lg-6 d-flex align-items-center"<"dt-action-buttons text-xl-end text-lg-start text-lg-end text-start "B>>' +
          '<"col-lg-6 d-flex align-items-center justify-content-lg-end flex-lg-nowrap flex-wrap pe-lg-1 p-0"f>' +
          '>t' +
          '<"d-flex justify-content-between mx-2 row"' +
          '<"col-sm-12 col-md-6"i>' +
          '<"col-sm-12 col-md-6"p>' +
          '>',
        language: {
          search: 'Buscar',
          searchPlaceholder: 'Buscar Nota de venta',
          paginate: {
            // remove previous & next text from pagination
            previous: '&nbsp;',
            next: '&nbsp;'
          }
        },
        // Buttons with Dropdown
        buttons: [
          {
            text: 'Nueva Nota de venta',
            className: 'btn btn-primary btn-add-record ms-2',
            action: function (e, dt, button, config) {
              window.location = saleorderCreate;
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
    }
  });
  