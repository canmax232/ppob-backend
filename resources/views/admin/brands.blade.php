<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Logo Brand - PPOB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/admin"><i class="bi bi-wallet2"></i> PPOB AdminPanel</a>
            <a href="/admin" class="btn btn-sm btn-outline-light">Kembali ke Dashboard</a>
        </div>
    </nav>

    <div class="container mt-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-tags-fill text-primary me-2"></i> Manajemen Logo Merek (Brand)</h5>
                <small class="text-muted">Satu logo yang Anda upload di sini akan otomatis mengubah <b>SEMUA</b> produk dengan merek yang sama.</small>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($brands as $brand)
                    <div class="col-md-3 col-6 mb-4">
                        <div class="card text-center h-100 border-0 shadow-sm p-3">
                            <div class="mb-3 d-flex align-items-center justify-content-center" style="height: 100px;">
                                @if($brand->image_url)
                                    <img src="{{ $brand->image_url }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center w-100 h-100">
                                        <h1 class="text-muted opacity-50 m-0">{{ substr($brand->brand, 0, 1) }}</h1>
                                    </div>
                                @endif
                            </div>
                            <h6 class="fw-bold text-truncate" title="{{ $brand->brand }}">{{ $brand->brand }}</h6>
                            <button class="btn btn-sm btn-primary mt-2 w-100" data-bs-toggle="modal" data-bs-target="#editBrand{{ md5($brand->brand) }}">
                                <i class="bi bi-image"></i> Ganti Logo
                            </button>
                        </div>
                    </div>

                    <div class="modal fade" id="editBrand{{ md5($brand->brand) }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <form action="/admin/brands/update" method="POST" enctype="multipart/form-data" class="modal-content text-start">
                                @csrf
                                <input type="hidden" name="brand" value="{{ $brand->brand }}">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">Edit Logo: {{ $brand->brand }}</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Logo Baru (PNG/JPG)</label>
                                        <input type="file" name="image" class="form-control" accept="image/*" required>
                                        <small class="text-danger">*Logo ini akan dipakai ke SEMUA denom {{ $brand->brand }}</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>