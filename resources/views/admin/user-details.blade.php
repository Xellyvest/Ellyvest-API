@extends('layouts.admin')

@section('title', ' Dashboard')

@section('content')
    <div class="page-body">
        <div class="container-fluid">
        <div class="page-title">
            <div class="row">
            <div class="col-6">
                <h4>User Details</h4>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.html">                                       
                    <svg class="stroke-icon">
                        <use href="../assets/svg/icon-sprite.svg#stroke-home"></use>
                    </svg></a></li>
                <li class="breadcrumb-item">Users</li>
                <li class="breadcrumb-item active"> User detail</li>
                </ol>
            </div>
            </div>
        </div>
        </div>
        <!-- Container-fluid starts-->
        <div class="container-fluid">
            <div class="edit-profile">
                <div class="row">
                <div class="col-xl-5">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Profile</h4>
                            <div class="card-options"><a class="card-options-collapse" href="#" data-bs-toggle="card-collapse"><i class="fe fe-chevron-up"></i></a><a class="card-options-remove" href="#" data-bs-toggle="card-remove"><i class="fe fe-x"></i></a></div>
                        </div>
                        <div class="card-body">
                                <div class="row mb-2">
                                    <div class="profile-title">
                                    <div class="media">   
                                        @if($user->avatar)                     
                                            <img class="img-70 rounded-circle" alt="" src="{{$user->avatar}}">
                                        @else
                                            <img class="img-70 rounded-circle" alt="" src="https://cdn-icons-png.flaticon.com/512/6596/6596121.png">
                                        @endif
                                        <div class="media-body">
                                        <h5 class="fw-bold f-20">{{ $user->first_name }} {{ $user->last_name }}</h5>
                                        <p>@ {{ $user->username }}</p>
                                        </div>
                                    </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-7">
                                        <div class="mb-3">
                                            <label class="form-label">Email-Address</label>
                                            <input class="form-control" value="{{ $user->email }}" disabled>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Phone</label>
                                            <input class="form-control" value="{{ $user->phone }}" disabled>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Country</label>
                                            <input class="form-control" value="{{ $user->country->name }}" disabled>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Currency</label>
                                            <input class="form-control" value="{{ $user->currency->name }} ({{ $user->currency->symbol }})" disabled>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <span class="mx-2 px-4 badge @if($user->status == 'active') badge-light-success @else badge-light-danger @endif">
                                                @if($user->status == 'active') Active @else Suspended @endif
                                            </span>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">KYC</label>
                                            <span class="mx-2 px-4 badge @if($user->kyc == 'approved') badge-light-success @elseif($user->kyc == 'pending') badge-light-warning @elseif($user->kyc == 'submitted') badge-light-info @else badge-light-danger @endif">
                                                @if($user->kyc == 'approved') Approved @elseif($user->kyc == 'pending') Pending @elseif($user->kyc == 'submitted') Submitted @else Declined @endif
                                            </span>
                                        </div>

                                        <div class="mb-3 d-flex">
                                            <label class="form-label">ID</label>
                                            <span class="mx-2 px-4 badge badge-light-primary">
                                                @if($user->id_type)
                                                    {{ $user->id_type }} - {{ $user->id_number }} 
                                                @else
                                                    ---- - 000-000-000
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-5">
                                        <div class="revenuegrowth-details my-3"> 
                                            <div class="growth-details">
                                                <span class="f-light f-12  text-uppercase">Cash Balance</span>
                                                <h4 class="fw-bold mb-1">{{ $user->currency->sign }}{{ number_format($balance, 2) }}</h4>
                                                <div class="mb-4">
                                                    <!-- <span class="f-light text-success f-12 f-w-600">+40.15%</span> -->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="revenuegrowth-details my-3"> 
                                            <div class="growth-details">
                                                <span class="f-light f-12  text-uppercase">Brokerage Balance</span>
                                                <h4 class="fw-bold mb-1">{{ $user->currency->sign }}{{ number_format($brokerage_balance, 2) }}</h4>
                                                <div class="mb-4">
                                                    <!-- <span class="f-light text-success f-12 f-w-600">+40.15%</span> -->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="revenuegrowth-details my-3"> 
                                            <div class="growth-details">
                                                <span class="f-light f-12  text-uppercase">Auto Balance</span>
                                                <h4 class="fw-bold mb-1">{{ $user->currency->sign }}{{ number_format($auto_balance, 2) }}</h4>
                                                <div class="mb-4">
                                                    <!-- <span class="f-light text-success f-12 f-w-600">+40.15%</span> -->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="revenuegrowth-details my-3"> 
                                            <div class="growth-details">
                                                <span class="f-light f-12  text-uppercase">Savings Balance</span>
                                                <h4 class="fw-bold mb-1">{{ $user->currency->sign }}{{ number_format($savings_balance, 2) }}</h4>
                                                <div class="mb-4">
                                                    <!-- <span class="f-light text-success f-12 f-w-600">+40.15%</span> -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        @if($user->front_id && $user->back_id)
                                            <div class="d-flex">
                                                <div class="m-1">
                                                    <span class="f-light">Front ID:</span>
                                                    <img class="rounded" style="width: 200px;" src="{{$user->front_id}}" alt="front id">
                                                </div>
                                                <div class="m-1">
                                                    <span class="f-light">Back ID:</span>
                                                    <img class="rounded" style="width: 200px;" src="{{$user->back_id}}" alt="back id">
                                                </div>
                                            </div>
                                            <div class="form-footer mt-4 d-flex">
                                                <form action="{{ route('admin.users.kyc', $user->id) }}" method="post">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="action" value="approved">
                                                    <button class="btn f-light badge badge-light-success" type="submit">Approve</button>
                                                </form>
                                                <form action="{{ route('admin.users.kyc', $user->id) }}" method="post">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="action" value="declined">
                                                    <button class="btn f-light badge badge-light-danger mx-2" type="submit">Decline</button>
                                                </form>
                                            </div>
                                            <form action="{{ route('admin.users.cancelkyc', $user->id) }}" method="post">
                                                @csrf
                                                @method('PUT')
                                                <button class="btn f-light badge badge-light-danger mx-2" type="submit">Cancel KYC</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-footer mt-4 d-flex">
                                    <!-- <a href="{{ route('admin.altLogin') }}?email={{ $user->email }}" type="button" class="btn btn-primary btn-block" onclick="window.open('{{ route('admin.altLogin') }}?email={{ $user->email }}', 'newwindow', 'width=full'); return false;">
                                        User Login
                                    </a> -->
                                    @if($user->status == 'active')
                                        <form action="{{ route('admin.users.toggle', $user->id) }}" method="post">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="action" value="suspended">
                                            <button class="btn btn-danger btn-block" type="submit">Suspend User</button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.users.toggle', $user->id) }}" method="post">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="action" value="active">
                                            <button class="btn btn-success btn-block" type="submit">Activate User</button>
                                        </form>
                                    @endif
                                </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-7">
                    <form class="card" action="{{ route('admin.users.update', $user->id) }}" method="post">
                        @csrf
                        @method('PUT')
                        <div class="card-header d-flex justify-content-between">
                            <h4 class="card-title mb-0">Edit Profile</h4>
                            <div class="form-footer d-flex">
                                <a href="#" 
                                    class="badge px-3 f-light badge badge-light-success text-success" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#creditTransactionModal">
                                    Credit
                                </a>
                                <a href="#" 
                                    class="badge px-3 f-light badge badge-light-danger text-danger" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#debitTransactionModal">
                                    Debit
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">First Name</label>
                                    <input class="form-control" type="text" value="{{ $user->first_name }}" name="first_name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input class="form-control" type="text" value="{{ $user->last_name }}" name="last_name">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input class="form-control" type="email" value="{{ $user->email }}" name="email">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <input class="form-control" type="text" value="{{ $user->address }}" name="address">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Country</label>
                                    <select class="form-control text-capitalize" id="country-select" name="country_id" required>
                                        <option value="">Select Country</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->id }}" {{ $user->country_id == $country->id ? 'selected' : '' }}>
                                                {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">State</label>
                                    <select class="form-control text-capitalize" id="state-select" name="state_id" required>
                                        <option value="">Select State</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Zipcode</label>
                                    <input class="form-control" type="text" value="{{ $user->zipcode }}" name="zipcode">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">DOB</label>
                                    <input class="form-control" type="date" value="{{ $user->dob ? \Carbon\Carbon::parse($user->dob)->format('Y-m-d') : '' }}" name="dob">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Employed</label>
                                    <input class="form-control" type="text" value="{{ $user->employed }}" name="employed">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nationality</label>
                                    <input class="form-control" type="text" value="{{ $user->nationality }}" name="nationality">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Experience</label>
                                    <input class="form-control" type="text" value="{{ $user->experience }}" name="experience">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                <label class="form-label">Currency</label>
                                <select class="form-control btn-square" name="currency_id">
                                    @foreach($currencies as $currency)
                                        <option value="{{$currency->id}}" @if($currency->id == $user->currency->id) selected @endif>{{$currency->name}}  ({{$currency->symbol}})</option>
                                    @endforeach
                                </select>
                                </div>
                            </div>
                        </div>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn btn-primary" type="submit">Update Profile</button>
                        </div>
                    </form>
                </div>

                <!-- Deposit Details Card -->
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-header d-flex justify-content-between">
                                <h4 class="card-title mb-0">Deposit Details</h4>
                                <!-- Tabs Navigation -->
                                <ul class="nav nav-tabs border-tab border-0 mb-0 nav-dark" id="deposit-methods-tab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link nav-border pt-0 txt-dark nav-dark active" id="deposit-bank-tab" data-bs-toggle="tab" href="#deposit-bank-methods" role="tab" aria-controls="bank" aria-selected="true">
                                            Bank Accounts
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link nav-border txt-dark nav-dark" id="deposit-crypto-tab" data-bs-toggle="tab" href="#deposit-crypto-methods" role="tab" aria-controls="crypto" aria-selected="false">
                                            Crypto Wallets
                                        </a>
                                    </li>
                                </ul>
                                <div class="form-footer d-flex">
                                    <button class="btn btn-success btn-sm" id="add-deposit-bank-btn" data-bs-target="#addPaymentMethodModal" data-type="bank" data-method-type="deposit" data-user="{{ $user->id }}" style="{{ $user->bankMethods->where('is_withdrawal', false)->count() > 0 ? 'display:none' : '' }}">
                                        <i class="fa fa-plus"></i> Add Bank Account
                                    </button>
                                    <button class="btn btn-success btn-sm ms-1" id="add-deposit-crypto-btn" data-bs-target="#addPaymentMethodModal" data-type="crypto" data-method-type="deposit" data-user="{{ $user->id }}" style="{{ $user->cryptoMethods->where('is_withdrawal', false)->count() >= 4 ? 'display:none' : '' }}">
                                        <i class="fa fa-plus"></i> Add Crypto Wallet
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Tab Content -->
                            <div class="tab-content" id="deposit-methods-tabContent">
                                <!-- Bank Accounts Tab -->
                                <div class="tab-pane fade active show" id="deposit-bank-methods" role="tabpanel" aria-labelledby="deposit-bank-tab">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Label</th>
                                                    <th>Bank Name</th>
                                                    <th>Account Number</th>
                                                    <th>Account Name</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($user->bankMethods->where('is_withdrawal', false) as $method)
                                                <tr>
                                                    <td>{{ $method->label ?? 'N/A' }}</td>
                                                    <td>{{ $method->bank_name }}</td>
                                                    <td>{{ $method->account_number }}</td>
                                                    <td>{{ $method->account_name }}</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary edit-method" 
                                                                data-method="{{ json_encode($method, JSON_HEX_APOS) }}"
                                                                data-type="bank"
                                                                data-method-type="deposit">
                                                            <i class="fa fa-edit"></i>
                                                        </button>
                                                        <form action="{{ route('admin.user.payment.delete', $method->id) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-outline-danger delete-method"
                                                                    data-id="{{ $method->id }}"
                                                                    data-delete-button
                                                                    data-model-name="payment method">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-4">No deposit bank accounts added yet</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Crypto Wallets Tab -->
                                <div class="tab-pane fade" id="deposit-crypto-methods" role="tabpanel" aria-labelledby="deposit-crypto-tab">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Label</th>
                                                    <th>Currency</th>
                                                    <th>Wallet Address</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($user->cryptoMethods->where('is_withdrawal', false) as $method)
                                                <tr>
                                                    <td>{{ $method->label ?? 'N/A' }}</td>
                                                    <td>{{ $method->currency }}</td>
                                                    <td class="text-truncate" style="max-width: 200px;">{{ $method->wallet_address }}</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary edit-method" 
                                                                data-method="{{ json_encode($method) }}"
                                                                data-type="crypto"
                                                                data-method-type="deposit">
                                                            <i class="fa fa-edit"></i>
                                                        </button>
                                                        <form action="{{ route('admin.user.payment.delete', $method->id) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-outline-danger delete-method"
                                                                    data-id="{{ $method->id }}"
                                                                    data-delete-button
                                                                    data-model-name="payment method">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-4">No deposit crypto wallets added yet</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Withdrawal Details Card -->
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-header d-flex justify-content-between">
                                <h4 class="card-title mb-0">Withdrawal Details</h4>
                                <!-- Tabs Navigation -->
                                <ul class="nav nav-tabs border-tab border-0 mb-0 nav-dark" id="withdrawal-methods-tab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link nav-border pt-0 txt-dark nav-dark active" id="withdrawal-bank-tab" data-bs-toggle="tab" href="#withdrawal-bank-methods" role="tab" aria-controls="bank" aria-selected="true">
                                            Bank Accounts
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link nav-border txt-dark nav-dark" id="withdrawal-crypto-tab" data-bs-toggle="tab" href="#withdrawal-crypto-methods" role="tab" aria-controls="crypto" aria-selected="false">
                                            Crypto Wallets
                                        </a>
                                    </li>
                                </ul>
                                {{-- <div class="form-footer d-flex">
                                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addPaymentMethodModal" data-type="bank" data-method-type="withdrawal" data-user="{{ $user->id }}">
                                        <i class="fa fa-plus"></i> Add Bank Account
                                    </button>
                                    <button class="btn btn-success btn-sm ms-1" data-bs-toggle="modal" data-bs-target="#addPaymentMethodModal" data-type="crypto" data-method-type="withdrawal" data-user="{{ $user->id }}">
                                        <i class="fa fa-plus"></i> Add Crypto Wallet
                                    </button>
                                </div> --}}
                            </div>
                            
                            <!-- Tab Content -->
                            <div class="tab-content" id="withdrawal-methods-tabContent">
                                <!-- Bank Accounts Tab -->
                                <div class="tab-pane fade active show" id="withdrawal-bank-methods" role="tabpanel" aria-labelledby="withdrawal-bank-tab">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Label</th>
                                                    <th>Bank Name</th>
                                                    <th>Account Number</th>
                                                    <th>Account Name</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($user->bankMethods->where('is_withdrawal', true) as $method)
                                                <tr>
                                                    <td>{{ $method->label ?? 'N/A' }}</td>
                                                    <td>{{ $method->bank_name }}</td>
                                                    <td>{{ $method->account_number }}</td>
                                                    <td>{{ $method->account_name }}</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary edit-method" 
                                                                data-method="{{ json_encode($method, JSON_HEX_APOS) }}"
                                                                data-type="bank"
                                                                data-method-type="withdrawal">
                                                            <i class="fa fa-edit"></i>
                                                        </button>
                                                        <form action="{{ route('admin.user.payment.delete', $method->id) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-outline-danger delete-method"
                                                                    data-id="{{ $method->id }}"
                                                                    data-delete-button
                                                                    data-model-name="payment method">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-4">No withdrawal bank accounts added yet</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Crypto Wallets Tab -->
                                <div class="tab-pane fade" id="withdrawal-crypto-methods" role="tabpanel" aria-labelledby="withdrawal-crypto-tab">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Label</th>
                                                    <th>Currency</th>
                                                    <th>Wallet Address</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($user->cryptoMethods->where('is_withdrawal', true) as $method)
                                                <tr>
                                                    <td>{{ $method->label ?? 'N/A' }}</td>
                                                    <td>{{ $method->currency }}</td>
                                                    <td class="text-truncate" style="max-width: 200px;">{{ $method->wallet_address }}</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary edit-method" 
                                                                data-method="{{ json_encode($method) }}"
                                                                data-type="crypto"
                                                                data-method-type="withdrawal">
                                                            <i class="fa fa-edit"></i>
                                                        </button>
                                                        <form action="{{ route('admin.user.payment.delete', $method->id) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-outline-danger delete-method"
                                                                    data-id="{{ $method->id }}"
                                                                    data-delete-button
                                                                    data-model-name="payment method">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-4">No withdrawal crypto wallets added yet</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-12">
                    <form class="card" action="{{ route('admin.user.settings', $user->id) }}" method="post">
                        @csrf
                        <input type="hidden" name="type" value="admin">
                        <div class="card-header d-flex justify-content-between">
                            <h4 class="card-title mb-0">User Settings</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Min Bank Deposit</label>
                                        <input class="form-control" type="text" value="{{ $user->settings->min_cash_bank_deposit }}" name="min_cash_bank_deposit">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Min Crypto Deposit</label>
                                        <input class="form-control" type="text" value="{{ $user->settings->min_cash_crypto_deposit }}" name="min_cash_crypto_deposit">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Max Bank Deposit</label>
                                        <input class="form-control" type="text" value="{{ $user->settings->max_cash_bank_deposit }}" name="max_cash_bank_deposit">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Max Crypto Deposit</label>
                                        <input class="form-control" type="text" value="{{ $user->settings->max_cash_crypto_deposit }}" name="max_cash_crypto_deposit">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Min Bank Withdrawal</label>
                                        <input class="form-control" type="text" value="{{ $user->settings->min_cash_bank_withdrawal }}" name="min_cash_bank_withdrawal">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Min Crypto Withdrawal</label>
                                        <input class="form-control" type="text" value="{{ $user->settings->min_cash_crypto_withdrawal }}" name="min_cash_crypto_withdrawal">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Max Bank Withdrawal</label>
                                        <input class="form-control" type="text" value="{{ $user->settings->max_cash_bank_withdrawal }}" name="max_cash_bank_withdrawal">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Max Crypto Withdrawal</label>
                                        <input class="form-control" type="text" value="{{ $user->settings->max_cash_crypto_withdrawal }}" name="max_cash_crypto_withdrawal">
                                    </div>
                                </div>

                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="sub-title">Lock Cash</label>
                                        <div class="form-check-size">
                                            <div class="form-check form-switch form-check-inline">
                                                <input class="form-check-input check-size" type="checkbox" role="switch" name="locked_cash" 
                                                    {{ $user->settings->locked_cash ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="sub-title">Lock Bank Deposit</label>
                                        <div class="form-check-size">
                                            <div class="form-check form-switch form-check-inline">
                                                <input class="form-check-input check-size" type="checkbox" role="switch" name="locked_bank_deposit"
                                                    {{ $user->settings->locked_bank_deposit ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Lock Cash Message</label>
                                        <input class="form-control" type="text" value="{{ $user->settings->locked_cash_message }}" name="locked_cash_message">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Lock Bank Deposit Message</label>
                                        <input class="form-control" type="text" value="{{ $user->settings->locked_bank_deposit_message }}" name="locked_bank_deposit_message">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Connect Wallet</label>
                                        <input class="form-control" type="text" value="{{ $user->settings->connect_wallet_network }}" name="connect_wallet">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Connect Wallet Phrase</label>
                                        <input class="form-control" type="text" value="{{ $user->settings->connect_wallet_phrase }}" name="connect_wallet_phrase">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-start">
                            <button class="btn btn-success" type="submit">Update Settings</button>
                        </div>
                    </form>
                </div>

                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-header d-flex justify-content-between">
                                <div class="d-flex">
                                    <h4 class="card-title mb-0">Wallet Connect </h4>
                                    <span class="mx-2 px-4 badge @if($user->settings->is_connect_activated == 1) badge-light-success @else badge-light-danger @endif">
                                        @if($user->settings->is_connect_activated == 1) Active @else Deactivated @endif
                                    </span>
                                </div>
                            </div>
                            <div class="my-4">
                                @php
                                    $wallets = json_decode($user->settings->connected_wallet, true);
                                @endphp

                                @if (!empty($wallets))
                                    <div class="row">
                                        @foreach ($wallets as $wallet)
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold my-2">{{ $wallet['wallet'] }}</label>
                                                <textarea class="form-control" readonly> {{ $wallet['phrase'] }} </textarea>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p>No connected wallets found.</p>
                                @endif
                            </div>
                            <div class="form-footer">
                                @if($user->settings->is_connect_activated)
                                    <form action="{{ route('admin.users.connect', $user->id) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-danger btn-sm ms-1" id="" type="submit">
                                            Deactivate Wallet
                                        </button>
                                    </form>
                                @else 
                                    <form action="{{ route('admin.users.connect', $user->id) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-success btn-sm ms-1" id="" type="submit">
                                            Activate Wallet
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-tabs border-tab border-0 mb-0 nav-primary" id="topline-tab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link nav-border pt-0 txt-primary nav-primary active" id="topline-top-user-tab" data-bs-toggle="tab" href="#topline-top-user" role="tab" aria-controls="topline-top-user" aria-selected="false" tabindex="-1">
                                        Transactions
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link nav-border txt-primary nav-primary" id="topline-top-description-tab" data-bs-toggle="tab" href="#topline-top-description" role="tab" aria-controls="topline-top-description" aria-selected="false" tabindex="-1">
                                        Trades
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link nav-border txt-primary nav-primary" id="topline-top-review-tab" data-bs-toggle="tab" href="#topline-top-review" role="tab" aria-controls="topline-top-description" aria-selected="false" tabindex="-1">
                                        Savings
                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content" id="topline-tabContent">
                                <div class="tab-pane fade active show" id="topline-top-user" role="tabpanel" aria-labelledby="topline-top-user-tab">
                                    <div class="card-body px-0 pb-0">
                                        <div class="user-content"> 
                                            <div class="table-responsive custom-scrollbar">
                                            <table class="table mb-0">
                                                <thead>
                                                <tr>
                                                    <th scope="col">#</th>
                                                    <th scope="col">Amount</th>
                                                    <th scope="col">Type</th>
                                                    <th scope="col">Comment</th>
                                                    <th scope="col">Status</th>
                                                    <th scope="col">Date</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($transactions as $transaction)
                                                        <tr>
                                                            <th scope="row">{{ $loop->iteration + ($transactions->currentPage() - 1) * $transactions->perPage() }}</th>
                                                            <td class="truncate-content">{{ $transaction->amount }}{{ $user->currency->symbol }}</td>
                                                            <td> 
                                                                <span class="truncate-content badge @if($transaction->type == 'credit') badge-light-success @elseif($transaction->type == 'transfer') badge-light-info @else badge-light-danger @endif">
                                                                    @if($transaction->type == 'credit') Credit @elseif($transaction->type == 'transfer') Transfer @else Debit @endif
                                                                </span> 
                                                            </td>
                                                            <td class="truncate-con"> 
                                                                {{ $transaction->comment }}
                                                            </td>
                                                            <td> 
                                                                <span class="badge @if($transaction->status == 'approved') badge-light-success  @elseif($transaction->status == 'pending') badge-light-warning @else badge-light-danger @endif">
                                                                    @if($transaction->status == 'approved') Approved @elseif($transaction->status == 'pending') Pending  @else Declined @endif
                                                                </span>
                                                            </td>
                                                            <td> <p class="truncate-content">{{ $transaction['created_at']->format('d M, Y \a\t h:i A') }}</p> </td>
                                                        </tr>

                                                    @endforeach
                                                </tbody>
                                            </table>
                                            @if($transactions->count() < 1)
                                                <p class="text-center my-2 py-4">No Transaction</p>
                                            @else
                                                <!-- Pagination Links -->
                                                <div class="jsgrid-pager">
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
                                                            <a href="{{ $transactions->nextPageUrl() }}">Next</a>
                                                        </span>
                                                        <span class="jsgrid-pager-nav-button">
                                                            <a href="{{ $transactions->url($transactions->lastPage()) }}">Last</a>
                                                        </span>
                                                    @else
                                                        <span class="jsgrid-pager-nav-button jsgrid-pager-nav-inactive-button">
                                                            <a href="javascript:void(0);">Next</a>
                                                        </span>
                                                        <span class="jsgrid-pager-nav-button jsgrid-pager-nav-inactive-button">
                                                            <a href="javascript:void(0);">Last</a>
                                                        </span>
                                                    @endif

                                                    &nbsp;&nbsp; {{ $transactions->currentPage() }} of {{ $transactions->lastPage() }}
                                                </div>
                                            @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="topline-top-description" role="tabpanel" aria-labelledby="topline-top-description-tab">
                                    <div class="card-body px-0 pb-0">  
                                    <!-- <div class="user-header pb-2"> 
                                        <h6 class="fw-bold">User Details:</h6>
                                    </div> -->
                                    <div class="user-content"> 
                                        <div class="table-responsive custom-scrollbar">
                                        <table class="table mb-0">
                                            <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Asset</th>
                                                <th scope="col">Amount</th>
                                                <th scope="col">Quantity</th>
                                                <th scope="col">P/L</th>
                                                <th scope="col">Date</th>
                                                <th scope="col">Status</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($trades as $trade)
                                                    <tr>
                                                        <th scope="row">1</th>
                                                        <td class="truncate-content">{{ $trade->asset->name }}</td>
                                                        <td class="truncate-content">{{ number_format($trade->amount, 2) }} {{ $user->currency->symbol }}</td>
                                                        <td>{{ number_format($trade->quantity, 6) }}</td>
                                                        <td> <p class="text-success">+0.00 {{ $user->currency->sign }}</p> </td>
                                                        <td> <p class="text-success">{{ $trade->status }}</p> </td>
                                                        <td> <p class="truncate-content">{{ $trade['created_at']->format('d M, Y \a\t h:i A') }}</p> </td>
                                                        <td> 
                                                            <span class="badge @if($trade->status == 'open') badge-light-success  @elseif($trade->status == 'hold') badge-light-warning @else badge-light-danger @endif">
                                                                @if($trade->status == 'open') Open @elseif($trade->status == 'hold') Hold  @else Closed @endif
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @if($trades->count() < 1)
                                            <p class="text-center my-2 py-4">No Trades</p>
                                        @endif
                                        </div>
                                    </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="topline-top-review" role="tabpanel" aria-labelledby="topline-top-review-tab">
                                    <div class="card-body px-0 pb-0">
                                    <div class="user-content"> 
                                        <div class="table-responsive custom-scrollbar">
                                        <table class="table mb-0">
                                            <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Account</th>
                                                <th scope="col">Amount</th>
                                                <th scope="col">Profit</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($savings_account as $account)
                                                    <tr>
                                                        <th scope="row">1</th>
                                                        <td>{{ $account->savingsAccount->name }}</td>
                                                        <td>{{ number_format($account->balance, 2) }} {{ $user->currency->symbol }}</td>
                                                        <td> <p class="text-success">+0.00 {{ $user->currency->sign }}</p> </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @if($savings_account->count() < 1)
                                            <p class="text-center my-2 py-4">No Account</p>
                                        @endif
                                        </div>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Container-fluid Ends-->
    </div>

   <!-- ::::::  MODAL SECTION   :::::: -->
    <div>
        <!-- Credit Modal -->
        <div class="modal fade" id="creditTransactionModal" tabindex="-1" aria-labelledby="creditTransactionModal" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body"> 
                        <div class="modal-toggle-wrapper"> 
                            <h4 class="text-center pb-2" id="">Credit User</h4> 
                            <form id="transactionForm" action="{{ route('admin.user.credit', $user->id) }}" method="POST">
                                @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Account</label>
                                        <select class="form-control text-capitalize" id="account" name="account" required>
                                            <option value="">---- Select Account ----</option>
                                            <option value="wallet">Cash Account</option>
                                            <option value="brokerage">Brokerage Account</option>
                                            <option value="auto">Auto Account</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Amount</label>
                                        <input class="form-control" type="number" placeholder="Enter amount..." name="amount">
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

        <!-- Debit Modal -->
        <div class="modal fade" id="debitTransactionModal" tabindex="-1" aria-labelledby="debitTransactionModal" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body"> 
                        <div class="modal-toggle-wrapper"> 
                            <h4 class="text-center pb-2" id="">Debit User</h4> 
                            <form id="transactionForm" action="{{ route('admin.user.debit', $user->id) }}" method="POST">
                                @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Account</label>
                                        <select class="form-control text-capitalize" id="account" name="account" required>
                                            <option value="">---- Select Account ----</option>
                                            <option value="wallet">Cash Account</option>
                                            <option value="brokerage">Brokerage Account</option>
                                            <option value="auto">Auto Account</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Amount</label>
                                        <input class="form-control" type="number" placeholder="Enter amount..." name="amount">
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
        <!-- Debit Modal -->
    </div>

    <!-- Add/Edit Payment Method Modal -->
    @include('components.add-edit')

    <!-- Delete Confirmation Modal -->
    @include('components.delete-modal', [
        'modalId' => 'deletePaymentMethodModal',
        'title' => 'Delete Payment Method',
        'body' => 'Are you sure you want to delete this payment method?',
        'actionLabel' => 'Delete'
    ])

    <!-- jQuery 3.6.0 from Google CDN -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const allStates = @json($states);

        $(document).ready(function () {
            const $countrySelect = $('#country-select');
            const $stateSelect = $('#state-select');
            const selectedStateId = "{{ old('state_id', $user->state_id ?? '') }}";

            function populateStates(countryId) {
                $stateSelect.empty().append('<option value="">-- Select State --</option>');

                allStates
                    .filter(state => state.country_id == countryId)
                    .forEach(state => {
                        const isSelected = state.id == selectedStateId ? 'selected' : '';
                        $stateSelect.append(`<option value="${state.id}" ${isSelected}>${state.name}</option>`);
                    });
            }

            $countrySelect.on('change', function () {
                const selectedCountryId = $(this).val();
                populateStates(selectedCountryId);
            });

            // Trigger on page load if country is already selected
            if ($countrySelect.val()) {
                populateStates($countrySelect.val());
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const transactionModal = document.getElementById('transactionModal');
            const modalTitle = document.getElementById('modalTitle');
            const transactionForm = document.getElementById('transactionForm');
            
            transactionModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const action = button.getAttribute('data-action');
                const url = button.getAttribute('data-url');
                
                // Set modal title and form action dynamically
                modalTitle.textContent = `${action} {{ $user->first_name }}`;
                transactionForm.action = url;
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Handle add method button to set the type
            $('[data-bs-target="#addPaymentMethodModal"]').click(function() {
                const user = $(this).data('user');
                const type = $(this).data('type');
                $('#paymentMethodType').val(type);
                $('#paymentMethodForm').attr('action', `/admin/user/payment-method/${user}`);
                $('#paymentMethodForm input[name="_method"]').remove();
                
                // Reset form and show relevant fields
                resetPaymentMethodForm(type);
                
                // Initialize and show the modal
                var addPaymentMethodModal = new bootstrap.Modal(document.getElementById('addPaymentMethodModal'));
                addPaymentMethodModal.show();
            });
            
            // Handle edit method button
            $(document).on('click', '.edit-method', function() {
                try {
                    // Safely parse the method data
                    const methodData = $(this).data('method');
                    const method = typeof methodData === 'string' ? JSON.parse(methodData) : methodData;
                    
                    const type = $(this).data('type');
                    
                    $('#paymentMethodType').val(type);
                    $('#paymentMethodForm').attr('action', `/admin/user/payment-method/${method.id}`);
                    $('#paymentMethodForm').append('<input type="hidden" name="_method" value="PUT">');
                    
                    // Populate form fields
                    resetPaymentMethodForm(type);
                    $('#methodId').val(method.id);
                    $('#label').val(method.label);
                    $('#is_withdrawal').prop('checked', method.is_withdrawal);
                    
                    if (type === 'bank') {
                        $('#bank_name').val(method.bank_name);
                        $('#account_name').val(method.account_name);
                        $('#account_number').val(method.account_number);
                        $('#routing_number').val(method.routing_number);
                        $('#bank_address').val(method.bank_address);
                        $('#bank_reference').val(method.bank_reference);
                        $('#addPaymentMethodModalLabel').text('Edit Bank Account');
                    } else {
                        $('#currency').val(method.currency);
                        $('#wallet_address').val(method.wallet_address);
                        $('#addPaymentMethodModalLabel').text('Edit Crypto Wallet');
                    }
                    
                    // Initialize and show the modal
                    var addPaymentMethodModal = new bootstrap.Modal(document.getElementById('addPaymentMethodModal'));
                    addPaymentMethodModal.show();
                    
                } catch (error) {
                    console.error('Error parsing method data:', error);
                    alert('Error loading payment method details. Please try again.');
                }
            });
            
            // Handle delete method button
            $(document).on('click', '.delete-method', function() {
                const methodId = $(this).data('id');
                const methodLabel = $(this).data('label');
                
                $('#deleteModelName').text(methodLabel);
                $('#deleteForm').attr('action', `/admin/user/payment-method/${methodId}`);
                
                // Initialize and show the modal
                var deleteModal = new bootstrap.Modal(document.getElementById('deletePaymentMethodModal'));
                deleteModal.show();
            });
            
            // Function to reset and show relevant fields
            function resetPaymentMethodForm(type) {
                // Hide all fields first
                $('.bank-fields, .crypto-fields').addClass('d-none');
                
                // Reset all inputs
                $('#paymentMethodForm')[0].reset();
                
                // Show relevant fields
                if (type === 'bank') {
                    $('.bank-fields').removeClass('d-none');
                    $('#addPaymentMethodModalLabel').text('Add Bank Account');
                } else {
                    $('.crypto-fields').removeClass('d-none');
                    $('#addPaymentMethodModalLabel').text('Add Crypto Wallet');
                }
            }
        });
    </script>



@endsection

@section('scripts')
    <script src="{{ asset('admin/assets/js/js-datatables/simple-datatables@latest.js') }}"></script>
    <script src="{{ asset('admin/assets/js/custom-list-product.js') }}"></script>
    <script src="{{ asset('admin/assets/js/owlcarousel/owl.carousel.js') }}"></script>
    <script src="{{ asset('admin/assets/js/ecommerce.js') }}"></script>
    <script src="{{ asset('admin/assets/js/tooltip-init.js') }}"></script>
@endsection