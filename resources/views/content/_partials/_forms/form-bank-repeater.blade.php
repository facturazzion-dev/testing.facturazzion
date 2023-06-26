<div data-repeater-item>
    <div class="row d-flex align-items-end">
        <div class="col-md-3 col-12">
            <input type="hidden" name="id" id="id" value="{{ $bank->id ?? '' }}" readOnly>
            <input type="text" class="form-control" name="name" value="{{ $bank->name ?? '' }}" placeholder="{{trans('company_bank.name')}}" />
        </div>
        <div class="col-md-3 col-12">
            <input type="number" class="form-control" name="account_number" value="{{ $bank->account_number ?? '' }}" placeholder="{{trans('company_bank.account_number')}}" />
        </div>
        <div class="col-md-4 col-12">
            <input type="number" class="form-control" name="clabe" value="{{ $bank->clabe ?? '' }}" placeholder="{{trans('company_bank.clabe')}}" />
        </div>
        <div class="col-md-1 col-12">
            <button class="btn btn-outline-danger text-nowrap px-1" data-repeater-delete type="button">
                <i data-feather="trash" class="me-25"></i>
            </button>
        </div>
    </div>
    <hr />
</div>
@section('page-script')
    @parent
    <script src="{{asset('js/scripts/content/form-bank-repeater.js')}}"></script>
@endsection