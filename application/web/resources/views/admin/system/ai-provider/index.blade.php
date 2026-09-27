<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengaturan AI</title>
    <style>
        @include('academic.partials.sidebar-styles')
        :root{--ai-green:#176b4d;--ai-ink:#173d33;--ai-muted:#5b756c;--ai-line:#cfe4d9;--ai-soft:#edf8f1;--ai-surface:#fff}
        body{background:#f3f8f5;color:var(--ai-ink)}.ai-page{display:grid;gap:1rem;max-width:58rem}.card{padding:clamp(1.15rem,2.8vw,1.8rem);background:var(--ai-surface);border:1px solid var(--ai-line);border-radius:1rem;box-shadow:0 10px 28px rgba(23,61,51,.05)}
        .eyebrow{margin:0 0 .35rem;color:var(--ai-green);font-size:.75rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}h1,h2,h3,p{margin-top:0}h1{margin-bottom:.35rem}h2{margin-bottom:.45rem}h3{margin-bottom:.35rem}.muted{color:var(--ai-muted)}.small{font-size:.9rem}
        .provider-head,.section-heading,.status-line,.actions{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap}.provider-head{align-items:flex-start}.provider-description{max-width:42rem;margin-bottom:0}.status-badge,.badge{display:inline-flex;align-items:center;gap:.35rem;padding:.32rem .7rem;border:1px solid #b9dac7;border-radius:999px;background:var(--ai-soft);color:var(--ai-green);font-size:.82rem;font-weight:800}.status-badge{white-space:nowrap}
        .settings-list{display:grid;margin:1.5rem 0 0;border-top:1px solid var(--ai-line)}.setting-row{display:grid;grid-template-columns:10rem minmax(0,1fr);gap:1rem;padding:1rem 0;border-bottom:1px solid var(--ai-line)}.setting-label{color:var(--ai-muted);font-size:.9rem;font-weight:700}.setting-value{min-width:0;overflow-wrap:anywhere;font-weight:750}.setting-value .subvalue{display:block;margin-top:.2rem;font-size:.88rem;font-weight:600}.public-row{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-top:1rem;padding:1rem 0 0;border-top:1px solid var(--ai-line)}.public-row strong{display:block;margin-bottom:.2rem}.empty-copy{max-width:38rem;margin:1.1rem 0 0}.empty-copy p:last-child{margin-bottom:0}
        .button,.button-link{display:inline-flex;align-items:center;justify-content:center;min-height:2.7rem;padding:.62rem 1rem;border:1px solid transparent;border-radius:.65rem;background:var(--ai-green);color:#fff;font:inherit;font-weight:800;text-decoration:none;cursor:pointer}.button.secondary,.button-link.secondary{background:var(--ai-soft);color:var(--ai-green);border-color:#b9dac7}.button.danger{color:#a23b43}.button[disabled]{cursor:wait;opacity:.68}.actions{justify-content:flex-start}.actions.end{justify-content:flex-end}.stack{display:grid;gap:.8rem}.field{display:grid;gap:.35rem;margin:.8rem 0}.field label{font-weight:750}.field input,.field select{width:100%;box-sizing:border-box;min-height:2.8rem;padding:.7rem .8rem;border:1px solid #b9dac7;border-radius:.6rem;background:#fff;color:inherit;font:inherit}.field select:disabled{background:#f1f5f3;color:#6b7d76}.helper{margin:.25rem 0 0}.notice{margin:0}.error-list{margin:0;padding:.75rem 1rem;border:1px solid #e7b6bd;border-radius:.7rem;color:#8e3039;background:#fff5f6}
        .edit-panel{margin-top:1.4rem;padding-top:1.3rem;border-top:1px solid var(--ai-line)}.edit-panel[hidden],.credential-form[hidden]{display:none}.advanced{overflow:hidden}.advanced>summary{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:0;color:var(--ai-ink);font-size:1.05rem;font-weight:800;cursor:pointer;list-style:none}.advanced>summary::-webkit-details-marker{display:none}.advanced>summary:after{content:'+';color:var(--ai-green);font-size:1.5rem;line-height:1}.advanced[open]>summary:after{content:'−'}.advanced-content{display:grid;gap:1.4rem;margin-top:1.2rem;padding-top:1.2rem;border-top:1px solid var(--ai-line)}.history-list{display:grid;gap:.7rem;margin-top:1rem}.history-item{display:grid;gap:.55rem;padding:.9rem;border:1px solid var(--ai-line);border-radius:.75rem;background:#fbfefc}hr{border:0;border-top:1px solid var(--ai-line);margin:1.25rem 0}
        @media(max-width:650px){.setting-row{grid-template-columns:1fr;gap:.3rem}.provider-head .status-badge{width:100%;justify-content:center}.button,.button-link{width:100%}.actions.end{justify-content:stretch}.public-row{align-items:flex-start;flex-direction:column}.history-item .actions{display:grid;grid-template-columns:1fr 1fr}.history-item .actions .button{width:100%}}@media(max-width:430px){.history-item .actions{grid-template-columns:1fr}}
    </style>
</head>
<body>
@php
    $publicAiEnabled = (bool) config('academic.ai.assistant_enabled');
    $activeConfiguration = $active?->configuration;
    $verifiedConfiguration = $configurations->first(fn ($configuration) => $configuration->status === 'VERIFIED');
    $currentConfiguration = $activeConfiguration ?? $verifiedConfiguration;
    $currentCredential = $currentConfiguration?->credential ?? $credentials->first(fn ($credential) => in_array($credential->status, ['VERIFIED', 'STANDBY'], true));
    $statusLabel = fn (string $status): string => match ($status) {
        'DRAFT' => 'Belum diuji', 'VERIFIED' => 'Siap digunakan', 'ACTIVE' => 'Sedang digunakan', 'STANDBY' => 'Siap digunakan', 'PENDING' => 'Perlu diuji', 'SUPERSEDED' => 'Konfigurasi lama', 'REVOKED' => 'Dicabut', default => 'Perlu perhatian',
    };
    $providerStatus = match (true) {
        $activeConfiguration !== null && $active?->runtime_enabled => 'Sedang digunakan',
        $activeConfiguration !== null => 'Dinonaktifkan',
        $verifiedConfiguration !== null => 'Siap digunakan',
        $currentCredential !== null => 'Perlu diuji',
        default => 'Belum dikonfigurasi',
    };
    $currentStatus = $activeConfiguration !== null && ! $active?->runtime_enabled ? 'Dinonaktifkan' : ($currentConfiguration ? $statusLabel($currentConfiguration->status) : ($currentCredential ? $statusLabel($currentCredential->status) : 'Belum dikonfigurasi'));
@endphp
<div class="waka-shell">
    @include('academic.partials.sidebar',['activeMenu'=>'settings'])
    <main class="waka-content">
        <header class="waka-topbar"><div><p class="eyebrow">Pengaturan Sistem</p><h1>Pengaturan AI</h1><p class="muted">Kelola koneksi OpenAI untuk fitur AI SISTEM IMTAQ.</p></div></header>
        @if(session('status'))<p class="notice">{{ session('status') }}</p>@endif
        @if($errors->any())<ul class="error-list" role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
        <div class="ai-page">
            <section class="card" aria-labelledby="provider-overview-title">
                <div class="provider-head"><div><p class="eyebrow">Provider AI</p><h2 id="provider-overview-title">OpenAI</h2><p class="muted provider-description">Satu tempat untuk melihat provider yang digunakan SISTEM IMTAQ.</p></div><span class="status-badge" aria-label="Status provider: {{ $providerStatus }}">{{ $providerStatus }}</span></div>
                @if($currentConfiguration || $currentCredential)
                    <div class="settings-list" aria-label="Ringkasan konfigurasi provider">
                        <div class="setting-row"><span class="setting-label">API Key</span><span class="setting-value">{{ $currentCredential?->label ?? 'Belum dipilih' }}<span class="subvalue muted">{{ $currentCredential ? '••••••••'.$currentCredential->secret_last4 : 'Belum ada API Key' }}</span></span></div>
                        <div class="setting-row"><span class="setting-label">Model</span><span class="setting-value">{{ $currentConfiguration?->model ?? 'Belum dipilih' }}</span></div>
                        <div class="setting-row"><span class="setting-label">Status</span><span class="setting-value">{{ $currentStatus }}<span class="subvalue muted">{{ $currentConfiguration?->verified_at?->format('d/m/Y H:i') ?? 'Belum ada pengujian' }}</span></span></div>
                    </div>
                    <div class="public-row"><div><strong>Public Academic AI</strong><span class="muted small">{{ $publicAiEnabled ? 'ON · Digunakan oleh Waka Akademik.' : 'OFF · Belum diaktifkan untuk Waka Akademik.' }}</span></div><span class="status-badge" aria-label="Public Academic AI: {{ $publicAiEnabled ? 'ON' : 'OFF' }}">{{ $publicAiEnabled ? 'ON' : 'OFF' }}</span></div>
                    <div class="actions" style="margin-top:1.2rem"><button class="button" type="button" data-open-config-form>Ubah Konfigurasi</button>@if($currentCredential && in_array($currentCredential->status, ['PENDING','VERIFIED','STANDBY'], true))<form method="post" action="{{ route('admin.system.ai-provider.credentials.verify',$currentCredential) }}">@csrf<button class="button secondary" type="submit">{{ $currentCredential->status === 'VERIFIED' ? 'Uji Ulang' : 'Uji Koneksi' }}</button></form>@endif</div>
                @else
                    <div class="empty-copy"><h3>Belum dikonfigurasi</h3><p class="muted">Tambahkan API Key OpenAI untuk mulai menyiapkan provider AI.</p><div class="actions"><button class="button" type="button" data-open-credential-form>Tambah API Key</button></div></div>
                    <div class="public-row"><div><strong>Public Academic AI</strong><span class="muted small">{{ $publicAiEnabled ? 'ON · Digunakan oleh Waka Akademik.' : 'OFF · Belum diaktifkan untuk Waka Akademik.' }}</span></div><span class="status-badge" aria-label="Public Academic AI: {{ $publicAiEnabled ? 'ON' : 'OFF' }}">{{ $publicAiEnabled ? 'ON' : 'OFF' }}</span></div>
                @endif
                <div class="edit-panel" data-config-edit-panel hidden aria-live="polite"></div>
                <template id="config-edit-template"><div class="section-heading"><div><h3>Ubah Konfigurasi</h3><p class="muted small">Pilih API Key dan model dari daftar yang ditemukan oleh server.</p></div></div><form class="stack" method="post" action="{{ route('admin.system.ai-provider.configurations.store') }}" data-models-url-template="{{ route('admin.system.ai-provider.credentials.models', ['credential' => '__CREDENTIAL__']) }}">@csrf<div class="field"><label for="credential_id">API Key</label><select id="credential_id" name="credential_id" required><option value="">Pilih API Key</option>@foreach($credentials->whereIn('status',['VERIFIED','STANDBY']) as $credential)<option value="{{ $credential->id }}">{{ $credential->label }} · ••••{{ $credential->secret_last4 }}</option>@endforeach</select></div><div class="field"><label for="model">Model AI</label><select id="model" name="model" required disabled aria-describedby="model-help"><option value="">{{ $currentCredential ? 'Pilih API Key terlebih dahulu' : 'Tambahkan API Key terlebih dahulu' }}</option></select><p id="model-help" class="muted small helper" role="status" aria-live="polite">Daftar model dimuat setelah API Key dipilih.</p></div><input type="hidden" name="max_output_tokens" value="800"><div class="actions"><button class="button secondary" type="button" data-cancel-config-form>Batal</button><button class="button" type="submit">Simpan &amp; Uji</button></div></form></template>
            </section>
            <details class="card advanced" id="advanced-settings"><summary aria-controls="advanced-settings-content">Pengaturan Lanjutan</summary><div class="advanced-content" id="advanced-settings-content">
                <section aria-labelledby="runtime-title"><div class="section-heading"><div><h3 id="runtime-title">Provider runtime</h3><p class="muted small">Kontrol runtime terpisah dari Public Academic AI.</p></div><span class="badge">{{ $active?->runtime_enabled ? 'Aktif' : 'Nonaktif' }}</span></div>@if($active)<form method="post" action="{{ route('admin.system.ai-provider.runtime.toggle') }}">@csrf<input type="hidden" name="enabled" value="{{ $active->runtime_enabled ? 0 : 1 }}"><button class="button secondary" type="submit">{{ $active->runtime_enabled ? 'Nonaktifkan Provider AI' : 'Aktifkan Provider AI' }}</button></form>@else<p class="muted small">Belum ada konfigurasi yang sedang digunakan.</p>@endif</section>
                <section aria-labelledby="credential-title"><div class="section-heading"><div><h3 id="credential-title">API Key</h3><p class="muted small">API Key tersimpan terenkripsi dan hanya ditampilkan sebagai empat karakter terakhir.</p></div><button class="button secondary" type="button" data-open-credential-form>Tambah API Key</button></div><form id="credential-form" class="stack credential-form" method="post" action="{{ route('admin.system.ai-provider.credentials.store') }}" hidden>@csrf<div class="field"><label for="label">Nama profil API Key</label><input id="label" name="label" required maxlength="120"></div><div class="field"><label for="secret">API Key</label><input id="secret" name="secret" type="password" required autocomplete="new-password"></div><button class="button" type="submit">Simpan API Key</button></form><div class="history-list">@forelse($credentials as $credential)<div class="history-item"><div class="status-line"><div><strong>{{ $credential->label }}</strong><div class="small muted">••••{{ $credential->secret_last4 }}</div></div><span class="badge">{{ $statusLabel($credential->status) }}</span></div><div class="actions">@if(in_array($credential->status, ['PENDING','VERIFIED','STANDBY'], true))<form method="post" action="{{ route('admin.system.ai-provider.credentials.verify',$credential) }}">@csrf<button class="button secondary" type="submit">{{ $credential->status === 'VERIFIED' ? 'Uji Ulang' : 'Uji Koneksi' }}</button></form>@endif<form method="post" action="{{ route('admin.system.ai-provider.credentials.revoke',$credential) }}">@csrf<button class="button secondary danger" type="submit">Cabut</button></form></div></div>@empty<p class="muted small">Belum ada API Key.</p>@endforelse</div></section>
                <section aria-labelledby="config-history-title"><div class="section-heading"><div><h3 id="config-history-title">Riwayat Konfigurasi</h3><p class="muted small">Konfigurasi lama dan DRAFT historis tetap tersimpan tanpa perubahan.</p></div></div><div class="history-list">@forelse($configurations as $configuration)<div class="history-item"><div class="status-line"><strong>{{ $configuration->model }}</strong><span class="badge">{{ $statusLabel($configuration->status) }}</span></div><div class="small muted">API Key: {{ $configuration->credential?->label }} · ••••{{ $configuration->credential?->secret_last4 }}</div><div class="small muted">Dibuat {{ $configuration->created_at?->format('d/m/Y H:i') }} · ID {{ substr((string) $configuration->id, 0, 8) }} · {{ $configuration->max_output_tokens }} token</div><div class="actions">@if($configuration->status === 'DRAFT')<form method="post" action="{{ route('admin.system.ai-provider.configurations.verify',$configuration) }}">@csrf<button class="button secondary" type="submit">Uji Konfigurasi</button></form>@endif @if($configuration->status === 'VERIFIED')<form method="post" action="{{ route('admin.system.ai-provider.configurations.activate',$configuration) }}">@csrf<button class="button" type="submit">Gunakan Konfigurasi Ini</button></form>@endif</div></div>@empty<p class="muted small">Belum ada riwayat konfigurasi.</p>@endforelse</div></section>
                <section aria-labelledby="output-title"><h3 id="output-title">Batas output</h3><p class="muted small">Nilai default tetap 800 token. Pengaturan ini berada di area lanjutan agar alur utama tetap sederhana.</p><div class="field"><label for="advanced-max-output-tokens">Max output tokens</label><input id="advanced-max-output-tokens" type="number" value="800" min="128" max="4000" disabled></div></section>
            </div></details>
        </div>
    </main>
</div>
<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const credentialForm = document.getElementById('credential-form');
    const advanced = document.getElementById('advanced-settings');
    const editPanel = document.querySelector('[data-config-edit-panel]');
    const configTemplate = document.getElementById('config-edit-template');
    let discoveryGeneration = 0;
    let discoveryController = null;
    const guardForm = (sensitiveForm) => { sensitiveForm.addEventListener('submit', () => { const submit = sensitiveForm.querySelector('button[type="submit"]'); if (!submit) return; submit.disabled = true; submit.setAttribute('aria-disabled', 'true'); }, { once: true }); };
    const revealCredentialForm = () => { if (!credentialForm) return; if (advanced) advanced.open = true; credentialForm.hidden = false; document.getElementById('label')?.focus(); };
    const bindConfigForm = () => {
        const form = editPanel?.querySelector('[data-models-url-template]');
        const credential = editPanel?.querySelector('#credential_id');
        const model = editPanel?.querySelector('#model');
        const help = editPanel?.querySelector('#model-help');
        if (!form || !credential || !model || !help) return;
        guardForm(form);
        credential.addEventListener('change', async () => {
            const generation = ++discoveryGeneration; const selectedCredential = credential.value; discoveryController?.abort(); discoveryController = new AbortController(); model.replaceChildren(new Option('Memuat daftar model…', '')); model.disabled = true; help.textContent = 'Mengambil daftar model dari provider…';
            if (!selectedCredential) { discoveryController.abort(); model.replaceChildren(new Option('Pilih API Key terlebih dahulu', '')); help.textContent = 'Daftar model dimuat setelah API Key dipilih.'; return; }
            try { const url = form.dataset.modelsUrlTemplate.replace('__CREDENTIAL__', encodeURIComponent(selectedCredential)); const response = await fetch(url, { method: 'POST', headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': csrf}, credentials: 'same-origin', signal: discoveryController.signal }); if (!response.ok) throw new Error('Model discovery gagal.'); const payload = await response.json(); if (generation !== discoveryGeneration || credential.value !== selectedCredential) return; const models = Array.isArray(payload.models) ? payload.models : []; model.replaceChildren(new Option(models.length ? 'Pilih model' : 'Tidak ada model tersedia', '')); models.forEach((id) => model.add(new Option(id, id))); model.disabled = models.length === 0; help.textContent = models.length ? 'Pilih model yang akan digunakan oleh provider.' : 'API Key tidak mengembalikan model yang dapat digunakan.'; }
            catch (error) { if (error.name === 'AbortError' || generation !== discoveryGeneration || credential.value !== selectedCredential) return; model.replaceChildren(new Option('Discovery gagal', '')); model.disabled = true; help.textContent = 'Daftar model gagal dimuat. Periksa API Key dan koneksi provider.'; }
        });
    };
    document.querySelectorAll('[data-open-credential-form]').forEach((button) => button.addEventListener('click', revealCredentialForm));
    document.querySelectorAll('form').forEach(guardForm);
    document.querySelector('[data-open-config-form]')?.addEventListener('click', () => { if (!editPanel || !configTemplate) return; editPanel.replaceChildren(configTemplate.content.cloneNode(true)); editPanel.hidden = false; bindConfigForm(); editPanel.querySelector('select')?.focus(); });
    document.querySelector('[data-config-edit-panel]')?.addEventListener('click', (event) => { if (!event.target.closest('[data-cancel-config-form]')) return; editPanel.hidden = true; editPanel.replaceChildren(); });
})();
</script>
</body>
</html>
