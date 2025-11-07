@extends('layouts.app')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><i class="bi bi-journals me-2"></i>List of Books</h5>
            </div>
        </div>
        <div class="card-body">
            <form method="get" class="row g-2">
                <div class="col-12 col-md-4">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                        placeholder="Search: title / author / ISBN / publisher">
                </div>
                <div class="col-6 col-md-2">
                    <input type="number" name="year_from" value="{{ request('year_from') }}" class="form-control"
                        placeholder="Year from" min="1900" max="2099">
                </div>
                <div class="col-6 col-md-2">
                    <input type="number" name="year_to" value="{{ request('year_to') }}" class="form-control"
                        placeholder="Year to" min="1900" max="2099">
                </div>
                <div class="col-12 col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Any status</option>
                        @foreach (['available', 'rented', 'reserved'] as $s)
                            <option value="{{ $s }}" @selected(request('status') == $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <input type="text" name="store" class="form-control" placeholder="Store location"
                        value="{{ request('store') }}">
                </div>

                <div class="col-6 col-md-2">
                    <input type="number" step="0.1" min="0" max="10" name="rating_min"
                        class="form-control" placeholder="Min rating" value="{{ request('rating_min') }}">
                </div>
                <div class="col-6 col-md-2">
                    <input type="number" step="0.1" min="0" max="10" name="rating_max"
                        class="form-control" placeholder="Max rating" value="{{ request('rating_max') }}">
                </div>

                <div class="col-12 col-md-3">
                    @isset($allAuthors)
                        <select name="author_id" class="form-select">
                            <option value="">Any author</option>
                            @foreach ($allAuthors as $a)
                                <option value="{{ $a->id }}" @selected(request('author_id') == $a->id)>{{ $a->name }}</option>
                            @endforeach
                        </select>
                    @endisset
                </div>

                <div class="col-12 col-md-5">
                    @isset($allCategories)
                        <div class="d-flex gap-2">
                            <select name="categories[]" multiple size="4" class="form-select" style="min-width:260px">
                                @foreach ($allCategories as $c)
                                    <option value="{{ $c->id }}" @if (collect((array) request('categories'))->contains($c->id)) selected @endif>
                                        {{ $c->name }}</option>
                                @endforeach
                            </select>
                            <select name="cat_logic" class="form-select" style="max-width:160px">
                                <option value="or" @selected(request('cat_logic', 'or') === 'or')>Category OR</option>
                                <option value="and" @selected(request('cat_logic') === 'and')>Category AND</option>
                            </select>
                        </div>
                    @else
                        <div class="form-text">Tip: filter categories via URL, e.g.
                            <code>?categories[]=1&categories[]=2&cat_logic=and</code>
                        </div>
                    @endisset
                </div>

                <div class="col-12 col-md-3">
                    <select name="sort" class="form-select">
                        <option value="weighted" @selected(request('sort', 'weighted') === 'weighted')>Weighted Rating</option>
                        <option value="votes" @selected(request('sort') === 'votes')>Total Votes</option>
                        <option value="recent" @selected(request('sort') === 'recent')>Recent Popularity (30d)</option>
                        <option value="alpha" @selected(request('sort') === 'alpha')>Alphabetical</option>
                    </select>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Apply</button>
                    <a class="btn btn-outline-secondary" href="{{ route('books.index') }}"><i
                            class="bi bi-arrow-counterclockwise me-1"></i>Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mt-3">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Categories</th>
                        <th>ISBN</th>
                        <th>Pub. Year</th>
                        <th>Status</th>
                        <th>Location</th>
                        <th class="text-end">Avg</th>
                        <th class="text-end">Votes</th>
                        <th class="text-end">Weighted</th>
                        <th class="text-center">Trend</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($books as $b)
                        <tr>
                            <td class="fw-semibold">{{ $b->title }}</td>
                            <td>{{ $b->author->name }}</td>
                            <td>
                                @foreach ($b->categories as $c)
                                    <span class="badge text-bg-secondary">{{ $c->name }}</span>
                                @endforeach
                            </td>
                            <td>{{ $b->isbn ?: '—' }}</td>
                            <td>{{ $b->publication_year }}</td>
                            <td>
                                @php $badge = $b->status==='available'?'success':($b->status==='rented'?'warning':'secondary'); @endphp
                                <span class="badge text-bg-{{ $badge }}">{{ ucfirst($b->status) }}</span>
                            </td>
                            <td>{{ $b->store_location ?: '—' }}</td>
                            <td class="text-end">{{ number_format($b->ratings_avg, 2) }}</td>
                            <td class="text-end">{{ $b->ratings_count }}</td>
                            <td class="text-end">{{ number_format($b->weighted_score, 2) }}</td>
                            <td class="text-center">
                                @if ($trendingUp[$b->id] ?? false)
                                    <span class="text-success" title="Improved last 7 days"><i
                                            class="bi bi-arrow-up-right"></i></span>
                                @else
                                    <span class="text-muted"><i class="bi bi-dash-lg"></i></span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    @if ($books->isEmpty())
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">No results</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-center">
                {{ $books->onEachSide(1)->links('vendor.pagination.bootstrap-sm') }}
            </div>
        </div>
    </div>
@endsection
