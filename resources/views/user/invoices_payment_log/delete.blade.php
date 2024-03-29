@extends('layouts/contentLayoutMaster')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="page-header clearfix">
            </div>
            <div class="details">
                {!! Form::open(['url' => $type . '/' . $payment->id, 'method' => 'delete', 'class' => 'bf']) !!}

                @include('user/'.$type.'/_details')

                {!! Form::close() !!}
            </div>
        </div>
    </div>
@stop
