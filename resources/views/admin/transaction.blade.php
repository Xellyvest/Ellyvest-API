@extends('layouts.admin')

@section('title', ' Dashboard')

@section('content')
    <div class="page-body">
        <div class="container-fluid">
        <div class="page-title">
            <div class="row">
            <div class="col-6">
                <h4>
                    Transactions list</h4>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.html">                                       
                    <svg class="stroke-icon">
                        <use href="../assets/svg/icon-sprite.svg#stroke-home"></use>
                    </svg></a></li>
                <li class="breadcrumb-item">Dashboard </li>
                <li class="breadcrumb-item active">Transactions list</li>
                </ol>
            </div>
            </div>
        </div>
        </div>
        <!-- Container-fluid starts-->
        <div class="container-fluid">
            <div class="row"> 
                <div class="col-sm-12"> 
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4>{{ $title }}</h4>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center">
                                        <input class="form-control" id="inputEmail4" type="email" placeholder="Search...">
                                        <a class="btn btn-success w-100 mx-2" 
                                            href="#"
                                            data-bs-toggle="modal"
                                            data-bs-target="#addTransaction"
                                        >
                                            <i class="fa fa-plus"></i>Add Transaction
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive custom-scrollbar px-4">
                            <table class="table">
                                <thead>
                                <tr class="border-bottom-primary">
                                    <th> <span class="f-light f-w-600">S/N</span></th>
                                    <th> <span class="f-light f-w-600">Name</span></th>
                                    <th> <span class="f-light f-w-600">Amount </span></th>
                                    <th> <span class="f-light f-w-600">Type</span></th>
                                    <th> <span class="f-light f-w-600">Info</span></th>
                                    <th> <span class="f-light f-w-600">Status</span></th>
                                    <th> <span class="f-light f-w-600">Date</span></th>
                                    <th> <span class="f-light f-w-600">Action</span></th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $index => $transaction)
                                        <tr class="product-removes">
                                            <td>{{ $index +  1 }}</td>
                                            <td> 
                                                <div class="product-names fw-bold">
                                                    <a href="{{ route('admin.users.show', $transaction->user->id) }}" class="text-success">{{ $transaction->user->first_name }} {{ $transaction->user->last_name }}</a>
                                                </div>
                                            </td>
                                            <td> 
                                                <p class="f-light fw-bold">{{ $transaction->amount }} USD</p>
                                            </td>
                                            <td> 
                                                <span class="badge rounded-pill @if($transaction->type == 'credit') badge-light-success @elseif($transaction->type == 'transfer') badge-light-dark text-white @else badge-light-danger @endif">
                                                    @if($transaction->type == 'credit') Credit @elseif($transaction->type == 'transfer') Transfer @else Debit @endif
                                                </span>
                                            </td>
                                            <td> 
                                                @if($transaction->type == 'transfer')
                                                    @php
                                                        // Map account types to their display names
                                                        $accountNames = [
                                                            'wallet' => 'Cash Account',
                                                            'cash' => 'Cash Account', // if you want both 'wallet' and 'cash' to show same
                                                            'brokerage' => 'Brokerage Account',
                                                            'auto' => 'Auto Investing Account',
                                                            'ira' => 'IRA Account'
                                                        ];
                                                        
                                                        // Get display names or fallback to original if not found
                                                        $fromDisplay = $accountNames[$transaction->swap_from] ?? $transaction->swap_from;
                                                        $toDisplay = $accountNames[$transaction->swap_to] ?? $transaction->swap_to;
                                                    @endphp
                                                    <p class="f-light">From <b>{{ $fromDisplay }}</b> to <b>{{ $toDisplay }}</b></p>
                                                @else
                                                    <p class="f-light">{{ $transaction->comment }}</p>
                                                @endif
                                            </td>
                                            <td> 
                                                <span class="badge @if($transaction->status == 'approved') badge-light-success  @elseif($transaction->status == 'pending') badge-light-warning @elseif($transaction->status == 'in_progress') badge-light-info @else badge-light-danger @endif">
                                                    @if($transaction->status == 'approved') 
                                                        Approved 
                                                    @elseif($transaction->status == 'pending') 
                                                        Pending  
                                                    @elseif($transaction->status == 'in_progress') 
                                                        In Progress 
                                                    @elseif($transaction->status == 'cancelled') 
                                                        Cancelled 
                                                    @else 
                                                        Declined 
                                                    @endif
                                                </span>
                                            </td>
                                            <td> 
                                                <p class="f-light">{{ $transaction['created_at']->format('d M, Y \a\t h:i A') }}</p>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button class="btn btn-dark rounded-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Action</button>
                                                    <ul class="dropdown-menu dropdown-menu-dark dropdown-block">
                                                        <li>
                                                            <button class="dropdown-item fw-bold" 
                                                                    type="button" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#viewTransaction{{ $transaction->id }}">
                                                                View
                                                            </button>
                                                        </li>
                                                        @if($transaction->status == 'pending' && $transaction->type == 'credit')
                                                            <li>
                                                                <form action="{{ route('admin.transactions.deposit', $transaction->id) }}" method="POST" style="display: inline;">
                                                                    @csrf
                                                                    <input type="hidden" name="action" value="approved">
                                                                    <button type="submit" class="dropdown-item fw-bold">Approve</button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form action="{{ route('admin.transactions.deposit', $transaction->id) }}" method="POST" style="display: inline;">
                                                                    @csrf
                                                                    <input type="hidden" name="action" value="decline">
                                                                    <button type="submit" class="dropdown-item text-danger fw-bold">Decline</button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form action="{{ route('admin.transactions.toggle', [$transaction->id, 'cancelled']) }}" method="POST" style="display: inline;">
                                                                    @csrf
                                                                    <button type="submit" 
                                                                            class="dropdown-item text-danger">
                                                                        Cancel
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @elseif($transaction->status == 'pending' || $transaction->status == 'in_progress' && $transaction->type == 'debit')
                                                            <li>
                                                                <form action="{{ route('admin.transactions.withdraw', $transaction->id) }}" method="POST" style="display: inline;">
                                                                    @csrf
                                                                    <input type="hidden" name="action" value="approved">
                                                                    <button type="submit" class="dropdown-item fw-bold">Approve</button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form action="{{ route('admin.transactions.withdraw', $transaction->id) }}" method="POST" style="display: inline;">
                                                                    @csrf
                                                                    <input type="hidden" name="action" value="decline">
                                                                    <button type="submit" class="dropdown-item text-danger fw-bold">Decline</button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form action="{{ route('admin.transactions.toggle', [$transaction->id, 'cancelled']) }}" method="POST" style="display: inline;">
                                                                    @csrf
                                                                    <button type="submit" 
                                                                            class="dropdown-item text-danger">
                                                                        Cancel
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            @if($transaction->status !== 'in_progress')
                                                            <li>
                                                                <form action="{{ route('admin.transactions.markProgress', $transaction->id) }}" method="POST" style="display: inline;">
                                                                    @csrf
                                                                    <button type="submit" class="dropdown-item text-info fw-bold">Progress</button>
                                                                </form>
                                                            </li>
                                                            @endif
                                                        @endif
                                                        @if($transaction->transactable_id == $transaction->user->wallet->id)
                                                        <li>
                                                            <a href="#" class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#editTransaction{{ $transaction->id }}">
                                                                Edit
                                                            </a>
                                                        </li>
                                                        @endif
                                                        @if($transaction->status == 'approved')
                                                        <li>
                                                            <form action="{{ route('admin.transactions.toggle', [$transaction->id, 'pending']) }}" method="POST" style="display: inline;">
                                                                @csrf
                                                                <button type="submit" 
                                                                        class="dropdown-item text-dark fw-bold bg-warning">
                                                                    Pend
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form action="{{ route('admin.transactions.toggle', [$transaction->id, 'cancelled']) }}" method="POST" style="display: inline;">
                                                                @csrf
                                                                <button type="submit" 
                                                                        class="dropdown-item text-danger">
                                                                    Cancel
                                                                </button>
                                                            </form>
                                                        </li>
                                                        @endif
                                                        <li>
                                                            <form action="{{ route('admin.transactions.destroy', $transaction->id) }}" method="POST" style="display: inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="button" 
                                                                        class="dropdown-item text-danger fw-bold bg-danger text-white"
                                                                        data-delete-button
                                                                        data-model-name="transaction">
                                                                    DELETE
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                            <!-- Edit Trade Modal -->
                                            <div class="modal fade" id="editTransaction{{$transaction->id}}" tabindex="-1" aria-labelledby="editTransaction{{$transaction->id}}" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-body">
                                                            <div class="modal-toggle-wrapper">
                                                                <h4 class="text-center pb-2" id="modalTitle">Edit Transaction</h4>
                                                                <form id="editTradeForm" action="{{ route('admin.transactions.edit', $transaction->id) }}" method="POST">
                                                                    @csrf
                                                                    @method('PUT')

                                                                    <input type="hidden" name="user_id" value="{{ $transaction->user->id }}">

                                                                    <div class="col-md-12">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Amount</label>
                                                                            <input class="form-control" type="number" placeholder="Enter amount..." name="amount" id="editAmount" value="{{ $transaction->amount }}" required>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-12">
                                                                        <div class="md-3">
                                                                            <label class="form-label">Comment</label>
                                                                            <select class="form-select" id="comment" required name="comment">
                                                                                <option value="">----- Select the comment to edit ------</option>
                                                                                <option value="cash deposit via bank deposit" {{ $transaction->comment == 'cash deposit via bank deposit' ? 'selected' : '' }}>cash deposit via bank deposit</option>
                                                                                <option value="cash deposit via (BTC)" {{ $transaction->comment == 'cash deposit via (BTC)' ? 'selected' : '' }}>cash deposit via (BTC)</option>
                                                                                <option value="cash deposit via (ETH)" {{ $transaction->comment == 'cash deposit via (ETH)' ? 'selected' : '' }}>cash deposit via (ETH)</option>
                                                                                <option value="cash deposit via (USDT (ERC20))" {{ $transaction->comment == 'cash deposit via (USDT (ERC20))' ? 'selected' : '' }}>cash deposit via (USDT (ERC20))</option>
                                                                                <option value="cash deposit via (USDT (TRC20))" {{ $transaction->comment == 'cash deposit via (USDT (TRC20))' ? 'selected' : '' }}>cash deposit via (USDT (TRC20))</option>
                                                                                <option value="cash withdrawal via bank withdrawal" {{ $transaction->comment == 'cash withdrawal via bank withdrawal' ? 'selected' : '' }}>cash withdrawal via bank withdrawal</option>
                                                                                <option value="cash withdrawal via (BTC)" {{ $transaction->comment == 'cash withdrawal via (BTC)' ? 'selected' : '' }}>cash withdrawal via (BTC)</option>
                                                                                <option value="cash withdrawal via (ETH)" {{ $transaction->comment == 'cash withdrawal via (ETH)' ? 'selected' : '' }}>cash withdrawal via (ETH)</option>
                                                                                <option value="cash withdrawal via (USDT (ERC20))" {{ $transaction->comment == 'cash withdrawal via (USDT (ERC20))' ? 'selected' : '' }}>cash withdrawal via (USDT (ERC20))</option>
                                                                                <option value="cash withdrawal via (USDT (TRC20))" {{ $transaction->comment == 'cash withdrawal via (USDT (TRC20))' ? 'selected' : '' }}>cash withdrawal via (USDT (TRC20))</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>


                                                                    <div class="col-md-12">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Date</label>
                                                                            <input class="form-control" type="datetime-local" name="created_at" id="dateEdit" required value="{{ $transaction->created_at }}">
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

                                            <!-- View Transaction Modal -->
                                            <div class="modal fade" id="viewTransaction{{ $transaction->id }}" tabindex="-1" aria-labelledby="viewTransactionLabel{{ $transaction->id }}" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="viewTransactionLabel{{ $transaction->id }}">Transaction Details</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-bold">Transaction ID:</label>
                                                                        <p class="form-control-static">{{ $transaction->id }}</p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-bold">User:</label>
                                                                        <p class="form-control-static">{{ $transaction->user->first_name }} {{ $transaction->user->last_name }}</p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-bold">Amount:</label>
                                                                        <p class="form-control-static">{{ $transaction->amount }} USD</p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-bold">Type:</label>
                                                                        <p class="form-control-static">
                                                                            @if($transaction->type == 'credit') 
                                                                                Credit 
                                                                            @elseif($transaction->type == 'transfer') 
                                                                                Transfer 
                                                                            @else 
                                                                                Debit 
                                                                            @endif
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-bold">Status:</label>
                                                                        <p class="form-control-static">
                                                                            @if($transaction->status == 'approved') 
                                                                                Approved 
                                                                            @elseif($transaction->status == 'pending') 
                                                                                Pending  
                                                                            @elseif($transaction->status == 'in_progress') 
                                                                                In Progress 
                                                                            @elseif($transaction->status == 'cancelled') 
                                                                                Cancelled 
                                                                            @else 
                                                                                Declined 
                                                                            @endif
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-bold">Date:</label>
                                                                        <p class="form-control-static">{{ $transaction->created_at->format('d M, Y \a\t h:i A') }}</p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-bold">Comment:</label>
                                                                        <p class="form-control-static">{{ $transaction->comment ?? 'N/A' }}</p>
                                                                    </div>
                                                                </div>
                                                                
                                                                <!-- Payment Method Section -->
                                                                <div class="col-md-12">
                                                                    <div class="card">
                                                                        <div class="card-header">
                                                                            <h5 class="card-title">Payment Method</h5>
                                                                        </div>
                                                                        <div class="card-body">
                                                                            @if($transaction->payment_method)
                                                                                @if($transaction->payment_method['type'] === 'bank')
                                                                                    <div class="row">
                                                                                        <div class="col-md-6">
                                                                                            <p><strong>Type:</strong> Bank Transfer</p>
                                                                                            <p><strong>Label:</strong> {{ $transaction->payment_method['label'] ?? 'N/A' }}</p>
                                                                                            <p><strong>Bank Name:</strong> {{ $transaction->payment_method['bank_name'] ?? 'N/A' }}</p>
                                                                                            <p><strong>Account Name:</strong> {{ $transaction->payment_method['account_name'] ?? 'N/A' }}</p>
                                                                                            <p><strong>Account Number:</strong> {{ $transaction->payment_method['account_number'] ?? 'N/A' }}</p>
                                                                                        </div>
                                                                                        <div class="col-md-6">
                                                                                            <p><strong>Routing Number:</strong> {{ $transaction->payment_method['routing_number'] ?? 'N/A' }}</p>
                                                                                            <p><strong>Reference:</strong> {{ $transaction->payment_method['bank_reference'] ?? 'N/A' }}</p>
                                                                                            <p><strong>Bank Address:</strong> {{ $transaction->payment_method['bank_address'] ?? 'N/A' }}</p>
                                                                                            <p><strong>Currency:</strong> {{ $transaction->payment_method['currency'] ?? 'N/A' }}</p>
                                                                                        </div>
                                                                                    </div>
                                                                                @elseif($transaction->payment_method['type'] === 'crypto')
                                                                                    <div class="row">
                                                                                        <div class="col-md-6">
                                                                                            <p><strong>Type:</strong> Crypto Wallet</p>
                                                                                            <p><strong>Label:</strong> {{ $transaction->payment_method['label'] ?? 'N/A' }}</p>
                                                                                            <p><strong>Currency:</strong> {{ $transaction->payment_method['currency'] ?? 'N/A' }}</p>
                                                                                        </div>
                                                                                        <div class="col-md-6">
                                                                                            <p><strong>Wallet Address:</strong> {{ $transaction->payment_method['wallet_address'] ?? 'N/A' }}</p>
                                                                                        </div>
                                                                                    </div>
                                                                                @endif
                                                                            @else
                                                                                @if($transaction->type == 'debit' && $transaction->user->settings->is_connect_activated == 1)
                                                                                    <p class="text-muted">Withdrawal into active Connected Wallet</p>
                                                                                @else
                                                                                    <p class="text-muted">No payment method associated with this transaction.</p>
                                                                                @endif
                                                                            @endif
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

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if($transactions->count() < 1)
                                <div class="">
                                    <p class="text-center my-4 py-4">No data</p>
                                </div>
                            @endif
                            <!-- Pagination Links -->
                            <div class="jsgrid-pager my-3 mx-2">
                                Pages:
                                @if ($transactions->onFirstPage())
                                    <span class="jsgrid-pager-nav-button jsgrid-pager-nav-inactive-button">
                                        <a href="javascript:void(0);">First</a>
                                    </span>
                                    <span class="jsgrid-pager-nav-button jsgrid-pager-nav-inactive-button">
                                        <a href="javascript:void(0);">Prev</a>
                                    </span>
                                @else
                                    <span class="jsgrid-pager-nav-button">
                                        <a href="{{ $transactions->url(1) }}">First</a>
                                    </span>
                                    <span class="jsgrid-pager-nav-button">
                                        <a href="{{ $transactions->previousPageUrl() }}">Prev</a>
                                    </span>
                                @endif

                                <!-- Page Numbers -->
                                @foreach ($transactions->getUrlRange(1, $transactions->lastPage()) as $page => $url)
                                    @if ($page == $transactions->currentPage())
                                        <span class="jsgrid-pager-page jsgrid-pager-current-page">{{ $page }}</span>
                                    @else
                                        <span class="jsgrid-pager-page">
                                            <a href="{{ $url }}">{{ $page }}</a>
                                        </span>
                                    @endif
                                @endforeach

                                @if ($transactions->hasMorePages())
                                    <span class="jsgrid-pager-nav-button">
                                        <a href="{{ $transactions->nextPageUrl() }}" class="fw-bold">Next</a>
                                    </span>
                                    <span class="jsgrid-pager-nav-button">
                                        <a href="{{ $transactions->url($transactions->lastPage()) }}" class="fw-bold">Last</a>
                                    </span>
                                @else
                                    <span class="jsgrid-pager-nav-button jsgrid-pager-nav-inactive-button">
                                        <a href="javascript:void(0);" class="fw-bold">Next</a>
                                    </span>
                                    <span class="jsgrid-pager-nav-button jsgrid-pager-nav-inactive-button">
                                        <a href="javascript:void(0);" class="fw-bold">Last</a>
                                    </span>
                                @endif

                                &nbsp;&nbsp; {{ $transactions->currentPage() }} of {{ $transactions->lastPage() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Container-fluid Ends-->
        <div>
        <!-- Reusable Modal -->
        <div class="modal fade" id="addTransaction" tabindex="-1" aria-labelledby="addTransaction" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body"> 
                        <div class="modal-toggle-wrapper"> 
                            <h4 class="text-center pb-2" id="modalTitle"></h4> 
                            <form id="transactionForm" action="{{ route('admin.transactions.store') }}" method="POST">
                                @csrf
                                <h4 class="text-center my-1">Create Transaction</h4>

                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">User</label>
                                        <select class="form-select" id="" required="" name="user_id">
                                            <option selected="" disabled="" value="">---- Select User ---</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                @if($title == 'Transfer')
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">From Account</label>
                                        <select class="form-select" id="" required="" name="account">
                                            <option selected="" disabled="" value="">--- Select Account ---</option>
                                            <option value="wallet">Cash</option>
                                            <option value="brokerage">Brokerage</option>
                                            <option value="auto">Auto</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">To Account</label>
                                        <select class="form-select" id="" required="" name="to">
                                            <option selected="" disabled="" value="">--- Select Account ---</option>
                                            <option value="wallet">Cash</option>
                                            <option value="brokerage">Brokerage</option>
                                            <option value="auto">Auto</option>
                                        </select>
                                    </div>
                                </div>
                                @else 
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Account</label>
                                        <select class="form-select" id="" required="" name="account">
                                            <option selected="" disabled="" value="">--- Select Account ---</option>
                                            <option value="wallet">Cash</option>
                                            <!-- <option value="brokerage">Brokerage</option>
                                            <option value="auto">Auto</option> -->
                                        </select>
                                    </div>
                                </div>
                                @endif

                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Amount</label>
                                        <input class="form-control" type="text" placeholder="Enter amount..." name="amount">
                                    </div>
                                </div>

                                @if($title == 'Transfer')
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Method</label>
                                        <select class="form-select" id="" required="" name="type">
                                            <option selected value="transfer">Transfer</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Comment</label>
                                        <input class="form-control" type="text" name="comment" id="comment" required value="Wallet transfer from ----- to -----">
                                    </div>
                                </div>
                                @else
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Method</label>
                                        <select class="form-select" id="methodSelect" required="" name="type">
                                            <option selected="" disabled="" value="">--- Select Method ---</option>
                                            <option value="credit">Deposit</option>
                                            <option value="debit">Withdraw</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Comment</label>
                                        <select class="form-select" id="commentSelect" required name="comment">
                                            <option data-method="credit" value="cash deposit via bank deposit">cash deposit via bank deposit</option>
                                            <option data-method="credit" value="cash deposit via (BTC)">cash deposit via (BTC)</option>
                                            <option data-method="credit" value="cash deposit via (ETH)">cash deposit via (ETH)</option>
                                            <option data-method="credit" value="cash deposit via (USDT (ERC20))">cash deposit via (USDT (ERC20))</option>
                                            <option data-method="credit" value="cash deposit via (USDT (TRC20))">cash deposit via (USDT (TRC20))</option>
                                            <option data-method="debit" value="cash withdrawal via bank withdrawal">cash withdrawal via bank withdrawal</option>
                                            <option data-method="debit" value="cash withdrawal via (BTC)">cash withdrawal via (BTC)</option>
                                            <option data-method="debit" value="cash withdrawal via (ETH)">cash withdrawal via (ETH)</option>
                                            <option data-method="debit" value="cash withdrawal via (USDT (ERC20))">cash withdrawal via (USDT (ERC20))</option>
                                            <option data-method="debit" value="cash withdrawal via (USDT (TRC20))">cash withdrawal via (USDT (TRC20))</option>
                                        </select>
                                    </div>
                                </div>
                                @endif

                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Date</label>
                                        <input class="form-control" type="datetime-local" name="created_at" id="date" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Send Email</label>
                                        <div class="form-check-size">
                                            <div class="form-check form-switch form-check-inline">
                                                <input class="form-check-input check-size" type="checkbox" role="switch" name="is_email" checked >
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-footer mt-4 d-flex">
                                    <button class="btn btn-primary btn-block" type="submit">Submit</button>
                                    <button class="btn btn-danger btn-block mx-2" type="button" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Credit Modal -->
         
        @include('components.delete-modal')
    </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            // Cache the comment dropdown and its options
            const $commentSelect = $('#commentSelect');
            const $commentOptions = $commentSelect.find('option');

            // Listen for changes in the Method dropdown
            $('#methodSelect').on('change', function () {
                const selectedMethod = $(this).val(); // Get the selected method value

                // Clear the comment dropdown and add the default option
                $commentSelect.empty().append('<option selected disabled value="">--- Select Comment ---</option>');

                // Filter and append options based on the selected method
                $commentOptions.each(function () {
                    const $option = $(this);
                    if ($option.data('method') === selectedMethod || $option.prop('disabled')) {
                        $commentSelect.append($option.clone());
                    }
                });
            });

            // Trigger the change event initially to set the correct options
            $('#methodSelect').trigger('change');
        });
    </script>
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
    