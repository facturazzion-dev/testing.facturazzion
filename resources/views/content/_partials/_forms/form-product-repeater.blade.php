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
            <div class="d-flex justify-content-start flex-wrap py-2">
                <div class="col-12 pe-50 col-md-3">
                    <p class="form-label">Clave Interna</p>
                    <input type="number" name="sku" id="sku" value="{{ $product->sku ?? $backup_product->sku }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 pe-50 col-md-3">
                    <p class="form-label">Clave Producto/Servicio</p>
                    <div class="input-group input-group-sm">
                        <input type="number" name="clave_sat" id="clave_sat" value="{{ $product->clave_sat ?? $backup_product->clave_sat }}" class="form-control form-control-sm">
                        <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#clave_sat_modal">
                            <i data-feather="search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-12 pe-50 col-md-6">
                    <p class="form-label">Descripción</p>
                    <div class="input-group input-group-sm">
                        <input type="hidden" name="product_id" id="product_id" value="{{ $product->product_id ?? '' }}" readOnly>
                        <input type="hidden" name="quantity_available" id="quantity_available" readOnly>
                        <textarea rows="1" name="description" id="description" class="form-control form-control-sm">{{ $product->description ?? $backup_product->description }}</textarea>
                        <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#product_id_modal">
                            <i data-feather="search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-12 pe-50 col-md-3">
                    <p class="form-label">Clave de Unidad</p>
                    <div class="input-group input-group-sm">
                        <input type="text" name="clave_unidad_sat" id="clave_unidad_sat" value="{{ $product->clave_unidad_sat ?? $backup_product->clave_unidad_sat }}" class="form-control form-control-sm">
                        <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#clave_unidad_sat_modal">
                            <i data-feather="search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-12 pe-50 col-md-3">
                    <p class="form-label">Unidad de Medida</p>
                    <input type="text" name="unidad_sat" id="unidad_sat" value="{{ $product->unidad_sat ?? $backup_product->unidad_sat }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 pe-50 col-md-2">
                    <p class="form-label">Cantidad</p>
                    <input type="number" min="0" name="quantity" class="form-control form-control-sm quantity " value="{{ $product->quantity ?? '1' }}" />
                </div>
                <div class="col-12 pe-50 col-md-2">
                    <p class="form-label">Precio</p>
                    <input type="number" min="0" name="price" class="form-control form-control-sm price " value="{{ $product->price ?? '0' }}" />
                </div>
                <div class="col-12 pe-50 col-md-2">
                    <p class="form-label">Descuento(%)</p>
                    <input type="number" min="0" name="discount" class="form-control form-control-sm discount" value="{{ $product->discount ?? '0' }}" />
                </div>
                <div class="col-12 pe-50 col-md-12">
                    <p class="form-label">Impuestos</p>
                    <select multiple name="taxes" class="form-select taxes">
                        <option value="">Impuestos...</option>
                        @foreach ($taxes as $tax)
                        <option value="{{ $tax->id.'_'.$tax->tax.'_'.$tax->tax_type.'_'.$tax->percentage.'_'.$tax->factor_type }}" 
                            {{ isset($docTaxes) ?
                                $docTaxes
                                    ->where('pivot.tax_id', $tax->id)
                                    ->where('pivot.product_id', $product->product_id)
                                    ->count() ?
                                    'selected':''
                                    :''
                            }}
                        >
                            {{
                                $tax->name . 
                                ' (' .
                                $tax->percentage * 100 .
                                '% - ' .
                                $tax->tax_type .
                                ')'
                            }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 pe-50 col-md-12">
                    <!-- Complementos -->
                    @php
                        if(isset($product)) {
                            $complemento = json_decode($product->complemento);
                        }
                    @endphp
                    <div class="btn-group mt-1 dropdown-concept-complement {{ isset($complemento->CURP) ? 'd-none' : '' }}">
                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle w-100" data-bs-toggle="dropdown" aria-expanded="false">
                            Agregar Complemento Concepto
                        </button>
                        <div class="dropdown-menu">
                            <a data-complement="iedu" class="dropdown-item" href="#">Instituciones educativas privadas (IEDU)</a>
                            <a data-complement="vehicle-sale" class="dropdown-item disabled" href="#">Venta de vehículos</a>
                            <a data-complement="third-party" class="dropdown-item disabled" href="#">Terceros</a>
                            <a data-complement="ieps" class="dropdown-item disabled" href="#">Acreditamiento del IEPS</a>
                        </div>
                    </div>
                    @include('content._partials.concept-complement-iedu')
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
                <div class="col-12 mx-3">
                    <p class="form-label text-nowrap">Importe</p>
                    <input type="text" name="total_amount" id="total_amount" value="0" class="form-control form-control-sm total_amount numeral-mask" readonly="">
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
@section('page-script')
    @parent
    <script src="{{asset('js/scripts/content/product-repeater.js')}}"></script>
    <script src="{{asset('js/scripts/content/concept-complement.js')}}"></script>
@stop