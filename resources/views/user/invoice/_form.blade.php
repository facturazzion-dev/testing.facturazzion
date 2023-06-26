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
    @if (isset($invoice))
    @if ($invoice->id != 0)
    {!! Form::model($invoice, ['url' => $type . '/' . $invoice->id, 'method' => 'put', 'files'=> true, 'id'=>'form']) !!}
    @else
    {!! Form::model($invoice, ['url' => 'invoice' , 'method' => 'post', 'files'=> true, 'id'=>'form']) !!}
    @endif
    @else
    {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id'=>'form']) !!}
    @endif
    <div id="sendby_ajax"></div>
    <!-- select company/customer -->
    <div class="row">
      <div class="col-md-6">
        <div class="form-group required {{ $errors->has('company_id') ? 'has-error' : '' }}">
          {!! Form::label('company_id', trans('invoice.company_id'), ['class' => 'form-label required']) !!}
          {!! Form::select('company_id', isset($companies)?$companies:null, $invoice->company_id ?? $company_fav ?? null, ['class' => 'form-select company', 'placeholder' => 'Seleccionar cliente']) !!}
          <span class="help-block">{{ $errors->first('company_id', ':message') }}</span>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group required {{ $errors->has('invoice_serie') ? 'has-error' : '' }}">
          {!! Form::label('invoice_serie', 'Serie', ['class' => 'form-label required']) !!}
          {!! Form::text('invoice_serie', isset($invoice->invoice_serie)?$invoice->invoice_serie:$invoice_serie , ['class' => 'form-control']) !!}
          <span class="help-block">{{ $errors->first('invoice_serie', ':message') }}</span>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group required {{ $errors->has('invoice_number') ? 'has-error' : '' }}">
          {!! Form::label('invoice_number', 'Folio', ['class' => 'form-label required']) !!}
          {!! Form::number('invoice_number', (isset($invoice->invoice_number) && $invoice->invoice_number != '')?$invoice->invoice_number:$invoice_number , ['class' => 'form-control']) !!}
          <span class="help-block">{{ $errors->first('invoice_number', ':message') }}</span>
        </div>
      </div>
    </div>
    <!-- company/customer details -->
    <div id="more_info" class="collapse collapse_info bg-light-secondary position-relative rounded p-2 m-1">
      <!-- begin company's info -->
      <div id="ModifyCompanyButtons" class="mb-2">
        <button type="button" class="ModifyCompany btn btn-sm btn-primary"></button>
        <button type="button" class="CancelCompany btn btn-sm btn-outline-secondary">Descartar</button>
      </div>
      @include('content._partials._forms.form-company')
      <div id="ModifyCompanyButtons" class="mt-2">
        <button type="button" class="ModifyCompany btn btn-sm btn-primary"></button>
        <button type="button" class="CancelCompany btn btn-sm btn-outline-secondary">Descartar</button>
      </div>
      <!-- end company's info -->
    </div>
    <div class="row">
      <div class="col-lg-3">
        <div class="form-group required {{ $errors->has('currency') ? 'has-error' : '' }}">
          {!! Form::label('currency', 'Moneda', ['class' => 'form-label required']) !!}
          {!! Form::select('currency', array('MXN'=>'Peso mexicano', 'USD' => 'Dólar americano'), isset($invoice->currency)?$invoice->currency:'MXN' , ['class' => 'form-select']) !!}
          <span class="help-block">{{ $errors->first('currency', ':message') }}</span>
        </div>
      </div>
      <div class="col-lg-2 d-none" id="currency-group">
        <div class="form-group  {{ $errors->has('exchange_rate') ? 'has-error' : '' }}">
          {!! Form::label('exchange_rate', 'Tipo de Cambio', ['class' => 'form-label ']) !!}
          {!! Form::text('exchange_rate', isset($invoice->exchange_rate)?$invoice->exchange_rate:'1', ['onClick' => "SelectAll('exchange_rate')", 'class' => 'form-control']) !!}
          <span class="help-block">{{ $errors->first('exchange_rate', ':message') }}</span>
        </div>
      </div>
      <div class="col-lg-3">
        <div class="form-group  {{ $errors->has('payment_term') ? 'has-error' : '' }}">
          {!! Form::label('payment_term', 'Condiciones de Pago', ['class' => 'form-label']) !!}
          <select name="payment_term" class="form-select" id="payment_term">
            <option value="0 {{trans('invoice.days')}}" @if(isset($invoice) && $invoice->payment_term.' '.trans('invoice.days')==0) selected @endif>{{trans('invoice.immediate_payment')}}</option>
            @if($payment_term1!='0' && $payment_term1!='')
            <option value="{{$payment_term1.' '.trans('invoice.days')}}" @if(isset($invoice) && $payment_term1.' '.trans(' invoice.days')==$invoice->payment_term) selected @endif>{{$payment_term1}} {{trans('invoice.days')}}</option>
            @endif
            @if($payment_term2!='0' && $payment_term2!='')
            <option value="{{$payment_term2.' '.trans('invoice.days')}}" @if(isset($invoice) && $payment_term2.' '.trans(' invoice.days')==$invoice->payment_term) selected @endif>{{$payment_term2}} {{trans('invoice.days')}}</option>
            @endif
            @if($payment_term3!='0' && $payment_term3!='')
            <option value="{{$payment_term3.' '.trans('invoice.days')}}" @if(isset($invoice) && $payment_term3.' '.trans(' invoice.days')==$invoice->payment_term) selected @endif>{{$payment_term3}} {{trans('invoice.days')}}</option>
            @endif
          </select>
          <span class="help-block">{{ $errors->first('payment_term', ':message') }}</span>
        </div>
      </div>
    </div>
    <!-- Product Details starts -->
    <div id="products" class="card-body mt-2 document-product-details">
      <div class="d-flex justify-content-end align-items-center mb-1">
        <label class="document-terms-title pe-1" for="iva_toggle">Precios con IVA incluido</label>
        <div class="form-check form-switch">
          <input type="checkbox" class="form-check-input" name="iva_toggle" id="iva_toggle" />
          <label class="form-check-label" for="iva_toggle"></label>
        </div>
      </div>
      <div data-repeater-list="products">
        @if(isset($invoice))
          @foreach ($invoice->products as $product)
            @include('content._partials._forms.form-product-repeater', [ 'docTaxes' => $invoice->taxes, 'backup_product' => $product, 'product' => $product->pivot, 'count' => $loop->index + 1])
          @endforeach
        @else
          @include('content._partials._forms.form-product-repeater', [ 'product' => $product_fav ])
        @endif
      </div>
      <div class="row mt-1">
        <div class="col-12 pe-50 col-md-12 px-0">
          <button type="button" class="btn btn-primary btn-sm btn-add-new" data-repeater-create>
            <i data-feather="plus" class="me-25"></i>
            <span class="align-middle">Agregar Concepto</span>
          </button>
        </div>
      </div>
    </div>
    <!-- Product Details ends -->
    <!-- Document Total starts -->
    <div class="card-body document-padding">
      <div class="row document-sales-total-wrapper">
        <div class="col-md-6 order-md-1 order-2 mt-md-0 mt-3">
          <div class="d-flex align-items-center mb-1">
            <label for="terms_and_conditions" class="form-label fw-bold">Comentarios:</label>
            {!! Form::textarea('terms_and_conditions', isset($invoice->terms_and_conditions)?$invoice->terms_and_conditions:null , ['class' => 'form-control', 'rows' => '2']) !!}
          </div>
        </div>
        <div class="col-md-6 d-flex justify-content-end order-md-2 order-1">
          <div class="document-total-wrapper">
            <div class="document-total-item">
              <p class="document-total-title">{{ trans('invoice.subtotal') }}:</p>
              <p class="document-total-amount">$<span id="subtotal">0</span></p>
              <input type="hidden" name="subtotal">
            </div>
            <div class="document-total-item">
              <p class="document-total-title">{{ trans('invoice.total_discount') }}:</p>
              <p class="document-total-amount">$<span id="total_discount">0</span></p>
              <input type="hidden" name="total_discount">
            </div>
            <div class="document-total-item d-none">
              <p class="document-total-title">{{ trans('invoice.tax_iva_tra') }}:</p>
              <p class="document-total-amount">$<span id="tax_iva_tra">0</span></p>
              <input type="hidden" name="tax_iva_tra">
            </div>
            <div class="document-total-item d-none">
              <p class="document-total-title">{{ trans('invoice.tax_ieps_tra') }}:</p>
              <p class="document-total-amount">$<span id="tax_ieps_tra">0</span></p>
              <input type="hidden" name="tax_ieps_tra">
            </div>
            <div class="document-total-item d-none">
              <p class="document-total-title">{{ trans('invoice.tax_iva_ret') }}:</p>
              <p class="document-total-amount">$<span id="tax_iva_ret">0</span></p>
              <input type="hidden" name="tax_iva_ret">
            </div>
            <div class="document-total-item d-none">
              <p class="document-total-title">{{ trans('invoice.tax_ieps_ret') }}:</p>
              <p class="document-total-amount">$<span id="tax_ieps_ret">0</span></p>
              <input type="hidden" name="tax_ieps_ret">
            </div>
            <div class="document-total-item d-none">
              <p class="document-total-title">{{ trans('invoice.tax_isr_ret') }}:</p>
              <p class="document-total-amount">$<span id="tax_isr_ret">0</span></p>
              <input type="hidden" name="tax_isr_ret">
            </div>
            <hr class="my-50" />
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

@include('content._partials._modals.modal-clave-sat-table')
@include('content._partials._modals.modal-clave-unidad-sat-table')
@include('content._partials._modals.modal-product-table')
@include('content._partials._modals.modal-add-product')
@include('content._partials._modals.modal-wait-dialog')
@include('content._partials._modals.modal-send-invoice')
@include('content._partials._modals.modal-preview-pdf')

@section('page-footer')
<footer class="footer footer-light footer-fixed">
  <p class="clearfix mb-0 d-flex align-items-center justify-content-md-end">
    <button type="button" id="invoice-preview" class="btn btn-outline-primary ms-1">Previsualizar</button>
    <button type="button" id="invoice-draft" class="btn btn-outline-primary ms-1">Guardar</button>
    <button type="button" id="invoice-create" class="btn btn-primary ms-1">Timbrar</button>
  </p>
</footer>
@endsection

@section('vendor-script')
<script src="{{ asset('vendors/js/forms/repeater/jquery.repeater.min.js') }}"></script>
<script src="{{ asset('vendors/js/forms/select/select2.full.min.js') }}"></script>
<script src="{{ asset('vendors/js/pickers/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.bootstrap5.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap5.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/datatables.buttons.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/buttons.bootstrap5.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/datatables.checkboxes.min.js')) }}"></script>
@endsection

@section('page-script')
<script>
  $(function () {
    'use strict';

    var assetPath = $('body').attr('data-asset-path'),
        basePath = assetPath + 'invoice/';

    var selected_company = '',
        companySelectInput = $("#company_id"),
        companyFormSection = $("#more_info"),
        CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content'),
        sendInvoiceModal = $('#sendInvoiceModal'),
        previewPdfModal = $('#previewPdfModal'),
        processingModal = $('#pleaseWaitDialog'),
        productRepeater = $('#products');

    /* Initializes product repeater */
    productRepeater.repeater({
        initEmpty: false,

        defaultValues: {
            //default values for product/service
            'clave_sat': "{{ $product_fav->clave_sat }}",
            'clave_unidad_sat': "{{ $product_fav->clave_unidad_sat }}",
            'description': "{{ $product_fav->description }}",
            'unidad_sat': "{{ $product_fav->unidad_sat }}",
            'quantity': '1',
            'price': "{{ $product_fav->price }}",
            'discount': '0',
            'total_amount': '0'
            //default values for iedu
        },

        ready: function () {
            var select2 = $('.taxes'),
                selectSm = $('.select2-size-sm'),
                selectedTaxes = <?php echo json_encode($taxes_fav );?>;
            // Init select2
            select2.select2({
                placeholder: 'Impuestos...',
                containerCssClass: 'select-sm'
            });
            //set select2 default value if empty
            if(select2.find(':selected').length == 0 && select2.find('option').length > 1) {
              select2.val(selectedTaxes).change();
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

        },

        show: function () {
            var select2 = $(this).find('.taxes'),
                selectSm = $(this).find('.select2-size-sm'),
                counterBadge = $(this).find('.product-count'),
                repeaterItems = $("div[data-repeater-item]"),
                conceptComplementDropdown = $('.dropdown-concept-complement .dropdown-item'),
                removeComplementButton = $('.concept-complement .close'),
                selectedTaxes = <?php echo json_encode($taxes_fav );?>;

            /* Displays concept complements */
            conceptComplementDropdown.on('click', function () {
                var $this = $(this),
                    selectedComplement = $this.data('complement');

                if (selectedComplement) {
                    //hides dropdown
                    $this.closest('.repeater-wrapper')
                        .find('.dropdown-concept-complement')
                        .addClass('d-none');
                    //shows complement concept form
                    $this.closest('.repeater-wrapper')
                        .find('#cc-' + selectedComplement)
                        .removeClass('d-none');
                }
            });
            removeComplementButton.on('click', function () {
                var $this = $(this),
                    conceptComplementForm = $this.closest('.repeater-wrapper').find('.concept-complement'),
                    conceptComplementDropdown = $this.closest('.repeater-wrapper').find('.dropdown-concept-complement');
                // resets and hides complement concept form
                conceptComplementForm.find('input').val('');
                conceptComplementForm.addClass('d-none');
                //shows dropdown
                conceptComplementDropdown.removeClass('d-none');
            })

            $(this).slideDown();
            select2.select2({
                placeholder: 'Impuestos...',
                containerCssClass: 'select-sm'
            });
            //set select2 default value if empty
            if(select2.find(':selected').length == 0 && select2.find('option').length > 1) {
                select2.val(selectedTaxes).change();
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
    
    companySelectInput.prepend('<option value="general_extranjero">XEXX010101000 - Público en general extranjero (4.0)</option>');
    companySelectInput.prepend('<option value="general">XAXX010101000 - Público en general (4.0)</option>');
    companySelectInput.prepend('<option value="new"> --- Crear Nuevo Cliente --- </option>');
    companySelectInput.change(function () {
        selected_company = $(this).val(); // get selected value
        $('.collapse').collapse('show');
        if (selected_company == 'new') {
            $('#country_id').change();
            $(".input-disable").prop('disabled', false);
            $(".ModifyCompany").html('Guardar cambios');
            $(".ModifyCompany").val('save');
            $(".CancelCompany").show();

        } else {
            getCompanyData($(this).val());
            $(".input-disable").prop('disabled', true);
            $(".ModifyCompany").html('Editar');
            $(".ModifyCompany").val('edit');
            $(".CancelCompany").hide();
        }
    });
    $('.ModifyCompany').click(function (e) {
        if ($(this).val() == 'save') {
            $(".input-disable").prop('disabled', true);
            $(".CancelCompany").hide();
            $(".ModifyCompany").html('Editar');
            $(".ModifyCompany").val('edit');
            //TODO: Save company's data in DB
            saveCompanyData();
        } else {
            $(".input-disable").prop('disabled', false);
            $(".CancelCompany").show();
            $(".ModifyCompany").html('Guardar cambios');
            $(".ModifyCompany").val('save');
        }
    });
    $('.CancelCompany').click(function (e) {
        companySelectInput.val(selected_company).change();
    });

    $(document).on('select2:open', function(e) {
        if(e.target.classList.contains('company')) {
            var searchCompanySearch = document.querySelector(`[aria-controls="select2-${e.target.id}-results"]`);
            searchCompanySearch.focus();
            searchCompanySearch.setAttribute("placeholder","🔎 Escribe el nombre de tu cliente…");
        }
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

    $('#invoice-preview').click(function (e) {
        e.preventDefault();
        syncTotalValues();
        processingModal.modal('show');
        $(".input-disable").prop('disabled', false);
        var form = $("#form"),
            data = new FormData(form[0]),
            iva_toggle = $('#iva_toggle').prop("checked");

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
    $('#invoice-draft').click(function (e) {
        syncTotalValues();
        $("#form").append('<input type="hidden" name="status" value="Borrador" />');
        $("#form").submit();
    });
    $('#invoice-create').click(function (e) {
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
        
        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            cache: false,
            processData: false,
            contentType: false,
            success: function (data, textStatus, xhr) {
                var { invoice_id } = data,
                    companyEmail = $('#email').val(),
                    iframePdfPath = basePath + invoice_id + '/print_quot',
                    downloadPdfPath = basePath + invoice_id + '/download_pdf',
                    downloadXmlPath = basePath + invoice_id + '/download_xml';

                sendInvoiceModal.find('.hidden-document-id').val(invoice_id);
                $('#modalSendInvoiceEmailTo').val(companyEmail);
                $('#modalSendInvoiceIframePdf').attr('src', iframePdfPath);
                $('#modalSendInvoiceDownloadPdf').attr('href', downloadPdfPath);
                $('#modalSendInvoiceDownloadXml').attr('href', downloadXmlPath);;

                processingModal.modal('hide');
                sendInvoiceModal.modal('show');
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

    /*Initilizes invoice totals on page load*/
    $('.price').keyup();
    /*Initilizes customer details if the field has value on page load*/
    if (companySelectInput.val() != "") {
        companySelectInput.trigger('change');
    }

    sendInvoiceModal.on('hide.bs.modal', function () {
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
                                } else {
                                    ivaTraTaxSum += taxAmount;
                                }
                                $this.val(total.toFixed(6));
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
                let { company } = msg;
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
                //reset form validation errors
                $('.error').each(function () {
                    $(this).removeClass('error');
                });
                //append new company to companies' select
                companySelectInput.append($('<option>', {
                    value: company.id,
                    text: company.sat_rfc + ' - ' + company.sat_name + ' / ' + company.name,
                }))
                companySelectInput.val(company.id).change();
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
                    // set data-ids
                    $('#country_id').attr('data-id', company.country_id);
                    $('#state_id').attr('data-id', company.state_id);
                    $('#city_id').attr('data-id', company.city_id);

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
                // highlight if fiscal_regimen is empty
                if(!company.fiscal_regimen) {
                    $('#fiscal_regimen').addClass('error');
                }

                if ($(".ModifyCompany").val() == 'edit') {
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
    function displayError(validator) {
        if ($.isEmptyObject(validator) == false) {
            $.each(validator.fields, function (key, value) {
                $('#' + value)
                    .addClass('error')
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
</script>
@endsection