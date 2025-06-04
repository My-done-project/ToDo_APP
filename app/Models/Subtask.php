<?php

namespace App\Models;

use App\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subtask extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function task(){
        return $this->belongsTo(Task::class);
    }
}
