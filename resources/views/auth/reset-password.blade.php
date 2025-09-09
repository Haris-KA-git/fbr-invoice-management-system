<x-guest-layout>
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h1 class="auth-title">Reset Password</h1>
            <p class="auth-subtitle">Enter your new password to secure your account</p>
        </div>

        <form method="POST" action="{{ route('password.store') }}" novalidate>
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Email Address -->
            <div class="form-floating">
                <input type="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       id="email" 
                       name="email" 
                       value="{{ old('email', $request->email) }}" 
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

            <!-- Password -->
            <div class="form-floating">
                <input type="password" 
                       class="form-control @error('password') is-invalid @enderror" 
                       id="password" 
                       name="password" 
                       placeholder="New Password"
                       required>
                <label for="password">New Password</label>
                @error('password')
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="form-floating">
                <input type="password" 
                       class="form-control @error('password_confirmation') is-invalid @enderror" 
                       id="password_confirmation" 
                       name="password_confirmation" 
                       placeholder="Confirm New Password"
                       required>
                <label for="password_confirmation">Confirm New Password</label>
                @error('password_confirmation')
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-auth">
                <i class="bi bi-check-circle me-2"></i>
                Reset Password
            </button>

            <!-- Back to Login -->
            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="auth-link">
                    <i class="bi bi-arrow-left me-1"></i>Back to Login
                </a>
            </div>
        </form>

        <!-- Security Tips -->
        <div class="demo-accounts">
            <div class="demo-title">
                <i class="bi bi-shield-check me-2"></i>Password Security Tips
            </div>
            
            <div class="demo-account">
                <div class="demo-role">
                    <i class="bi bi-check2 text-success me-2"></i>Use Strong Passwords
                </div>
                <div class="demo-description">
                    Include uppercase, lowercase, numbers, and special characters
                </div>
            </div>
            
            <div class="demo-account">
                <div class="demo-role">
                    <i class="bi bi-check2 text-success me-2"></i>Keep It Private
                </div>
                <div class="demo-description">
                    Never share your password with others or store it in plain text
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>