<?php

namespace Tests\Feature;

use App\Models\{Author, Book, BookRating, RaterCooldown};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RatingStoreTest extends TestCase
{
    use RefreshDatabase;

    protected function fingerprint(): string
    {
        return sha1('127.0.0.1|testing-agent');
    }

    public function test_submit_rating_and_updates_book_aggregate(): void
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id'=>$author->id,'ratings_count'=>2,'ratings_avg'=>7.0]);

        $res = $this->post('/ratings', [
            'author_id' => $author->id,
            'book_id'   => $book->id,
            'score'     => 9,
        ], ['User-Agent'=>'testing-agent']);

        $res->assertRedirect('/books');
        $book->refresh();
        $this->assertEquals(3, $book->ratings_count);
        $this->assertEqualsWithDelta((7*2+9)/3, $book->ratings_avg, 0.0001);
        $this->assertDatabaseHas('book_ratings', ['book_id'=>$book->id, 'score'=>9]);
        $this->assertDatabaseHas('rater_cooldowns', ['rater_fingerprint'=>$this->fingerprint()]);
    }

    public function test_invalid_book_author_combination_fails(): void
    {
        $a1 = Author::factory()->create();
        $a2 = Author::factory()->create();
        $b  = Book::factory()->create(['author_id'=>$a1->id]);

        $res = $this->from('/ratings/create')->post('/ratings', [
            'author_id' => $a2->id,
            'book_id'   => $b->id,
            'score'     => 5,
        ], ['User-Agent'=>'testing-agent']);

        $res->assertSessionHasErrors('book_id');
    }

    public function test_24h_cooldown_is_enforced(): void
    {
        $a = Author::factory()->create();
        $b = Book::factory()->create(['author_id'=>$a->id]);

        RaterCooldown::create([
            'rater_fingerprint' => $this->fingerprint(),
            'last_rating_at' => now()->subHours(2),
        ]);

        $res = $this->from('/ratings/create')->post('/ratings', [
            'author_id' => $a->id,
            'book_id'   => $b->id,
            'score'     => 7,
        ], ['User-Agent'=>'testing-agent']);

        $res->assertStatus(302);
        $res->assertSessionHasErrors(); // thrown ValidationException or abort handled
        $this->assertDatabaseCount('book_ratings', 0);
    }

    public function test_ajax_endpoints_are_fast_and_filtered(): void
    {
        $a1 = Author::factory()->create(['name'=>'Alice']);
        $a2 = Author::factory()->create(['name'=>'Bob']);

        $b1 = Book::factory()->create(['author_id'=>$a1->id, 'title'=>'Alpha Book']);
        $b2 = Book::factory()->create(['author_id'=>$a1->id, 'title'=>'Beta Book']);
        $b3 = Book::factory()->create(['author_id'=>$a2->id, 'title'=>'Gamma Book']);

        $ra = $this->getJson('/ajax/authors?q=Al');
        $ra->assertOk()->assertJsonFragment(['id'=>$a1->id,'name'=>'Alice']);

        $rb = $this->getJson('/ajax/books?author_id='.$a1->id.'&q=Beta');
        $rb->assertOk()->assertJsonMissing(['id'=>$b1->id])->assertJsonFragment(['id'=>$b2->id,'title'=>'Beta Book']);
    }
}
