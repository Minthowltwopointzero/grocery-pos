<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Grocery POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg,#0f172a, #1e293b); min-height:100vh; display:flex; align-items:center; justify-content:center; font-family:'Segoe UI',Arial,sans-serif;}
        .login-card { width:380px; border:none; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,.25); }
    </style>
</head>
<body>
    <div class="card login-card">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <i class="bi bi-shop-window" style="font-size:3rem;color:#1e293b;"></i>
                <h4 class="mt-2 mb-0 fw-bold">Sari-Sari Store</h4>
                <div class="text-muted small">Sign in to continue</div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
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
                <button type="submit" class="btn btn-dark w-100">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
