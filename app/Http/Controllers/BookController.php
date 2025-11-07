<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookRating;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'q'            => $request->input('q'),
            'author_id'    => $request->input('author_id'),
            'year_from'    => $request->input('year_from'),
            'year_to'      => $request->input('year_to'),
            'status'       => $request->input('status'),
            'store'        => $request->input('store'),
            'rating_min'   => $request->input('rating_min'),
            'rating_max'   => $request->input('rating_max'),
            'category_ids' => $request->input('categories', []),
            'cat_logic'    => $request->input('cat_logic', 'or'),
        ];

        $sort = $request->input('sort', 'weighted');

        $books = Book::query()
            ->select([
                'books.id','books.title','books.author_id','books.isbn','books.publisher',
                'books.publication_year','books.status','books.store_location',
                'books.ratings_count','books.ratings_avg'
            ])
            ->with(['author:id,name', 'categories:id,name'])
            ->filter($filters)
            ->withWeightedScore()
            ->withRecentPopularity();

        if ($sort === 'votes') {
            $books->orderByDesc('books.ratings_count');
        } elseif ($sort === 'alpha') {
            $books->orderBy('books.title');
        } elseif ($sort === 'recent') {
            $books->orderByDesc('recent_votes_30')->orderByDesc('books.ratings_count');
        } else {
            $books->orderByDesc('weighted_score')->orderByDesc('books.ratings_count');
        }

        $books = $books->paginate(50)->appends($request->query());

        $ids = $books->pluck('id');
        $recent7 = Carbon::now()->subDays(7);
        $prev7   = Carbon::now()->subDays(14);

        $trendRows = BookRating::select([
                'book_id',
                DB::raw("AVG(CASE WHEN created_at >= '{$recent7}' THEN score END) AS avg_recent"),
                DB::raw("AVG(CASE WHEN created_at >= '{$prev7}' AND created_at < '{$recent7}' THEN score END) AS avg_prev")
            ])
            ->whereIn('book_id', $ids)
            ->groupBy('book_id')
            ->pluck('avg_recent', 'book_id')
            ->map(function ($avgRecent, $bookId) use ($ids, $recent7, $prev7) { return $avgRecent; });

        $prevRows = BookRating::select([
                'book_id',
                DB::raw("AVG(CASE WHEN created_at >= '{$prev7}' AND created_at < '{$recent7}' THEN score END) AS avg_prev")
            ])
            ->whereIn('book_id', $ids)
            ->groupBy('book_id')
            ->pluck('avg_prev', 'book_id');

        $trendingUp = [];
        foreach ($ids as $bid) {
            $avgRecent = (float)($trendRows[$bid] ?? 0);
            $avgPrev   = (float)($prevRows[$bid] ?? 0);
            $trendingUp[$bid] = $avgRecent > $avgPrev && $avgRecent > 0;
        }

        return view('books.index', compact('books','trendingUp'));
    }
}
