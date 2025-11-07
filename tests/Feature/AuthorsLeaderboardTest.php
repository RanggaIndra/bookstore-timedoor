<?php

namespace Tests\Feature;

use App\Models\{Author, Book};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthorsLeaderboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_popularity_tab_orders_by_voters_over_5(): void
    {
        $a1 = Author::factory()->create(['name'=>'A1']);
        $a2 = Author::factory()->create(['name'=>'A2']);

        $b11 = Book::factory()->create(['author_id'=>$a1->id]);
        $b21 = Book::factory()->create(['author_id'=>$a2->id]);

        DB::table('book_ratings')->insert([
            ['book_id'=>$b11->id,'score'=>6,'rater_fingerprint'=>'f1','created_at'=>now(),'updated_at'=>now()],
            ['book_id'=>$b11->id,'score'=>7,'rater_fingerprint'=>'f2','created_at'=>now(),'updated_at'=>now()],
            ['book_id'=>$b21->id,'score'=>3,'rater_fingerprint'=>'g1','created_at'=>now(),'updated_at'=>now()],
        ]);

        $res = $this->get('/authors/top?tab=popularity');
        $res->assertOk();
        $html = $res->getContent();
        $firstPos = strpos($html, 'A1');
        $secondPos = strpos($html, 'A2');
        $this->assertTrue($firstPos !== false && $secondPos !== false && $firstPos < $secondPos);
    }

    public function test_average_tab_shows_avg_and_total(): void
    {
        $a = Author::factory()->create();
        $b = Book::factory()->create(['author_id'=>$a->id]);
        DB::table('book_ratings')->insert([
            ['book_id'=>$b->id,'score'=>8,'rater_fingerprint'=>'x','created_at'=>now(),'updated_at'=>now()],
            ['book_id'=>$b->id,'score'=>6,'rater_fingerprint'=>'y','created_at'=>now(),'updated_at'=>now()],
        ]);

        $res = $this->get('/authors/top?tab=average');
        $res->assertOk();
        $res->assertSee('Average Rating');
        $res->assertSee('Total Ratings');
    }

    public function test_trending_tab_calculates_trending_score(): void
    {
        $a = Author::factory()->create();
        $b = Book::factory()->create(['author_id'=>$a->id]);

        DB::table('book_ratings')->insert([
            ['book_id'=>$b->id,'score'=>4,'rater_fingerprint'=>'p1','created_at'=>now()->subDays(50),'updated_at'=>now()->subDays(50)],
            ['book_id'=>$b->id,'score'=>9,'rater_fingerprint'=>'p2','created_at'=>now()->subDays(5),'updated_at'=>now()->subDays(5)],
            ['book_id'=>$b->id,'score'=>8,'rater_fingerprint'=>'p3','created_at'=>now()->subDays(3),'updated_at'=>now()->subDays(3)],
        ]);

        $res = $this->get('/authors/top?tab=trending');
        $res->assertOk();
        $res->assertSee('Trending Score');
    }
}
