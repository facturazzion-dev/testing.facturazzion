<div class="repeater-wrapper" data-repeater-item>
    <div class="row">
        <div class="col-12 d-flex product-details-border position-relative p-0">
            <div class="
                  d-flex
                  flex-column
                  align-items-center
                  justify-content-center
                  px-25
                ">
                <span class="badge badge-light-secondary rounded-pill product-count">
                {{ $count ?? "1" }}
                </span>
            </div>
            <div class="d-flex w-100 justify-content-start flex-wrap py-2">
                <div class="col-12 pe-50">
                    <p class="form-label">Buscar CFDI</p>
                    <!-- {!! Form::select('invoice_id', array(''=>'Folio de la factura', 'otro' => '--- Otro ---'), isset($ppd)?$ppd->invoice_id:'' , ['class' => 'form-select invoice_id']) !!} -->
                    <select name="invoice_id" class="form-select invoice_id">
                        <option value="">Folio de la factura</option>
                        <option value="otro">--- Otro ---</option>
                        @if (isset($invoices))
                            @foreach ($invoices as $invoice)
                            <option value="{{ $invoice['id'] }}"
                                    data-uuid="{{ $invoice['uuid_sat'] }}"
                                    data-date="{{ $invoice['invoice_date'] }}"
                                    data-unpaid="{{ $invoice['unpaid_amount'] }}"
                                    data-paid="{{ $invoice['paid_amount'] }}"
                                    data-total="{{ $invoice['final_price'] }}"
                                    data-currency="{{ $invoice['currency'] }}"
                                    data-partiality="{{ $invoice['partiality'] }}"
                                    data-rate="{{ $invoice['exchange_rate'] }}"
                                    
                                {{ $invoice['id'] == $ppd->id ? 'selected' : '' }}
                            >
                                {{ $invoice['invoice_serie'] . $invoice['invoice_number'] }}
                            </option>
                            @endforeach
                        @endif
                    </select>
                </div>                    
                <div class="col-12 d-flex info_section"></div>
                <div class="col-12 pe-50 col-lg-2 col-md-4 serie d-none hidden-field">
                    <p class="form-label">Serie</p>
                    <input type="text" name="cfdi_serie" value="" class="cfdi_serie form-control form-control-sm">
                </div>
                <div class="col-12 pe-50 col-lg-2 col-md-4 folio d-none hidden-field">
                    <p class="form-label">Folio</p>
                    <input type="text" name="cfdi_number" value="" class="cfdi_number form-control form-control-sm">
                </div>
                <div class="col-12 pe-50 col-lg-3 col-md-4 d-none hidden-field">
                    <p class="form-label">Fecha</p>
                    <input type="text" name="invoice_date" value="" class="invoice_date form-control form-control-sm" readOnly>
                </div>
                <div class="col-12 pe-50 col-lg-6 col-md-8 d-none hidden-field">
                    <p class="form-label">UUID</p>
                    <input type="text" name="uuid_sat" value="" class="uuid_sat form-control text-uppercase form-control-sm" readOnly>
                </div>
                <div class="col-12 pe-50 col-lg-3 col-md-4 d-none hidden-field">
                    <p class="form-label">Total CFDI</p>
                    <input type="text" name="final_price" value="" class="final_price form-control form-control-sm" readOnly>
                </div>
                <div class="col-12 pe-50 col-lg-3 col-md-4 d-none hidden-field">
                    <p class="form-label">Moneda</p>
                    <select name="currency" class="form-select currency input-disable" disabled>
                        <option value="MXN">Peso mexicano</option>
                        <option value="USD">Dólar americano</option>
                    </select>
                </div>
                <div class="col-12 pe-50 col-lg-3 col-md-4 d-none hidden-field">
                    <p class="form-label">Monto Abonos</p>
                    <input type="text" name="paid_amount" value="" class="paid_amount form-control form-control-sm" readOnly>
                </div>
                <div class="col-12 pe-50 col-lg-3 col-md-4 d-none hidden-field">
                    <p class="form-label">Saldo pendiente</p>
                    <input type="text" name="unpaid_amount" value="" class="unpaid_amount form-control form-control-sm" readOnly>
                </div>
            </div>
            <div class="
                    d-flex
                    flex-column
                    align-items-center
                    justify-content-center
                    border-start
                    p-50
                  ">
                <div class="col-12 mx-25">
                    <p class="form-label text-nowrap">Parcialidad</p>
                    <input type="text" name="faction" value="1" class="faction form-control form-control-sm" readOnly>
                </div>
            </div>
            <div class="
                    d-flex
                    flex-column
                    align-items-center
                    justify-content-center
                    border-start
                    p-50
                    ">
                <!-- <div class="col-12">
                    <p class="form-label">Equivalencia</p>
                    <input data-bs-toggle="popover"
                        data-bs-content="Se debe registrar el número de unidades de la moneda señalada en la factura que equivalen a una unidad de la moneda del pago."
                        data-bs-trigger="hover"
                        title="Tipo de Cambio"
                        type="text" name="tipo_cambio_dr" value="" class="tipo_cambio_dr form-control form-control-sm" readOnly>
                </div> -->
                <div class="col-12">
                    <p class="form-label text-nowrap">Monto de pago</p>
                    <input type="text" name="total_payment" value="0" class="total_payment form-control form-control-sm" readOnly>
                </div>
            </div>
            <div class="
                    d-flex
                    flex-column
                    align-items-center
                    justify-content-between
                    border-start
                    invoice-product-actions
                    py-50
                    px-25
                  ">
                <i data-feather="trash-2" class="cursor-pointer font-medium-3" data-repeater-delete></i>
            </div>
        </div>
    </div>
</div>
