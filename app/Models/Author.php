<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Author extends Model
{
    use HasFactory;

    protected $fillable = ['uuid','name','country'];

    protected static function booted() {
        static::creating(function ($m) {
            $m->uuid ??= (string) Str::uuid();
        });
    }

    public function books() { return $this->hasMany(Book::class); }
}
