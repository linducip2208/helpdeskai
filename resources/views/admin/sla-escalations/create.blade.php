@extends('layouts.admin')
@section('title', __('New Escalation Rule'))
@section('content')
@include('admin.sla-escalations.form', ['rule' => null])
@endsection
