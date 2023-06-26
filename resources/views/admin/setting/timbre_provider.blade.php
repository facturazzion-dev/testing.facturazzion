<div class="tab-pane" id="timbre_provider_settings">
    <div class="form-group required {{ $errors->has('timbrador_selected') ? 'has-error' : '' }}">
        {!! Form::label('timbrador_selected', trans('settings.timbrador_selected'), ['class' => 'control-label']) !!}
        <div class="controls">
            <div class="form-inline">
                <div class="radio">
                    <div class="form-inline">
                        {!! Form::radio('timbrador_selected', 'gofac',(isset($settings['timbrador_selected']) && $settings['timbrador_selected']=='gofac')?true:false,['class' => 'icheck gofac'])  !!}
                        {!! Form::label('true', trans('settings.gofac'),['class'=>'ml-1 mr-2'])  !!}
                    </div>
                </div>
                <div class="radio">
                    <div class="form-inline">
                        {!! Form::radio('timbrador_selected', 'paybook_sync', (isset($settings['timbrador_selected']) && $settings['timbrador_selected']=='paybook_sync')?true:false,['class' => 'icheck paybook_sync'])  !!}
                        {!! Form::label('false', trans('settings.paybook_sync'),['class'=>'ml-1 mr-2']) !!}
                    </div>
                </div>
            </div>
            <span class="help-block">{{ $errors->first('timbrador_selected', ':message') }}</span>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 gofac">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">GoFac</h5>
                    <p class="card-text">http://gofac.com.mx</p>
                    <div>
                        <div class="form-group required {{ $errors->has('gofac_url') ? 'has-error' : '' }}">
                            {!! Form::label('gofac_url', trans('settings.gofac_url'), ['class' => 'control-label']) !!}
                            <div class="controls">
                                {!! Form::input('text','gofac_url', old('gofac_url', (isset($settings['gofac_url'])?$settings['gofac_url']:"")), ['class' => 'form-control']) !!}
                                <span class="help-block">{{ $errors->first('gofac_url', ':message') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group required {{ $errors->has('gofac_mode') ? 'has-error' : '' }}">
                        {!! Form::label('gofac_mode', trans('settings.gofac_mode'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            <div class="form-inline">
                                <div class="radio">
                                    <div class="form-inline">
                                        {!! Form::radio('gofac_mode', 'sandbox',(isset($settings['gofac_mode']) && $settings['gofac_mode']=='sandbox')?true:false,['class' => 'icheck sandbox'])  !!}
                                        {!! Form::label('true', trans('settings.sandbox'),['class'=>'ml-1 mr-2'])  !!}
                                    </div>
                                </div>
                                <div class="radio">
                                    <div class="form-inline">
                                        {!! Form::radio('gofac_mode', 'live', (isset($settings['gofac_mode']) && $settings['gofac_mode']=='live')?true:false,['class' => 'icheck live'])  !!}
                                        {!! Form::label('false', trans('settings.live'),['class'=>'ml-1 mr-2']) !!}
                                    </div>
                                </div>
                            </div>
                            <span class="help-block">{{ $errors->first('gofac_mode', ':message') }}</span>
                        </div>
                    </div>
                    <div class="gofac_sandbox">
                        <div class="form-group required {{ $errors->has('gofac_sandbox_username') ? 'has-error' : '' }}">
                            {!! Form::label('gofac_sandbox_username', trans('settings.gofac_sandbox_username'), ['class' => 'control-label']) !!}
                            <div class="controls">
                                {!! Form::input('text','gofac_sandbox_username', old('gofac_sandbox_username', (isset($settings['gofac_sandbox_username'])?$settings['gofac_sandbox_username']:"")), ['class' => 'form-control']) !!}
                                <span class="help-block">{{ $errors->first('gofac_sandbox_username', ':message') }}</span>
                            </div>
                        </div>

                        <div class="form-group required {{ $errors->has('gofac_sandbox_password') ? 'has-error' : '' }}">
                            {!! Form::label('gofac_sandbox_password', trans('settings.gofac_sandbox_password'), ['class' => 'control-label']) !!}
                            <div class="controls">
                                {!! Form::input('text','gofac_sandbox_password', old('gofac_sandbox_password', (isset($settings['gofac_sandbox_password'])?$settings['gofac_sandbox_password']:"")), ['class' => 'form-control']) !!}
                                <span class="help-block">{{ $errors->first('gofac_sandbox_password', ':message') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="gofac_live">
                        <div class="form-group required {{ $errors->has('gofac_live_username') ? 'has-error' : '' }}">
                            {!! Form::label('gofac_live_username', trans('settings.gofac_live_username'), ['class' => 'control-label']) !!}
                            <div class="controls">
                                {!! Form::input('text','gofac_live_username', old('gofac_live_username', (isset($settings['gofac_live_username'])?$settings['gofac_live_username']:"")), ['class' => 'form-control']) !!}
                                <span class="help-block">{{ $errors->first('gofac_live_username', ':message') }}</span>
                            </div>
                        </div>

                        <div class="form-group required {{ $errors->has('gofac_live_password') ? 'has-error' : '' }}">
                            {!! Form::label('gofac_live_password', trans('settings.gofac_live_password'), ['class' => 'control-label']) !!}
                            <div class="controls">
                                {!! Form::input('text','gofac_live_password', old('gofac_live_password', (isset($settings['gofac_live_password'])?$settings['gofac_live_password']:"")), ['class' => 'form-control']) !!}
                                <span class="help-block">{{ $errors->first('gofac_live_password', ':message') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>                        
        </div>    
        <div class="col-md-6 paybook_sync">    
            <div class="card">
      <div class="card-body">
        <h5 class="card-title">Paybook</h5>
        <p class="card-text">https://www.paybook.com/sync/</p>
        <div class="paybook_url">
            <div class="form-group required {{ $errors->has('paybook_url') ? 'has-error' : '' }}">
                {!! Form::label('paybook_url', trans('settings.paybook_url'), ['class' => 'control-label']) !!}
                <div class="controls">
                    {!! Form::input('text','paybook_url', old('paybook_url', (isset($settings['paybook_url'])?$settings['paybook_url']:"")), ['class' => 'form-control']) !!}
                    <span class="help-block">{{ $errors->first('paybook_url', ':message') }}</span>
                </div>
            </div>
        </div>
        <div class="form-group required {{ $errors->has('paybook_mode') ? 'has-error' : '' }}">
            {!! Form::label('paybook_mode', trans('settings.paybook_mode'), ['class' => 'control-label']) !!}
            <div class="controls">
                <div class="form-inline">
                    <div class="radio">
                        <div class="form-inline">
                            {!! Form::radio('paybook_mode', 'sandbox',(isset($settings['paybook_mode']) && $settings['paybook_mode']=='sandbox')?true:false,['class' => 'icheck sandbox'])  !!}
                            {!! Form::label('true', trans('settings.sandbox'),['class'=>'ml-1 mr-2'])  !!}
                        </div>
                    </div>
                    <div class="radio">
                        <div class="form-inline">
                            {!! Form::radio('paybook_mode', 'live', (isset($settings['paybook_mode']) && $settings['paybook_mode']=='live')?true:false,['class' => 'icheck live'])  !!}
                            {!! Form::label('false', trans('settings.live'),['class'=>'ml-1 mr-2']) !!}
                        </div>
                    </div>
                </div>
                <span class="help-block">{{ $errors->first('paybook_mode', ':message') }}</span>
            </div>
        </div>
        <div class="paybook_sandbox">
            <div class="form-group required {{ $errors->has('paybook_sandbox_key') ? 'has-error' : '' }}">
                {!! Form::label('paybook_sandbox_key', trans('settings.paybook_sandbox_key'), ['class' => 'control-label']) !!}
                <div class="controls">
                    {!! Form::input('text','paybook_sandbox_key', old('paybook_sandbox_key', (isset($settings['paybook_sandbox_key'])?$settings['paybook_sandbox_key']:"")), ['class' => 'form-control']) !!}
                    <span class="help-block">{{ $errors->first('paybook_sandbox_key', ':message') }}</span>
                </div>
            </div>
        </div>
        <div class="paybook_live">
            <div class="form-group required {{ $errors->has('paybook_production_key') ? 'has-error' : '' }}">
                {!! Form::label('paybook_production_key', trans('settings.paybook_production_key'), ['class' => 'control-label']) !!}
                <div class="controls">
                    {!! Form::input('text','paybook_production_key', old('paybook_production_key', (isset($settings['paybook_production_key'])?$settings['paybook_production_key']:"")), ['class' => 'form-control']) !!}
                    <span class="help-block">{{ $errors->first('paybook_production_key', ':message') }}</span>
                </div>
            </div>
        </div>
      </div>
            </div>
        </div>
    </div>    
</div>
@section('scripts')

@stop
