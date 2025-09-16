<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <a href="{{ route('business-profiles.show', $businessProfile) }}" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="h3 mb-0">Edit Business Profile</h2>
                <p class="text-muted mb-0">Update {{ $businessProfile->business_name }} information</p>
            </div>
        </div>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('business-profiles.update', $businessProfile) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="business_name" class="form-label">Business Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('business_name') is-invalid @enderror" 
                                       id="business_name" name="business_name" value="{{ old('business_name', $businessProfile->business_name) }}" required>
                                @error('business_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="service_type" class="form-label">Service Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('service_type') is-invalid @enderror" id="service_type" name="service_type" required>
                                    <option value="">Select Service Type</option>
                                    <option value="IT Services" {{ old('service_type', $businessProfile->service_type) == 'IT Services' ? 'selected' : '' }}>IT Services</option>
                                    <option value="Retail" {{ old('service_type', $businessProfile->service_type) == 'Retail' ? 'selected' : '' }}>Retail</option>
                                    <option value="Wholesale" {{ old('service_type', $businessProfile->service_type) == 'Wholesale' ? 'selected' : '' }}>Wholesale</option>
                                    <option value="Manufacturing" {{ old('service_type', $businessProfile->service_type) == 'Manufacturing' ? 'selected' : '' }}>Manufacturing</option>
                                    <option value="Consultancy" {{ old('service_type', $businessProfile->service_type) == 'Consultancy' ? 'selected' : '' }}>Consultancy</option>
                                    <option value="Healthcare" {{ old('service_type', $businessProfile->service_type) == 'Healthcare' ? 'selected' : '' }}>Healthcare</option>
                                    <option value="Education" {{ old('service_type', $businessProfile->service_type) == 'Education' ? 'selected' : '' }}>Education</option>
                                    <option value="Logistics" {{ old('service_type', $businessProfile->service_type) == 'Logistics' ? 'selected' : '' }}>Logistics</option>
                                    <option value="E-commerce" {{ old('service_type', $businessProfile->service_type) == 'E-commerce' ? 'selected' : '' }}>E-commerce</option>
                                    <option value="Other" {{ old('service_type', $businessProfile->service_type) == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('service_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="strn_ntn" class="form-label">STRN/NTN</label>
                                <input type="text" class="form-control @error('strn_ntn') is-invalid @enderror" 
                                       id="strn_ntn" name="strn_ntn" value="{{ old('strn_ntn', $businessProfile->strn_ntn) }}">
                                @error('strn_ntn')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="cnic" class="form-label">CNIC (if individual)</label>
                                <input type="text" class="form-control @error('cnic') is-invalid @enderror" 
                                       id="cnic" name="cnic" value="{{ old('cnic', $businessProfile->cnic) }}">
                                @error('cnic')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="province_code" class="form-label">Province Code <span class="text-danger">*</span></label>
                                <select class="form-select @error('province_code') is-invalid @enderror" id="province_code" name="province_code" required>
                                    <option value="">Select Province</option>
                                    <option value="01" {{ old('province_code', $businessProfile->province_code) == '01' ? 'selected' : '' }}>Punjab</option>
                                    <option value="02" {{ old('province_code', $businessProfile->province_code) == '02' ? 'selected' : '' }}>Sindh</option>
                                    <option value="03" {{ old('province_code', $businessProfile->province_code) == '03' ? 'selected' : '' }}>KPK</option>
                                    <option value="04" {{ old('province_code', $businessProfile->province_code) == '04' ? 'selected' : '' }}>Balochistan</option>
                                    <option value="05" {{ old('province_code', $businessProfile->province_code) == '05' ? 'selected' : '' }}>Islamabad</option>
                                </select>
                                @error('province_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3" required>{{ old('address', $businessProfile->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="branch_name" class="form-label">Branch Name</label>
                                <input type="text" class="form-control @error('branch_name') is-invalid @enderror" 
                                       id="branch_name" name="branch_name" value="{{ old('branch_name', $businessProfile->branch_name) }}">
                                @error('branch_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="branch_code" class="form-label">Branch Code</label>
                                <input type="text" class="form-control @error('branch_code') is-invalid @enderror" 
                                       id="branch_code" name="branch_code" value="{{ old('branch_code', $businessProfile->branch_code) }}">
                                @error('branch_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="contact_phone" class="form-label">Contact Phone</label>
                                <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" 
                                       id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $businessProfile->contact_phone) }}">
                                @error('contact_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="contact_email" class="form-label">Contact Email</label>
                                <input type="email" class="form-control @error('contact_email') is-invalid @enderror" 
                                       id="contact_email" name="contact_email" value="{{ old('contact_email', $businessProfile->contact_email) }}">
                                @error('contact_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="fbr_api_token" class="form-label">FBR API Token</label>
                            <textarea class="form-control @error('fbr_api_token') is-invalid @enderror" 
                                      id="fbr_api_token" name="fbr_api_token" rows="3" 
                                      placeholder="Enter your FBR API Bearer Token">{{ old('fbr_api_token', $businessProfile->fbr_api_token) }}</textarea>
                            @error('fbr_api_token')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">This token is provided by PRAL and is valid for 5 years.</div>
                        </div>

                        <div class="mb-4">
                            <label for="whitelisted_ips" class="form-label">Whitelisted IPs</label>
                            <input type="text" class="form-control @error('whitelisted_ips') is-invalid @enderror" 
                                   id="whitelisted_ips" name="whitelisted_ips" 
                                   value="{{ old('whitelisted_ips', is_array($businessProfile->whitelisted_ips) ? implode(', ', $businessProfile->whitelisted_ips) : '') }}"
                                   placeholder="Enter comma-separated IP addresses">
                            @error('whitelisted_ips')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Enter comma-separated IP addresses that are whitelisted with FBR.</div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="logo" class="form-label">Business Logo</label>
                                <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                                       id="logo" name="logo" accept="image/*">
                                @error('logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Upload your business logo (max 2MB)</div>
                                
                                @if($businessProfile->logo_path)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $businessProfile->logo_path) }}" 
                                             alt="Current Logo" class="img-thumbnail" style="max-height: 100px;">
                                        <small class="d-block text-muted">Current logo</small>
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_sandbox" name="is_sandbox" value="1" 
                                           {{ old('is_sandbox', $businessProfile->is_sandbox) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_sandbox">
                                        Sandbox Mode
                                    </label>
                                    <div class="form-text">Use FBR sandbox environment for testing</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('business-profiles.show', $businessProfile) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2 me-2"></i>Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>