@section('vendor-style')
<link rel="stylesheet" href="{{asset('vendors/css/forms/select/select2.min.css')}}">
@endsection

<div class="card">        
    <div class="card-body">
        @if (isset($company))
            {!! Form::model($company, ['url' => $type . '/' . $company->id, 'method' => 'put', 'files'=> true]) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true]) !!}
        @endif   
        @include('content._partials._forms.form-company-tabs')
        </br>
        <!-- Form Actions -->
        <div class="d-flex align-items-center justify-content-md-end">
            <a href="{{ route($type.'.index') }}" class="btn btn-flat-dark">{{trans('table.cancel')}}</a>
            <button type="submit" class="btn btn-primary">{{trans('table.ok')}}</button>
        </div>
        <!-- ./ form actions -->
        {!! Form::close() !!}
    </div>
</div>

@section('vendor-script')
<script src="{{ asset('vendors/js/forms/repeater/jquery.repeater.min.js') }}"></script>
<script src="{{ asset('vendors/js/forms/select/select2.full.min.js') }}"></script>
@endsection