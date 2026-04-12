<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - PPOB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .product-img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="bi bi-wallet2"></i> PPOB AdminPanel</a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">Hai, {{ auth()->user()->name }}</span>
                <a href="/logout" class="btn btn-sm btn-danger"><i class="bi bi-box-arrow-right"></i> Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body p-4">
                        <h6 class="text-uppercase opacity-75">Sisa Saldo Server</h6>
                        <h2 class="fw-bold mb-0">Rp {{ number_format($balance, 0, ',', '.') }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm bg-success text-white">
                    <div class="card-body p-4">
                        <h6 class="text-uppercase opacity-75">Total Pendapatan</h6>
                        <h2 class="fw-bold mb-0">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-box-seam me-2"></i> Manajemen Produk & Harga</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Gambar</th>
                                        <th>Nama Produk</th>
                                        <th>Harga Pusat</th>
                                        <th>Harga Jual</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $item)
                                    <tr>
                                        <td>
                                            @if($item->image_url)
                                                <img src="{{ $item->image_url }}" class="product-img shadow-sm" alt="logo">
                                            @else
                                                <div class="bg-secondary text-white product-img d-flex align-items-center justify-content-center">
                                                    <i class="bi bi-image"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $item->product_name }}</div>
                                            <small class="text-muted">{{ $item->brand }} - {{ $item->category }}</small>
                                        </td>
                                        <td class="text-muted">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                        <td class="fw-bold text-primary">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editPrice{{ $item->id }}">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="editPrice{{ $item->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <form action="/admin/update-price/{{ $item->id }}" method="POST" enctype="multipart/form-data" class="modal-content">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Produk: {{ $item->product_name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Harga Jual Baru (Rp)</label>
                                                        <input type="number" name="harga_jual" class="form-control form-control-lg" value="{{ $item->harga_jual }}" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Ganti Gambar/Logo Produk</label>
                                                        <input type="file" name="image" class="form-control" accept="image/png, image/jpeg, image/jpg">
                                                        <div class="form-text text-danger">*Maksimal 2MB (PNG/JPG)</div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Tren Pendapatan</h6>
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold">Transaksi Terbaru</h6>
                    </div>
                    <ul class="list-group list-group-flush">
                        @forelse($recentTransactions as $trx)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <div class="fw-bold small">{{ $trx->product_name }}</div>
                                    <small class="text-muted"><i class="bi bi-phone"></i> {{ $trx->target_number }}</small>
                                </div>
                                <span class="text-danger fw-bold small">- Rp {{ number_format($trx->amount, 0, ',', '.') }}</span>
                            </li>
                        @empty
                            <p class="text-muted text-center mt-3 p-3">Belum ada transaksi.</p>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const chartDates = {!! $chartDates !!};
        const chartTotals = {!! $chartTotals !!};

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartDates,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: chartTotals,
                    borderColor: '#1E847F',
                    backgroundColor: 'rgba(30, 132, 127, 0.2)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>