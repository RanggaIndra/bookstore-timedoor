<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid','author_id','title','isbn','publisher','publication_year',
        'status','store_location','ratings_count','ratings_avg'
    ];

    protected static function booted() {
        static::creating(function ($m) {
            $m->uuid ??= (string) Str::uuid();
        });
    }

    public function author() { return $this->belongsTo(Author::class); }
    public function categories() { return $this->belongsToMany(Category::class, 'book_category'); }
    public function ratings() { return $this->hasMany(BookRating::class); }

    public function scopeWithWeightedScore(Builder $q, int $m = 50): Builder {
        return $q->addSelect([
            DB::raw("
                (
                    (books.ratings_count/(books.ratings_count + $m)) * books.ratings_avg
                    +
                    ($m/(books.ratings_count + $m)) * (SELECT COALESCE(AVG(score),0) FROM book_ratings)
                ) as weighted_score
            ")
        ]);
    }

    public function scopeWithRecentPopularity(Builder $q): Builder {
        $since = now()->subDays(30)->toDateTimeString();
        return $q->addSelect([
            DB::raw("(SELECT COUNT(*) FROM book_ratings br WHERE br.book_id = books.id AND br.created_at >= '{$since}') as recent_votes_30")
        ]);
    }

    public function scopeFilter(Builder $q, array $f): Builder {
        $q->when($f['author_id'] ?? null, fn($qq,$v)=>$qq->where('author_id',$v));

        $q->when($f['year_from'] ?? null, fn($qq,$v)=>$qq->where('publication_year','>=',$v))
          ->when($f['year_to'] ?? null, fn($qq,$v)=>$qq->where('publication_year','<=',$v));

        $q->when($f['status'] ?? null, fn($qq,$v)=>$qq->whereIn('status',(array)$v));
        $q->when($f['store'] ?? null, fn($qq,$v)=>$qq->where('store_location','like',"%$v%"));

        $q->when($f['rating_min'] ?? null, fn($qq,$v)=>$qq->where('ratings_avg','>=',$v))
          ->when($f['rating_max'] ?? null, fn($qq,$v)=>$qq->where('ratings_avg','<=',$v));

        if (!empty($f['category_ids'])) {
            $ids = array_map('intval',(array)$f['category_ids']);
            if (($f['cat_logic'] ?? 'or') === 'and') {
                foreach ($ids as $cid) {
                    $q->whereExists(function($sub) use ($cid) {
                        $sub->from('book_category as bc')
                            ->whereColumn('bc.book_id','books.id')
                            ->where('bc.category_id',$cid);
                    });
                }
            } else {
                $q->whereExists(function($sub) use ($ids) {
                    $sub->from('book_category as bc')
                        ->whereColumn('bc.book_id','books.id')
                        ->whereIn('bc.category_id',$ids);
                });
            }
        }

        if ($s = ($f['q'] ?? null)) {
            $q->where(function($qq) use ($s) {
                $qq->where('title','like',"%$s%")
                   ->orWhere('isbn','like',"%$s%")
                   ->orWhere('publisher','like',"%$s%")
                   ->orWhereHas('author', fn($qa)=>$qa->where('name','like',"%$s%"));
            });
        }

        return $q;
    }
}
