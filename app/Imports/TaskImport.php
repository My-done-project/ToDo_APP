<?php

namespace App\Imports;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;

class TaskImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Task([
            'user_id'  => Auth::id(),
            'title'    => $row[0],
            'notes'    => $row[1],
            'priority' => $row[2],
            'due_date' => $row[3],
            'status'   => $row[4],
        ]);
    }
}
