<x-guest-layout>
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="bi bi-envelope-check"></i>
            </div>
            <h1 class="auth-title">Verify Email</h1>
            <p class="auth-subtitle">We've sent a verification link to your email address. Please check your inbox and click the link to verify your account.</p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                A new verification link has been sent to your email address.
            </div>
        @endif

        <div class="d-grid gap-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-auth">
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    Resend Verification Email
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary w-100" style="height: 50px; border-radius: 12px;">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Log Out
                </button>
            </form>
        </div>

        <!-- Email Tips -->
        <div class="demo-accounts">
            <div class="demo-title">
                <i class="bi bi-lightbulb me-2"></i>Can't Find the Email?
            </div>
            
            <div class="demo-account">
                <div class="demo-role">
                    <i class="bi bi-folder2 text-warning me-2"></i>Check Spam Folder
                </div>
                <div class="demo-description">
                    Sometimes verification emails end up in spam or junk folders
                </div>
            </div>
            
            <div class="demo-account">
                <div class="demo-role">
                    <i class="bi bi-clock text-info me-2"></i>Wait a Few Minutes
                </div>
                <div class="demo-description">
                    Email delivery can take a few minutes during peak times
                </div>
            </div>
            
            <div class="demo-account">
                <div class="demo-role">
                    <i class="bi bi-envelope text-primary me-2"></i>Check Email Address
                </div>
                <div class="demo-description">
                    Make sure you entered the correct email address during registration
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>