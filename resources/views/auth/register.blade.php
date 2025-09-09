<x-guest-layout>
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="bi bi-person-plus"></i>
            </div>
            <h1 class="auth-title">Create Account</h1>
            <p class="auth-subtitle">Join the FBR Invoice System today</p>
        </div>

        <form method="POST" action="{{ route('register') }}" novalidate>
            @csrf

            <!-- Name -->
            <div class="form-floating">
                <input type="text" 
                       class="form-control @error('name') is-invalid @enderror" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}" 
                       placeholder="Full Name"
                       required 
                       autofocus>
                <label for="name">Full Name</label>
                @error('name')
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Email Address -->
            <div class="form-floating">
                <input type="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       id="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       placeholder="name@example.com"
                       required>
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
                       placeholder="Password"
                       required>
                <label for="password">Password</label>
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
                       placeholder="Confirm Password"
                       required>
                <label for="password_confirmation">Confirm Password</label>
                @error('password_confirmation')
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Terms Agreement -->
            <div class="form-check mb-4">
                <input type="checkbox" class="form-check-input" id="terms" required>
                <label class="form-check-label" for="terms">
                    I agree to the <a href="#" class="auth-link">Terms of Service</a> and 
                    <a href="#" class="auth-link">Privacy Policy</a>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-auth">
                <i class="bi bi-person-check me-2"></i>
                Create Account
            </button>

            <!-- Login Link -->
            <div class="text-center mt-4">
                <span class="text-muted">Already have an account?</span>
                <a href="{{ route('login') }}" class="auth-link ms-1">Sign in</a>
            </div>
        </form>

        <!-- Features Preview -->
        <div class="demo-accounts">
            <div class="demo-title">
                <i class="bi bi-star me-2"></i>What You'll Get
            </div>
            
            <div class="row g-2">
                <div class="col-6">
                    <div class="demo-account">
                        <div class="demo-role">
                            <i class="bi bi-shield-check text-success me-1"></i>FBR Compliant
                        </div>
                        <div class="demo-description">Fully compliant invoicing</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="demo-account">
                        <div class="demo-role">
                            <i class="bi bi-cloud-upload text-info me-1"></i>Real-time Sync
                        </div>
                        <div class="demo-description">Automatic FBR submission</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="demo-account">
                        <div class="demo-role">
                            <i class="bi bi-graph-up text-warning me-1"></i>Smart Reports
                        </div>
                        <div class="demo-description">Comprehensive analytics</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="demo-account">
                        <div class="demo-role">
                            <i class="bi bi-people text-primary me-1"></i>Multi-tenant
                        </div>
                        <div class="demo-description">Multiple businesses</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>