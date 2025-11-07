<?php

namespace App\Http\Controllers;

use App\Models\{Author, Book};
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AuthorController extends Controller
{
    public function leaderboard(Request $request)
    {
        $tab = $request->input('tab', 'popularity');

        if ($tab === 'average') {
            $rows = Author::query()
                ->leftJoin('books', 'books.author_id', '=', 'authors.id')
                ->leftJoin('book_ratings', 'book_ratings.book_id', '=', 'books.id')
                ->groupBy('authors.id','authors.name')
                ->select(
                    'authors.id','authors.name',
                    DB::raw('COALESCE(AVG(book_ratings.score),0) as avg_score'),
                    DB::raw('COUNT(book_ratings.id) as ratings_total')
                )
                ->orderByDesc('avg_score')
                ->limit(20)
                ->get()
                ->map(fn($r) => [
                    'id' => (int)$r->id,
                    'name' => $r->name,
                    'avg_score' => (float)$r->avg_score,
                    'ratings_total' => (int)$r->ratings_total,
                ])
                ->toArray();

        } elseif ($tab === 'trending') {
            $recentFrom = Carbon::now()->subDays(30)->toDateTimeString();
            $prevFrom   = Carbon::now()->subDays(60)->toDateTimeString();

            $rows = Author::query()
                ->leftJoin('books', 'books.author_id', '=', 'authors.id')
                ->leftJoin('book_ratings as br', 'br.book_id', '=', 'books.id')
                ->where('br.created_at', '>=', $prevFrom)
                ->groupBy('authors.id','authors.name')
                ->select(
                    'authors.id','authors.name',
                    DB::raw("COALESCE(AVG(CASE WHEN br.created_at >= '{$recentFrom}' THEN br.score END),0) as avg_recent"),
                    DB::raw("COALESCE(AVG(CASE WHEN br.created_at >= '{$prevFrom}' AND br.created_at < '{$recentFrom}' THEN br.score END),0) as avg_prev"),
                    DB::raw("COUNT(CASE WHEN br.created_at >= '{$recentFrom}' THEN 1 END) as voters_30")
                )
                ->get()
                ->map(function ($r) {
                    $avgRecent = (float)$r->avg_recent;
                    $avgPrev   = (float)$r->avg_prev;
                    $voters30  = (int)$r->voters_30;
                    $trend     = ($avgRecent - $avgPrev) * max($voters30, 1);
                    return [
                        'id' => (int)$r->id,
                        'name' => $r->name,
                        'trending_score' => round($trend, 3),
                        'voters_30' => $voters30,
                    ];
                })
                ->sortByDesc('trending_score')
                ->take(20)
                ->values()
                ->toArray();

        } else {
            $rows = Author::query()
                ->leftJoin('books', 'books.author_id', '=', 'authors.id')
                ->leftJoin('book_ratings', 'book_ratings.book_id', '=', 'books.id')
                ->groupBy('authors.id','authors.name')
                ->select(
                    'authors.id','authors.name',
                    DB::raw('COUNT(CASE WHEN book_ratings.score > 5 THEN 1 END) as voters_pop'),
                    DB::raw('COUNT(book_ratings.id) as ratings_total')
                )
                ->orderByDesc('voters_pop')
                ->limit(20)
                ->get()
                ->map(fn($r) => [
                    'id' => (int)$r->id,
                    'name' => $r->name,
                    'voters_pop' => (int)$r->voters_pop,
                    'ratings_total' => (int)$r->ratings_total,
                ])
                ->toArray();
        }

        $authorIds = collect($rows)->pluck('id')->all();

        if (!empty($authorIds)) {
            $bookAverages = Book::query()
                ->select('books.id','books.author_id','books.title', DB::raw('AVG(br.score) as avg_score'))
                ->join('book_ratings as br','br.book_id','=','books.id')
                ->whereIn('books.author_id', $authorIds)
                ->groupBy('books.id','books.author_id','books.title')
                ->get()
                ->groupBy('author_id');

            $rows = collect($rows)->map(function ($r) use ($bookAverages) {
                $perAuthor = $bookAverages->get($r['id']) ?? collect();
                if ($perAuthor->isNotEmpty()) {
                    $best  = $perAuthor->sortByDesc('avg_score')->first();
                    $worst = $perAuthor->sortBy('avg_score')->first();
                    $r['best_book']  = ['title' => $best->title,  'avg' => (float)$best->avg_score];
                    $r['worst_book'] = ['title' => $worst->title, 'avg' => (float)$worst->avg_score];
                }
                return $r;
            })->toArray();
        }

        return view('authors.index', [
            'rows' => collect($rows)->map(fn($r) => (object)$r),
            'tab'  => $tab,
        ]);
    }
}
