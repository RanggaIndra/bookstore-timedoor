<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Bookstore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-3">
        <div class="container">
            <a class="navbar-brand" href="{{ route('books.index') }}">
                <i class="bi bi-book-half me-2"></i>Bookstore
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div id="topnav" class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('books.index') ? 'active' : '' }}"
                            href="{{ route('books.index') }}"><i class="bi bi-journals me-1"></i>Books</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('authors.top') ? 'active' : '' }}"
                            href="{{ route('authors.top') }}"><i class="bi bi-people-fill me-1"></i>Top Authors</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('ratings.create') ? 'active' : '' }}"
                            href="{{ route('ratings.create') }}"><i class="bi bi-star-fill me-1"></i>Input Rating</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mb-5">
        @if (session('ok'))
            <div class="alert alert-success d-flex align-items-center"><i
                    class="bi bi-check-circle-fill me-2"></i>{{ session('ok') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Oops</div>
                <ul class="mb-0">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
