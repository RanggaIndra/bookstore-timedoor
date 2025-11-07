@extends('layouts.app')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-star-fill me-2"></i>Input Rating</h5>
        </div>
        <div class="card-body">
            <form method="post" action="{{ route('ratings.store') }}" class="row g-3">
                @csrf

                <div class="col-12 col-lg-4">
                    <label class="form-label" for="author_search">Author</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input id="author_search" type="search" class="form-control"
                            placeholder="Type to search author...">
                    </div>
                    <select id="author_id" name="author_id" class="form-select mt-2" size="6" required></select>
                </div>

                <div class="col-12 col-lg-6">
                    <label class="form-label" for="book_search">Book</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input id="book_search" type="search" class="form-control"
                            placeholder="Type to search book (after choosing author)..." disabled>
                    </div>
                    <select id="book_id" name="book_id" class="form-select mt-2" size="6" required
                        disabled></select>
                    @error('book_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-lg-2">
                    <label for="score" class="form-label">Score</label>
                    <select id="score" name="score" class="form-select" required>
                        @for ($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                    @error('score')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send-fill me-1"></i>Submit
                    </button>
                    <a href="{{ route('books.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-list-ul me-1"></i>Back to list
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const authorSel = document.getElementById('author_id');
        const authorSearch = document.getElementById('author_search');
        const bookSel = document.getElementById('book_id');
        const bookSearch = document.getElementById('book_search');

        let aTimer = null,
            bTimer = null;

        function debounce(fn, wait, key) {
            if (key === 'a') {
                clearTimeout(aTimer);
                aTimer = setTimeout(fn, wait);
            } else {
                clearTimeout(bTimer);
                bTimer = setTimeout(fn, wait);
            }
        }

        function renderOptions(select, items, valueKey, textKey) {
            select.innerHTML = '';
            items.forEach(it => {
                const opt = document.createElement('option');
                opt.value = it[valueKey];
                opt.textContent = it[textKey];
                select.appendChild(opt);
            });
        }

        function fetchAuthors() {
            const q = authorSearch.value.trim();
            const url = new URL('{{ route('ajax.authors') }}', window.location.origin);
            if (q) url.searchParams.set('q', q);
            fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(items => renderOptions(authorSel, items, 'id', 'name'));
        }

        function fetchBooks() {
            const authorId = authorSel.value;
            if (!authorId) {
                bookSel.innerHTML = '';
                bookSel.disabled = true;
                bookSearch.disabled = true;
                return;
            }
            const q = bookSearch.value.trim();
            const url = new URL('{{ route('ajax.books') }}', window.location.origin);
            url.searchParams.set('author_id', authorId);
            if (q) url.searchParams.set('q', q);
            fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(items => {
                    renderOptions(bookSel, items, 'id', 'title');
                    bookSel.disabled = items.length === 0;
                });
        }

        authorSearch.addEventListener('input', () => debounce(fetchAuthors, 250, 'a'));
        authorSel.addEventListener('change', () => {
            bookSearch.disabled = !authorSel.value;
            bookSearch.value = '';
            fetchBooks();
        });
        bookSearch.addEventListener('input', () => debounce(fetchBooks, 250, 'b'));

        fetchAuthors();
    </script>
@endsection
