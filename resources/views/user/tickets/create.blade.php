<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">{{ __('Create Ticket') }}</h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('user.tickets.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" name="subject" id="subject" value="{{ old('subject') }}"
                                class="form-control"
                                placeholder="Brief summary of your issue" required>
                            @error('subject') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="body" class="form-label">Description</label>
                            <textarea name="body" id="body" rows="6"
                                class="form-control"
                                placeholder="Describe your issue in detail..." required>{{ old('body') }}</textarea>
                            @error('body') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="mb-3">
                                    <label for="department_id" class="form-label">Department</label>
                                    <select name="department_id" id="department_id" class="form-select" required>
                                        <option value="">Select Department</option>
                                        @foreach($departments ?? [] as $department)
                                        <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select name="priority" id="priority" class="form-select">
                                        <option value="low">Low</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        @include('tickets._custom_fields', ['customFields' => $customFields ?? collect(), 'fieldsUrl' => $fieldsUrl ?? null])
                        <div class="d-flex justify-content-end pt-2">
                            <button type="submit" class="btn btn-primary">Submit Ticket</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
