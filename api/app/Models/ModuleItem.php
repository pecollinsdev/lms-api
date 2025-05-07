<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Module;

class ModuleItem extends Model
{
    protected $fillable = [
        'module_id', 'type', 'title', 'description', 'due_date', 'order'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
} 