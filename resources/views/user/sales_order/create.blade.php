@extends('layouts/contentLayoutMaster')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    @include('user/'.$type.'/_form')
@stop
