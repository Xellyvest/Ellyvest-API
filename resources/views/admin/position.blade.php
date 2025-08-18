@extends('layouts.admin')

@section('title', ' Positions')

@section('content')
    <div class="page-body">
        <div class="container-fluid">
        <div class="page-title">
            <div class="row">
            <div class="col-6">
                <h4>
                    Positions list</h4>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.html">                                       
                    <svg class="stroke-icon">
                        <use href="../assets/svg/icon-sprite.svg#stroke-home"></use>
                    </svg></a></li>
                <li class="breadcrumb-item">Dashboard </li>
                <li class="breadcrumb-item active">Positions list</li>
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
                                    <h4>Positions</h4>
                                </div>
                                <div class="d-flex align-items-center">
                                    <input class="form-control" id="inputEmail4" type="email" placeholder="Search...">
                                    <a class="btn btn-success w-100 mx-2" 
                                        href="#"
                                        data-bs-toggle="modal"
                                        data-bs-target="#addTrade" 
                                        data-action="Credit"
                                        data-url=""
                                    >
                                        <i class="fa fa-plus"></i>Add Positions
                                    </a>
                                </div>
                            </div>
                            <div class="my-4">
                                <div class="">
                                    <form method="GET" action="{{ route('admin.positions') }}">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="user_id" class="form-label">Filter by User</label>
                                                <select name="user_id" id="user_id" class="form-select">
                                                    <option value="">All Users</option>
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->id }}" {{ $selectedUser == $user->id ? 'selected' : '' }}>
                                                            {{ $user->first_name }} {{ $user->last_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <label for="account" class="form-label">Filter by Account Type</label>
                                                <select name="account" id="account" class="form-select">
                                                    <option value="">All Accounts</option>
                                                    <option value="wallet" {{ $selectedAccount == 'wallet' ? 'selected' : '' }}>Wallet</option>
                                                    <option value="brokerage" {{ $selectedAccount == 'brokerage' ? 'selected' : '' }}>Brokerage</option>
                                                    <option value="auto" {{ $selectedAccount == 'auto' ? 'selected' : '' }}>Auto</option>
                                                    <option value="savings" {{ $selectedAccount == 'savings' ? 'selected' : '' }}>Savings</option>
                                                </select>
                                            </div>
                                            
                                            <div class="col-md-4 d-flex align-items-end">
                                                <button type="submit" class="btn btn-dark me-2">Filter</button>
                                                <a href="{{ route('admin.positions') }}" class="btn btn-light">Reset</a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive custom-scrollbar px-4">
                            <table class="table">
                                <thead>
                                <tr class="border-bottom-primary">
                                    <th> <span class="f-light f-w-600">S/N</span></th>
                                    <th> <span class="f-light f-w-600">Name</span></th>
                                    <th> <span class="f-light f-w-600">Asset</span></th>
                                    <th> <span class="f-light f-w-600">Amount </span></th>
                                    <th> <span class="f-light f-w-600">Quantity </span></th>
                                    <th> <span class="f-light f-w-600">Account</span></th>
                                    <th> <span class="f-light f-w-600">Plan</span></th>
                                    <th> <span class="f-light f-w-600">Leverage</span></th>
                                    <th> <span class="f-light f-w-600">Dividends</span></th>
                                    <th> <span class="f-light f-w-600">P/L</span></th>
                                    <th> <span class="f-light f-w-600">Extra</span></th>
                                    <th> <span class="f-light f-w-600">Status</span></th>
                                    <th> <span class="f-light f-w-600">Date</span></th>
                                    <th> <span class="f-light f-w-600">Action</span></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($trades as $index => $trade)
                                    @php 
                                        $assetPrice = $trade->asset->price;
                                        $quantity = $trade->quantity;
                                        $extra = $trade->extra;
                                        $leverage = abs((float)($trade->leverage ?? 1));

                                        $singleProfit = ($assetPrice * $quantity) - $trade->amount;
                                        $profit = $singleProfit * $leverage;
                                        $extra = $trade->extra * $leverage;
                                    @endphp
                                    <tr class="">
                                        <td>{{ $index +  1 }}</td>
                                        <td> 
                                            <div class="product-names fw-bold">
                                                <a href="{{ route('admin.users.show', $trade->user->id) }}" class="text-success">{{ $trade->user->first_name }} {{ $trade->user->last_name }}</a>
                                            </div>
                                        </td>
                                        <td> 
                                            <div class="product-names fw-bold">
                                                <a href="#" class="text-success">{{ $trade->asset->name }}</a>
                                            </div>
                                        </td>
                                        <td> 
                                            <p class="f-light fw-bold">{{ $trade->amount }} USD</p>
                                        </td>
                                        <td> 
                                            <p class="f-light fw-bold">{{ $trade->quantity }}</p>
                                        </td>
                                        <td> 
                                            <p class="f-light fw-bold text-capitalize">{{ $trade->account }}</p>
                                        </td>
                                        <td> 
                                            @if($trade->savings)
                                                <a href="#" class="f-light fw-bold text-success">{{ $trade->savings->savingsAccount->name }}</a>
                                            @elseif($trade->autoInvest)
                                                <a href="#" class="f-light fw-bold text-success">{{ $trade->autoInvest->plan->name }}</a>
                                            @else
                                                <p class="f-light fw-bold">---</p>
                                            @endif
                                        </td>
                                        <td> 
                                            <p class="f-light fw-bold">{{ $trade->leverage ? $trade->leverage : '1' }}x</p>
                                        </td>
                                        <td> 
                                            <p class="f-light fw-bold">{{ $trade->dividends }}%</p>
                                        </td>
                                        <td> 
                                            <p class="f-light @if($profit >= 0) text-success @else text-danger @endif">{{ number_format($profit, 2) }} USD</p>
                                        </td>
                                        <td> 
                                            <p class="f-light @if($extra >= 0) text-success @else text-danger @endif">{{ number_format($extra, 2) }} USD</p>
                                        </td>
                                        <td> 
                                            <span class="badge @if($trade->status == 'open') badge-light-success  @elseif($trade->status == 'hold') badge-light-warning @else badge-light-danger @endif">
                                                @if($trade->status == 'open') Open @elseif($trade->status == 'hold') Hold  @else Closed @endif
                                            </span>
                                        </td>
                                        <td> 
                                            <p class="f-light">{{ $trade['created_at']->format('d M, Y \a\t h:i A') }}</p>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-dark rounded-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Action</button>
                                                    <ul class="dropdown-menu dropdown-menu-dark dropdown-block">
                                                        <li>
                                                            <a href="#" class="dropdown-item fw-bold" data-bs-toggle="modal" data-bs-target="#editTrade{{ $trade->id }}">
                                                                Edit
                                                            </a>
                                                        </li>
                                                        {{-- @if($trade->status !== 'open')
                                                            <li>
                                                                <form action="{{ route('admin.trade.toggle', $trade->id) }}" method="POST" style="display: inline;">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <input type="hidden" name="action" value="open">
                                                                    <button type="submit" class="dropdown-item fw-bold text-success">Open</button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                        @if($trade->status !== 'hold')
                                                        <li>
                                                            <form action="{{ route('admin.trade.toggle', $trade->id) }}" method="POST" style="display: inline;">
                                                                @csrf
                                                                @method('PUT')
                                                                <input type="hidden" name="action" value="hold">
                                                                <button type="submit" class="dropdown-item text-warning fw-bold">Hold</button>
                                                            </form>
                                                        </li>
                                                        @endif
                                                        @if($trade->status !== 'close')
                                                        <li>
                                                            <form action="{{ route('admin.trade.toggle', $trade->id) }}" method="POST" style="display: inline;">
                                                                @csrf
                                                                @method('PUT')
                                                                <input type="hidden" name="action" value="close">
                                                                <button type="submit" class="dropdown-item text-danger fw-bold">Close</button>
                                                            </form>
                                                        </li>
                                                        @endif --}}
                                                        <li>
                                                            <form action="{{ route('admin.position.close', $trade->id) }}" method="POST" style="display: inline;">
                                                                @csrf
                                                                <input type="hidden" name="user_id" value="{{ $trade->user->id }}">
                                                                <input type="hidden" name="asset_id" value="{{ $trade->asset->id }}">
                                                                <input type="hidden" name="position_id" value="{{ $trade->id }}">
                                                                <input type="hidden" name="quantity" value="{{ $trade->quantity }}">
                                                                <button type="submit" class="dropdown-item text-white bg-danger fw-bold">Close</button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Trade Modal -->
                                    <div class="modal fade" id="editTrade{{$trade->id}}" tabindex="-1" aria-labelledby="editTrade{{$trade->id}}" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-body">
                                                    <div class="modal-toggle-wrapper">
                                                        <h4 class="text-center pb-2" id="modalTitle">Edit Position</h4>
                                                        <form id="editTradeForm" action="{{ route('admin.position.update', $trade->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <label class="form-label">User</label>
                                                                    <select class="form-select" id="" required="" name="user_id">
                                                                        @foreach($users as $user)
                                                                            @if($user->id === $trade->user->id)
                                                                                <option selected value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }}</option>
                                                                            @endif
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Asset</label>
                                                                    <input type="text" class="form-control mb-2" id="assetSearchs{{$trade->id}}" placeholder="Asset Search Asist...">
                                                                    <select class="form-select" id="assetSelects{{$trade->id}}" required name="asset_id">
                                                                        <option selected disabled value="">---- Select Asset ---</option>
                                                                        @foreach($assets as $asset)
                                                                            @if($asset->id === $trade->asset->id)
                                                                                <option selected value="{{ $asset->id }}" data-price="{{ $trade->price }}">{{ $asset->name }} ({{ $asset->symbol }})</option>
                                                                            @else
                                                                                <option value="{{ $asset->id }}" data-price="{{ $trade->price }}">{{ $asset->name }} ({{ $asset->symbol }})</option>
                                                                            @endif
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Amount</label>
                                                                    <input class="form-control" id="amountInputs{{$trade->id}}" type="number" placeholder="---" name="amount" required value="{{ $trade->amount }}" step="any">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Quantity</label>
                                                                    <input class="form-control" id="quantityInputs{{$trade->id}}" type="number" placeholder="Enter quantity..." name="quantity" required value="{{ $trade->quantity }}" step="any">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <label class="form-label" for="accountSelect">Account</label>
                                                                    <select class="form-select" id="accountSelect" required name="account" {{ $trade->account === 'auto' ? 'disabled' : '' }}>
                                                                        <option disabled value="">---- Select Account ---</option>
                                                                        <option value="wallet" {{ $trade->account === 'wallet' ? 'selected' : '' }}>Cash</option>
                                                                        <option value="brokerage" {{ $trade->account === 'brokerage' ? 'selected' : '' }}>Brokerage</option>
                                                                        <option value="auto" {{ $trade->account === 'auto' ? 'selected' : '' }}>Auto Investing</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Dividends (%)</label>
                                                                    <input class="form-control" type="number" placeholder="Dividends Precentage" name="dividends" step="any" value="{{ $trade->dividends }}">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Entry</label>
                                                                    <input class="form-control" type="number" placeholder="(Optional)" name="entry" value="{{ $trade->entry }}" id="editEntry" step="any">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Exit</label>
                                                                    <input class="form-control" type="number" placeholder="(Optional)" name="exit" value="{{ $trade->exit }}" id="editExit" step="any">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Interval</label>
                                                                    <input class="form-control" type="number" placeholder="(Optional)" name="interval" value="{{ $trade->interval }}" id="editInterval" step="any">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <label class="form-label">S/L</label>
                                                                    <input class="form-control" type="number" placeholder="(Optional)" name="sl" value="{{ $trade->sl }}" id="editSl" step="any">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <label class="form-label">T/P</label>
                                                                    <input class="form-control" type="number" placeholder="(Optional)" name="tp" value="{{ $trade->tp }}" id="editTp" step="any">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Leverage</label>
                                                                    <input class="form-control" type="number" placeholder="(Optional)" name="leverage" value="{{ $trade->leverage }}" id="leverage" step="any">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <label class="form-label">P&L (USD)</label>
                                                                    <input class="form-control" type="number" placeholder="(Optional)" name="extra" value="{{ $trade->extra }}" id="editExtra" step="any">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Date</label>
                                                                    <input class="form-control" type="datetime-local" name="created_at" id="dateEdit" required value="{{ $trade->created_at }}">
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
                                    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                                    <script>
                                        $(document).ready(function () {
                                            // Function to calculate quantity
                                            function calculateQuantity() {
                                                const selectedAsset = $('#assetSelects{{$trade->id}} option:selected');
                                                const price = parseFloat(selectedAsset.data('price')) || 0;
                                                const amount = parseFloat($('#amountInputs{{$trade->id}}').val()) || 0;

                                                const quantity = price !== 0 ? (amount / price) : 0;
                                                
                                                $('#quantityInputs{{$trade->id}}').val(quantity.toFixed(8)); // Use toFixed(8) for precision
                                            }

                                            // Bind events to the asset select and amount input
                                            $('#assetSelects{{$trade->id}}').on('change', calculateQuantity);
                                            $('#amountInputs{{$trade->id}}').on('input', calculateQuantity);


                                            // Initial calculation in case there are pre-filled values
                                            // calculateQuantity();
                                        });
                                    </script>
                                    <script>
                                        document.getElementById('assetSearchs{{$trade->id}}').addEventListener('input', function () {
                                            const search = this.value.toLowerCase();
                                            const options = document.getElementById('assetSelects{{$trade->id}}').options;

                                            for (let i = 0; i < options.length; i++) {
                                                const text = options[i].textContent.toLowerCase();
                                                options[i].style.display = text.includes(search) ? '' : 'none';
                                            }
                                        });
                                    </script>
                                @endforeach
                                </tbody>
                            </table>
                            @if($trades->count() < 1)
                                <div class="">
                                    <p class="text-center my-4 py-4">No data</p>
                                </div>
                            @endif
                            <!-- Pagination Links -->
                            <div class="jsgrid-pager my-3 mx-2">
                                Pages:
                                @if ($trades->onFirstPage())
                                    <span class="jsgrid-pager-nav-button jsgrid-pager-nav-inactive-button">
                                        <a href="javascript:void(0);">First</a>
                                    </span>
                                    <span class="jsgrid-pager-nav-button jsgrid-pager-nav-inactive-button">
                                        <a href="javascript:void(0);">Prev</a>
                                    </span>
                                @else
                                    <span class="jsgrid-pager-nav-button">
                                        <a href="{{ $trades->url(1) }}">First</a>
                                    </span>
                                    <span class="jsgrid-pager-nav-button">
                                        <a href="{{ $trades->previousPageUrl() }}">Prev</a>
                                    </span>
                                @endif

                                <!-- Page Numbers -->
                                @foreach ($trades->getUrlRange(1, $trades->lastPage()) as $page => $url)
                                    @if ($page == $trades->currentPage())
                                        <span class="jsgrid-pager-page jsgrid-pager-current-page">{{ $page }}</span>
                                    @else
                                        <span class="jsgrid-pager-page">
                                            <a href="{{ $url }}">{{ $page }}</a>
                                        </span>
                                    @endif
                                @endforeach

                                @if ($trades->hasMorePages())
                                    <span class="jsgrid-pager-nav-button">
                                        <a href="{{ $trades->nextPageUrl() }}" class="fw-bold">Next</a>
                                    </span>
                                    <span class="jsgrid-pager-nav-button">
                                        <a href="{{ $trades->url($trades->lastPage()) }}" class="fw-bold">Last</a>
                                    </span>
                                @else
                                    <span class="jsgrid-pager-nav-button jsgrid-pager-nav-inactive-button">
                                        <a href="javascript:void(0);" class="fw-bold">Next</a>
                                    </span>
                                    <span class="jsgrid-pager-nav-button jsgrid-pager-nav-inactive-button">
                                        <a href="javascript:void(0);" class="fw-bold">Last</a>
                                    </span>
                                @endif

                                &nbsp;&nbsp; {{ $trades->currentPage() }} of {{ $trades->lastPage() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Container-fluid Ends-->
    </div>

    <!-- Reusable Modal -->
    <div class="modal fade" id="addTrade" tabindex="-1" aria-labelledby="addTrade" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body"> 
                    <div class="modal-toggle-wrapper"> 
                        <h4 class="text-center pb-2" id="modalTitle"></h4> 
                        <form id="transactionForm" action="{{ route('admin.position.create') }}" method="POST">
                            @csrf
                            <h4 class="text-center my-1">Open a Position</h4>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">User</label>
                                    <select class="form-select" id="userSelect" required name="user_id">
                                        <option selected disabled value="">---- Select User ---</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Asset</label>
                                    <input type="text" class="form-control mb-2" id="assetSearch" placeholder="Asset Search Asist...">
                                    <select class="form-select" id="assetSelect" required name="asset_id">
                                        <option selected disabled value="">---- Select Asset ---</option>
                                        @foreach($assets as $asset)
                                            <option value="{{ $asset->id }}" data-price="{{ $asset->price }}">
                                                {{ $asset->name }} ({{ $asset->symbol }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Account</label>
                                    <select class="form-select" id="addAccountSelect" required name="account">
                                        <option selected disabled value="">---- Select Account ---</option>
                                        <option value="wallet">Cash</option>
                                        <option value="brokerage">Brokerage</option>
                                        <option value="auto">Auto Investing</option>
                                        <option value="savings">Savings Account</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Auto Investment Plan Field (Hidden by default) -->
                            <div class="col-md-12" id="autoPlanField" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">Auto Investment Plan</label>
                                    <select class="form-select" id="autoPlanSelect" name="auto_plan_investment_id">
                                        <option selected disabled value="">---- Select Plan ---</option>
                                        <!-- Options will be loaded via AJAX -->
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12" id="savingsAccountField" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">Savings Account</label>
                                    <select class="form-select" id="savingsAccountSelect" name="savings_account_id">
                                        <option selected disabled value="">---- Select Savings Account ---</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Amount</label>
                                    <input class="form-control" id="amountInput" type="number" placeholder="Enter amount..." name="amount" required step="any">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Quantity</label>
                                    <input class="form-control" id="quantityInput" type="number" placeholder="Quantity will be calculated..." name="quantity" required step="any">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Dividends (%)</label>
                                    <input class="form-control" type="number" placeholder="Dividends Precentage" name="dividends" step="any" value="0.0">
                                </div>
                            </div>

                            <!-- Other fields remain the same -->
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Entry</label>
                                    <input class="form-control" type="number" placeholder="(Optional)" name="entry" step="any">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Exit</label>
                                    <input class="form-control" type="number" placeholder="(Optional)" name="exit" step="any">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Interval</label>
                                    <input class="form-control" type="number" placeholder="(Optional)" name="interval" step="any">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">S/L</label>
                                    <input class="form-control" type="number" placeholder="(Optional)" name="sl" step="any">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">T/P</label>
                                    <input class="form-control" type="number" placeholder="(Optional)" name="tp" step="any">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Leverage</label>
                                    <input class="form-control" type="number" name="leverage" id="leverage" step="any" value="1">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">P&L (USD)</label>
                                    <input class="form-control" type="number" placeholder="" name="extra" value="0.00" step="any">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Date</label>
                                    <input class="form-control" type="datetime-local" name="created_at" id="date" required>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label" for="checkbox1">Notify User</label>
                                    <div class="form-check-size">
                                        <div class="form-check form-switch form-check-inline">
                                            <input class="form-check-input check-size" type="checkbox" role="switch" name="notify" >
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const accountSelect = document.getElementById('addAccountSelect');
        const autoPlanField = document.getElementById('autoPlanField');
        const savingsAccountField = document.getElementById('savingsAccountField');
        const userSelect = document.getElementById('userSelect');
        const autoPlanSelect = document.getElementById('autoPlanSelect');
        const savingsAccountSelect = document.getElementById('savingsAccountSelect');

        let selectedUserId = null;

        // Show/hide account-specific fields based on selection
        accountSelect.addEventListener('change', function() {
            autoPlanField.style.display = this.value === 'auto' ? 'block' : 'none';
            savingsAccountField.style.display = this.value === 'savings' ? 'block' : 'none';
            
            if (selectedUserId && (this.value === 'auto' || this.value === 'savings')) {
                loadAccountOptions(selectedUserId, this.value);
            }
        });

        userSelect.addEventListener('change', function() {
            selectedUserId = this.value;
            if (accountSelect.value === 'auto' || accountSelect.value === 'savings') {
                loadAccountOptions(selectedUserId, accountSelect.value);
            }
        });

        function loadAccountOptions(userId, accountType) {
            const route = accountType === 'auto' 
                ? "{{ route('admin.auto.investment.users', ['user' => 'USER_ID']) }}"
                : "{{ route('admin.account.savings.users', ['user' => 'USER_ID']) }}";

            const url = route.replace('USER_ID', userId);
            const selectElement = accountType === 'auto' ? autoPlanSelect : savingsAccountSelect;
            const otherSelectElement = accountType === 'auto' ? savingsAccountSelect : autoPlanSelect;
            
            // Reset the other select
            otherSelectElement.innerHTML = '<option selected disabled value="">---- Select ---</option>';
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    selectElement.innerHTML = '<option selected disabled value="">---- Select ---</option>';
                    
                    if (data.length > 0) {
                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.id;
                            
                            if (accountType === 'auto') {
                                option.textContent = `${item.plan.name} -- ${item.balance}USD`;
                            } else {
                                option.textContent = `${item.savings_account.name} -- ${item.balance}USD`;
                            }
                            
                            selectElement.appendChild(option);
                        });
                    } else {
                        selectElement.innerHTML += '<option disabled>No accounts found</option>';
                    }
                })
                .catch(error => {
                    console.error(`Error loading ${accountType} accounts:`, error);
                    selectElement.innerHTML = '<option selected disabled value="">Error loading accounts</option>';
                });
        }

        // Asset price calculation logic remains the same
        const assetSelect = document.getElementById('assetSelect');
        const amountInput = document.getElementById('amountInput');
        const quantityInput = document.getElementById('quantityInput');

        amountInput.addEventListener('input', function() {
            if (assetSelect.value && this.value) {
                const price = parseFloat(assetSelect.selectedOptions[0].dataset.price);
                const amount = parseFloat(this.value);
                quantityInput.value = (amount / price).toFixed(8);
            }
        });

        quantityInput.addEventListener('input', function() {
            if (assetSelect.value && this.value) {
                const price = parseFloat(assetSelect.selectedOptions[0].dataset.price);
                const quantity = parseFloat(this.value);
                amountInput.value = (quantity * price).toFixed(2);
            }
        });
    });
    </script>
    <!-- Credit Modal -->
@endsection

@section('scripts')
    <script>
        document.getElementById('assetSearch').addEventListener('input', function () {
            const search = this.value.toLowerCase();
            const options = document.getElementById('assetSelect').options;

            for (let i = 0; i < options.length; i++) {
                const text = options[i].textContent.toLowerCase();
                options[i].style.display = text.includes(search) ? '' : 'none';
            }
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let now = new Date();
            let formattedDateTime = now.toISOString().slice(0, 16); // Format: YYYY-MM-DDTHH:MM
            document.getElementById("date").value = formattedDateTime;
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            // Listen for changes in the asset dropdown and amount input
            $('#assetSelect, #amountInput').on('input change', function () {
                const selectedAsset = $('#assetSelect option:selected');
                const price = parseFloat(selectedAsset.data('price')) || 0; // Get the asset price
                const amount = parseFloat($('#amountInput').val()) || 0; // Get the entered amount

                // Calculate quantity: quantity = amount / price
                const quantity = price !== 0 ? (amount / price) : 0;

                // Update the quantity input field
                $('#quantityInput').val(quantity.toFixed(8)); // Use toFixed(8) for precision
            });
        });
    </script>

    <script src="{{ asset('admin/assets/js/js-datatables/simple-datatables@latest.js') }}"></script>
    <script src="{{ asset('admin/assets/js/custom-list-product.js') }}"></script>
    <script src="{{ asset('admin/assets/js/owlcarousel/owl.carousel.js') }}"></script>
    <script src="{{ asset('admin/assets/js/ecommerce.js') }}"></script>
    <script src="{{ asset('admin/assets/js/tooltip-init.js') }}"></script>
@endsection
    