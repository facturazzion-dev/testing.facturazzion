<div class="tab-pane" id="timbre_provider_settings">
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