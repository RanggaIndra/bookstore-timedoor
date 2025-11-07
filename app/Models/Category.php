<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['uuid','name'];

    protected static function booted() {
        static::creating(function ($m) {
            $m->uuid ??= (string) Str::uuid();
        });
    }

    public function books() { return $this->belongsToMany(Book::class, 'book_category'); }
}
