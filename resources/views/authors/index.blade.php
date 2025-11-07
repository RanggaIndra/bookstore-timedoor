@extends('layouts.app')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Top 20 Authors</h5>
        </div>
        <div class="card-body">
            <ul class="nav nav-pills mb-3">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'popularity' ? 'active' : '' }}"
                        href="{{ route('authors.top', ['tab' => 'popularity']) }}">
                        <i class="bi bi-graph-up-arrow me-1"></i>Popularity
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'average' ? 'active' : '' }}"
                        href="{{ route('authors.top', ['tab' => 'average']) }}">
                        <i class="bi bi-star-fill me-1"></i>Average Rating
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'trending' ? 'active' : '' }}"
                        href="{{ route('authors.top', ['tab' => 'trending']) }}">
                        <i class="bi bi-lightning-charge-fill me-1"></i>Trending
                    </a>
                </li>
            </ul>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Author</th>
                            @if ($tab === 'popularity')
                                <th class="text-end">Voters (score > 5)</th>
                            @elseif ($tab === 'average')
                                <th class="text-end">Average Score</th>
                                <th class="text-end">Total Ratings</th>
                            @else
                                <th class="text-end">Trending Score</th>
                                <th class="text-end">Voters (30d)</th>
                            @endif
                            <th>Best-rated Book</th>
                            <th>Worst-rated Book</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $i => $r)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $r->name }}</td>

                                @if ($tab === 'popularity')
                                    <td class="text-end">{{ $r->voters_pop ?? 0 }}</td>
                                @elseif ($tab === 'average')
                                    <td class="text-end">{{ number_format($r->avg_score ?? 0, 2) }}</td>
                                    <td class="text-end">{{ $r->ratings_total ?? 0 }}</td>
                                @else
                                    <td class="text-end">{{ number_format($r->trending_score ?? 0, 3) }}</td>
                                    <td class="text-end">{{ $r->voters_30 ?? 0 }}</td>
                                @endif

                                <td>
                                    @if (!empty($r->best_book))
                                        <div class="small">
                                            <i class="bi bi-trophy-fill text-warning me-1"></i>{{ $r->best_book['title'] }}
                                            <span
                                                class="badge text-bg-success ms-1">{{ number_format($r->best_book['avg'], 2) }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if (!empty($r->worst_book))
                                        <div class="small">
                                            <i
                                                class="bi bi-emoji-frown-fill text-danger me-1"></i>{{ $r->worst_book['title'] }}
                                            <span
                                                class="badge text-bg-secondary ms-1">{{ number_format($r->worst_book['avg'], 2) }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @if ($rows->isEmpty())
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No data</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
