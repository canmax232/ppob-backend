<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - PPOB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-md-4"><div class="card p-3 text-center shadow-sm"><h5 class="text-muted">Total Pengguna</h5><h2 class="fw-bold text-primary">{{ $totalUsers }}</h2></div></div>
            <div class="col-md-4"><div class="card p-3 text-center shadow-sm"><h5 class="text-muted">Produk Aktif</h5><h2 class="fw-bold text-success">{{ $totalProducts }}</h2></div></div>
            <div class="col-md-4"><div class="card p-3 text-center shadow-sm"><h5 class="text-muted">Total Transaksi</h5><h2 class="fw-bold text-warning">{{ $totalTransactions }}</h2></div></div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card p-4 shadow-sm">
                    <h5 class="mb-3"><i class="bi bi-graph-up-arrow"></i> Grafik Pendapatan Transaksi</h5>
                    <canvas id="revenueChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-7">
                <div class="card p-4 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="bi bi-box-seam"></i> Manajemen Produk</h5>
                        
                        <form action="/admin/sync" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-arrow-repeat"></i> Sinkronkan Harga Digiflazz
                            </button>
                        </form>
                    </div>

                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Kode SKU</th>
                                <th>Nama Produk</th>
                                <th>Harga Jual</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $prod)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $prod->product_code }}</span></td>
                                <td>{{ $prod->name }}</td>
                                <td class="fw-bold text-success">Rp {{ number_format($prod->price, 0, ',', '.') }}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $prod->id }}">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>
                                    
                                    <div class="modal fade" id="editModal{{ $prod->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form action="/admin/product/{{ $prod->id }}" method="POST">
                                                @csrf
                                                <div class="modal-content">
                                                    <div class="modal-header bg-warning">
                                                        <h5 class="modal-title fw-bold">Ubah Data: {{ $prod->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        <div class="mb-3">
                                                            <label class="fw-bold">Kode Produk (SKU Digiflazz)</label>
                                                            <input type="text" name="product_code" class="form-control" value="{{ $prod->product_code }}" required>
                                                            <small class="text-muted">Pastikan kode ini SAMA PERSIS dengan SKU di Digiflazz (contoh: tsel10).</small>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="fw-bold">Harga Jual ke Pelanggan</label>
                                                            <input type="number" name="price" class="form-control" value="{{ $prod->price }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Simpan Perubahan</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card p-4 shadow-sm">
                    <h5 class="mb-3"><i class="bi bi-receipt"></i> Transaksi Masuk</h5>
                    <ul class="list-group list-group-flush">
                        @forelse($recentTransactions as $trx)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $trx->product->name ?? 'Produk Dihapus' }}</h6>
                                    <small class="text-muted"><i class="bi bi-telephone"></i> {{ $trx->target_number }}</small>
                                </div>
                                <span class="text-danger fw-bold">- Rp {{ number_format($trx->amount, 0, ',', '.') }}</span>
                            </li>
                        @empty
                            <p class="text-muted text-center mt-3">Belum ada transaksi.</p>
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