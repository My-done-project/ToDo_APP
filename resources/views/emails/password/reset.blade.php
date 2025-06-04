@component('mail::message')
# Reset Password Request

Hello,

We received a request to reset your password.
Click the button below to set a new password.

@component('mail::button', ['url' => $url])
Reset Password
@endcomponent

This link will expire in 60 minutes.

If you didn’t request this, no action is needed.

Thanks,<br>
**ToDoList Team**
@endcomponent
