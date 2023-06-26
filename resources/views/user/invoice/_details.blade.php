<div class="card">
    <div class="card-body">
        <div id="sendby_ajax" class="center-edit"></div>
        <div class="row">
            <div id="cfdi" class="col-12">
                <div style="text-align: center;">
                    <iframe id="iframe_pdf" src="{{ url($type).'/'.$invoice->id.'/print_quot' }}" style="width:100%; height:500px;" frameborder="0"></iframe>
                </div>
            </div>
        </div>
        @if (@$action == trans('action.show'))
            <a href="{{ url($type) }}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
        @endif
    </div>
</div>