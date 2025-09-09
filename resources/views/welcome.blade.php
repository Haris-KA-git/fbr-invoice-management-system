<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FBR Invoice System - Digital Invoicing Solution for Pakistan</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: float 20s ease-in-out infinite;
            z-index: 0;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .feature-card {
            transition: transform 0.3s ease;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .navbar {
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .btn {
            border-radius: 12px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .demo-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .demo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            background: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background: rgba(102, 126, 234, 0.95);">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-file-earmark-text me-2"></i>FBR Invoice System
            </a>
            
            <div class="navbar-nav ms-auto">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="nav-link">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="nav-link">Login</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="nav-link">Register</a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section d-flex align-items-center text-white">
        <div class="container hero-content">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Complete FBR Digital Invoicing Solution</h1>
                    <p class="lead mb-4">
                        Generate FBR-compliant invoices, manage customers and inventory, 
                        and sync with Pakistan's Federal Board of Revenue in real-time.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="{{ route('login') }}" class="btn btn-light btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Get Started
                        </a>
                        <a href="#features" class="btn btn-outline-light btn-lg">
                            Learn More
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="bi bi-file-earmark-text" style="font-size: 15rem; opacity: 0.8;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Powerful Features</h2>
                <p class="lead text-muted">Everything you need for FBR-compliant invoicing</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 border-0 shadow">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-shield-check text-success display-4 mb-3"></i>
                            <h5 class="card-title">FBR Compliant</h5>
                            <p class="card-text">
                                Fully compliant with Pakistan's FBR Digital Invoicing API v1.12. 
                                Automatic validation and submission to FBR servers.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 border-0 shadow">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-building text-primary display-4 mb-3"></i>
                            <h5 class="card-title">Multi-Tenant</h5>
                            <p class="card-text">
                                Manage multiple business profiles under one system. 
                                Perfect for accounting firms and business consultants.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 border-0 shadow">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-cloud-upload text-info display-4 mb-3"></i>
                            <h5 class="card-title">Real-time Sync</h5>
                            <p class="card-text">
                                Automatic synchronization with FBR servers. 
                                Offline queue system ensures no invoice is lost.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 border-0 shadow">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-file-pdf text-danger display-4 mb-3"></i>
                            <h5 class="card-title">PDF Generation</h5>
                            <p class="card-text">
                                Professional PDF invoices with QR codes, business branding, 
                                and FBR compliance formatting.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 border-0 shadow">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-graph-up text-warning display-4 mb-3"></i>
                            <h5 class="card-title">Smart Reports</h5>
                            <p class="card-text">
                                Comprehensive reporting and analytics. 
                                Track sales, taxes, and FBR submission status.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 border-0 shadow">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-people text-secondary display-4 mb-3"></i>
                            <h5 class="card-title">Role Management</h5>
                            <p class="card-text">
                                Role-based access control with Admin, Accountant, 
                                Cashier, and Auditor roles.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Demo Accounts Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-6 fw-bold">Try Demo Accounts</h2>
                <p class="lead text-muted">Test the system with pre-configured demo accounts</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card demo-card border-primary" onclick="fillDemoCredentials('admin@fbrvoice.com', 'admin123')">
                        <div class="card-header bg-primary text-white text-center" style="border-radius: 16px 16px 0 0;">
                            <h6 class="mb-0">Admin Account</h6>
                        </div>
                        <div class="card-body text-center">
                            <p class="mb-1"><strong>Email:</strong> admin@fbrvoice.com</p>
                            <p class="mb-3"><strong>Password:</strong> admin123</p>
                            <small class="text-muted">Full system access</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="card demo-card border-success" onclick="fillDemoCredentials('accountant@fbrvoice.com', 'accountant123')">
                        <div class="card-header bg-success text-white text-center" style="border-radius: 16px 16px 0 0;">
                            <h6 class="mb-0">Accountant</h6>
                        </div>
                        <div class="card-body text-center">
                            <p class="mb-1"><strong>Email:</strong> accountant@fbrvoice.com</p>
                            <p class="mb-3"><strong>Password:</strong> accountant123</p>
                            <small class="text-muted">Invoice & report access</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="card demo-card border-warning" onclick="fillDemoCredentials('cashier@fbrvoice.com', 'cashier123')">
                        <div class="card-header bg-warning text-dark text-center" style="border-radius: 16px 16px 0 0;">
                            <h6 class="mb-0">Cashier</h6>
                        </div>
                        <div class="card-body text-center">
                            <p class="mb-1"><strong>Email:</strong> cashier@fbrvoice.com</p>
                            <p class="mb-3"><strong>Password:</strong> cashier123</p>
                            <small class="text-muted">Invoice creation only</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="card demo-card border-info" onclick="fillDemoCredentials('demo@business.com', 'demo123')">
                        <div class="card-header bg-info text-white text-center" style="border-radius: 16px 16px 0 0;">
                            <h6 class="mb-0">Demo Business</h6>
                        </div>
                        <div class="card-body text-center">
                            <p class="mb-1"><strong>Email:</strong> demo@business.com</p>
                            <p class="mb-3"><strong>Password:</strong> demo123</p>
                            <small class="text-muted">Sample business data</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="btn btn-light btn-lg">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login Now
                </a>
                <p class="text-white-50 mt-3">
                    <i class="bi bi-info-circle me-1"></i>Click any demo card above to auto-fill login credentials
                </p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>FBR Invoice System</h5>
                    <p class="mb-0">Complete Digital Invoicing Solution for Pakistan</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">&copy; {{ date('Y') }} FBR Invoice System. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function fillDemoCredentials(email, password) {
            // Store credentials in session storage for the login page
            sessionStorage.setItem('demo_email', email);
            sessionStorage.setItem('demo_password', password);
            
            // Redirect to login page
            window.location.href = '{{ route("login") }}';
        }
    </script>
</body>
</html>