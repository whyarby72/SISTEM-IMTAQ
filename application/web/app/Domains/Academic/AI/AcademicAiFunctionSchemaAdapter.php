<?php

namespace App\Domains\Academic\AI;

use App\Domains\Academic\AI\Contracts\AcademicAiTool;

final class AcademicAiFunctionSchemaAdapter
{
    public function schema(AcademicAiTool $tool): array
    {
        $properties = [];
        $required = [];

        foreach ($tool->inputSchema() as $name => $definition) {
            $property = ['type' => $this->jsonType($definition['type'] ?? 'string', ($definition['required'] ?? false) === false)];
            if (($definition['type'] ?? null) === 'uuid') {
                $property['format'] = 'uuid';
            }
            if (($definition['type'] ?? null) === 'date') {
                $property['format'] = 'date';
            }
            if (isset($definition['values'])) {
                $property['enum'] = array_values($definition['values']);
                if (($definition['required'] ?? false) === false) {
                    $property['enum'][] = null;
                }
            }
            if (isset($definition['max'])) {
                $property['maximum'] = (int) $definition['max'];
            }
            $properties[$name] = $property;
            $required[] = $name;
        }

        return [
            'type' => 'function',
            'name' => $tool->name(),
            'description' => $tool->description(),
            'parameters' => [
                'type' => 'object',
                'properties' => $properties,
                'required' => $required,
                'additionalProperties' => false,
            ],
            'strict' => true,
        ];
    }

    private function jsonType(string $type, bool $nullable): string|array
    {
        $jsonType = match ($type) {
            'integer' => 'integer',
            'boolean' => 'boolean',
            default => 'string',
        };

        return $nullable ? [$jsonType, 'null'] : $jsonType;
    }
}
