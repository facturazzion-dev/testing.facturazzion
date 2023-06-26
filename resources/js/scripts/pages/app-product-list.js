$(function () {
  'use strict';

  var dtProductTable = $('.product-list-table'),
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
      basePath = assetPath + 'product/',
      productCreate = basePath + 'create',
      productData = basePath + 'data';
  }

  // datatable
  if (dtProductTable.length) {
    var dtProduct = dtProductTable.DataTable({
      ajax: productData, // JSON file to add data
      autoWidth: false,
      columns: [
        // columns according to JSON
        { data: '' },
        { data: 'sku' },
        { data: 'clave_sat' },
        { data: 'product_name' },
        { data: 'quantity_available' },
        { data: 'clave_unidad_sat' },
        { data: '' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          orderable: false,
          responsivePriority: 2,
          targets: 0,
          render: function (data, type, full, meta) {
            return ''
          }
        },
        {
          // name and description
          targets: 3,
          responsivePriority: 4,
          render: function (data, type, full, meta) {
            var $name = full['product_name'],
              $description = full['description'],
              $productView = basePath + full['product_id'] + '/show';

            var $row_output =
              '<div class="d-flex justify-content-left align-items-center">' +
              '<div class="d-flex flex-column">' +
              '<a href="' +
              $productView +
              '" class="user_name text-truncate text-body"><span class="fw-bolder">' +
              $name +
              '</span></a>' +
              '<small class="emp_post text-muted">' +
              $description +
              '</small>' +
              '</div>' +
              '</div>'
            return $row_output
          }
        },
        {
          // clave y unidad de medida
          targets: 5,
          responsivePriority: 5,
          render: function (data, type, full, meta) {
            var $clave = full['clave_unidad_sat'],
              $unidad = full['unidad_sat'];

            var $row_output =
              '<div class="d-flex justify-content-left align-items-center">' +
              '<div class="d-flex flex-column">' +
              '<span class="user_name text-truncate text-body"><span class="fw-bolder">' +
              $clave +
              '</span></span>' +
              '<small class="emp_post text-muted">' +
              $unidad +
              '</small>' +
              '</div>' +
              '</div>'
            return $row_output
          }
        },
        {
          // Actions
          targets: -1,
          title: 'Opciones',
          width: '80px',
          orderable: false,
          render: function (data, type, full, meta) {
            var $productId = full['product_id'],
              $isFavorite = full['favorite'],
              $productEdit = basePath + $productId + '/edit',
              $productDelete = basePath + $productId + '/delete';


            return (
              '<div class="d-flex align-items-center col-actions">' +
              bookmarkAction.init($productId, $isFavorite) +
              '<a class="me-1" href="' +
              $productEdit +
              '" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar producto">' +
              feather.icons['edit'].toSvg({ class: 'font-medium-2 text-body' }) +
              '</a>' +
              '<a class="me-25" href="' +
              $productDelete +
              '" data-bs-toggle="tooltip" data-bs-placement="top" title="Borrar producto">' +
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
        searchPlaceholder: 'Buscar Producto',
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
              exportOptions: { columns: [1, 2, 3, 4, 5] }
            },
            {
              extend: 'excel',
              text: feather.icons['file'].toSvg({ class: 'font-small-4 me-50' }) + 'Excel',
              className: 'dropdown-item',
              exportOptions: { columns: [1, 2, 3, 4, 5] }
            },
            {
              extend: 'pdf',
              text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 me-50' }) + 'Pdf',
              className: 'dropdown-item',
              exportOptions: { columns: [1, 2, 3, 4, 5] }
            },
            {
              extend: 'copy',
              text: feather.icons['copy'].toSvg({ class: 'font-small-4 me-50' }) + 'Copiar',
              className: 'dropdown-item',
              exportOptions: { columns: [1, 2, 3, 4, 5] }
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
          text: feather.icons['plus'].toSvg({ class: 'font-small-4 me-50' }) + 'Crear Producto',
          className: 'btn btn-primary btn-add-record ms-2',
          action: function (e, dt, button, config) {
            window.location = productCreate;
          }
        },
      ],
      // For responsive popup
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Detalle de ' + data['product_name'];
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

    dtProduct.on('click', '.favorite', function (e) {
      e.preventDefault();
      var $this = $(this),
        productId = $this.data('id'),
        isFavorite = $this.data('is-favorite'),
        setFavorite = basePath + productId + '/toggle-favorite',
        message = isFavorite ? 'Se removerá este producto de favorito.' : 'Al seleccionarlo como favorito se mostrará como predeterminado al momento de crear una nueva factura.';

      Swal.fire({
        title: 'Producto favorito',
        text: message,
        footer: '<span>* Recuerda que solo se permite un producto favorito.</span>',
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
          var { product } = result.value,
            activeBookmarkList = $('.product-list-table').find('.bookmark-icon.text-warning');

          for (var i = 0; i < activeBookmarkList.length; i++) {
            var activeBookmark = activeBookmarkList[i].parentElement,
              productId = activeBookmark.dataset.id;
            $(activeBookmark).replaceWith(bookmarkAction.init(productId, 0));
          }
          $this.replaceWith(bookmarkAction.init(productId, 1));

          Swal.fire({
            icon: 'success',
            title: '¡Listo!',
            text: `El producto '${product.product_name}' ha sido actualizado.`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
          dtProduct.draw();
        }
      })
    });
  }
});
