<?php

namespace App\Http\Controllers\Academic;

use App\Domains\Academic\AI\AcademicAiAuthorization;
use App\Domains\Academic\AI\AcademicAiOrchestrator;
use App\Domains\Academic\AI\Contracts\AcademicAiToolContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\AcademicAiQueryRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

final class AcademicAiController extends Controller
{
    public function __construct(
        private readonly AcademicAiAuthorization $authorization,
        private readonly AcademicAiOrchestrator $orchestrator,
    ) {}

    public function query(AcademicAiQueryRequest $request): JsonResponse
    {
        $user = $request->user();
        $requestId = (string) Str::uuid();

        if (! config('academic.ai.assistant_enabled', false)) {
            return $this->response('UNAVAILABLE', $requestId, 'Asisten AI belum diaktifkan untuk pilot.', [], 503);
        }

        try {
            $this->authorization->requireReadAccess($user);
            $runtime = $this->orchestrator->ask(new AcademicAiToolContext($user, $requestId), $request->validated('question'));
        } catch (AuthorizationException) {
            return $this->response('ERROR', $requestId, 'Anda tidak memiliki akses ke Asisten AI Akademik.', [], 403);
        }

        if ($runtime->status !== 'COMPLETED') {
            return $this->response('ERROR', $requestId, 'Asisten AI sedang tidak tersedia. Silakan coba lagi nanti.', $runtime->warnings, 503);
        }

        return $this->response('OK', $requestId, $runtime->text, $runtime->warnings, 200);
    }

    private function response(string $status, string $requestId, ?string $answer, array $warnings, int $httpStatus): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'request_id' => $requestId,
            'answer' => $answer,
            'warnings' => $warnings,
        ], $httpStatus)->header('Cache-Control', 'no-store');
    }
}
