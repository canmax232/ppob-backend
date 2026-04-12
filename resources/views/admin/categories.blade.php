@extends('layouts.admin') {{-- Asumsi Bos punya layout utama --}}
@section('content')
<div class="container mt-4">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between">
            <h5 class="mb-0 fw-bold">Manajemen Logo Kategori</h5>
            <a href="/admin" class="btn btn-sm btn-secondary">Kembali ke Dashboard</a>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($categories as $cat)
                <div class="col-md-3 mb-4">
                    <div class="card text-center h-100 border-0 shadow-sm p-3">
                        <div class="mb-3">
                            @if($cat->icon_url)
                                <img src="{{ $cat->icon_url }}" style="width: 80px; height: 80px; object-fit: contain;">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px;">
                                    <i class="bi bi-grid-fill text-muted" style="font-size: 2rem;"></i>
                                </div>
                            @endif
                        </div>
                        <h6 class="fw-bold">{{ $cat->name }}</h6>
                        <button class="btn btn-sm btn-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#editCat{{ $cat->id }}">
                            Ganti Logo
                        </button>
                    </div>
                </div>

                <div class="modal fade" id="editCat{{ $cat->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <form action="/admin/categories/{{ $cat->id }}" method="POST" enctype="multipart/form-data" class="modal-content">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Kategori: {{ $cat->name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Nama Kategori</label>
                                    <input type="text" name="name" class="form-control" value="{{ $cat->name }}">
                                </div>
                                <div class="mb-3 text-start">
                                    <label class="form-label">Logo Baru (PNG/JPG)</label>
                                    <input type="file" name="image" class="form-control" accept="image/*">
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
@endsection