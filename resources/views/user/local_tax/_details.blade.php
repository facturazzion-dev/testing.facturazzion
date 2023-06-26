<div class="card">
    <div class="card-body">
        
        <div class="row">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('tax.name')}}</label>
                    <div class="controls">
                        {{ $tax->name }}
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('tax.tax')}}</label>
                    <div class="controls">
                        {{ $tax->tax}}
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('tax.tax_type')}}</label>
                    <div class="controls">
                        {{ $tax->tax_type }}
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('tax.percentage')}}</label>
                    <div class="controls">
                        {{ $tax->percentage }}
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('tax.factor_type')}}</label>
                    <div class="controls">
                        {{ $tax->factor_type }}
                    </div>
                </div>
            </div>
            
        </div>
        <div class="form-group">
            <div class="controls">
                @if (@$action == trans('action.show'))
                    <a href="{{ url($type) }}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                @else
                    <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> {{trans('table.delete')}}</button>
                    <a href="{{ url($type) }}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                @endif
            </div>
        </div>
    </div>
</div>