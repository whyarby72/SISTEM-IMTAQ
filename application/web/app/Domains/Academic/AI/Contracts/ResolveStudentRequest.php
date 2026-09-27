<?php

namespace App\Domains\Academic\AI\Contracts;

final class ResolveStudentRequest
{
    public function __construct(
        public readonly string $query,
        public readonly ?string $classId = null,
    ) {}

    public static function fromArray(array $input): self
    {
        $data = AcademicAiInput::validated($input, [
            'query' => ['required', 'string', 'min:1', 'max:100'],
            'class_id' => ['nullable', 'uuid'],
        ]);

        return new self(trim($data['query']), $data['class_id'] ?? null);
    }
}
