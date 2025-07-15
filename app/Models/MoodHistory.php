<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoodHistory extends Model
{
    protected $fillable = [
        'user_id',
        'text',
        'mood',
        'polarity',
        'subjectivity'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'polarity' => 'float',
        'subjectivity' => 'float'
    ];
}
