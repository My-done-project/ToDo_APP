<?php

namespace App\Console\Commands;

use App\Mail\TaskReminderMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTaskReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:send-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send task reminder emails to users with pending tasks.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::with(['tasks' => function ($query) {
            $query->where('status', '!=', 'Done')
                  ->whereNotNull('due_date')
                  ->whereDate('due_date', '<=', now()->addDay());
        }])->get();

        foreach ($users as $user) {
            if ($user->tasks->count()) {
                Mail::to($user->email)->send(new TaskReminderMail($user, $user->tasks));
            }
        }

        $this->info('Task reminders sent successfully.');
    }
}
