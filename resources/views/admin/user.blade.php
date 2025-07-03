@extends('layouts.admin')

@section('title', ' Dashboard')

@section('content')
    <div class="page-body">
        <div class="container-fluid">
        <div class="page-title">
            <div class="row">
            <div class="col-6">
                <h4>
                    Users list</h4>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.html">                                       
                    <svg class="stroke-icon">
                        <use href="../assets/svg/icon-sprite.svg#stroke-home"></use>
                    </svg></a></li>
                <li class="breadcrumb-item">Dashboard </li>
                <li class="breadcrumb-item active">users list</li>
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
                                    <h4>Users</h4>
                                </div>
                                <div class="d-flex align-items-center">
                                    <form action="{{ route('admin.users') }}" method="GET" class="d-flex align-items-center">
                                        <input 
                                            type="text" 
                                            name="search" 
                                            class="form-control" 
                                            placeholder="Search in {{ $users->total() }} Users..." 
                                            value="{{ request('search') }}"
                                        >
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive custom-scrollbar px-4">
                            <table class="table">
                                <thead>
                                <tr class="border-bottom-primary">
                                    <th> <span class="f-light f-w-600">Name</span></th>
                                    <th> <span class="f-light f-w-600">Email </span></th>
                                    <th> <span class="f-light f-w-600">Phone</span></th>
                                    <th> <span class="f-light f-w-600">Country</span></th>
                                    <th> <span class="f-light f-w-600">Currency</span></th>
                                    <th> <span class="f-light f-w-600">Status</span></th>
                                    <th> <span class="f-light f-w-600">Joined</span></th>
                                    <!-- <th> <span class="f-light f-w-600">Balance</span></th> -->
                                    <th> <span class="f-light f-w-600">Action</span></th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $index => $user)
                                        <tr class="">
                                            <!-- <td>{{ $index +  1 }}</td> -->
                                            <td> 
                                                <div class="product-names fw-bold">
                                                {{ $index +  1 }}. <a href="{{ route('admin.users.show', $user->id) }}" class="text-success truncate-content">{{ $user->first_name }} {{ $user->last_name }}</a>
                                                </div>
                                            </td>
                                            <td> 
                                                <p class="f-light truncate-content">{{ $user->email }}</p>
                                            </td>
                                            <td> 
                                                <p class="f-light">{{ $user->phone }}</p>
                                            </td>
                                            <td> 
                                                <p class="f-light text-capitalize">{{ $user->country->name }}</p>
                                            </td>
                                            <td> 
                                                <p class="f-light">USD</p>
                                            </td>
                                            <td> 
                                                <span class="badge @if($user->status == 'active') badge-light-success @else badge-light-danger @endif">
                                                    @if($user->status == 'active') Active @else Suspended @endif
                                                </span>
                                            </td>
                                            <td> 
                                                <p class="f-light truncate-content">{{ $user['created_at']->format('d M, Y \a\t h:i A') }}</p>
                                            </td>
                                            <!-- <td>
                                                <p class="f-light truncate-content">
                                                    @if($user->wallet)
                                                        {{ $user->currency->sign }} {{ $user->wallet->getBalance('wallet') }}
                                                    @endif
                                                </p>
                                            </td> -->
                                            <td>
                                                <div class="btn-group">
                                                    <button class="btn btn-dark rounded-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Action</button>
                                                        <ul class="dropdown-menu dropdown-menu-dark dropdown-block">
                                                            <li>
                                                                <a href="{{ route('admin.users.show', $user->id) }}" class="dropdown-item text-dark fw-bold">View</a>
                                                            </li>
                                                            @if($user->status == 'active')
                                                                <li>
                                                                    <form action="{{ route('admin.users.toggle', $user->id) }}" method="POST" style="display: inline;">
                                                                        @csrf
                                                                        @method('PUT')
                                                                        <input type="hidden" name="action" value="suspended">
                                                                        <button type="submit" class="dropdown-item text-danger fw-bold">Block</button>
                                                                    </form>
                                                                </li>
                                                            @else
                                                                <li>
                                                                    <form action="{{ route('admin.users.toggle', $user->id) }}" method="POST" style="display: inline;">
                                                                        @csrf
                                                                        @method('PUT')
                                                                        <input type="hidden" name="action" value="active">
                                                                        <button type="submit" class="dropdown-item text-success fw-bold">Activiate</button>
                                                                    </form>
                                                                </li>
                                                            @endif
                                                            <li>
                                                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display: inline;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="button" 
                                                                            class="dropdown-item text-danger fw-bold bg-danger text-white"
                                                                            data-delete-button
                                                                            data-model-name="user">
                                                                        DELETE
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if($users->count() < 1)
                                <div class="">
                                    <p class="text-center my-4 py-4">No data</p>
                                </div>
                            @endif
                            <!-- Pagination Links -->
                            <div class="jsgrid-pager my-3 mx-2">
                                Pages:
                                @if ($users->onFirstPage())
                                    <span class="jsgrid-pager-nav-button jsgrid-pager-nav-inactive-button">
                                        <a href="javascript:void(0);">First</a>
                                    </span>
                                    <span class="jsgrid-pager-nav-button jsgrid-pager-nav-inactive-button">
                                        <a href="javascript:void(0);">Prev</a>
                                    </span>
                                @else
                                    <span class="jsgrid-pager-nav-button">
                                        <a href="{{ $users->url(1) }}">First</a>
                                    </span>
                                    <span class="jsgrid-pager-nav-button">
                                        <a href="{{ $users->previousPageUrl() }}">Prev</a>
                                    </span>
                                @endif

                                <!-- Page Numbers -->
                                @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                                    @if ($page == $users->currentPage())
                                        <span class="jsgrid-pager-page jsgrid-pager-current-page">{{ $page }}</span>
                                    @else
                                        <span class="jsgrid-pager-page">
                                            <a href="{{ $url }}">{{ $page }}</a>
                                        </span>
                                    @endif
                                @endforeach

                                @if ($users->hasMorePages())
                                    <span class="jsgrid-pager-nav-button">
                                        <a href="{{ $users->nextPageUrl() }}" class="fw-bold">Next</a>
                                    </span>
                                    <span class="jsgrid-pager-nav-button">
                                        <a href="{{ $users->url($users->lastPage()) }}" class="fw-bold">Last</a>
                                    </span>
                                @else
                                    <span class="jsgrid-pager-nav-button jsgrid-pager-nav-inactive-button">
                                        <a href="javascript:void(0);" class="fw-bold">Next</a>
                                    </span>
                                    <span class="jsgrid-pager-nav-button jsgrid-pager-nav-inactive-button">
                                        <a href="javascript:void(0);" class="fw-bold">Last</a>
                                    </span>
                                @endif

                                &nbsp;&nbsp; {{ $users->currentPage() }} of {{ $users->lastPage() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Container-fluid Ends-->

        @include('components.delete-modal')
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('admin/assets/js/js-datatables/simple-datatables@latest.js') }}"></script>
    <script src="{{ asset('admin/assets/js/custom-list-product.js') }}"></script>
    <script src="{{ asset('admin/assets/js/owlcarousel/owl.carousel.js') }}"></script>
    <script src="{{ asset('admin/assets/js/ecommerce.js') }}"></script>
    <script src="{{ asset('admin/assets/js/tooltip-init.js') }}"></script>
@endsection
    