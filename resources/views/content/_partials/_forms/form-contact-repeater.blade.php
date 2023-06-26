<div data-repeater-item>
    <div class="row d-flex align-items-end">
        <div class="col-md-2 col-12">
            <input type="hidden" name="id" id="id" value="{{ $contact->id ?? '' }}" readOnly>
            <input type="text" class="form-control" name="name" value="{{ $contact->name ?? '' }}" placeholder="{{trans('contact.name')}}" />
        </div>
        <div class="col-md-3 col-12">
            <input type="text" class="form-control" name="last_name" value="{{ $contact->last_name ?? '' }}" placeholder="{{trans('contact.last_name')}}" />
        </div>
        <div class="col-md-2 col-12">
            <input type="text" class="form-control" name="job_title" value="{{ $contact->job_title ?? '' }}" placeholder="{{trans('contact.job_title')}}" />
        </div>
        <div class="col-md-2 col-12">
            <input type="email" class="form-control" name="email" value="{{ $contact->email ?? '' }}" placeholder="{{trans('contact.email')}}" />
        </div>
        <div class="col-md-2 col-12">
            <input type="text" class="form-control" name="phone" value="{{ $contact->phone ?? '' }}" placeholder="{{trans('contact.phone')}}" />
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
    <script src="{{asset('js/scripts/content/form-contact-repeater.js')}}"></script>
@endsection