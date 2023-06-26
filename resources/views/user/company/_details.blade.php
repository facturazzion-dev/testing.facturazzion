<div class="card">
    <div class="card-body">
        <div class="form-group">
            <label class="control-label" for="title"><b>{{ $company->name }}</b></label>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h3>Información de ventas</h3>

                <div class="row">
                    <div class="col-12 col-sm-3 m-t-10">
                        <div class="txt">
                            <strong>{{trans('company.total_sales')}} (sin impuestos)</strong>
                        </div>
                        <div class="number c-primary">${{$total_sales}} </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 m-t-20">
                <h3>{{trans('company.details')}}</h3>

                <div class="widget-member2 m-t-10">
                    <div class="row">
                        <div class="col-lg-10 col-sm-8 col-12">
                            <div class="row">
                                @if($company->sat_rfc)
                                    <div class="col-sm-12">
                                        <p>{{ $company->sat_rfc}}</p>
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                @if(isset($company->sat_name))
                                    <div class="col-xlg-4 col-lg-6 col-sm-4 word-break">
                                        <p>{{ $company->sat_name}}</p>
                                    </div>
                                @endif
                                @if(isset($company->email))
                                    <div class="col-xlg-4 col-lg-6 col-sm-4 align-right">
                                        <p>{{ $company->email}}</p>
                                    </div>
                                @endif
                                @if(isset($company->phone))
                                    <div class="col-xlg-4 col-lg-6 col-sm-4">
                                        <p>{{  $company->phone}}</p>
                                    </div>
                                @endif                                
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        @if(isset($company->contactPerson))
                            <div class="col-xlg-4 col-lg-6 col-sm-4 align-right">
                                <label class="control-label">{{ trans('company.main_contact_person') }}</label>
                                <p>
                                    <i class="icon-user c-gray-light p-r-10"></i> {{ $company->contactPerson->full_name}}
                                </p>
                            </div>
                        @endif
                        @if(isset($company->salesTeam))
                            <div class="col-xlg-4 col-lg-6 col-sm-4">
                                <label class="control-label">{{ trans('company.sales_team_id') }}</label>
                                <p><i class="fa fa-check c-gray-light"></i> {{ $company->salesTeam->salesteam}}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div>
            <div class="form-group">
                <div class="controls">
                    @if (@$action == trans('action.show'))
                        <a href="{{ url($type) }}" class="btn btn-flat-dark">{{trans('table.back')}}</a>
                    @else
                        <button type="submit" class="btn btn-danger">{{trans('table.delete')}}
                        </button>
                        <a href="{{ url($type) }}" class="btn btn-flat-dark">{{trans('table.back')}}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>