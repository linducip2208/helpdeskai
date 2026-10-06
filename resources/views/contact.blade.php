<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">{{ __('Contact Us') }}</h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title fs-2">Get in Touch</h3>
                    <p class="text-secondary mb-3">Have a question or need help? Send us a message and we'll get back to you shortly.</p>

                    @if(session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                    @endif

                    <form action="{{ route('contact.send') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control" required>
                                    @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control" required>
                                    @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" name="subject" id="subject" value="{{ old('subject') }}" class="form-control" required>
                            @error('subject') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea name="message" id="message" rows="5" class="form-control" required>{{ old('message') }}</textarea>
                            @error('message') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </div>
                    </form>

                    <div class="mt-3 pt-3 border-top">
                        <div class="row row-cards text-center text-secondary small">
                            <div class="col-12 col-sm-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-1"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <p class="mb-0">{{ config('mail.from.address', 'support@helpdeskai.com') }}</p>
                            </div>
                            <div class="col-12 col-sm-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-1"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <p class="mb-0">+1 (555) 000-0000</p>
                            </div>
                            <div class="col-12 col-sm-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-1"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <p class="mb-0">San Francisco, CA</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
