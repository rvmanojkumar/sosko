{{-- resources/views/auth/admin-login.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login - {{ config('app.name') }}</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #EBF5FB 0%, #D6EAF8 100%);
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background Circles */
        .bg-circle {
            position: fixed;
            border-radius: 50%;
            background: rgba(41, 128, 185, 0.05);
            pointer-events: none;
            animation: float 20s infinite ease-in-out;
        }

        .circle-1 {
            width: 400px;
            height: 400px;
            top: -200px;
            right: -200px;
            background: rgba(41, 128, 185, 0.1);
        }

        .circle-2 {
            width: 600px;
            height: 600px;
            bottom: -300px;
            left: -300px;
            background: rgba(26, 82, 118, 0.08);
            animation-delay: -5s;
        }

        .circle-3 {
            width: 300px;
            height: 300px;
            top: 50%;
            right: 10%;
            background: rgba(41, 128, 185, 0.05);
            animation-delay: -10s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(30px, -30px) rotate(10deg); }
        }

        /* Main Container */
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        /* Glass Card */
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
            display: flex;
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        /* Left Side - Illustration */
        .login-illustration {
            flex: 1;
            background: linear-gradient(135deg, #2980B9 0%, #1A5276 100%);
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .login-illustration::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 40px 40px;
            animation: moveGrid 30s linear infinite;
        }

        @keyframes moveGrid {
            0% { transform: translate(0, 0); }
            100% { transform: translate(40px, 40px); }
        }

        .illustration-icon {
            font-size: 80px;
            margin-bottom: 24px;
            position: relative;
            z-index: 1;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .illustration-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }

        .illustration-text {
            font-size: 14px;
            opacity: 0.9;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }

        .feature-list {
            margin-top: 32px;
            text-align: left;
            position: relative;
            z-index: 1;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .feature-item i {
            width: 24px;
            margin-right: 12px;
            font-size: 16px;
        }

        /* Right Side - Form */
        .login-form {
            flex: 1;
            padding: 48px;
            background: white;
        }

        .logo-area {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #2980B9, #1A5276);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .logo-icon i {
            font-size: 32px;
            color: white;
        }

        .logo-text {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, #2980B9, #1A5276);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .welcome-text {
            text-align: center;
            margin-bottom: 32px;
        }

        .welcome-text h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 8px;
        }

        .welcome-text p {
            color: #6B7280;
            font-size: 14px;
        }

        /* Tabs */
        .role-tabs {
            display: flex;
            background: #F3F4F6;
            border-radius: 12px;
            padding: 4px;
            margin-bottom: 32px;
        }

        .role-tab {
            flex: 1;
            text-align: center;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            color: #6B7280;
        }

        .role-tab.active {
            background: white;
            color: #2980B9;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .role-tab i {
            margin-right: 8px;
        }

        /* Form Group */
        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            font-size: 18px;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: #2980B9;
            box-shadow: 0 0 0 3px rgba(41, 128, 185, 0.1);
        }

        /* Password Toggle */
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #9CA3AF;
            transition: color 0.3s;
        }

        .password-toggle:hover {
            color: #2980B9;
        }

        /* Options */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-size: 14px;
            color: #6B7280;
        }

        .checkbox-label input {
            margin-right: 8px;
            cursor: pointer;
        }

        .forgot-link {
            color: #2980B9;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s;
        }

        .forgot-link:hover {
            color: #1A5276;
        }

        /* Login Button */
        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #2980B9, #1A5276);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .login-btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(41, 128, 185, 0.3);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        /* Alert Messages */
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert i {
            margin-right: 12px;
            font-size: 18px;
        }

        .alert-danger {
            background: #FEF2F2;
            border-left: 4px solid #EF4444;
            color: #DC2626;
        }

        .alert-success {
            background: #F0FDF4;
            border-left: 4px solid #22C55E;
            color: #16A34A;
        }

        /* Loading State */
        .login-btn.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .login-btn.loading span {
            display: none;
        }

        .login-btn.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid white;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-card {
                flex-direction: column;
                max-width: 400px;
            }
            
            .login-illustration {
                padding: 32px;
            }
            
            .login-form {
                padding: 32px;
            }
            
            .illustration-title {
                font-size: 24px;
            }
            
            .feature-list {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="bg-circle circle-1"></div>
    <div class="bg-circle circle-2"></div>
    <div class="bg-circle circle-3"></div>

    <div class="login-container">
        <div class="login-card">
            <!-- Left Side - Illustration -->
            <div class="login-illustration">
                <div class="illustration-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="illustration-title">
                    Welcome Back!
                </div>
                <div class="illustration-text">
                    Sign in to access your dashboard and manage your store
                </div>
                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fas fa-chart-simple"></i>
                        <span>Real-time analytics & insights</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-box"></i>
                        <span>Product & inventory management</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Order tracking & fulfillment</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-users"></i>
                        <span>Customer & vendor management</span>
                    </div>
                </div>
            </div>

            <!-- Right Side - Form -->
            <div class="login-form">
                <div class="logo-area">
                    <div class="logo-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="logo-text">{{ config('app.name') }}</div>
                </div>

                <div class="welcome-text">
                    <h2>Sign In</h2>
                    <p>Enter your credentials to access your account</p>
                </div>

                <!-- Role Tabs -->
                <div class="role-tabs">
                    <div class="role-tab active" data-role="admin">
                        <i class="fas fa-user-shield"></i> Admin
                    </div>
                    <div class="role-tab" data-role="vendor">
                        <i class="fas fa-store"></i> Vendor
                    </div>
                </div>

                <!-- Error Messages -->
                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif

                <form id="loginForm" method="POST" action="{{ route('login') }}">
                    @csrf
                    <input type="hidden" name="role" id="selectedRole" value="admin">
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   placeholder="admin@example.com"
                                   required 
                                   autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter your password"
                                   required>
                            <span class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember"> Remember me
                        </label>
                        {{--<a href="{{ route('password.request') }}" class="forgot-link">
                            Forgot Password?
                        </a>--}}
                    </div>

                    <button type="submit" class="login-btn" id="loginBtn">
                        <span>Sign In</span>
                    </button>
                </form>

                <div style="text-align: center; margin-top: 32px; padding-top: 24px; border-top: 1px solid #E5E7EB;">
                    <p style="font-size: 12px; color: #9CA3AF;">
                        <i class="fas fa-shield-alt"></i> Secure login with 256-bit encryption
                    </p>
                    <p style="font-size: 12px; color: #9CA3AF; margin-top: 8px;">
                        © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Role tab switching
        const tabs = document.querySelectorAll('.role-tab');
        const roleInput = document.getElementById('selectedRole');
        const emailInput = document.getElementById('email');
        const loginBtn = document.getElementById('loginBtn');
        
        // Demo credentials for easy testing
        const demoCredentials = {
            admin: {
                email: 'admin@example.com',
                password: 'password'
            },
            vendor: {
                email: 'vendor@example.com',
                password: 'password'
            }
        };

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                this.classList.add('active');
                
                const role = this.dataset.role;
                roleInput.value = role;
                
                // Auto-fill demo credentials
                if (demoCredentials[role]) {
                    emailInput.value = demoCredentials[role].email;
                    // Optionally auto-fill password (commented for security)
                    // document.getElementById('password').value = demoCredentials[role].password;
                }
                
                // Animate form
                const form = document.querySelector('.login-form');
                form.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    form.style.transform = 'scale(1)';
                }, 150);
            });
        });

        // Password visibility toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Form submission loading state
        const loginForm = document.getElementById('loginForm');
        loginForm.addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            // Button will stay in loading state until form submits
        });

        // Add floating animation effect to input fields
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.parentElement.classList.remove('focused');
                }
            });
        });

        // Pre-fill admin credentials on page load (for development)
        if (emailInput.value === '') {
            emailInput.value = demoCredentials.admin.email;
        }
        
        // Add smooth transition for card on load
        document.querySelector('.login-card').style.opacity = '0';
        document.querySelector('.login-card').style.transform = 'translateY(20px)';
        setTimeout(() => {
            document.querySelector('.login-card').style.transition = 'all 0.5s ease';
            document.querySelector('.login-card').style.opacity = '1';
            document.querySelector('.login-card').style.transform = 'translateY(0)';
        }, 100);
    </script>
</body>
</html>