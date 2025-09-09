<x-guest-layout>
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="bi bi-shield-check"></i>
            </div>
            <h1 class="auth-title">Confirm Password</h1>
            <p class="auth-subtitle">Please confirm your password before continuing with this secure action</p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" novalidate>
            @csrf

            <!-- Password -->
            <div class="form-floating">
                <input type="password" 
                       class="form-control @error('password') is-invalid @enderror" 
                       id="password" 
                       name="password" 
                       placeholder="Password"
                       required 
                       autofocus>
                <label for="password">Current Password</label>
                @error('password')
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-auth">
                <i class="bi bi-check-circle me-2"></i>
                Confirm Password
            </button>

            <!-- Cancel Link -->
            <div class="text-center mt-4">
                <a href="{{ url()->previous() }}" class="auth-link">
                    <i class="bi bi-arrow-left me-1"></i>Cancel
                </a>
            </div>
        </form>

        <!-- Security Notice -->
        <div class="demo-accounts">
            <div class="demo-title">
                <i class="bi bi-info-circle me-2"></i>Security Notice
            </div>
            
            <div class="demo-account">
                <div class="demo-role">
                    <i class="bi bi-shield-lock text-warning me-2"></i>Secure Area
                </div>
                <div class="demo-description">
                    You're accessing a secure area that requires password confirmation for your protection.
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>