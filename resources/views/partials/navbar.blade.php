<nav class="navbar navbar-expand-lg">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold row" href="{{ route('home') }}">
            <img src="{{ asset('images/greplens.svg') }}" alt="Greplens logo" height="40"
                class="d-inline-block align-text-top col">
            <div class="col d-flex align-items-center" style="line-height: 1;">
                <span class="navbar-title">Greplens</span>
            </div>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            @auth
                <div class="navbar-nav ms-4">
                    <a class="nav-link {{ request()->routeIs('home') ? 'nav-link-active' : '' }}"
                        href="{{ route('home') }}">Home</a>
                    <a class="nav-link {{ request()->routeIs('rules.*') ? 'nav-link-active' : '' }}"
                        href="{{ route('rules.index') }}">Rules</a>
                    <a class="nav-link {{ request()->routeIs('projects.*') ? 'nav-link-active' : '' }}"
                        href="{{ route('projects.index') }}">Projects</a>
                    <a class="nav-link {{ request()->routeIs('findings.*') ? 'nav-link-active' : '' }}"
                        href="{{ route('findings.index') }}">Findings</a>
                </div>

                <div class="navbar-nav ms-auto">
                    <span class="nav-link disabled" style="color: rgba(249, 247, 247, 0.5);">
                        {{ Auth::user()->name }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="nav-link border-0 bg-transparent" style="cursor: pointer;">
                            Logout
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </div>
</nav>
