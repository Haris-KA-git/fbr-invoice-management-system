<x-guest-layout>
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="bi bi-file-earmark-text"></i>
            </div>
            <h1 class="auth-title">Welcome Back</h1>
            <p class="auth-subtitle">Sign in to your FBR Invoice System account</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" novalidate>
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

            <!-- Remember Me & Forgot Password -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember_me" name="remember">
                    <label class="form-check-label" for="remember_me">
                        Remember me
                    </label>
                </div>
                
                @if (Route::has('password.request'))
                    <a class="auth-link" href="{{ route('password.request') }}">
                        Forgot password?
                    </a>
                @endif
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-auth">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                Sign In
            </button>

            <!-- Register Link -->
            @if (Route::has('register'))
                <div class="text-center mt-4">
                    <span class="text-muted">Don't have an account?</span>
                    <a href="{{ route('register') }}" class="auth-link ms-1">Create one</a>
                </div>
            @endif
        </form>

        <!-- Demo Accounts -->
        <div class="demo-accounts">
            <div class="demo-title">
                <i class="bi bi-play-circle me-2"></i>Try Demo Accounts
            </div>
            
            <div class="row g-2">
                <div class="col-6">
                    <div class="demo-account" data-email="admin@fbrvoice.com" data-password="admin123">
                        <div class="demo-role">Admin</div>
                        <div class="demo-credentials">admin@fbrvoice.com</div>
                        <div class="demo-description">Full system access</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="demo-account" data-email="accountant@fbrvoice.com" data-password="accountant123">
                        <div class="demo-role">Accountant</div>
                        <div class="demo-credentials">accountant@fbrvoice.com</div>
                        <div class="demo-description">Invoice management</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="demo-account" data-email="cashier@fbrvoice.com" data-password="cashier123">
                        <div class="demo-role">Cashier</div>
                        <div class="demo-credentials">cashier@fbrvoice.com</div>
                        <div class="demo-description">Invoice creation</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="demo-account" data-email="demo@business.com" data-password="demo123">
                        <div class="demo-role">Demo User</div>
                        <div class="demo-credentials">demo@business.com</div>
                        <div class="demo-description">Sample business</div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-2">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>Click any demo account to auto-fill credentials
                </small>
            </div>
        </div>
    </div>

    <script>
        // Demo account click to fill
        document.querySelectorAll('.demo-account').forEach(account => {
            account.addEventListener('click', function() {
                const email = this.dataset.email;
                const password = this.dataset.password;
                
                if (email && password) {
                    const emailInput = document.getElementById('email');
                    const passwordInput = document.getElementById('password');
                    
                    if (emailInput && passwordInput) {
                        emailInput.value = email;
                        passwordInput.value = password;
                        
                        // Trigger focus events to update floating labels
                        emailInput.dispatchEvent(new Event('input'));
                        passwordInput.dispatchEvent(new Event('input'));
                        
                        // Add visual feedback
                        this.style.background = '#e0f2fe';
                        setTimeout(() => {
                            this.style.background = 'white';
                        }, 500);
                    }
                }
            });
        });
        
        // Check for demo credentials from welcome page
        document.addEventListener('DOMContentLoaded', function() {
            const demoEmail = sessionStorage.getItem('demo_email');
            const demoPassword = sessionStorage.getItem('demo_password');
            
            if (demoEmail && demoPassword) {
                const emailInput = document.getElementById('email');
                const passwordInput = document.getElementById('password');
                
                if (emailInput && passwordInput) {
                    emailInput.value = demoEmail;
                    passwordInput.value = demoPassword;
                    
                    // Trigger events to update floating labels
                    emailInput.dispatchEvent(new Event('input'));
                    passwordInput.dispatchEvent(new Event('input'));
                    
                    // Clear session storage
                    sessionStorage.removeItem('demo_email');
                    sessionStorage.removeItem('demo_password');
                    
                    // Show notification
                    const notification = document.createElement('div');
                    notification.className = 'alert alert-success';
                    notification.innerHTML = '<i class="bi bi-check-circle me-2"></i>Demo credentials filled automatically. Click Sign In to continue.';
                    document.querySelector('.auth-card form').prepend(notification);
                    
                    setTimeout(() => {
                        notification.remove();
                    }, 5000);
                }
            }
        });

        // Enhanced form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            // Real-time validation
            emailInput.addEventListener('input', function() {
                validateEmail(this);
            });

            passwordInput.addEventListener('input', function() {
                validatePassword(this);
            });

            form.addEventListener('submit', function(e) {
                let isValid = true;

                if (!validateEmail(emailInput)) {
                    isValid = false;
                }

                if (!validatePassword(passwordInput)) {
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });

            function validateEmail(input) {
                const email = input.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (!email) {
                    showError(input, 'Email is required');
                    return false;
                } else if (!emailRegex.test(email)) {
                    showError(input, 'Please enter a valid email address');
                    return false;
                } else {
                    clearError(input);
                    return true;
                }
            }

            function validatePassword(input) {
                const password = input.value;
                
                if (!password) {
                    showError(input, 'Password is required');
                    return false;
                } else if (password.length < 6) {
                    showError(input, 'Password must be at least 6 characters');
                    return false;
                } else {
                    clearError(input);
                    return true;
                }
            }

            function showError(input, message) {
                input.classList.add('is-invalid');
                let feedback = input.parentNode.querySelector('.invalid-feedback');
                if (!feedback) {
                    feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    input.parentNode.appendChild(feedback);
                }
                feedback.innerHTML = `<i class="bi bi-exclamation-circle me-1"></i>${message}`;
            }

            function clearError(input) {
                input.classList.remove('is-invalid');
                const feedback = input.parentNode.querySelector('.invalid-feedback');
                if (feedback && !feedback.textContent.includes('{{ $message }}')) {
                    feedback.remove();
                }
            }
        });
    </script>
</x-guest-layout>