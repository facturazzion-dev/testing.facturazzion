<div class="modal fade" id="addCityModal" tabindex="-1" aria-labelledby="addCity" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-0">
                <div class="px-sm-5 mx-50">
                    <h4 class="fw-bolder">Crear Ciudad</h4>
                    <div id="addCityForm" class="row g-1">
                        <div class="col-12">
                            <input type="hidden" name="h_state_id" value="" />
                            <label class="form-label" for="h_name">Nombre</label>
                            <input type="text" name="h_name" class="form-control" value="" />
                        </div>
                        <div class="col-12 text-center">
                            <a type="button" class="btn btn-flat-dark mt-2 me-1" data-bs-dismiss="modal">Cancelar</a>
                            <a type="button" id="addCityButton" class="btn btn-primary mt-2">{{trans('table.ok')}}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@section('page-script')
@parent
<script src="{{asset('js/scripts/content/modal-add-city.js')}}"></script>
@endsection