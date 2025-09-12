<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <a href="{{ route('items.show', $item) }}" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="h3 mb-0">Edit Item</h2>
                <p class="text-muted mb-0">Update {{ $item->name }} information</p>
            </div>
        </div>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('items.update', $item) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="business_profile_id" class="form-label">Business Profile <span class="text-danger">*</span></label>
                            <select class="form-select @error('business_profile_id') is-invalid @enderror" id="business_profile_id" name="business_profile_id" required>
                                <option value="">Select Business Profile</option>
                                @foreach($businessProfiles as $profile)
                                    <option value="{{ $profile->id }}" {{ old('business_profile_id', $item->business_profile_id) == $profile->id ? 'selected' : '' }}>
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
                                <label for="item_code" class="form-label">Item Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('item_code') is-invalid @enderror" 
                                       id="item_code" name="item_code" value="{{ old('item_code', $item->item_code) }}" required>
                                @error('item_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Unique identifier for this item within the business profile</div>
                            </div>

                            <div class="col-md-6">
                                <label for="name" class="form-label">Item Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $item->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $item->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="hs_code" class="form-label">HS Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('hs_code') is-invalid @enderror" 
                                       id="hs_code" name="hs_code" value="{{ old('hs_code', $item->hs_code) }}" required>
                                @error('hs_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Harmonized System Code for tax classification (required for FBR)</div>
                            </div>

                            <div class="col-md-6">
                                <label for="unit_of_measure" class="form-label">Unit of Measure <span class="text-danger">*</span></label>
                                <select class="form-select @error('unit_of_measure') is-invalid @enderror" id="unit_of_measure" name="unit_of_measure" required>
                                    <option value="">Select Unit</option>
                                    <option value="PCS" {{ old('unit_of_measure', $item->unit_of_measure) == 'PCS' ? 'selected' : '' }}>Pieces (PCS)</option>
                                    <option value="KG" {{ old('unit_of_measure', $item->unit_of_measure) == 'KG' ? 'selected' : '' }}>Kilogram (KG)</option>
                                    <option value="LTR" {{ old('unit_of_measure', $item->unit_of_measure) == 'LTR' ? 'selected' : '' }}>Liter (LTR)</option>
                                    <option value="MTR" {{ old('unit_of_measure', $item->unit_of_measure) == 'MTR' ? 'selected' : '' }}>Meter (MTR)</option>
                                    <option value="SQM" {{ old('unit_of_measure', $item->unit_of_measure) == 'SQM' ? 'selected' : '' }}>Square Meter (SQM)</option>
                                    <option value="HR" {{ old('unit_of_measure', $item->unit_of_measure) == 'HR' ? 'selected' : '' }}>Hour (HR)</option>
                                    <option value="DAY" {{ old('unit_of_measure', $item->unit_of_measure) == 'DAY' ? 'selected' : '' }}>Day (DAY)</option>
                                    <option value="SET" {{ old('unit_of_measure', $item->unit_of_measure) == 'SET' ? 'selected' : '' }}>Set (SET)</option>
                                </select>
                                @error('unit_of_measure')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="price" class="form-label">Unit Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₨</span>
                                    <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" 
                                           id="price" name="price" value="{{ old('price', $item->price) }}" required>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="tax_rate" class="form-label">Tax Rate (%) <span class="text-danger">*</span></label>
                                <select class="form-select @error('tax_rate') is-invalid @enderror" id="tax_rate" name="tax_rate" required>
                                    <option value="">Select Tax Rate</option>
                                    <option value="0" {{ old('tax_rate', $item->tax_rate) == '0' ? 'selected' : '' }}>0% (Exempt)</option>
                                    <option value="5" {{ old('tax_rate', $item->tax_rate) == '5' ? 'selected' : '' }}>5%</option>
                                    <option value="12" {{ old('tax_rate', $item->tax_rate) == '12' ? 'selected' : '' }}>12%</option>
                                    <option value="17" {{ old('tax_rate', $item->tax_rate) == '17' ? 'selected' : '' }}>17% (Standard)</option>
                                    <option value="18" {{ old('tax_rate', $item->tax_rate) == '18' ? 'selected' : '' }}>18%</option>
                                </select>
                                @error('tax_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="sro_references" class="form-label">SRO References</label>
                            <input type="text" class="form-control @error('sro_references') is-invalid @enderror" 
                                   id="sro_references" name="sro_references" 
                                   value="{{ old('sro_references', is_array($item->sro_references) ? implode(', ', $item->sro_references) : '') }}"
                                   placeholder="Enter comma-separated SRO numbers">
                            @error('sro_references')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Enter relevant SRO (Statutory Regulatory Order) numbers, separated by commas</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('items.show', $item) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2 me-2"></i>Update Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>