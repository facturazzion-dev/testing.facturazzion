@section('vendor-style')
<link rel="stylesheet" href="{{asset('vendors/css/pickers/flatpickr/flatpickr.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/forms/select/select2.min.css')}}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap5.min.css')) }}">
@endsection
@section('page-style')
<link rel="stylesheet" href="{{asset('css/base/plugins/forms/pickers/form-flat-pickr.css')}}">
<link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
<link rel="stylesheet" href="{{asset('css/base/pages/app-document.css')}}">
@endsection

<div class="card document-add">
    <div class="card-body document-preview-card">
        @if (isset($payment))
            @if ($payment->id != 0)
                {!! Form::model($payment, ['url' => $type . '/' . $payment->id, 'method' => 'put', 'files'=> true, 'id'=>'form']) !!}
            @else
                {!! Form::model($payment, ['url' => $type , 'method' => 'post', 'files'=> true, 'id'=>'form']) !!}
            @endif
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id'=>'form']) !!}
        @endif
        <div id="sendby_ajax"></div>
        <!-- select company/customer -->
        <div class="row">
            <div class="col-md-6">
                <div class="form-group required {{ $errors->has('company_id') ? 'has-error' : '' }}">
                    {!! Form::label('company_id', trans('quotation.company_id'), ['class' => 'form-label required']) !!}
                    {!! Form::select('company_id', isset($companies)?$companies:null, null, ['class' => 'form-select company', 'placeholder' => 'Seleccionar cliente']) !!}
                    <span class="help-block">{{ $errors->first('company_id', ':message') }}</span>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group required {{ $errors->has('payment_serie') ? 'has-error' : '' }}">
                    {!! Form::label('payment_serie', 'Serie', ['class' => 'form-label required']) !!}
                    {!! Form::text('payment_serie', isset($payment->payment_serie)?$payment->payment_serie:$payment_serie , ['class' => 'form-control']) !!}
                    <span class="help-block">{{ $errors->first('payment_serie', ':message') }}</span>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group required {{ $errors->has('payment_number') ? 'has-error' : '' }}">
                    {!! Form::label('payment_number', 'Folio', ['class' => 'form-label required']) !!}
                    {!! Form::number('payment_number', (isset($payment->payment_number) && $payment->payment_number != '')?$payment->payment_number:$payment_number , ['class' => 'form-control']) !!}
                    <span class="help-block">{{ $errors->first('payment_number', ':message') }}</span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                <div class="form-group required {{ $errors->has('payment_date') ? 'has-error' : '' }}">
                    {!! Form::label('payment_date', trans('invoice.payment_date'), ['class' => 'form-label required']) !!}
                    {!! Form::text('payment_date', isset($payment->payment_date)?$payment->payment_date:null , ['class' => 'form-control flatpickr-basic']) !!}
                    <span class="help-block">{{ $errors->first('payment_date', ':message') }}</span>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group required {{ $errors->has('payment_currency') ? 'has-error' : '' }}">
                    {!! Form::label('payment_currency', 'Moneda de pago', ['class' => 'form-label required']) !!}
                    {!! Form::select('payment_currency', array('MXN'=>'Peso mexicano', 'USD' => 'Dólar americano'), isset($payment->payment_currency)?$payment->payment_currency:'MXN' , ['class' => 'form-select select2 payment-select2']) !!}
                    <span class="help-block">{{ $errors->first('payment_currency', ':message') }}</span>
                </div>
            </div>
            <div class="col-lg-2 d-none" id="currency-group">
                <div class="form-group  {{ $errors->has('exchange_rate') ? 'has-error' : '' }}">
                    {!! Form::label('exchange_rate', 'Tipo de Cambio a MXN', ['class' => 'form-label ']) !!}
                    {!! Form::text('exchange_rate', isset($payment->exchange_rate)?$payment->exchange_rate:'1', ['onClick' => "SelectAll('exchange_rate')", 'class' => 'form-control']) !!}
                    <span class="help-block">{{ $errors->first('exchange_rate', ':message') }}</span>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group required {{ $errors->has('payment_type') ? 'has-error' : '' }}">
                    {!! Form::label('payment_type', 'Forma de pago', ['class' => 'form-label required']) !!}
                    {!! Form::select('payment_type', array(
                            '01'=>'Efectivo - 01',
                            '02'=>'Cheque nominativo - 02',
                            '03'=>'Transferencia electrónica de fondos - 03',
                            '04'=>'Tarjeta de crédito - 04',
                            '05'=>'Monedero electrónico - 05',
                            '06'=>'Dinero electrónico - 06',
                            '08'=>'Vales de despensa - 08',
                            '12'=>'Dación en pago - 12',
                            '13'=>'Pago por subrogación - 13',
                            '14'=>'Pago por consignación - 14',
                            '15'=>'Condonación - 15',
                            '17'=>'Compensación - 17',
                            '23'=>'Novación - 23',
                            '24'=>'Confusión - 24',
                            '25'=>'Remisión de deuda - 25',
                            '26'=>'Prescripción o caducidad - 26',
                            '27'=>'A satisfacción del acreedor - 27',
                            '28'=>'Tarjeta de débito - 28',
                            '29'=>'Tarjeta de servicios - 29',
                            '30'=>'Aplicación de anticipos - 30',
                            '31'=>'Intermediario pagos - 31',
                            '99'=>'Por definir - 99'),'01', ['class' => 'form-select select2 payment-select2']) !!}
                    <span class="help-block">{{ $errors->first('payment_type', ':message') }}</span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4">
                <div class="form-group  {{ $errors->has('companyBanks') ? 'has-error' : '' }}">
                    {!! Form::label('companyBanks', 'Cuenta Cliente', ['class' => 'form-label  ']) !!}
                    {!! Form::select('companyBanks', [], null, ['class' => 'form-select select2 payment-select2']) !!}
                    <span class="help-block">{{ $errors->first('companyBanks', ':message') }}</span>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group  {{ $errors->has('organizationBanks') ? 'has-error' : '' }}">
                    {!! Form::label('organizationBanks', 'Cuenta Beneficiario', ['class' => 'form-label  ']) !!}
                    {!! Form::select('organizationBanks', [], null, ['class' => 'form-select select2 payment-select2']) !!}
                    <span class="help-block">{{ $errors->first('organizationBanks', ':message') }}</span>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group  {{ $errors->has('transaction_number') ? 'has-error' : '' }}">
                    {!! Form::label('transaction_number', 'Número Operación', ['class' => 'form-label  ']) !!}
                    {!! Form::text('transaction_number', null, ['class' => 'form-control']) !!}
                    <span class="help-block">{{ $errors->first('transaction_number', ':message') }}</span>
                </div>
            </div>
        </div>
        <!-- Invoices selection starts -->
        <div id="invoices" class="card-body mt-2 document-product-details" style="display: none;">
            <div data-repeater-list="invoices">
                @if(isset($payment))
                    @foreach ($payment->paidInvoices as $ppd)
                        @include('content._partials._forms.form-cfdi-repeater', ['count' => $loop->index + 1])
                    @endforeach
                @else
                    @include('content._partials._forms.form-cfdi-repeater')
                @endif
            </div>
            <div class="row mt-1">
                <div class="col-12 pe-50 col-md-12 px-0">
                    <button type="button" class="btn btn-primary btn-sm btn-add-new" data-repeater-create>
                        <i data-feather="plus" class="me-25"></i>
                        <span class="align-middle">Agregar Factura</span>
                    </button>
                </div>
            </div>
        </div>
        <!-- Invoices selection ends -->
        <!-- Document Total starts -->
        <div class="card-body document-padding">
            <div class="row document-sales-total-wrapper">
                <div class="col-md-6 offset-md-6 d-flex justify-content-end order-md-2 order-1">
                    <div class="document-total-wrapper">
                        <div class="document-total-item">
                            <p class="document-total-title">Total:</p>
                            <p class="document-total-amount">$<span id="total">0</span></p>
                            <input type="hidden" name="total">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Document Total ends -->
        {!! Form::close() !!}
    </div>
</div>
@include('content._partials._modals.modal-send-rep')
@include('content._partials._modals.modal-preview-pdf')
@include('content._partials._modals.modal-wait-dialog')

@section('page-footer')
<footer class="footer footer-light footer-fixed">
  <p class="clearfix mb-0 d-flex align-items-center justify-content-md-end">
    <button type="button" id="rep-preview" class="btn btn-outline-primary ms-1">Previsualizar</button>
    <button type="button" id="rep-draft" class="btn btn-outline-primary ms-1 d-none">Guardar</button>
    <button type="button" id="rep-create" class="btn btn-primary ms-1">Crear</button>
  </p>
</footer>
@endsection

@section('vendor-script')
<script src="{{asset('vendors/js/extensions/moment.min.js')}}"></script>
<script src="{{ asset('vendors/js/forms/repeater/jquery.repeater.min.js') }}"></script>
<script src="{{ asset('vendors/js/forms/select/select2.full.min.js') }}"></script>
<script src="{{ asset(mix('vendors/js/forms/cleave/cleave.min.js')) }}"></script>
<script src="{{ asset('vendors/js/pickers/flatpickr/flatpickr.min.js') }}"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>

@endsection

@section('page-script')
<script>
    $(function () {
    'use strict';

    var assetPath = $('body').attr('data-asset-path'),
        basePath = assetPath + 'invoices_payment_log/';

    var companySelectInput = $("#company_id"),
        select = $('.payment-select2'),
        basicPickr = $('.flatpickr-basic'),
        invoiceRepeater = $('#invoices'),
        sendRepModal = $('#sendRepModal'),
        previewPdfModal = $('#previewPdfModal'),
        processingModal = $('#pleaseWaitDialog'),
        documentBlock = $('.document-preview-card');    
    
    select.each(function () {
        var $this = $(this);
        $this.wrap('<div class="position-relative"></div>');
        $this.select2({
        // the following code is used to disable x-scrollbar when click in select input and
        // take 100% width in responsive also
        dropdownAutoWidth: true,
        width: '100%',
        dropdownParent: $this.parent()
        });
    });

    companySelectInput.select2({
        placeholder: 'Seleccionar cliente'
    });

    if (basicPickr.length) {
        basicPickr.flatpickr({
            locale: 'es',
            defaultDate: 'today',
            maxDate: 'today',
            altInput: true,
            altFormat: "F j, Y",
            dateFormat: "Y-m-d",
        });
    }

    companySelectInput.change(function () {
        var selectedCompany = $(this).val(); // get selected value
        // resetRepeater();
        loadingInvoices(true);        
        getCompanyData(selectedCompany);
    });
    
    $('#payment_currency').change(function () {
        if($(this).val() == 'USD') {
            $('#exchange_rate').val('')
            $('#currency-group').removeClass('d-none');
            getExchangeRate('USD');
        } else {
            $('#currency-group').addClass('d-none');
            $('#exchange_rate').val('1')
        }
    });

    $(document).on('change', '.invoice_id', function (e) {
        var $this = $(this),
            selectedOption = $this.find(':selected'),
            $wrapper = $this.closest('.repeater-wrapper'),
            serieDiv = $wrapper.find('.serie'),
            numberDiv = $wrapper.find('.folio'),
            cfdiSerieInput = $wrapper.find('.cfdi_serie'),
            cfdiNumberInput = $wrapper.find('.cfdi_number'),
            uuidSatInput = $wrapper.find('.uuid_sat'),
            invoiceDateInput = $wrapper.find('.invoice_date'),
            finalPriceInput = $wrapper.find('.final_price'),
            currencyInput = $wrapper.find('.currency'),
            totalPaymentInput = $wrapper.find('.total_payment'),
            factionInput = $wrapper.find('.faction'),
            unpaidAmountInput = $wrapper.find('.unpaid_amount'),
            paidAmountInput = $wrapper.find('.paid_amount'),
            infoSection = $wrapper.find('.info_section'),
            hiddenFields = $wrapper.find('.hidden-field'),
            currencyObj = {
                'MXN': 'Peso mexicano',
                'USD': 'Dólar americano'
            };

        if(selectedOption.val() == 'otro') {
            infoSection.html('');
            hiddenFields.each(function () {
                $(this).removeClass('d-none');
            });
            serieDiv.attr('style','display: block');
            numberDiv.attr('style','display: block');
            cfdiSerieInput.val('');
            cfdiNumberInput.val('');
            uuidSatInput.attr("readOnly", false).val('');
            invoiceDateInput.attr("readOnly", false).val('');
            finalPriceInput.attr("readOnly", false).val('0');
            currencyInput.attr("disabled", false).val('MXN').change();
            totalPaymentInput.attr("readOnly", true).val('0');        
            factionInput.attr("readOnly", true).val('1');
            unpaidAmountInput.val('0');
            paidAmountInput.val('0');
        } else {
            var paymentCurrency = $('#payment_currency').val(),
                uuid = selectedOption.data('uuid'),
                date = selectedOption.data('date'),
                unpaid = selectedOption.data('unpaid'),
                paid = selectedOption.data('paid'),
                total = selectedOption.data('total'),
                currency = selectedOption.data('currency'),
                partiality = selectedOption.data('partiality'),
                rate = selectedOption.data('rate'),    

                cfdiInfoSection = 
                    `<div class="mt-2">
                        <h6 class="mb-2">Detalle de la factura:</h6>
                        <table>
                            <tbody>
                            <tr>
                                <td class="pe-1">Saldo pendiente:</td>
                                <td><strong>$${unpaid}</strong></td>
                            </tr>
                            <tr>
                                <td class="pe-1">Monto abonado:</td>
                                <td>$${paid}</td>
                            </tr>
                            <tr>
                                <td class="pe-1">Total Cfdi:</td>
                                <td>$${total} ${currency}</td>
                            </tr>
                            <tr>
                                <td class="pe-1">Moneda:</td>
                                <td>${currencyObj[currency]}</td>
                            </tr>
                            <tr>
                                <td class="pe-1">Fecha:</td>
                                <td>${date}</td>
                            </tr>
                            <tr>
                                <td class="pe-1">UUID:</td>
                                <td>${uuid}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>`;
    
            hiddenFields.each(function () {
                $(this).addClass('d-none');
            });
            infoSection.html(cfdiInfoSection);
            // serieDiv.attr('style','display: none');
            // numberDiv.attr('style','display: none');
            // uuidSatInput.attr("readOnly", true);
            // invoiceDateInput.attr("readOnly", true);
            // finalPriceInput.attr("readOnly", true);
            // currencyInput.attr("disabled", true);
            totalPaymentInput.attr("readOnly", false);
            factionInput.attr("readOnly", false);           

            // uuidSatInput.val(uuid);
            // invoiceDateInput.val(date);
            // unpaidAmountInput.val(unpaid);
            // paidAmountInput.val(paid);
            // finalPriceInput.val(total);
            // currencyInput.val(currency).change();                
            // //is same currency total payment is equal to the unpaid amount, else apply exchange rate to the unpaid amount
            totalPaymentInput.val(currency == paymentCurrency ? unpaid : unpaid * rate);
            if (typeof partiality != 'undefined') {
                factionInput.val(partiality);
            }                           
            
        }
        updateTotalPayment();  
    });

    $(document).on('keyup', '.total_payment', function (e) {
        updateTotalPayment();
    });
    $(document).on('keyup', '.final_price', function (e) {
        var $this = $(this),
            totalPaymentInput = $this.closest('.repeater-wrapper').find('.total_payment');
        
        totalPaymentInput.val($this.val());
        updateTotalPayment();
    });

    $('#rep-preview').click(function (e) {
        e.preventDefault();
        processingModal.modal('show');
        $(".input-disable").prop('disabled', false);
        var form = $("#form"),
            data = new FormData(form[0]);
        
        data.delete('_method');
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
        
    });

    $('#rep-draft').click(function (e) {
        // syncTotalValues();
        $("#form").append('<input type="hidden" name="status" value="Borrador" />');
        $("#form").submit();
    });

    $('#rep-create').click(function (e) {
        // syncTotalValues();
        processingModal.modal('show');
        $(".input-disable").prop('disabled', false);
        var form = $("#form"),
            url = form.attr("action"),
            data = new FormData(form[0]);
        
        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            cache: false,
            processData: false,
            contentType: false,
            success: function (data, textStatus, xhr) {
                var { payment_id } = data,
                    companyEmail = $('#email').val(),
                    iframePdfPath = basePath + payment_id + '/print_quot',
                    downloadPdfPath = basePath + payment_id + '/download_pdf',
                    downloadXmlPath = basePath + payment_id + '/download_xml';

                sendRepModal.find('hidden-document-id').val(payment_id);
                    $('#modalSendRepEmailTo').val(companyEmail);
                    $('#modalSendRepIframePdf').attr('src', iframePdfPath);
                    $('#modalSendRepDownloadPdf').attr('href', downloadPdfPath);
                    $('#modalSendRepDownloadXml').attr('href', downloadXmlPath);

                processingModal.modal('hide');
                sendRepModal.modal('show');
            },
            error: function (reject) {
                processingModal.modal('hide');
                if (reject.status === 400) {
                    var { message, validator } = reject.responseJSON;
                    displayError(validator);
                    alert(message + '\n\n' + validator.message);
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
        
    });    

    function displayError(validator) {
        if ($.isEmptyObject(validator) == false) {
            $.each(validator.fields, function (key, value) {
                $('#' + value)
                    .addClass('error')
            });
        }
    }

    function getExchangeRate(currency = 'USD') {
        var paymentDate = $("#payment_date").val(),
            dateObj = moment(paymentDate,'YYYY-MM-DD'),
            endDate = dateObj.format('DD-MM-YYYY'),
            initialDate = dateObj.subtract(1, 'days').format('DD-MM-YYYY');
            
        if (currency == 'USD') {
            fetch(`https://sidofqa.segob.gob.mx/dof/sidof/indicadores/158/${initialDate}/${endDate}`)
            .then(response => {
              if (!response.ok) {
                throw new Error(response.statusText);
              }
              return response.json();
            })
            .then((data) => {
                let {TotalIndicadores, ListaIndicadores} = data;
                $('#exchange_rate').val(ListaIndicadores[TotalIndicadores-1].valor);
            })
            .catch(error => {
                console.log(`Request failed: ${error}`);
            });            
        }
    }

    function getCompanyData(company) {
        $.ajax({
            type: "GET",
            url: "{{ url('company/ajax_company_data')}}",
            data: {'id': company, _token: '{{ csrf_token() }}'},
            success: function (company_data) {
                let {company} = company_data;
                paymentLog(company.id);
                // company's bank accounts 
                // $.each(company_data[0].banks_list, function(key, value) {   
                //         $('#companyBanks')
                //             .append($("<option></option>")
                //                     .attr("value",value.id)
                //                     .text(value.name + " - " + value.account_number)); 
                // });
            }
        });
    }        
    function paymentLog(company_id){
        $.ajax({
            type: "GET",
            url: "{{ url('invoices_payment_log/payment_logs')}}",
            data: {'id': company_id, _token: '{{ csrf_token() }}' },
            success: function (data) {                    
                populateCfdiSelect(data);
                loadingInvoices(false);
            }
        });
    }

    function initRepeater() {
        /* Initializes product repeater */
        invoiceRepeater.repeater({
            initEmpty: false,

            defaultValues: {
                //default values for product/service
                'faction': '1',
                'currency': 'MXN',
                'tipo_cambio_dr': '1',
                'total_payment': '0'
            },

            ready: function () {
                var cfdiSelect = $('.invoice_id'),
                    currencySelect = $('.currency');
                //init select for iedu complement
                cfdiSelect.select2({
                    placeholder: 'Folio de la factura',
                    containerCssClass: 'select-sm',                    
                    escapeMarkup: function (markup) {
                        return markup;
                    }, // let our custom formatter work
                    templateResult: formatInvoice,
                    templateSelection: formatInvoiceSelection,
                    // the following code is used to disable x-scrollbar when click in select input and
                    // take 100% width in responsive also
                    dropdownAutoWidth: true,
                    width: '100%',
                    dropdownParent: cfdiSelect.parent()
                });
                currencySelect.select2({
                    containerCssClass: 'select-sm'
                });
            },

            show: function () {
                var cfdiSelect = $(this).find('.invoice_id'),
                    currencySelect = $(this).find('.currency'),
                    counterBadge = $(this).find('.product-count'),
                    repeaterItems = $("div[data-repeater-item]");
                    
                $(this).slideDown();
                //re-init select for iedu complement
                cfdiSelect.select2({
                    placeholder: 'Folio de la factura',
                    containerCssClass: 'select-sm',
                    escapeMarkup: function (markup) {
                        return markup;
                    }, // let our custom formatter work
                    templateResult: formatInvoice,
                    templateSelection: formatInvoiceSelection,
                    // the following code is used to disable x-scrollbar when click in select input and
                    // take 100% width in responsive also
                    dropdownAutoWidth: true,
                    width: '100%',
                    dropdownParent: cfdiSelect.parent()
                });
                currencySelect.select2({
                    containerCssClass: 'select-sm'
                });
                
                counterBadge.text(repeaterItems.length);
            },

            hide: function (deleteElement) {
                if (confirm('¿Estás seguro que deseas quitar este CFDI?')) {
                    $(this).slideUp(deleteElement, function () {
                        $(this).remove();
                        //updates product count number
                        $('.product-count').each(function (index) {
                            var $this = $(this);
                            $this.text(index + 1);
                        })
                        updateTotalPayment();
                    });

                }
            }
        });
    }

    function populateCfdiSelect(invoices) {
        let options = '';
        $.each(invoices, function(index, invoice){
            options += 
            '<option value="' + invoice.id + '"' +
            'data-uuid="' + invoice.uuid_sat + '"' +
            'data-date="' + invoice.invoice_date + '"' +
            'data-unpaid="' + invoice.unpaid_amount + '"' +
            'data-paid="' + invoice.paid_amount + '"' +
            'data-total="' + invoice.final_price + '"' +
            'data-currency="' + invoice.currency + '"' +
            'data-partiality="' + ((typeof invoice['partiality'] != 'undefined') ? invoice['partiality'] : '1') + '"' +
            'data-rate="' + invoice.exchange_rate + '"' +
            '>' +
             invoice.invoice_serie + '' + invoice.invoice_number + 
            '</option>';
        });
        $('.invoice_id').each(function () {
            var $this = $(this);
            $this.append(options);
        });
        initRepeater();
        invoiceRepeater.attr('style','display: block');
        // $.each(company_data[0].banks_list, function(key, value) {   
        //         $('#companyBanks')
        //             .append($("<option></option>")
        //                     .attr("value",value.id)
        //                     .text(value.name + " - " + value.account_number)); 
        // });
    }

    function resetRepeater() {
        $('[data-repeater-list]').empty();
        $('[data-repeater-create]').click();
    }

    function formatInvoice(invoice) {
        var originalOption = invoice.element;
        if (invoice.id == 'otro') {
            return invoice.text;
        }
        var unpaid = $(originalOption).data('unpaid');
            
        var markup =
        "<div class='d-flex align-items-center justify-content-between'>" +
        "<div class='d-flex justify-content-start align-items-center'>" +
        invoice.text +
        '</div>' +
        "<div class='text-warning float-end'>Saldo: $" +
        unpaid +
        '</div></div>';

      return markup;
    }

    function updateTotalPayment() {
        $('#total').val(0);
        var total_payment = 0;

        $('.total_payment').each(function () {
            total_payment += parseFloat($(this).val());
            $('#total').text(total_payment.toFixed(2));                
            $('input[name^="total"]').val(total_payment.toFixed(2));
        });
    }

    function formatInvoiceSelection(invoice) {
        return invoice.text;
    }
    function loadingInvoices(state) {
        if(state) {
            documentBlock.block({
                message:
                '<div class="d-flex justify-content-center align-items-center"><p class="me-50 mb-0">Cargando facturas PPD del cliente con saldo pendiente de pago...</p> <div class="spinner-grow spinner-grow-sm text-white" role="status"></div> </div>',
                css: {
                    backgroundColor: 'transparent',
                    color: '#fff',
                    border: '0'
                },
                overlayCSS: {
                    opacity: 0.5
                }
            });
        } else {
            documentBlock.unblock();            
        }
    }
    

    /*Initilizes invoice totals on page load*/
    // $('.final_price').keyup();
    /*Initilizes customer details if the field has value on page load*/
    if (companySelectInput.val() != "") {
        initRepeater();
        invoiceRepeater.attr('style','display: block');
        $('.invoice_id').trigger('change');
    }
});
</script>
@endsection