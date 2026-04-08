<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin PPOB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body p-4 text-center">
                        <h4 class="mb-4 fw-bold text-teal">Gembok Admin Panel</h4>
                        
                        @if($errors->any())
                            <div class="alert alert-danger">{{ $errors->first() }}</div>
                        @endif

                        <form action="/login" method="POST">
                            @csrf
                            <div class="mb-3 text-start">
                                <label>Email Admin</label>
                                <input type="email" name="email" class="form-control" required value="admin@ppob.com">
                            </div>
                            <div class="mb-4 text-start">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required value="admin123">
                            </div>
                            <button type="submit" class="btn btn-dark w-100">Masuk Dapur</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>