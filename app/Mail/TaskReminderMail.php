<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaskReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $tasks;

    public function __construct($user, $tasks)
    {
        $this->user = $user;
        $this->tasks = $tasks;
    }

    public function build()
    {
        return $this->subject('Task Reminder - ToDoList App')
                    ->markdown('emails.tasks.reminder');
    }
}
