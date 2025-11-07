<?php

namespace Tests\Feature;

use App\Models\{Author, Book, Category};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BooksIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Pagination\Paginator::useBootstrapFive();
    }

    public function test_paginates_50_per_page_and_renders(): void
    {
        $author = Author::factory()->create();
        Book::factory()->count(120)->create(['author_id'=>$author->id]);

        $res = $this->get('/books');
        $res->assertOk();
        $res->assertSee('pagination');
        $this->assertEquals(50, $res->original->getData()['books']->perPage());
    }

    public function test_filters_by_author_year_and_rating_range(): void
    {
        $a1 = Author::factory()->create();
        $a2 = Author::factory()->create();

        $b1 = Book::factory()->create(['author_id'=>$a1->id,'publication_year'=>2010,'ratings_count'=>3,'ratings_avg'=>8]);
        $b2 = Book::factory()->create(['author_id'=>$a1->id,'publication_year'=>2001,'ratings_count'=>3,'ratings_avg'=>4]);
        $b3 = Book::factory()->create(['author_id'=>$a2->id,'publication_year'=>2015,'ratings_count'=>3,'ratings_avg'=>9]);

        $res = $this->get('/books?author_id='.$a1->id.'&year_from=2005&year_to=2020&rating_min=7&rating_max=10');
        $res->assertOk();
        $titles = $res->original->getData()['books']->pluck('title')->all();
        $this->assertContains($b1->title, $titles);
        $this->assertNotContains($b2->title, $titles);
        $this->assertNotContains($b3->title, $titles);
    }

    public function test_sort_by_recent_popularity_uses_recent_votes(): void
    {
        $a = Author::factory()->create();
        $hot = Book::factory()->create(['author_id'=>$a->id, 'title'=>'Hot']);
        $cold = Book::factory()->create(['author_id'=>$a->id, 'title'=>'Cold']);

        DB::table('book_ratings')->insert([
            ['book_id'=>$hot->id,'score'=>5,'rater_fingerprint'=>'x1','created_at'=>now()->subDays(2),'updated_at'=>now()->subDays(2)],
            ['book_id'=>$hot->id,'score'=>6,'rater_fingerprint'=>'x2','created_at'=>now()->subDays(1),'updated_at'=>now()->subDays(1)],
            ['book_id'=>$cold->id,'score'=>10,'rater_fingerprint'=>'y1','created_at'=>now()->subDays(70),'updated_at'=>now()->subDays(70)],
        ]);
        $hot->update(['ratings_count'=>2,'ratings_avg'=>5.5]);
        $cold->update(['ratings_count'=>1,'ratings_avg'=>10]);

        $res = $this->get('/books?sort=recent');
        $res->assertOk();
        $books = $res->original->getData()['books']->items();
        $this->assertEquals('Hot', $books[0]->title);
    }
}
