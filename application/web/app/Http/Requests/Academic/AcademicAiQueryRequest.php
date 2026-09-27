<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;

final class AcademicAiQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:'.(int) config('academic.ai.question_max_length', 4000)],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('question')) {
            $this->merge(['question' => trim((string) $this->input('question'))]);
        }
    }

    public function withValidator($validator): void
    {
        $unknown = array_diff(array_keys($this->all()), ['question']);
        if ($unknown !== []) {
            $validator->after(function ($validator) use ($unknown): void {
                $validator->errors()->add('request', 'Field AI tidak diizinkan: '.implode(', ', $unknown));
            });
        }
    }
}
