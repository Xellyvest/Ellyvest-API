@extends('layouts.admin')

@section('title', ' Dashboard')

@section('content')
    <div class="page-body">
        <div class="container-fluid">
        <div class="page-title">
            <div class="row">
            <div class="col-6">
                <h4>
                    Auto-Invest</h4>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.html">                                       
                    <svg class="stroke-icon">
                        <use href="../assets/svg/icon-sprite.svg#stroke-home"></use>
                    </svg></a></li>
                <li class="breadcrumb-item">Dashboard </li>
                <li class="breadcrumb-item active">Auto-Investment list</li>
                </ol>
            </div>
            </div>
        </div>
        </div>

        <div class="container-fluid">
            <div class="row"> 
                <div class="col-sm-12"> 
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4>Investments</h4>
                                </div>
                                <div class="d-flex align-items-center">
                                    <input class="form-control" id="inputEmail4" type="email" placeholder="Search...">
                                    <a class="btn btn-success w-100 mx-2" href="#" data-bs-toggle="modal" data-bs-target="#createInvestmentModal">
                                        <i class="fa fa-plus"></i>Add Investment
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive custom-scrollbar px-4">
                            <table class="table">
                                <thead>
                                <tr class="border-bottom-primary">
                                    <th> <span class="f-light f-w-600">S/N</span></th>
                                    <th> <span class="f-light f-w-600">Name</span></th>
                                    <th> <span class="f-light f-w-600">Plan</span></th>
                                    <th> <span class="f-light f-w-600">Amount </span></th>
                                    <th> <span class="f-light f-w-600">Started</span></th>
                                    <th> <span class="f-light f-w-600">Expires</span></th>
                                    <th> <span class="f-light f-w-600">Status</span></th>
                                    <th> <span class="f-light f-w-600">P/L</span></th>
                                    <th> <span class="f-light f-w-600">Action</span></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($investments as $index => $investment)
                                    @php
                                        $totalProfit = 0;
                                        foreach($investment->positions as $position) {
                                            $assetPrice = $position->asset->price;
                                            $quantity = $position->quantity;
                                            $extra = $position->extra;
                                            $leverage = abs($position->leverage ?? 1);
                                            
                                            $singleProfit = ($assetPrice * $quantity) - $position->amount;
                                            $profit = ($singleProfit * $leverage) + $extra;
                                            $totalProfit += $profit;
                                        }
                                    @endphp
                                    <tr class="">
                                        <td>{{ $index + 1 }}</td>
                                        <td> 
                                            <div class="product-names fw-bold">
                                                <a href="{{ route('admin.users.show', $investment->user->id) }}" class="text-success">{{ $investment->user->first_name }} {{ $investment->user->last_name }}</a>
                                            </div>
                                        </td>
                                        <td> 
                                            <div class="product-names fw-bold">
                                                <a href="{{ route('admin.auto.plans') }}" class="text-success">{{ $investment->plan->name }}</a>
                                            </div>
                                        </td>
                                        <td> 
                                            <p class="f-light fw-bold">{{ $investment->amount }} {{ $investment->user->currency->symbol }}</p>
                                        </td>
                                        <td> 
                                            <p class="f-light">{{ $investment['start_at']->format('d M, Y \a\t h:i A') }}</p>
                                        </td>
                                        <td> 
                                            <p class="f-light">{{ $investment['expire_at']->format('d M, Y \a\t h:i A') }}</p>
                                        </td>
                                        <td> 
                                            <span class="badge @if($investment->expire_at > now()) badge-light-success @else badge-light-danger @endif">
                                                @if($investment->expire_at > now()) Active @else Expired @endif
                                            </span>
                                        </td>
                                        <td> 
                                            <p class="f-light @if($totalProfit >= 0) text-success @else text-danger @endif">
                                                {{ $totalProfit >= 0 ? '+' : '' }}{{ number_format($totalProfit, 2) }} {{ $investment->user->currency->symbol }}
                                            </p>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-dark rounded-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Action</button>
                                                <ul class="dropdown-menu dropdown-menu-dark dropdown-block">
                                                    <li>
                                                        <button class="dropdown-item fw-bold" 
                                                                type="button" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#viewInvestment{{ $investment->id }}">
                                                            View
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <a href="#" class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#editTrade{{ $investment->id }}">
                                                            Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('admin.auto.investment.close', $investment->id) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" 
                                                                    class="dropdown-item text-danger fw-bold bg-danger text-white"
                                                                    data-delete-button
                                                                    data-model-name="Auto Investing">
                                                                DELETE
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Trade Modal -->
                                    <div class="modal fade" id="editTrade{{$investment->id}}" tabindex="-1" aria-labelledby="editTrade{{$investment->id}}" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-body">
                                                    <div class="modal-toggle-wrapper">
                                                        <h4 class="text-center pb-2" id="modalTitle">Edit Trade</h4>
                                                        <form id="editTradeForm" action="{{ route('admin.auto-investments.update', $investment->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Date Started</label>
                                                                    <input class="form-control" type="datetime-local" name="start_at" id="dateEdit" required value="{{ $investment->start_at }}" step="any">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Date Expired</label>
                                                                    <input class="form-control" type="datetime-local" name="expire_at" id="dateEdit" required value="{{ $investment->expire_at }}" step="any">
                                                                </div>
                                                            </div>

                                                            <div class="form-footer mt-4 d-flex">
                                                                <button class="btn btn-primary btn-block" type="submit">Update</button>
                                                                <button class="btn btn-danger btn-block mx-2" type="button" data-bs-dismiss="modal">Cancel</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- View Auto Investment Modal -->
                                    <div class="modal fade" id="viewInvestment{{ $investment->id }}" tabindex="-1" aria-labelledby="viewInvestmentLabel{{ $investment->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-xl">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="viewInvestmentLabel{{ $investment->id }}">Auto Investment Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <!-- Investment Details Section -->
                                                        <div class="col-md-12 mb-4">
                                                            <div class="card">
                                                                <div class="card-header bg-dark">
                                                                    <h5 class="card-title mb-0 text-white">Investment Overview</h5>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col-md-4">
                                                                            <div class="mb-3">
                                                                                <label class="form-label fw-bold">User:</label>
                                                                                <p class="form-control-static">
                                                                                    <a href="{{ route('admin.users.show', $investment->user->id) }}" class="text-success">
                                                                                        {{ $investment->user->first_name }} {{ $investment->user->last_name }}
                                                                                    </a>
                                                                                </p>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <div class="mb-3">
                                                                                <label class="form-label fw-bold">Plan:</label>
                                                                                <p class="form-control-static">
                                                                                    <a href="{{ route('admin.auto.plans') }}" class="text-success">
                                                                                        {{ $investment->plan->name }}
                                                                                    </a>
                                                                                </p>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <div class="mb-3">
                                                                                <label class="form-label fw-bold">Invested Amount:</label>
                                                                                <p class="form-control-static">{{ number_format($investment->amount, 2) }} {{ $investment->user->currency->symbol }}</p>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <div class="mb-3">
                                                                                <label class="form-label fw-bold">Start Date:</label>
                                                                                <p class="form-control-static">{{ $investment->start_at->format('d M, Y \a\t h:i A') }}</p>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <div class="mb-3">
                                                                                <label class="form-label fw-bold">End Date:</label>
                                                                                <p class="form-control-static">{{ $investment->expire_at->format('d M, Y \a\t h:i A') }}</p>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <div class="mb-3">
                                                                                <label class="form-label fw-bold">Status:</label>
                                                                                <p class="form-control-static">
                                                                                    <span class="badge @if($investment->expire_at > now()) badge-light-success @else badge-light-danger @endif">
                                                                                        @if($investment->expire_at > now()) Active @else Expired @endif
                                                                                    </span>
                                                                                </p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Positions Table Section -->
                                                        <div class="col-md-12">
                                                            <div class="card">
                                                                <div class="card-header bg-dark">
                                                                    <h5 class="card-title mb-0 text-white">Associated Positions ({{ $investment->positions->count() }})</h5>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        @if($investment->positions->count() > 0)
                                                                            <div class="row">
                                                                                @foreach($investment->positions as $index => $position)
                                                                                    @php 
                                                                                        $assetPrice = $position->asset->price;
                                                                                        $quantity = $position->quantity;
                                                                                        $extra = $position->extra;
                                                                                        $leverage = abs($position->leverage ?? 1);

                                                                                        $singleProfit = ($assetPrice * $quantity) - $position->amount;
                                                                                        $profit = ($singleProfit * $leverage) + $extra;
                                                                                    @endphp
                                                                                    <div class="col-md-6 mb-4">
                                                                                        <div class="card h-100 border">
                                                                                            <div class="card-body">
                                                                                                <div class="d-flex align-items-center mb-1">
                                                                                                    <div class="avatar-xs me-2">
                                                                                                        <span class="avatar-title bg-light rounded">
                                                                                                            <img src="{{ $position->asset->image_url }}" alt="" class="img-fluid" width="24">
                                                                                                        </span>
                                                                                                    </div>
                                                                                                    <h4 class="mb-0 fw-bold">{{ $position->asset->name }} ({{ $position->asset->symbol }})</h4>
                                                                                                </div>

                                                                                                <ul class="list-group list-group-flush">
                                                                                                    <li class="list-group-item d-flex justify-content-between">
                                                                                                        <strong>Quantity:</strong>
                                                                                                        <span>{{ number_format($position->quantity, 8) }}</span>
                                                                                                    </li>
                                                                                                    <li class="list-group-item d-flex justify-content-between">
                                                                                                        <strong>Amount:</strong>
                                                                                                        <span>{{ number_format($position->amount, 2) }} {{ $investment->user->currency->symbol }}</span>
                                                                                                    </li>
                                                                                                    <li class="list-group-item d-flex justify-content-between">
                                                                                                        <strong>Leverage:</strong>
                                                                                                        <span>x{{ $position->leverage }}</span>
                                                                                                    </li>
                                                                                                    <li class="list-group-item d-flex justify-content-between">
                                                                                                        <strong>Profit & Loss:</strong>
                                                                                                        <span class="@if($profit >= 0) text-success @else text-danger @endif">{{ number_format($profit, 2) }} USD</span>
                                                                                                    </li>
                                                                                                    <li class="list-group-item d-flex justify-content-between">
                                                                                                        <strong>Status:</strong>
                                                                                                        <span class="badge {{ $position->status === 'open' ? 'badge-light-success' : 'badge-light-danger' }}">
                                                                                                            {{ ucfirst($position->status) }}
                                                                                                        </span>
                                                                                                    </li>
                                                                                                </ul>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        @else
                                                                            <div class="alert alert-light" role="alert">
                                                                                No positions found for this investment.
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                </tbody>
                            </table>
                            @if($investments->count() < 1)
                                <div class="">
                                    <p class="text-center my-4 py-4">No data</p>
                                </div>
                            @endif
                            <!-- Pagination Links -->
                            {{-- <div class="jsgrid-pager my-3 mx-2">
                                Pages:
                                @if ($investments->onFirstPage())
                                    <span class="jsgrid-pager-nav-button jsgrid-pager-nav-inactive-button">
                                        <a href="javascript:void(0);">First</a>
                                    </span>
                                    <span class="jsgrid-pager-nav-button jsgrid-pager-nav-inactive-button">
                                        <a href="javascript:void(0);">Prev</a>
                                    </span>
                                @else
                                    <span class="jsgrid-pager-nav-button">
                                        <a href="{{ $investments->url(1) }}">First</a>
                                    </span>
                                    <span class="jsgrid-pager-nav-button">
                                        <a href="{{ $investments->previousPageUrl() }}">Prev</a>
                                    </span>
                                @endif

                                <!-- Page Numbers -->
                                @foreach ($investments->getUrlRange(1, $investments->lastPage()) as $page => $url)
                                    @if ($page == $investments->currentPage())
                                        <span class="jsgrid-pager-page jsgrid-pager-current-page">{{ $page }}</span>
                                    @else
                                        <span class="jsgrid-pager-page">
                                            <a href="{{ $url }}">{{ $page }}</a>
                                        </span>
                                    @endif
                                @endforeach

                                @if ($investments->hasMorePages())
                                    <span class="jsgrid-pager-nav-button">
                                        <a href="{{ $investments->nextPageUrl() }}" class="fw-bold">Next</a>
                                    </span>
                                    <span class="jsgrid-pager-nav-button">
                                        <a href="{{ $investments->url($investments->lastPage()) }}" class="fw-bold">Last</a>
                                    </span>
                                @else
                                    <span class="jsgrid-pager-nav-button jsgrid-pager-nav-inactive-button">
                                        <a href="javascript:void(0);" class="fw-bold">Next</a>
                                    </span>
                                    <span class="jsgrid-pager-nav-button jsgrid-pager-nav-inactive-button">
                                        <a href="javascript:void(0);" class="fw-bold">Last</a>
                                    </span>
                                @endif

                                &nbsp;&nbsp; {{ $investments->currentPage() }} of {{ $investments->lastPage() }}
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Container-fluid Ends-->
    </div>

    <!-- resources/views/investments/create-modal.blade.php -->
    <div class="modal fade" id="createInvestmentModal" tabindex="-1" role="dialog" aria-labelledby="createInvestmentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createInvestmentModalLabel">Create New Investment</h5>
                </div>
                <form action="{{ route('admin.auto.plans.invest') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group my-2">
                            <label for="user_id">Select User</label>
                            <select class="form-control select2" id="user_id" name="user_id" required>
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group my-2">
                            <label for="auto_plan_id">Select Investment Plan</label>
                            <select class="form-control select2" id="auto_plan_id" name="auto_plan_id" required>
                                <option value="">Select Plan</option>
                                @foreach($autoPlans as $plan)
                                    <option value="{{ $plan->id }}">
                                        {{ $plan->name }} 
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group my-2">
                            <label for="amount">Amount</label>
                            <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                            <small class="text-muted" id="amountHelp">Enter amount between min and max for selected plan</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create Investment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('components.delete-modal')

@endsection

@section('scripts')

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let now = new Date();
            let formattedDateTime = now.toISOString().slice(0, 16); // Format: YYYY-MM-DDTHH:MM
            document.getElementById("date").value = formattedDateTime;
        });
    </script>
    <script src="{{ asset('admin/assets/js/js-datatables/simple-datatables@latest.js') }}"></script>
    <script src="{{ asset('admin/assets/js/custom-list-product.js') }}"></script>
    <script src="{{ asset('admin/assets/js/owlcarousel/owl.carousel.js') }}"></script>
    <script src="{{ asset('admin/assets/js/ecommerce.js') }}"></script>
    <script src="{{ asset('admin/assets/js/tooltip-init.js') }}"></script>
@endsection
    