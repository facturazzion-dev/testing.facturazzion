<div class="modal fade" id="clave_unidad_sat_modal" tabindex="-1" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <input type="hidden" name="identifier">
            <div class="modal-header">
                <h4 class="modal-title">Claves de Unidad</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true , 'id' => 'search-unidad-sat']) !!}
                <div class="input-group mb-3">
                    <input type="text" name="searchable_unidad_sat" class="form-control" placeholder="Buscar por unidad o id" aria-label="Buscar por unidad o id" aria-describedby="basic-addon2">
                    <div class="input-group-append">
                        <button class="btn btn-outline-primary" type="submit">Buscar</button>
                    </div>
                </div>
                {!! Form::close() !!}
                <div class="table-responsive">
                    <table id="clave_unidad_sat_data" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>{{ trans('product.nombre') }}</th>
                                <th>{{ trans('product.sat_id') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@section('page-script')
    @parent
    <script src="{{asset('js/scripts/content/modal-table-clave-unidad-sat.js')}}"></script>
@stop