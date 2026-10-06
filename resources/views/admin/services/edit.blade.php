@extends('layouts.admin')
@section('title', 'Edit Service')
@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <a href="{{ route('admin.services.index') }}" class="btn btn-link px-0">&larr; Back to Services</a>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.services.update', $service ?? 0) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label">Service Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $service->name ?? '') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" rows="4" class="form-control">{{ old('description', $service->description ?? '') }}</textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" name="price" id="price" step="0.01" value="{{ old('price', $service->price ?? '') }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="duration" class="form-label">Duration</label>
                                <input type="text" name="duration" id="duration" value="{{ old('duration', $service->duration ?? '') }}" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-check form-switch">
                            <input type="checkbox" name="is_active" value="1" {{ ($service->is_active ?? true) ? 'checked' : '' }} class="form-check-input">
                            <span class="form-check-label">Active</span>
                        </label>
                    </div>
                    <div class="form-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.services.index') }}" class="btn">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
