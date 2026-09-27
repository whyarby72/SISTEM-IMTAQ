<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Academic\AI\Exceptions\AiProviderAdminOperationException;
use App\Domains\Academic\AI\Exceptions\AiProviderDiscoveryException;
use App\Domains\Academic\AI\Exceptions\AiProviderVerificationException;
use App\Domains\Academic\AI\Models\AiProviderActiveConfiguration;
use App\Domains\Academic\AI\Models\AiProviderConfiguration;
use App\Domains\Academic\AI\Models\AiProviderCredential;
use App\Domains\Academic\AI\Services\AiProviderConfigurationService;
use App\Domains\Academic\Services\AcademicAuthorizationService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class AiProviderConfigurationController
{
    public function __construct(private readonly AiProviderConfigurationService $service) {}

    public function index(Request $request): View
    {
        $this->authorizeSuperAdmin($request);

        return view('admin.system.ai-provider.index', [
            'credentials' => AiProviderCredential::query()->where('provider', 'openai')->latest()->get(),
            'configurations' => AiProviderConfiguration::query()->with('credential')->where('provider', 'openai')->latest()->get(),
            'active' => AiProviderActiveConfiguration::query()->with('configuration.credential')->where('provider', 'openai')->first(),
            'publicAiEnabled' => (bool) config('academic.ai.assistant_enabled'),
        ]);
    }

    public function storeCredential(Request $request): RedirectResponse
    {
        $actor = $this->authorizeSuperAdmin($request);
        $validator = Validator::make($request->all(), ['label' => ['required', 'string', 'max:120'], 'secret' => ['required', 'string', 'max:500']]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->except('secret'));
        }
        $payload = $validator->validated();
        $this->service->createCredential($actor, $payload['label'], $payload['secret']);

        return back()->with('status', 'Credential tersimpan sebagai PENDING. Lanjutkan Verify.');
    }

    public function verifyCredential(Request $request, AiProviderCredential $credential): RedirectResponse
    {
        $actor = $this->authorizeSuperAdmin($request);
        try {
            $this->service->verifyCredential($actor, $credential);
        } catch (AiProviderAdminOperationException $exception) {
            return back()->withErrors(['credential' => 'Verifikasi credential gagal: '.$exception->category.'.']);
        }

        return back()->with('status', 'Credential berhasil diverifikasi.');
    }

    public function discoverModels(Request $request, AiProviderCredential $credential): JsonResponse
    {
        $actor = $this->authorizeSuperAdmin($request);

        try {
            return response()->json(['models' => $this->service->discoverModels($credential, $actor)]);
        } catch (AiProviderDiscoveryException $exception) {
            return response()->json(['status' => 'ERROR', 'error' => $exception->category], 422);
        }
    }

    public function storeConfiguration(Request $request): RedirectResponse
    {
        $actor = $this->authorizeSuperAdmin($request);
        $payload = $request->validate(['credential_id' => ['required', 'uuid'], 'model' => ['required', 'string', 'max:160'], 'max_output_tokens' => ['required', 'integer', 'min:128', 'max:4000']]);
        $credential = AiProviderCredential::query()->findOrFail($payload['credential_id']);
        try {
            $this->service->createConfiguration($actor, $credential, $payload['model'], (int) $payload['max_output_tokens']);
        } catch (AiProviderDiscoveryException $exception) {
            return back()->withErrors(['model' => 'Discovery model gagal: '.$exception->category.'.']);
        }

        return back()->with('status', 'Configuration version dibuat sebagai DRAFT.');
    }

    public function verifyConfiguration(Request $request, AiProviderConfiguration $configuration): RedirectResponse
    {
        $actor = $this->authorizeSuperAdmin($request);
        try {
            $this->service->verifyConfiguration($actor, $configuration);
        } catch (AiProviderVerificationException $exception) {
            return back()->withErrors(['configuration' => 'Verifikasi gagal: '.$exception->category.'.']);
        }

        return back()->with('status', 'Configuration berhasil diverifikasi dan siap diaktifkan.');
    }

    public function activate(Request $request, AiProviderConfiguration $configuration): RedirectResponse
    {
        $actor = $this->authorizeSuperAdmin($request);
        try {
            $this->service->activate($actor, $configuration);
        } catch (AiProviderAdminOperationException $exception) {
            return back()->withErrors(['configuration' => 'Aktivasi configuration gagal: '.$exception->category.'.']);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('status', 'Configuration aktif diperbarui secara transaksional.');
    }

    public function revoke(Request $request, AiProviderCredential $credential): RedirectResponse
    {
        $actor = $this->authorizeSuperAdmin($request);
        $this->service->revoke($actor, $credential);

        return back()->with('status', 'Credential direvoke.');
    }

    public function toggleRuntime(Request $request): RedirectResponse
    {
        $actor = $this->authorizeSuperAdmin($request);
        try {
            $this->service->setRuntimeEnabled($actor, $request->boolean('enabled'));
        } catch (AiProviderAdminOperationException $exception) {
            return back()->withErrors(['runtime' => 'Perubahan runtime gagal: '.$exception->category.'.']);
        }

        return back()->with('status', 'Runtime provider diperbarui. Public AI tetap OFF.');
    }

    private function authorizeSuperAdmin(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $authorization = app(AcademicAuthorizationService::class);
        if (! $authorization->hasEffectiveRole($user, 'SUPER_ADMIN', Carbon::now())
            || ! $authorization->hasInstitutionWideAuthority($user, Carbon::now())) {
            throw new AuthorizationException('Only Super Admin may manage AI provider configuration.');
        }

        return $user;
    }
}
