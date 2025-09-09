<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="h3 mb-0">Edit Customer</h2>
                <p class="text-muted mb-0">Update {{ $customer->name }} information</p>
            </div>
        </div>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('customers.update', $customer) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="business_profile_id" class="form-label">Business Profile <span class="text-danger">*</span></label>
                            <select class="form-select @error('business_profile_id') is-invalid @enderror" id="business_profile_id" name="business_profile_id" required>
                                <option value="">Select Business Profile</option>
                                @foreach($businessProfiles as $profile)
                                    <option value="{{ $profile->id }}" {{ old('business_profile_id', $customer->business_profile_id) == $profile->id ? 'selected' : '' }}>
                                        {{ $profile->business_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('business_profile_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $customer->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="customer_type" class="form-label">Customer Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('customer_type') is-invalid @enderror" id="customer_type" name="customer_type" required>
                                    <option value="">Select Type</option>
                                    <option value="registered" {{ old('customer_type', $customer->customer_type) == 'registered' ? 'selected' : '' }}>Registered</option>
                                    <option value="unregistered" {{ old('customer_type', $customer->customer_type) == 'unregistered' ? 'selected' : '' }}>Unregistered</option>
                                </select>
                                @error('customer_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="ntn_cnic" class="form-label">NTN/CNIC</label>
                            <input type="text" class="form-control @error('ntn_cnic') is-invalid @enderror" 
                                   id="ntn_cnic" name="ntn_cnic" value="{{ old('ntn_cnic', $customer->ntn_cnic) }}">
                            @error('ntn_cnic')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Enter NTN for registered customers or CNIC for individuals</div>
                        </div>

                        <div class="mb-4">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3">{{ old('address', $customer->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="contact_phone" class="form-label">Contact Phone</label>
                                <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" 
                                       id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $customer->contact_phone) }}">
                                @error('contact_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="contact_email" class="form-label">Contact Email</label>
                                <input type="email" class="form-control @error('contact_email') is-invalid @enderror" 
                                       id="contact_email" name="contact_email" value="{{ old('contact_email', $customer->contact_email) }}">
                                @error('contact_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('customers.show', $customer) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2 me-2"></i>Update Customer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('customer_type').addEventListener('change', function() {
            const ntnCnicLabel = document.querySelector('label[for="ntn_cnic"]');
            const ntnCnicHelp = document.querySelector('#ntn_cnic').nextElementSibling;
            
            if (this.value === 'registered') {
                ntnCnicLabel.innerHTML = 'NTN <span class="text-danger">*</span>';
                ntnCnicHelp.textContent = 'Enter NTN for registered customers';
                document.getElementById('ntn_cnic').required = true;
            } else if (this.value === 'unregistered') {
                ntnCnicLabel.innerHTML = 'CNIC';
                ntnCnicHelp.textContent = 'Enter CNIC for individual customers';
                document.getElementById('ntn_cnic').required = false;
            }
        });
    </script>
    @endpush
</x-app-layout>