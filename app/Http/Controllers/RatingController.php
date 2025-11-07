<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRatingRequest;
use App\Models\{Author, Book, BookRating, RaterCooldown};
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RatingController extends Controller
{
    public function create()
    {
        return view('ratings.create');
    }

    public function store(StoreRatingRequest $request): RedirectResponse
    {
        $finger = $request->fingerprint();
        $bookId = (int)$request->book_id;
        $score  = (int)$request->score;

        DB::transaction(function () use ($finger, $bookId, $score) {
            $cooldown = RaterCooldown::lockForUpdate()->find($finger);
            $now = now();

            if ($cooldown && $now->diffInHours($cooldown->last_rating_at) < 24) {
                abort(429, 'Anda sudah memberi rating dalam 24 jam terakhir.');
            }

            $book = Book::lockForUpdate()->findOrFail($bookId);

            BookRating::create([
                'book_id' => $bookId,
                'score'   => $score,
                'rater_fingerprint' => $finger,
            ]);

            $newCount = $book->ratings_count + 1;
            $newAvg   = (($book->ratings_avg * $book->ratings_count) + $score) / $newCount;

            $book->update([
                'ratings_count' => $newCount,
                'ratings_avg'   => $newAvg,
            ]);

            RaterCooldown::updateOrCreate(
                ['rater_fingerprint' => $finger],
                ['last_rating_at' => $now]
            );
        });

        return redirect()->route('books.index')->with('ok', 'Terima kasih! Rating tersimpan.');
    }

    public function ajaxAuthors(Request $r)
    {
        $q = trim((string)$r->query('q', ''));
        $items = Author::query()
            ->when($q, fn($qq) => $qq->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->limit(20)
            ->get(['id','name']);

        return response()->json($items);
    }

    public function ajaxBooks(Request $r)
    {
        $authorId = (int)$r->query('author_id', 0);
        if ($authorId <= 0) return response()->json([]);

        $q = trim((string)$r->query('q', ''));
        $items = Book::query()
            ->where('author_id', $authorId)
            ->when($q, fn($qq) => $qq->where('title', 'like', "%{$q}%"))
            ->orderBy('title')
            ->limit(30)
            ->get(['id','title','author_id']);

        return response()->json($items);
    }
}
