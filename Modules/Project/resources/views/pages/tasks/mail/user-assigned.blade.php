<x-mail::message>
# New Task Assignment

Hi {{ $user->fullname }},

You have been assigned a new task: **{{ $task->title }}**.

You can view the task details by clicking the button below:

<x-mail::button :url="$url">
View Task
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
