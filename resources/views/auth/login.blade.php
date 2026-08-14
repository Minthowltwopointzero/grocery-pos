<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Grocery POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', 'Segoe UI', Arial, sans-serif; }
        body {
            background: radial-gradient(circle at top left, #312e81, #1e1b4b 55%, #14123a);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 380px;
            border: none;
            border-radius: 1.25rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,.4);
        }
        .brand-icon {
            width: 56px; height: 56px;
            border-radius: 1rem;
            background: linear-gradient(135deg, #6366f1, #4338ca);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto;
            box-shadow: 0 10px 20px rgba(99,102,241,.35);
        }
        .form-control { border-radius: .6rem; border-color: #e2e4f1; padding: .6rem .85rem; }
        .form-control:focus { border-color: #6366f1; box-shadow: 0 0 0 .2rem rgba(99,102,241,.15); }
        .btn-login {
            background: linear-gradient(135deg, #6366f1, #4338ca);
            border: none;
            border-radius: .6rem;
            font-weight: 700;
            padding: .6rem;
        }
        .btn-login:hover { background: linear-gradient(135deg, #4f46e5, #312e81); }
    </style>
</head>
<body>
    <div class="card login-card">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="brand-icon mb-3">
                    <i class="bi bi-basket3-fill text-white" style="font-size:1.6rem;"></i>
                </div>
                <h4 class="mt-2 mb-0 fw-bold" style="letter-spacing:-.02em;">Grocery POS</h4>
                <div class="text-muted small">Sign in to continue</div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger py-2 small" style="border-radius:.6rem; border:none; background:#fee2e2; color:#991b1b;">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Username</label>
                    <input type="text" name="username" class="form-control" value="{{ old('username') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label small" for="remember">Remember me</label>
                </div>
                <button type="submit" class="btn btn-login w-100 text-white">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
