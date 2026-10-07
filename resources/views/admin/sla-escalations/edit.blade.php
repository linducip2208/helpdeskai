@extends('layouts.admin')
@section('title', __('Edit Escalation Rule'))
@section('content')
@include('admin.sla-escalations.form', ['rule' => $rule ?? null])
@endsection
