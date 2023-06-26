$(function () {
    'use strict';

    var assetPath = $('body').attr('data-asset-path'),
        basePath = assetPath + 'quotation/';

    var selected_company = '',
        companySelectInput = $("#company_id"),
        companyFormSection = $("#more_info"),
        CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content'),
        sendQuotationModal = $('#sendQuotationModal'),
        previewPdfModal = $('#previewPdfModal'),
        processingModal = $('#pleaseWaitDialog'),
        productRepeater = $('#products');

    /* Initializes product repeater */
    productRepeater.repeater({
        initEmpty: false,

        defaultValues: {
            //default values for product/service
            'clave_sat': '01010101',
            'clave_unidad_sat': 'E48',
            'description': 'No existe en el catálogo',
            'unidad_sat': 'Unidad de servicio',
            'quantity': '1',
            'price': '0',
            'discount': '0',
            'total_amount': '0'
            //default values for iedu
        },

        ready: function () {
            var numeralMask = $('.numeral-mask'),
                select2 = $('.taxes'),
                selectSm = $('.select2-size-sm');
            // Init select2
            select2.select2({
                placeholder: 'Impuestos...',
                containerCssClass: 'select-sm'
            });
            //set select2 default value if empty
            if(select2.find(':selected').length == 0 && select2.find('option').length > 1) {
                select2.val(select2.find('option')[1].value).change();
              }
            //init select for iedu complement
            selectSm.each(function () {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>');
                $this.select2({
                    dropdownAutoWidth: true,
                    dropdownParent: $this.parent(),
                    width: '100%',
                    containerCssClass: 'select-sm'
                });
            });
            if (numeralMask.length) {
                new Cleave(numeralMask, {
                    numeral: true,
                    numeralThousandsGroupStyle: 'thousand'
                });
            }

        },

        show: function () {
            var numeralMask = $(this).find('.numeral-mask'),
                select2 = $(this).find('.taxes'),
                selectSm = $(this).find('.select2-size-sm'),
                counterBadge = $(this).find('.product-count'),
                repeaterItems = $("div[data-repeater-item]");

            $(this).slideDown();
            select2.select2({
                placeholder: 'Impuestos...',
                containerCssClass: 'select-sm'
            });
            //set select2 default value if empty
            if(select2.find(':selected').length == 0 && select2.find('option').length > 1) {
                select2.val(select2.find('option')[1].value).change();
              }
            //re-init select for iedu complement
            selectSm.each(function () {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>');
                $this.select2({
                    dropdownAutoWidth: true,
                    dropdownParent: $this.parent(),
                    width: '100%',
                    containerCssClass: 'select-sm'
                });
            });
            numeralMask.each(function () {
                var $this = $(this);
                new Cleave($this, {
                    numeral: true,
                    numeralThousandsGroupStyle: 'thousand'
                });
            });
            counterBadge.text(repeaterItems.length);
        },

        hide: function (deleteElement) {
            if (confirm('¿Estás seguro que deseas quitar este concepto?')) {
                $(this).slideUp(deleteElement, function () {
                    $(this).remove();
                    //updates product count number
                    $('.product-count').each(function (index) {
                        var $this = $(this);
                        $this.text(index + 1);
                    })
                });

            }
        }
    });
    /* Updates total amounts */
    $(document).on('keyup', '.quantity, .price, .discount', function (e) {
        var $this = $(this),
            quantityInput = $this.closest('.repeater-wrapper').find('.quantity'),
            priceInput = $this.closest('.repeater-wrapper').find('.price'),
            totalAmount = $this.closest('.repeater-wrapper').find('.total_amount');

        if (totalAmount.val() !== null) {
            var price = parseFloat(priceInput.val()),
                quantity = parseFloat(quantityInput.val()),
                finalValue = quantity * price;
            totalAmount.val(finalValue.toFixed(6));
            update_total_price();
        }
    });
    $(document).on('change', '.taxes', function (e) {
        update_total_price();
    });

    companySelectInput.select2({
        placeholder: 'Seleccionar cliente'
    });

    companySelectInput.prepend('<option value="new"> --- Crear Nuevo Cliente --- </option>');
    companySelectInput.change(function () {
        selected_company = $(this).val(); // get selected value
        $('.collapse').collapse('show');
        if (selected_company == 'new') {
            $(".input-disable").prop('disabled', false);
            $(".input-disable").val('');
            $("#ModifyCompany").html('Guardar cambios');
            $("#ModifyCompany").val('save');
            $("#CancelCompany").show();

        } else {
            getCompanyData($(this).val());
            $(".input-disable").prop('disabled', true);
            $("#ModifyCompany").html('Editar');
            $("#ModifyCompany").val('edit');
            $("#CancelCompany").hide();
        }
    });
    $('#ModifyCompany').click(function (e) {
        if ($(this).val() == 'save') {
            $(".input-disable").prop('disabled', true);
            $("#CancelCompany").hide();
            $("#ModifyCompany").html('Editar');
            $("#ModifyCompany").val('edit');
            //TODO: Save company's data in DB
            saveCompanyData();
        } else {
            $(".input-disable").prop('disabled', false);
            $("#CancelCompany").show();
            $("#ModifyCompany").html('Guardar cambios');
            $("#ModifyCompany").val('save');
        }
    });
    $('#CancelCompany').click(function (e) {
        companySelectInput.val(selected_company).change();
    });
    
    $("#currency").change(function () {
        if ($(this).val() == "USD") {
            $("#currency-group").removeClass('d-none');
        } else {
            $("#currency-group").addClass('d-none');
        }
    });
    $('#iva_toggle').change(function () {
        update_total_price();
    });    

    $('#quotation-preview').click(function (e) {
        e.preventDefault();
        syncTotalValues();
        processingModal.modal('show');
        $(".input-disable").prop('disabled', false);
        var form = $("#form"),
            data = new FormData(form[0]),            
            iva_toggle = $('#iva_toggle').prop("checked");
        
        data.delete('_method');
        if (iva_toggle) {
            set_product_iva_price();
        }
        $.ajax({
            url: basePath + 'preview',
            type: 'POST',
            data: data,
            cache: false,
            processData: false,
            contentType: false,
            success: function (data, textStatus, xhr) {
                var { base64Pdf } = data;

                $('#modalPreviewIframePdf').attr('src', base64Pdf);
                processingModal.modal('hide');
                previewPdfModal.modal('show');
            },
            error: function (reject) {
                processingModal.modal('hide');
                if (reject.status === 400) {
                    var error = reject.responseJSON.message;
                    alert(error);
                } else if (reject.status === 422) {
                    var errors = reject.responseJSON;
                    if ($.isEmptyObject(errors) == false) {
                        $.each(errors.errors, function (key, value) {
                            $('#' + key)
                                .closest('.form-group')
                                .addClass('has-error')
                                .append('<span class="help-block"><strong>' + value + '</strong></span>');
                        });
                    }
                }
            }
        });
        $(".input-disable").prop('disabled', true);
        if ($('#iva_toggle').prop("checked")) {
            last_product_iva_price();
        }
    });
    $('#quotation-draft').click(function (e) {
        syncTotalValues();
        $("#form").append('<input type="hidden" name="status" value="Borrador" />');
        $("#form").submit();
    });
    $('#quotation-create').click(function (e) {
        syncTotalValues();
        processingModal.modal('show');
        $(".input-disable").prop('disabled', false);
        var form = $("#form"),
            url = form.attr("action"),
            data = new FormData(form[0]),
            iva_toggle = $('#iva_toggle').prop("checked");
        /* Informar si un producto esta agotado. */
        // 1. Saber que productos estoy facturando.
        // 2. Ajax.
        // 3. Mostrar resultados.
        /* Fin de informar si un producto esta agotado. */
        if (iva_toggle) {
            set_product_iva_price();
        }
        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            cache: false,
            processData: false,
            contentType: false,
            success: function (data, textStatus, xhr) {
                var { quotation_id } = data,
                    companyEmail = $('#email').val(),
                    iframePdfPath = basePath + quotation_id + '/print_quot',
                    downloadPdfPath = basePath + quotation_id + '/download_pdf';

                $('#modalSendQuotationId').val(quotation_id);
                $('#modalSendQuotationEmailTo').val(companyEmail);
                $('#modalSendQuotationIframePdf').attr('src', iframePdfPath);
                $('#modalSendQuotationDownloadPdf').attr('href', downloadPdfPath);

                processingModal.modal('hide');
                sendQuotationModal.modal('show');
            },
            error: function (reject) {
                processingModal.modal('hide');
                if (reject.status === 400) {
                    var error = reject.responseJSON.message;
                    alert(error);
                } else if (reject.status === 422) {
                    var errors = reject.responseJSON;
                    if ($.isEmptyObject(errors) == false) {
                        $.each(errors.errors, function (key, value) {
                            $('#' + key)
                                .closest('.form-group')
                                .addClass('has-error')
                                .append('<span class="help-block"><strong>' + value + '</strong></span>');
                        });
                    }
                }
            }
        });
        $(".input-disable").prop('disabled', true);
        if ($('#iva_toggle').prop("checked")) {
            last_product_iva_price();
        }
    });
    
    /*Initilizes quotation totals on page load*/
    $('.price').keyup();
    /*Initilizes customer details if the field has value on page load*/
    if (companySelectInput.val() != "") {
        companySelectInput.trigger('change');
    }

    sendQuotationModal.on('hide.bs.modal', function () {
        location.href = basePath + 'create';
    });

    function syncTotalValues() {
        $('input[name=subtotal]').val($('#subtotal').text());
        $('input[name=total_discount]').val($('#total_discount').text());
        $('input[name=tax_iva_tra]').val($('#tax_iva_tra').text());
        $('input[name=tax_ieps_tra]').val($('#tax_ieps_tra').text());
        $('input[name=tax_iva_ret]').val($('#tax_iva_ret').text());
        $('input[name=tax_ieps_ret]').val($('#tax_ieps_ret').text());
        $('input[name=tax_isr_ret]').val($('#tax_isr_ret').text());
        $('input[name=total]').val($('#total').text());
    }
    function insufficientProducts(number, total) {
        if (total == 0) {
            title = "Producto agotado.";
        } else {
            title = "Productos insuficientes en stock.";
        }
        var msg = "Su stock quedará en: " + total;
        $("#quantity" + number).popover({
            title: title,
            content: msg,
            placement: 'bottom',
            trigger: 'hover'
        });
        setTimeout(function () {
            $("#quantity" + number).popover('dispose');
            $("#quantity" + number).popover({
                title: title,
                content: msg,
                placement: 'bottom',
                trigger: 'hover'
            });
            //$('input').attr('data-content', msg);
            //$('input').attr('data-original-title', title);
            //var popover = $('input').data('popover');
            //popover.setContent();
        });
    }
    function last_product_iva_price() {
        $('.price').each(function (index) {
            $(this).val(_all_prices[index]);
        });
    }
    function set_product_iva_price() {
        var is_included = $('#iva_toggle').prop("checked");
        $('.total_amount').each(function () {
            var $this = $(this),
                quantityInput = $this.closest('.repeater-wrapper').find('.quantity'),
                priceInput = $this.closest('.repeater-wrapper').find('.price'),
                quantity = quantityInput.val();
            quantity = parseFloat(quantityInput.val());
            total = parseFloat($this.val());
            price = parseFloat(total / quantity);
            if (is_included) {
                priceInput.val(price.toFixed(6));
            }
        });
    }
    function update_total_price() {
        var isIvaIncluded = $('#iva_toggle').prop("checked"),
            //Computing variables
            subtotalSum = 0,
            discountSum = 0,
            ivaTraTaxSum = 0,
            iepsTraTaxSum = 0,
            iepsRetTaxSum = 0,
            isrRetTaxSum = 0,
            ivaRetTaxSum = 0,
            grandTotal = 0,
            //Labels for displaying the results
            subtotalLabel = $('#subtotal'),
            discountLabel = $('#total_discount'),
            taxIvaTraLabel = $('#tax_iva_tra'),
            taxIepsTraLabel = $('#tax_ieps_tra'),
            taxIepsRetLabel = $('#tax_ieps_ret'),
            taxIsrRetLabel = $('#tax_isr_ret'),
            taxIvaRetLabel = $('#tax_iva_ret'),
            totalLabel = $('#total');

        $('.total_amount').each(function () {
            var $this = $(this),
                quantityInput = $this.closest('.repeater-wrapper').find('.quantity'),
                priceInput = $this.closest('.repeater-wrapper').find('.price'),
                discountInput = $this.closest('.repeater-wrapper').find('.discount'),
                taxesInput = $this.closest('.repeater-wrapper').find('.taxes'),
                stringTaxesArray = taxesInput.val().toString().split(","),
                quantity = parseFloat(quantityInput.val()),
                price = parseFloat(priceInput.val()),
                discount = parseFloat(discountInput.val() / 100),
                /* We re-calculate the total amount using quantity and price
                because when iva is included, its value is updated, therefore
                it becomes dirty for future updates of its own value.
                */
                total = quantity * price;

            for (var index = stringTaxesArray.length - 1; index >= 0; index--) {
                /* stringTax: '803_002_Traslado_0.16_Tasa'                     
                    taxArray[0] = 803
                    taxArray[1] = 002
                    taxArray[2] = Traslado
                    taxArray[3] = 0.16
                    taxArray[4] = Tasa
                */
                var taxArray = stringTaxesArray[index].split("_"),
                    tax = parseFloat(taxArray[3]);

                if ((taxArray[4] == 'Tasa') || (taxArray[4] == 'Exento')) {
                    var taxAmount = tax * total * (1 - discount);
                    switch (taxArray[1]) {
                        case '001'://ISR
                            isrRetTaxSum += taxAmount;
                            break;
                        case '002'://IVA
                            if (taxArray[2] == 'Traslado') {
                                if (isIvaIncluded) {
                                    //divide total by tax to update new total amount
                                    total = total * (1 - discount) / (1 + tax);
                                    taxAmount = tax * total;
                                    ivaTraTaxSum += taxAmount;
                                    $this.val(total.toFixed(6));
                                } else {
                                    ivaTraTaxSum += taxAmount;
                                }
                            } else {
                                ivaRetTaxSum += taxAmount;
                            }
                            break;
                        case '003'://IEPS
                            if (taxArray[2] == 'Traslado') {
                                iepsTraTaxSum += taxAmount;
                            } else {
                                iepsRetTaxSum += taxAmount;
                            }
                            break;
                        default:
                            break;
                    }
                }
            }
            subtotalSum += total;
            discountSum += total * discount;
        });

        subtotalSum = roundNumber2td(subtotalSum, 2);
        discountSum = roundNumber2td(discountSum, 2);
        ivaTraTaxSum = roundNumber2td(ivaTraTaxSum, 2);
        iepsTraTaxSum = roundNumber2td(iepsTraTaxSum, 2);
        iepsRetTaxSum = roundNumber2td(iepsRetTaxSum, 2);
        isrRetTaxSum = roundNumber2td(isrRetTaxSum, 2);
        ivaRetTaxSum = roundNumber2td(ivaRetTaxSum, 2);
        grandTotal = roundNumber2td(subtotalSum
            - discountSum
            + ivaTraTaxSum
            + iepsTraTaxSum
            - iepsRetTaxSum
            - isrRetTaxSum
            - ivaRetTaxSum, 2);

        subtotalLabel.text(subtotalSum);
        discountLabel.text(discountSum);
        taxIvaTraLabel.text(ivaTraTaxSum);
        taxIepsTraLabel.text(iepsTraTaxSum);
        taxIepsRetLabel.text(iepsRetTaxSum);
        taxIsrRetLabel.text(isrRetTaxSum);
        taxIvaRetLabel.text(ivaRetTaxSum);
        totalLabel.text(grandTotal);

        updateVisibility(taxIvaTraLabel);
        updateVisibility(taxIepsTraLabel);
        updateVisibility(taxIepsRetLabel);
        updateVisibility(taxIsrRetLabel);
        updateVisibility(taxIvaRetLabel);
    }
    function updateVisibility(listener) {
        if (listener.text() == '0') {
            listener.closest('.document-total-item').addClass('d-none');
        } else {
            listener.closest('.document-total-item').removeClass('d-none');
        }
    }
    function saveCompanyData() {
        companyFormSection.block({
            message:
                '<div class="d-flex justify-content-center align-items-center"><p class="me-50 mb-0">Guardando cambios...</p> <div class="spinner-grow spinner-grow-sm text-white" role="status"></div> </div>',
            css: {
                backgroundColor: 'transparent',
                color: '#fff',
                border: '0'
            },
            overlayCSS: {
                opacity: 0.5
            }
        });
        $.ajax({
            type: "POST",
            url: "/company/create",
            data: {
                '_token': CSRF_TOKEN,
                'id': companySelectInput.val(),
                'name': $('#company_name').val(),
                'phone': $('#phone').val(),
                'sat_name': $('#sat_name').val(),
                'sat_rfc': $('#sat_rfc').val(),
                'email': $('#email').val(),
                'street': $('#street').val(),
                'exterior_no': $('#exterior_no').val(),
                'interior_no': $('#interior_no').val(),
                'suburb': $('#suburb').val(),
                'zip_code': $('#zip_code').val(),
                'country_id': $('#country_id').val(),
                'state_id': $('#state_id').val(),
                'city_id': $('#city_id').val(),
                'payment_method': $('#payment_method').val(),
                'payment_type': $('#payment_type').val(),
                'cfdi_use': $('#cfdi_use').val(),
                'fiscal_regimen': $('#fiscal_regimen').val(),
            },
            success: function (msg) {
                companyFormSection.block({
                    message: '<div class="p-1 bg-success">Guardado</div>',
                    timeout: 500,
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
                companyFormSection.block({
                    message: '<div class="p-1 bg-danger">Algo salió mal</div>',
                    timeout: 500,
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
    }
    function getCompanyData(id) {
        companyFormSection.block({
            message: '<div class="spinner-border text-primary" role="status"></div>',
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
            type: "GET",
            url: '/company/ajax_company_data',
            data: { 'id': id },
            success: function (response) {
                var { company } = response;
                if (company.country_id) {
                    $('#country_id').val(company.country_id).change();
                    if (company.state_id) {
                        $('#state_id').val(company.state_id).change();
                        if (company.city_id) {
                            $('#city_id').val(company.city_id).change();
                        }
                    }
                }
                // company's information
                $('#company_name').val(company.name);
                $('#phone').val(company.phone);
                $('#sat_name').val(company.sat_name);
                $('#sat_rfc').val(company.sat_rfc);
                $('#email').val(company.email);
                $('#street').val(company.street);
                $('#exterior_no').val(company.exterior_no);
                $('#interior_no').val(company.interior_no);
                $('#suburb').val(company.suburb);
                $('#zip_code').val(company.zip_code);

                // company's preferences
                $('#payment_method').val(company.payment_method).change();
                $('#payment_type').val(company.payment_type).change();
                $('#cfdi_use').val(company.cfdi_use).change();
                $('#fiscal_regimen').val(company.fiscal_regimen).change();

                if ($("#ModifyCompany").val() == 'edit') {
                    $(".input-disable").prop('disabled', true);
                }
                companyFormSection.unblock();
            }
        });
    }
    function roundNumber2td(num, scale) {
        if (!("" + num).includes("e")) {
            return +(Math.round(num + "e+" + scale) + "e-" + scale);
        } else {
            var arr = ("" + num).split("e");
            var sig = ""
            if (+arr[1] + scale > 0) {
                sig = "+";
            }
            return +(Math.round(+arr[0] + "e" + sig + (+arr[1] + scale)) + "e-" + scale);
        }
    }
    function plusSize(x) {
        $('#description' + x).on('keydown', autosize);

        function autosize() {
            var el = this;
            setTimeout(function () {
                el.style.cssText = 'height:auto; padding:0';
                el.style.cssText = 'height:' + el.scrollHeight + 'px';
            }, 0);

            $.ajax({
                type: "GET",
                url: "{{ url('product/products_autocomplete')}}",
                data: { 'searchable_product_name': $('#description' + x).val(), _token: '{{ csrf_token() }}' },
                success: function (data) {
                    console.log(data);
                    $('#product_data' + x).fadeIn();
                    $('#product_data' + x).html(data);
                }
            });
        }
    }    
    function selectAll(id) {
        var no_quantity = $("#" + id).val();
        var number = id.substr(-1);
        var available = $("#quantity_available" + number).val();
        if (available != "") {
            if (no_quantity > (available - 1)) {
                var total = available - no_quantity;
                insufficientProducts(number, total);
            } else {
                $("#" + id).popover('dispose');
            }
        }
        document.getElementById(id).focus();
        document.getElementById(id).select();
    }
});