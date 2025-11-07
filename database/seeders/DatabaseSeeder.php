<?php

namespace Database\Seeders;

use App\Models\{Author, Category, Book, BookRating};
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Author::factory()->count(1000)->create();
        Category::factory()->count(3000)->create();

        $books = Book::factory()->count(100000)->create();

        $books->each(function ($book) {
            $cats = \App\Models\Category::inRandomOrder()->limit(rand(2,4))->pluck('id');
            $book->categories()->attach($cats);
        });

        Book::chunk(5000, function ($books) {
            foreach ($books as $book) {
                BookRating::factory()->count(rand(2,10))->create(['book_id' => $book->id]);
                $book->ratings_count = $book->ratings()->count();
                $book->ratings_avg = $book->ratings()->avg('score') ?? 0;
                $book->save();
            }
        });
    }
}
