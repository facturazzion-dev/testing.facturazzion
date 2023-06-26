@php
    $levels = [
    "Preescolar",
    "Primaria",
    "Secundaria",
    "Bachillerato o su equivalente"
];
@endphp
<div id="cc-iedu" class="card concept-complement m-1 product-details-border bg-transparent {{ isset($complemento->CURP) ? '' : 'd-none' }}">
    <div class="card-header">
        <span class="badge badge-light-primary">Complemento Instituciones educativas privadas (IEDU)</span>
        <div class="heading-elements">
            <ul class="list-inline mb-0">
                <li>
                    <a class="close"><i data-feather="x"></i></a>
                </li>
            </ul>
        </div>
    </div>
    <div class="card-content">
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-md-4">
                    <p class="form-label">RVOE</p>
                    <input type="text" name="iedu_rvoe" id="iedu_rvoe" value="{{ $complemento->autRVOE ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-md-4">
                    <p class="form-label">Nivel Educativo</p>
                    <select name="iedu_niv_edu" id="iedu_niv_edu" class="form-select select2-size-sm">
                        @foreach ($levels as $level)
                        <option value="{{ $level }}" {{ isset($complemento->nivelEducativo) && $complemento->nivelEducativo == $level ? 'selected' : '' }}>
                            {{$level}}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <p class="form-label">RFC de pago (opcional)</p>
                    <input type="text" name="iedu_rfc" id="iedu_rfc" value="{{ $complemento->rfcPago ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-md-6">
                    <p class="form-label">Nombre del alumno</p>
                    <input type="text" name="iedu_alumno" id="iedu_alumno" value="{{ $complemento->nombreAlumno ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-md-6">
                    <p class="form-label">CURP</p>
                    <input type="text" name="iedu_curp" id="iedu_curp" value="{{ $complemento->CURP ?? '' }}" class="form-control form-control-sm">
                </div>
            </div>
        </div>
    </div>
</div>