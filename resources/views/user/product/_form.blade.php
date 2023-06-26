<div class="card">
    <div class="card-body">
        @if (isset($product))
            {!! Form::model($product, ['url' => $type . '/' . $product->id, 'method' => 'put', 'files'=> true]) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true]) !!}
        @endif
        
        <div class="row">
            <div class="col-5 col-sm-5 col-md-3 col-lg-2">
                {!! Form::label('sku', trans('product.sku'), ['class' => 'form-label']) !!}
                {!! Form::text('sku', null, ['class' => 'form-control']) !!}
            </div>
            <div class="col-7 col-sm-7 col-md-3 col-lg-2">
            {!! Form::label('clave_sat', 'Clave SAT', ['class' => 'form-label required']) !!}
                <div class="input-group">
                    {!! Form::text('clave_sat', null, ['class' => 'form-control']) !!}
                    <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#clave_sat_modal">
                        <i data-feather="search"></i>
                    </button>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4">
                {!! Form::label('description', trans('product.description'), ['class' => 'form-label']) !!}
                {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => '1']) !!}
            </div>
            <div class="col-12 col-sm-6 col-lg-4">
                {!! Form::label('product_name', trans('product.product_name'), ['class' => 'form-label required']) !!}
                {!! Form::text('product_name', null, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="row">
            <div class="col-6 col-sm-5 col-md-3 col-lg-2">
                {!! Form::label('product_type', 'Tipo', ['class' => 'form-label required']) !!}
                {!! Form::select('product_type', $product_types, (isset($product)?$product->product_type:null), ['class' => 'form-select select2']) !!}
            </div>            
            <div class="col-6 col-sm-7 col-md-3 col-lg-2">
                {!! Form::label('clave_unidad_sat', 'Clave unidad', ['class' => 'form-label required']) !!}
                <div class="input-group">
                    {!! Form::text('clave_unidad_sat', null, ['class' => 'form-control']) !!}
                    <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#clave_unidad_sat_modal">
                        <i data-feather="search"></i>
                    </button>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                {!! Form::label('unidad_sat', 'Unidad de medida', ['class' => 'form-label required']) !!}
                {!! Form::text('unidad_sat', null, ['class' => 'form-control']) !!}
            </div>
            <div class="col-7 col-sm-6 col-md-3 col-lg-2">
                {!! Form::label('sale_price', trans('product.sale_price'), ['class' => 'form-label required']) !!}
                {!! Form::text('sale_price', null, ['class' => 'form-control']) !!}
            </div>
            <div class="col-5 col-sm-6 col-lg-3">
                {!! Form::label('quantity_available', trans('product.quantity_available'), ['class' => 'form-label required']) !!}
                {!! Form::input('number','quantity_available', null, ['class' => 'form-control','min' => 0]) !!}
            </div>
            <div class="col-lg-3 d-none">
                {!! Form::label('quantity_on_hand', trans('product.quantity_on_hand'), ['class' => 'form-label required']) !!}
                {!! Form::input('number','quantity_on_hand', null, ['class' => 'form-control','min' => 0]) !!}
            </div>                      
        </div>

        <!-- Form Actions -->
        <div class="mt-2">
                <a href="{{ route($type.'.index') }}" class="btn btn-outline-secondary"><i class="fa fa-arrow-left"></i> {{trans('table.cancel')}}</a>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check-square-o"></i> {{trans('table.ok')}}</button>
        </div>
        <!-- ./ form actions -->

        {!! Form::close() !!}
    </div>
</div>

@include('content._partials._modals.modal-clave-sat-table')
@include('content._partials._modals.modal-clave-unidad-sat-table')

@section('vendor-script')
<script src="{{ asset('vendors/js/forms/select/select2.full.min.js') }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.bootstrap5.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap5.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/datatables.buttons.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/buttons.bootstrap5.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/datatables.checkboxes.min.js')) }}"></script>
@endsection

@section('page-script')
<script src="{{asset('js/scripts/pages/app-product.js')}}"></script>
@endsection