<?php

namespace App\Domains\Academic\AI\Providers;

use App\Domains\Academic\AI\Contracts\AcademicAiModelProvider;
use App\Domains\Academic\AI\Contracts\AcademicAiProviderRequest;
use App\Domains\Academic\AI\Contracts\AcademicAiProviderResponse;
use App\Domains\Academic\AI\Services\AiProviderConfigurationResolver;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class OpenAiResponsesProvider implements AcademicAiModelProvider
{
    public function name(): string
    {
        return 'openai';
    }

    public function respond(AcademicAiProviderRequest $request): AcademicAiProviderResponse
    {
        $runtime = app(AiProviderConfigurationResolver::class)->resolve();
        if ($runtime === null) {
            throw new RuntimeException('OpenAI provider belum dikonfigurasi.');
        }
        $apiKey = (string) $runtime['credential'];

        $payload = [
            'model' => $runtime['model'],
            'instructions' => $request->instructions,
            'input' => $request->input === [] ? $request->question : $request->input,
            'tools' => $request->tools,
            'tool_choice' => 'auto',
            'parallel_tool_calls' => false,
            'store' => false,
            'max_output_tokens' => (int) $runtime['max_output_tokens'],
        ];
        if ($request->previousResponseId !== null) {
            $payload['previous_response_id'] = $request->previousResponseId;
        }

        /** @var PendingRequest $client */
        $client = Http::withToken($apiKey)
            ->withHeaders(['X-Client-Request-Id' => $request->correlationId])
            ->acceptJson()
            ->timeout((int) config('services.openai.timeout_seconds', 30));
        $response = $client->post(rtrim((string) config('services.openai.base_url'), '/').'/v1/responses', $payload);

        if ($response->failed()) {
            throw new RuntimeException('OpenAI provider request gagal dengan status '.$response->status().'.');
        }

        $body = $response->json();
        if (! is_array($body) || ! isset($body['id'], $body['output'])) {
            throw new RuntimeException('OpenAI provider response tidak valid.');
        }

        $items = [];
        foreach ((array) $body['output'] as $item) {
            if (($item['type'] ?? null) === 'function_call') {
                $items[] = [
                    'type' => 'function_call',
                    'name' => $item['name'] ?? null,
                    'arguments' => $item['arguments'] ?? null,
                    'call_id' => $item['call_id'] ?? null,
                ];

                continue;
            }
            if (($item['type'] ?? null) === 'message') {
                foreach ((array) ($item['content'] ?? []) as $content) {
                    if (($content['type'] ?? null) === 'output_text') {
                        $items[] = ['type' => 'text', 'text' => $content['text'] ?? ''];
                    }
                }
            }
        }

        return new AcademicAiProviderResponse(
            responseId: (string) $body['id'],
            providerRequestId: $response->header('x-request-id'),
            model: (string) ($body['model'] ?? $runtime['model']),
            items: $items,
            usage: is_array($body['usage'] ?? null) ? $body['usage'] : [],
        );
    }
}
