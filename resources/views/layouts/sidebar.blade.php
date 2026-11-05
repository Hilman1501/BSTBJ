<div class="sidebar sidebar-style-2">
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <div class="user">
                <div class="avatar-sm float-left mr-2">
                     <img src="{{ asset('atlantis/assets/img/profile.png') }}" alt="..." class="avatar-img rounded-circle">
                </div>
                <div class="info">
                    <a data-toggle="collapse" href="#collapseExample" aria-expanded="true">
                        <span>
                            Super Admin
                            <span class="user-level">Administrator</span>
                            <span class="caret"></span>
                        </span>
                    </a>
                <div class="clearfix"></div>

                    {{-- <div class="collapse in" id="collapseExample">
                        <ul class="nav">
                            <li>
                                <a href="#profile">
                                    <span class="link-collapse">Change Password</span>
                                </a>
                            </li>
                        </ul>
                    </div> --}}
                </div>
            </div>
            <ul class="nav nav-primary">
                <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                {{-- <li class="nav-item {{ request()->routeIs('monitoring') ? 'active' : '' }}">
                    <a href="{{ route('monitoring') }}">
                        <i class="fas fa-desktop"></i>
                        <p>Monitoring</p>
                    </a>
                </li> --}}
                <li class="nav-item {{ request()->routeIs('create-bstbj.*') ? 'active' : '' }}">
                    <a href="{{ route('create-bstbj.select') }}">
                        <i class="fas fa-plus-circle"></i>
                        <p>Create BSTBJ</p>
                    </a>
                </li>
                @if (auth()->user()->canAccessOutstandingBstbj())
                <li class="nav-item {{ request()->routeIs('outstanding-bstbj') ? 'active' : '' }}">
                    <a href="{{ route('outstanding-bstbj') }}">
                        <i class="fas fa-clipboard-list"></i>
                        <p>Outstanding BSTBJ</p>
                    </a>
                </li>
                @endif
                <li class="nav-item {{ request()->routeIs('historical-bstbj') ? 'active' : '' }}">
                    <a href="{{ route('historical-bstbj') }}">
                        <i class="fas fa-history"></i>
                        <p>Historical BSTBJ</p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
