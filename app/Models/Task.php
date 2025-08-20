<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'user_id',
        'completed'  // add if you also want to update completed status
    ];

    protected $casts = [
        'completed' => 'boolean',
    ];

    // accessor so API can return completed instead of is_completed
    protected $appends = ['completed'];

    public function getCompletedAttribute()
    {
        return (bool) $this->attributes['completed'];
    }

    // Relationship with images
    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
