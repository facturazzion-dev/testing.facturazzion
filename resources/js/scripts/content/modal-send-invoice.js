'use strict';

var sendInvoiceForm = $('#sendInvoiceForm');

if ($('body').attr('data-framework') === 'laravel') {
    var assetPath = $('body').attr('data-asset-path'),
        basePath = assetPath + 'invoice/';
}

sendInvoiceForm.on('submit', function (e) {
    e.preventDefault();

    sendInvoiceForm.block({
        message:
            '<div class="d-flex justify-content-center align-items-center"><p class="me-50 mb-0" text-primary>Enviando Factura...</p> <div class="spinner-grow spinner-grow-sm text-primary" role="status"></div> </div>',
        css: {
            backgroundColor: 'transparent',
            border: '0'
        },
        overlayCSS: {
            backgroundColor: '#fff',
            opacity: 0.8
        }
    });
    $.ajax({
        type: "POST",
        url: basePath + 'send_cfdi',
        data: sendInvoiceForm.serialize(),
        success: function (msg) {
            sendInvoiceForm.block({
                message: '<div class="p-1 bg-primary">' + msg + '</div>',
                timeout: 2000,
                css: {
                    backgroundColor: 'transparent',
                    color: '#fff',
                    border: '0'
                },
                overlayCSS: {
                    opacity: 0.25
                }
            });
        },
        error: function (e) {
            sendInvoiceForm.block({
                message: '<div class="p-1 bg-danger">' + e + '</div>',
                timeout: 2000,
                css: {
                    backgroundColor: 'transparent',
                    color: '#fff',
                    border: '0'
                },
                overlayCSS: {
                    opacity: 0.25
                }
            });
        }
    });
});