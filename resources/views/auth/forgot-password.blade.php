<x-guest-layout>
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="bi bi-key"></i>
            </div>
            <h1 class="auth-title">Forgot Password?</h1>
            <p class="auth-subtitle">No problem. Just let us know your email address and we'll send you a password reset link.</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" novalidate>
            @csrf

            <!-- Email Address -->
            <div class="form-floating">
                <input type="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       id="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       placeholder="name@example.com"
                       required 
                       autofocus>
                <label for="email">Email Address</label>
                @error('email')
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-auth">
                <i class="bi bi-envelope me-2"></i>
                Send Reset Link
            </button>

            <!-- Back to Login -->
            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="auth-link">
                    <i class="bi bi-arrow-left me-1"></i>Back to Login
                </a>
            </div>
        </form>

        <!-- Help Section -->
        <div class="demo-accounts">
            <div class="demo-title">
                <i class="bi bi-question-circle me-2"></i>Need Help?
            </div>
            
            <div class="demo-account">
                <div class="demo-role">
                    <i class="bi bi-envelope text-primary me-2"></i>Contact Support
                </div>
                <div class="demo-description">
                    If you're having trouble accessing your account, contact our support team for assistance.
                </div>
            </div>
            
            <div class="demo-account">
                <div class="demo-role">
                    <i class="bi bi-book text-info me-2"></i>Documentation
                </div>
                <div class="demo-description">
                    Check our user guide for account recovery and system usage instructions.
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>