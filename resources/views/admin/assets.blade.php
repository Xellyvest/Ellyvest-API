@extends('layouts.admin')

@section('title', 'Asset Management')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-6">
                    <h4>Asset List</h4>
                </div>
                <div class="col-6">
                    <button class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#addAssetModal">
                        <i class="fa fa-plus"></i> Sync New Asset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <form action="{{ route('admin.asset') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search name or symbol..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="type" class="form-select" onchange="this.form.submit()">
                            <option value="">All Types</option>
                            <option value="stocks" {{ request('type') == 'stocks' ? 'selected' : '' }}>Stocks</option>
                            <option value="crypto" {{ request('type') == 'crypto' ? 'selected' : '' }}>Crypto</option>
                            <option value="etf" {{ request('type') == 'etf' ? 'selected' : '' }}>ETF</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-dark">Filter</button>
                    </div>
                </form>
            </div>
            
            <div class="table-responsive px-4">
                <table class="table table-bordertop">
                    <thead>
                        <tr>
                            <th>Asset</th>
                            <th>Symbol</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>24h Change</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $item->img }}" alt="" style="width: 30px; margin-right: 10px;">
                                    <span class="fw-bold">{{ $item->name }}</span>
                                </div>
                            </td>
                            <td><span class="badge badge-light-secondary text-dark">{{ $item->symbol }}</span></td>
                            <td><span class="text-uppercase small">{{ $item->type }}</span></td>
                            <td>${{ number_format($item->price, 2) }}</td>
                            <td>
                                <span class="{{ $item->change >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $item->change >= 0 ? '+' : '' }}{{ number_format($item->changes_percentage, 2) }}%
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $item->status == 'active' ? 'badge-light-success' : 'badge-light-danger' }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-dark btn-sm dropdown-toggle" data-bs-toggle="dropdown">Action</button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <form action="{{ route('admin.assets.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Delete this asset?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">Delete</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">No assets found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <!-- Pagination Links -->
                <div class="jsgrid-pager my-3 mx-2">
                    Pages:
                    @if ($assets->onFirstPage())
                        <span class="jsgrid-pager-nav-button jsgrid-pager-nav-inactive-button">
                            <a href="javascript:void(0);">First</a>
                        </span>
                        <span class="jsgrid-pager-nav-button jsgrid-pager-nav-inactive-button">
                            <a href="javascript:void(0);">Prev</a>
                        </span>
                    @else
                        <span class="jsgrid-pager-nav-button">
                            <a href="{{ $assets->url(1) }}">First</a>
                        </span>
                        <span class="jsgrid-pager-nav-button">
                            <a href="{{ $assets->previousPageUrl() }}">Prev</a>
                        </span>
                    @endif

                    <!-- Page Numbers -->
                    @foreach ($assets->getUrlRange(1, $assets->lastPage()) as $page => $url)
                        @if ($page == $assets->currentPage())
                            <span class="jsgrid-pager-page jsgrid-pager-current-page">{{ $page }}</span>
                        @else
                            <span class="jsgrid-pager-page">
                                <a href="{{ $url }}">{{ $page }}</a>
                            </span>
                        @endif
                    @endforeach

                    @if ($assets->hasMorePages())
                        <span class="jsgrid-pager-nav-button">
                            <a href="{{ $assets->nextPageUrl() }}" class="fw-bold">Next</a>
                        </span>
                        <span class="jsgrid-pager-nav-button">
                            <a href="{{ $assets->url($assets->lastPage()) }}" class="fw-bold">Last</a>
                        </span>
                    @else
                        <span class="jsgrid-pager-nav-button jsgrid-pager-nav-inactive-button">
                            <a href="javascript:void(0);" class="fw-bold">Next</a>
                        </span>
                        <span class="jsgrid-pager-nav-button jsgrid-pager-nav-inactive-button">
                            <a href="javascript:void(0);" class="fw-bold">Last</a>
                        </span>
                    @endif

                    &nbsp;&nbsp; {{ $assets->currentPage() }} of {{ $assets->lastPage() }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addAssetModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.assets.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Sync Asset from FMP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label></label>
                    <input type="text" name="symbol" class="form-control" required placeholder="">
                </div>
                <div class="mb-3">
                    <label>Asset Type</label>
                    <select name="type" class="form-select">
                        <option value="stocks">Stock</option>
                        <option value="crypto">Crypto</option>
                        <option value="etf">ETF</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Fetch & Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
    