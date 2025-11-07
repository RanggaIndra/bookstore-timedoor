<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookRating extends Model
{
    use HasFactory;

    protected $fillable = ['book_id','score','rater_fingerprint'];

    public function book() { return $this->belongsTo(Book::class); }
}
