<!doctype html>
<html lang="az">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h5 class="mb-3">Admin girişi</h5>
                    @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
                    <form method="POST" action="{{ route('admin.login.submit') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">E-poçt</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Şifrə</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="d-grid"><button class="btn btn-primary" type="submit">Daxil ol</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
