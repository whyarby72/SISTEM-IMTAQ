<?php

namespace App\Domains\Academic\AI\Tools;

use App\Domains\Academic\AI\AcademicAiAuthorization;
use App\Domains\Academic\AI\Contracts\AcademicAiTool;
use App\Domains\Academic\AI\Contracts\AcademicAiToolContext;
use App\Domains\Academic\AI\Contracts\AcademicAiToolResult;
use App\Domains\Academic\AI\Contracts\ResolveStudentRequest;
use App\Domains\Academic\AI\StudentIdentityResolver;

final class ResolveStudentTool implements AcademicAiTool
{
    public function __construct(private readonly AcademicAiAuthorization $authorization, private readonly StudentIdentityResolver $resolver) {}

    public function name(): string
    {
        return 'resolve_student';
    }

    public function description(): string
    {
        return 'Resolve a student search term to one authorized canonical student identity.';
    }

    public function inputSchema(): array
    {
        return ['query' => ['type' => 'string', 'required' => true], 'class_id' => ['type' => 'uuid', 'required' => false]];
    }

    public function execute(AcademicAiToolContext $context, array $input): AcademicAiToolResult
    {
        $this->authorization->requireReadAccess($context->user);
        $request = ResolveStudentRequest::fromArray($input);
        $resolved = $this->resolver->resolve($request);

        $result = AcademicAiToolResult::ok($this->name(), $context, [
            'filters' => ['query' => $request->query, 'class_id' => $request->classId],
            'entities' => ['student' => $resolved['student']],
            'records' => $resolved['candidates'],
            'warnings' => $resolved['status'] === 'AMBIGUOUS' ? [['code' => 'AMBIGUOUS_STUDENT']] : [],
            'canonical_entity_ids' => $resolved['student'] ? [$resolved['student']['student_id']] : [],
            'basis_count' => count($resolved['candidates']) ?: ($resolved['student'] ? 1 : 0),
        ]);

        return new AcademicAiToolResult(
            tool: $result->tool,
            status: $resolved['status'] === 'RESOLVED' ? 'OK' : $resolved['status'],
            filters: $result->filters,
            entities: $result->entities,
            metrics: $result->metrics,
            records: $result->records,
            warnings: $result->warnings,
            evidence: $result->evidence,
            dataAsOf: $result->dataAsOf,
        );
    }
}
