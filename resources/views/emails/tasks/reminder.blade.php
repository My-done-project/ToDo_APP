@component('mail::message')
# Hello {{ $user->name }}

Here’s your task reminder:

@foreach($tasks as $task)
- **{{ $task->title }}** → Due: {{ $task->due_date }}
@endforeach

Stay productive 💪

Thanks,
ToDoList App
@endcomponent
