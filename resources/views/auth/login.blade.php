<x-guest-layout>
    <div class="container-fluid vh-100">
        <div class="row h-100">
            <!-- Left Side - Branding -->
            <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="text-center text-white">
                    <div class="mb-4">
                        <i class="bi bi-file-earmark-text display-1"></i>
                    </div>
                    <h1 class="display-4 fw-bold mb-3">FBR Invoice System</h1>
                    <p class="lead mb-4">Complete Digital Invoicing Solution for Pakistan</p>
                    <div class="row text-center">
                        <div class="col-4">
                            <i class="bi bi-shield-check display-6 mb-2"></i>
                            <p class="small">FBR Compliant</p>
                        </div>
                        <div class="col-4">
                            <i class="bi bi-cloud-upload display-6 mb-2"></i>
                            <p class="small">Real-time Sync</p>
                        </div>
                        <div class="col-4">
                            <i class="bi bi-graph-up display-6 mb-2"></i>
                            <p class="small">Smart Reports</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <div class="w-100" style="max-width: 400px;">
                    <div class="text-center mb-4">
                        <h2 class="h3 mb-3">Welcome Back</h2>
                        <p class="text-muted">Sign in to your account to continue</p>
                    </div>

                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Email Address -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember_me" name="remember">
                            <label class="form-check-label" for="remember_me">
                                Remember me
                            </label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Sign In
                            </button>
                        </div>

                        <div class="text-center mt-3">
                            @if (Route::has('password.request'))
                                <a class="text-decoration-none" href="{{ route('password.request') }}">
                                    Forgot your password?
                                </a>
                            @endif
                        </div>
                    </form>

                    <!-- Demo Accounts -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="small text-muted mb-2">Demo Accounts:</h6>
                        <div class="row small">
                            <div class="col-6">
                                <strong>Admin:</strong><br>
                                admin@fbrvoice.com<br>
                                admin123
                            </div>
                            <div class="col-6">
                                <strong>Accountant:</strong><br>
                                accountant@fbrvoice.com<br>
                                accountant123
                            </div>
                        </div>
                        <div class="row small mt-2">
                            <div class="col-6">
                                <strong>Cashier:</strong><br>
                                cashier@fbrvoice.com<br>
                                cashier123
                            </div>
                            <div class="col-6">
                                <strong>Demo User:</strong><br>
                                demo@business.com<br>
                                demo123
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>