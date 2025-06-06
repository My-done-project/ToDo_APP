<?php

namespace App\Models;

use App\Models\User;
use App\Models\Subtask;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function subtasks(){
        return $this->hasMany(Subtask::class);
    }
}
