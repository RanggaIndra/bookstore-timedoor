<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaterCooldown extends Model
{
    protected $primaryKey = 'rater_fingerprint';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['rater_fingerprint', 'last_rating_at'];
}
