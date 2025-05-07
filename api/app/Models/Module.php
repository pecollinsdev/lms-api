<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Course;
use App\Models\ModuleItem;

class Module extends Model
{
    protected $fillable = [
        'course_id', 'title', 'description', 'start_date', 'end_date'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function items()
    {
        return $this->hasMany(ModuleItem::class);
    }
} 