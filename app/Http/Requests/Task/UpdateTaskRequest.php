<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'     => 'sometimes|required|string|max:255',
            'notes'     => 'nullable|string',
            'priority'  => 'nullable|in:Low,Medium,High,Urgent',
            'due_date'  => 'nullable|date|after_or_equal:today',
            'attachment'=> 'nullable|file|mimes:jpg,png,pdf,docx,txt|max:2048',
        ];
    }
}
