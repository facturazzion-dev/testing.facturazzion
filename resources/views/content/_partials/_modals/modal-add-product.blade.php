<div class="modal fade" id="product_add_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog " role="document">
        <div class="modal-content">
            <input type="hidden" name="identifier">
            <div class="modal-header">
                <h4 class="modal-title">Crear Nuevo Produco o Servicio</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            {!! Form::open(['url' => '/product/ajax-store', 'method' => 'post', 'files'=> true , 'id' => 'add-new-product']) !!}
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 ">
                        <div class="form-group">
                            {!! Form::label('sku', trans('product.sku'), ['class' => 'form-label']) !!}
                            <div class="controls">
                                {!! Form::text('sku', null, ['class' => 'form-control']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 ml-auto">
                        <div class="form-group required" id="f_clave_sat">
                            {!! Form::label('clave_sat', 'Clave SAT', ['class' => 'form-label required']) !!}
                            <div class="controls input-group">
                                {!! Form::text('clave_sat', null, ['class' => 'form-control']) !!}
                                <div class="input-group-append"><button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#clave_sat_modal" data-identifier="add_product"><i class="fa fa-search"></i></button></div>
                            </div>
                            <span id="clave_sat_error" class="help-block"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 ">
                        <div class="form-group required" id="f_description">
                            {!! Form::label('description', trans('product.description'), ['class' => 'form-label required']) !!}
                            <div class="controls">
                                {!! Form::text('description', null, ['class' => 'form-control']) !!}
                            </div>
                            <span id="description_error" class="help-block"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group required" id="f_product_name">
                            {!! Form::label('product_name', trans('product.product_name'), ['class' => 'form-label required']) !!}
                            <div class="controls">
                                {!! Form::text('product_name', null, ['class' => 'form-control']) !!}
                            </div>
                            <span id="product_name_error" class="help-block"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 ">
                        <div class="form-group required" id="f_product_type">
                            {!! Form::label('product_type', 'Tipo', ['class' => 'form-label required']) !!}
                            <div class="controls">
                                {!! Form::select('product_type', $product_types, (isset($product)?$product->product_type:null), ['class' => 'form-control']) !!}
                            </div>
                            <span id="product_type_error" class="help-block"></span>
                        </div>
                    </div>
                    <div class="col-md-6 ml-auto">
                        <div class="form-group required" id="f_clave_unidad_sat">
                            {!! Form::label('clave_unidad_sat', 'Clave unidad', ['class' => 'form-label required']) !!}
                            <div class="controls input-group">
                                {!! Form::text('clave_unidad_sat', null, ['class' => 'form-control']) !!}
                                <div class="input-group-append"><button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#clave_unidad_sat_modal" data-identifier="add_product"><i class="fa fa-search"></i></button></div>
                            </div>
                            <span id="clave_unidad_sat_error" class="help-block"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 ">
                        <div class="form-group required" id="f_unidad_sat">
                            {!! Form::label('unidad_sat', 'Unidad de medida', ['class' => 'form-label required']) !!}
                            <div class="controls">
                                {!! Form::text('unidad_sat', null, ['class' => 'form-control']) !!}
                            </div>
                            <span id="unidad_sat_error" class="help-block"></span>
                        </div>
                    </div>
                    <div class="col-md-6 ml-auto">
                        <div class="form-group required" id="f_sale_price">
                            {!! Form::label('sale_price', trans('product.sale_price'), ['class' => 'form-label required']) !!}
                            <div class="controls">
                                {!! Form::text('sale_price', null, ['class' => 'form-control']) !!}
                            </div>
                            <span id="sale_price_error" class="help-block"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls">
                            {!! Form::input('hidden','quantity_available', '10', ['class' => 'form-control','min' => 0]) !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="form-group">
                    <div class="controls">
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-ban"></i> Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="fa fa-check-square-o"></i> {{trans('table.ok')}}</button>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
@section('page-script')
@parent
<script src="{{asset('js/scripts/content/modal-add-product.js')}}"></script>
@endsection