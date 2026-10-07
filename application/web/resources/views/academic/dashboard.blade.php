<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ringkasan Akademik</title>
    <style>
        @include('admin.partials.styles')
        :root{--waka-green:#086b4f;--waka-deep:#07553f;--waka-soft:#e8f6ef;--waka-blue:#e9f2ff;--waka-amber:#fff3df;--waka-purple:#f0edff;--waka-ink:#12372d;--waka-muted:#6a8278}
        body{padding:0;background:#f4f8f6;color:var(--waka-ink)}
        .waka-shell{display:grid;grid-template-columns:4.4rem minmax(0,1fr);min-height:100vh}
        .waka-sidebar{display:flex;flex-direction:column;background:linear-gradient(180deg,var(--waka-deep),#063d31);color:#ecfff5;padding:1.35rem .55rem;position:sticky;top:0;height:100vh;width:4.4rem;z-index:10;transition:width .2s ease,padding .2s ease;overflow:hidden}
        .waka-sidebar:hover,.waka-sidebar:focus-within{width:14rem;padding-left:1rem;padding-right:1rem;box-shadow:10px 0 24px rgba(6,61,49,.16)}
        .waka-brand{display:flex;gap:.65rem;align-items:center;justify-content:center;color:#fff;text-decoration:none;font-weight:800;font-size:1.15rem;letter-spacing:-.03em;padding:.25rem .45rem 1.8rem;white-space:nowrap}
        .waka-brand small{display:block;color:#bde4d0;font-size:.65rem;letter-spacing:.08em;text-transform:uppercase;margin-top:.15rem}
        .waka-mark{width:2.75rem;height:2.75rem;border-radius:.35rem;object-fit:contain;background:#fff;display:block;flex:none}
        .waka-nav{display:grid;gap:.35rem}.waka-nav a{display:flex;align-items:center;gap:.65rem;color:#d1eee0;text-decoration:none;padding:.7rem .75rem;border-radius:.65rem;font-size:.86rem}.waka-nav a:hover,.waka-nav a.active{background:#f4fff8;color:var(--waka-deep);font-weight:800}.waka-nav .nav-icon{width:1.35rem;text-align:center;font-size:1.2rem;line-height:1.1}
        .waka-brand-text,.waka-nav a span:not(.nav-icon),.waka-sidebar-footer{display:none}.waka-sidebar:hover .waka-brand-text,.waka-sidebar:focus-within .waka-brand-text,.waka-sidebar:hover .waka-nav a span:not(.nav-icon),.waka-sidebar:focus-within .waka-nav a span:not(.nav-icon),.waka-sidebar:hover .waka-sidebar-footer,.waka-sidebar:focus-within .waka-sidebar-footer{display:block}.waka-sidebar:hover .waka-nav a,.waka-sidebar:focus-within .waka-nav a,.waka-sidebar:hover .waka-brand,.waka-sidebar:focus-within .waka-brand{justify-content:flex-start;padding-left:.75rem}
        .waka-sidebar-footer{margin-top:auto;border-top:1px solid rgba(255,255,255,.18);padding-top:1rem;color:#bde4d0;font-size:.75rem;line-height:1.45;white-space:nowrap}.waka-sidebar-account{display:flex;align-items:center;gap:.55rem;margin-bottom:.55rem}.waka-sidebar-account strong{display:block;color:#fff;font-size:.88rem}.waka-sidebar-avatar{width:1.85rem;height:1.85rem;border-radius:50%;display:grid;place-items:center;background:#ccebdc;color:var(--waka-green);font-size:.68rem;font-weight:900;flex:none}.waka-sidebar-footer small{display:block}.waka-sidebar-logout{width:100%;margin-top:.8rem;border:1px solid rgba(255,255,255,.3);border-radius:.5rem;background:rgba(255,255,255,.08);color:#fff;padding:.45rem .6rem;font:inherit;font-weight:800;text-align:left;cursor:pointer}.waka-sidebar-logout:hover,.waka-sidebar-logout:focus-visible{background:#f4fff8;color:var(--waka-deep)}
        .waka-content{min-width:0;padding:1.45rem 1.65rem 2.25rem}.waka-topbar{display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-bottom:1.15rem}.waka-kicker{margin:0;color:var(--waka-green);font-size:.7rem;font-weight:900;letter-spacing:.13em;text-transform:uppercase}.waka-title{font-size:clamp(1.55rem,2.8vw,2.2rem);margin:.15rem 0 0;letter-spacing:-.05em}.waka-period-context{display:grid;gap:.15rem;justify-items:end;text-align:right}.waka-period-context strong{color:var(--waka-ink);font-size:.92rem}.waka-period-context span{color:var(--waka-muted);font-size:.72rem}.waka-filter{display:grid;grid-template-columns:minmax(14rem,1.35fr) repeat(2,minmax(9rem,1fr)) auto;align-items:start;gap:.7rem;background:#fff;border:1px solid #dceae2;border-radius:.85rem;padding:.8rem 1rem;margin-bottom:1.15rem;box-shadow:0 5px 16px rgba(16,72,51,.04)}.waka-filter-heading{grid-column:1/-1;display:flex;align-items:baseline;gap:.65rem;color:var(--waka-ink)}.waka-filter-heading strong{font-size:.76rem;letter-spacing:.1em;text-transform:uppercase}.waka-filter-heading small{color:var(--waka-muted);font-size:.68rem}.waka-filter label{display:flex;flex-direction:column;gap:.3rem;margin:0;color:var(--waka-muted);font-size:.72rem;font-weight:800}.waka-filter input,.waka-filter select{width:100%;min-width:0;height:2.5rem;margin-top:0;padding:.4rem .6rem}.waka-filter-help{display:block;max-width:14rem;color:var(--waka-muted);font-size:.64rem;font-weight:500;line-height:1.3}.waka-filter button{align-self:end;display:inline-flex;align-items:center;justify-content:center;min-width:7.5rem;height:2.5rem;margin-top:0;padding:0 .8rem;border-radius:.65rem;box-shadow:0 4px 10px rgba(8,107,79,.14);transition:transform .15s ease,box-shadow .15s ease}.waka-filter button:hover{transform:translateY(-1px);box-shadow:0 7px 14px rgba(8,107,79,.2)}.waka-filter button:active{transform:translateY(0)}.waka-filter button:focus-visible{outline:3px solid #b9e7cf;outline-offset:2px}.waka-filter-note{grid-column:1/-1;margin:0;color:var(--waka-muted);font-size:.7rem;justify-self:end}
        .waka-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.85rem;margin-bottom:1rem}.waka-kpi{display:flex;flex-direction:column;min-height:8.6rem;border:1px solid #dceae2;border-radius:1rem;padding:1rem;background:#fff;box-shadow:0 5px 16px rgba(16,72,51,.035);min-width:0}.waka-kpi.primary{padding:1.1rem;border-color:#c9e3d5;background:#fbfefc}.waka-kpi.data-quality{border-color:#d4e1f1;background:#f7faff}.waka-kpi.context{background:#fff}.waka-kpi.blue{background:var(--waka-blue);border-color:#d6e6fb}.waka-kpi.purple{background:var(--waka-purple);border-color:#e0d9ff}.waka-kpi.neutral{background:#f5f8f6;border-color:#dfe7e2}.waka-kpi-head{display:flex;align-items:center;justify-content:space-between;gap:.5rem}.waka-kpi-label{color:var(--waka-muted);font-size:.77rem;font-weight:800}.waka-kpi.primary .waka-kpi-label,.waka-kpi.data-quality .waka-kpi-label{font-size:.82rem;color:var(--waka-ink)}.waka-kpi-icon{width:1.85rem;height:1.85rem;border-radius:.55rem;display:grid;place-items:center;background:rgba(255,255,255,.72);color:var(--waka-green);font-weight:900}.waka-kpi.data-quality .waka-kpi-icon{color:#416b91}.waka-kpi.neutral .waka-kpi-icon{color:var(--waka-muted)}.waka-kpi-value{display:block;font-size:1.8rem;line-height:1.1;margin:.45rem 0 .25rem;color:var(--waka-green);letter-spacing:-.05em}.waka-kpi.primary .waka-kpi-value,.waka-kpi.data-quality .waka-kpi-value{font-size:2rem;font-weight:950}.waka-kpi.data-quality .waka-kpi-value{color:#315f86}.waka-kpi.context .waka-kpi-value{font-size:1.7rem}.waka-kpi.neutral .waka-kpi-value,.waka-status-value.neutral{color:var(--waka-muted)}.waka-kpi-meta{margin-top:auto;color:var(--waka-muted);font-size:.75rem;line-height:1.35}.waka-kpi.primary .waka-kpi-meta,.waka-kpi.data-quality .waka-kpi-meta{font-size:.78rem}
        .waka-main-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(17rem,.34fr);gap:1rem 1.2rem;align-items:start}.waka-main-grid>div{display:contents}.waka-main-grid>div>section:first-child{grid-column:1;grid-row:1;margin-bottom:0}.waka-main-grid>#pemantauan,.waka-main-grid>#pengisian-kehadiran,.waka-main-grid>#tren-kehadiran{grid-column:1/-1;margin-bottom:0}.waka-main-grid>#pemantauan{grid-row:2}.waka-main-grid>#pengisian-kehadiran{grid-row:3}.waka-main-grid>#tren-kehadiran{grid-row:4}.waka-main-grid>.waka-rail{grid-column:2;grid-row:1}.waka-main-grid>.waka-rail .waka-card{margin-bottom:0}.waka-card{background:#fff;border:1px solid #dceae2;border-radius:1rem;padding:1.1rem;margin-bottom:1.2rem;box-shadow:0 8px 24px rgba(16,72,51,.05)}.waka-card-heading{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;margin-bottom:1rem}.waka-card-heading h2{margin:0;font-size:1.08rem;letter-spacing:-.03em}.waka-card-heading p{margin:.3rem 0 0;color:var(--waka-muted);font-size:.78rem}.waka-link{color:var(--waka-green);font-weight:800;text-decoration:none;font-size:.78rem;white-space:nowrap}.waka-link.neutral{color:var(--waka-muted);background:#eef3f0;border:1px solid #dce7e1;border-radius:999px;padding:.32rem .55rem}.waka-status-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1.25rem}.waka-status{padding:.1rem 0}.waka-status + .waka-status{border-left:1px solid #dceae2;padding-left:1.25rem}.waka-status-title{font-weight:900}.waka-status-value{font-size:1.45rem;font-weight:900;color:var(--waka-green);margin:.3rem 0;overflow-wrap:anywhere}.waka-status-note{display:block;color:var(--waka-muted);font-size:.75rem;line-height:1.45;overflow-wrap:anywhere}.waka-unavailable{border:1px dashed #cbd9d1;background:#f7faf8;color:var(--waka-muted);border-radius:.75rem;padding:1.1rem;font-size:.82rem}.waka-unavailable strong{display:block;color:var(--waka-ink);margin-bottom:.3rem}
        .waka-class-list{display:grid;gap:.65rem}.waka-class-row{border:1px solid #e2eee8;border-radius:.75rem;padding:.75rem;transition:border-color .15s ease,background .15s ease,box-shadow .15s ease}.waka-class-row:hover{border-color:#b9dfc8;background:#fbfefc;box-shadow:0 4px 12px rgba(16,72,51,.05)}.waka-class-row-top{display:flex;justify-content:space-between;gap:.75rem;align-items:flex-start}.waka-class-main{min-width:0}.waka-class-name{display:block;font-weight:900}.waka-class-summary,.waka-class-missing{display:block;color:var(--waka-muted);font-size:.76rem;line-height:1.35;margin-top:.2rem}.waka-class-rate{display:block;color:var(--waka-green);font-size:1.05rem;font-weight:950;white-space:nowrap}.waka-class-rate.neutral{color:var(--waka-muted);font-size:.78rem;font-weight:800}.waka-progress{height:.45rem;background:#e6f0eb;border-radius:99px;overflow:hidden;margin-top:.65rem}.waka-progress span{display:block;height:100%;border-radius:99px;background:var(--waka-green);width:var(--progress,0%)}.waka-session-list{display:grid;gap:.6rem}.waka-session-day{color:var(--waka-green);font-size:.72rem;font-weight:900;letter-spacing:.04em;margin:.35rem .15rem 0;text-transform:uppercase}.waka-session-row{display:flex;align-items:center;justify-content:space-between;gap:1rem;border:1px solid #dceae2;border-radius:.75rem;padding:.75rem;text-decoration:none;color:var(--waka-ink);background:#f8fcfa}.waka-session-row:hover{border-color:#9bcbb2;background:#effaf3}.waka-session-row strong,.waka-session-row small{display:block}.waka-session-row small{color:var(--waka-muted);font-size:.75rem;margin-top:.2rem}.waka-session-meta{display:flex;justify-content:flex-end;margin-top:.35rem}.waka-session-status{display:inline-flex;border-radius:999px;padding:.18rem .45rem;background:#e5f4eb;color:var(--waka-green);font-size:.68rem;font-weight:800}.waka-session-action{color:var(--waka-green);font-size:.78rem;font-weight:900;white-space:nowrap}
        .waka-main-grid>div>#pemantauan,.waka-main-grid>div>#pengisian-kehadiran,.waka-main-grid>div>#tren-kehadiran{grid-column:1/-1;margin-bottom:0}.waka-main-grid>div>#pemantauan{grid-row:2}.waka-main-grid>div>#pengisian-kehadiran{grid-row:3}.waka-main-grid>div>#tren-kehadiran{grid-row:4}.waka-main-grid>div>section:first-child{grid-column:1;grid-row:1;margin-bottom:0}
        .waka-trend{display:grid;gap:.65rem}.waka-trend-row{display:grid;grid-template-columns:5rem minmax(0,1fr);gap:.5rem .8rem;align-items:center;padding:.65rem 0;border-top:1px solid #edf3ef}.waka-trend-row:first-child{border-top:0}.waka-trend-date{color:var(--waka-muted);font-size:.73rem;font-weight:800}.waka-trend-metrics{display:grid;gap:.35rem;min-width:0}.waka-trend-metric{display:grid;grid-template-columns:5.9rem minmax(0,1fr) 4.1rem;align-items:center;gap:.55rem;min-width:0}.waka-trend-metric-label{color:var(--waka-muted);font-size:.7rem;font-weight:800}.waka-trend-track{height:.45rem;background:#e7eef0;border-radius:99px;overflow:hidden;min-width:0}.waka-trend-track span{display:block;height:100%;border-radius:inherit;width:var(--progress,0%);background:var(--waka-green)}.waka-trend-track.completeness span{background:#50769a}.waka-trend-value{color:var(--waka-ink);font-size:.72rem;font-weight:900;text-align:right;white-space:nowrap}.waka-trend-value.neutral{color:var(--waka-muted);font-weight:800}.waka-trend-detail{grid-column:2;color:var(--waka-muted);font-size:.68rem;line-height:1.3}
        .waka-trend-options{display:flex;align-items:center;gap:.7rem;flex-wrap:wrap;margin:-.45rem 0 1rem;color:var(--waka-muted);font-size:.73rem}.waka-trend-options .waka-link{padding:.25rem .45rem;border-radius:999px}.waka-trend-options .waka-link.active{background:var(--waka-soft)}
        .waka-session-filters{display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;margin:-.35rem 0 1rem;color:var(--waka-muted);font-size:.73rem}.waka-session-filters .waka-link{padding:.28rem .55rem;border-radius:999px;border:1px solid #dceae2}.waka-session-filters .waka-link.active{background:var(--waka-soft);border-color:#b9dfc8}
        .waka-quick-actions{display:grid;gap:.55rem}.waka-quick-action{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.75rem .8rem;border:1px solid #dceae2;border-radius:.7rem;background:#f8fcfa;color:var(--waka-ink);text-decoration:none;font-size:.8rem;font-weight:800}.waka-quick-action:hover,.waka-quick-action:focus-visible{border-color:#9bcbb2;background:#effaf3}.waka-quick-action.primary{background:var(--waka-soft);border-color:#b9dfc8;color:var(--waka-green)}.waka-quick-action span:last-child{color:var(--waka-green);font-size:1rem}.waka-footnote{color:var(--waka-muted);font-size:.72rem;margin:0}
        @media(max-width:1000px){.waka-sidebar{padding:.9rem .55rem}.waka-sidebar:hover,.waka-sidebar:focus-within{padding-left:.7rem;padding-right:.7rem}.waka-brand{padding-bottom:1.4rem}.waka-nav a{font-size:.8rem}.waka-main-grid{grid-template-columns:1fr}.waka-main-grid>div>section:first-child,.waka-main-grid>#pemantauan,.waka-main-grid>#pengisian-kehadiran,.waka-main-grid>#tren-kehadiran{grid-column:1}.waka-main-grid>.waka-rail{grid-column:1;grid-row:2}.waka-main-grid>#pemantauan{grid-row:3}.waka-main-grid>#pengisian-kehadiran{grid-row:4}.waka-main-grid>#tren-kehadiran{grid-row:5}.waka-rail{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}.waka-rail .waka-card{margin-bottom:0}}
        @media(max-width:680px){.waka-shell{display:block}.waka-sidebar{position:fixed;left:0;top:0;width:4.4rem;height:100vh;padding:1rem .55rem;overflow:hidden}.waka-sidebar.is-expanded{width:12rem;padding-left:1rem;padding-right:1rem;box-shadow:10px 0 24px rgba(6,61,49,.16)}.waka-brand{padding:.2rem .45rem 1.4rem}.waka-sidebar .waka-brand-text,.waka-sidebar .waka-nav a span:not(.nav-icon),.waka-sidebar .waka-sidebar-footer{display:none}.waka-sidebar.is-expanded .waka-brand-text,.waka-sidebar.is-expanded .waka-nav a span:not(.nav-icon),.waka-sidebar.is-expanded .waka-sidebar-footer{display:block}.waka-sidebar.is-expanded .waka-nav a{justify-content:flex-start}.waka-sidebar.is-expanded .waka-brand{justify-content:flex-start;padding-left:1.75rem}.waka-nav a{justify-content:center;padding:.7rem .55rem}.waka-content{padding:.9rem;margin-left:4.4rem}}
        @media(max-width:1000px){.waka-main-grid>div>section:first-child,.waka-main-grid>div>#pemantauan,.waka-main-grid>div>#pengisian-kehadiran,.waka-main-grid>div>#tren-kehadiran{grid-column:1}.waka-main-grid>.waka-rail{grid-column:1;grid-row:2}.waka-main-grid>div>#pemantauan{grid-row:3}.waka-main-grid>div>#pengisian-kehadiran{grid-row:4}.waka-main-grid>div>#tren-kehadiran{grid-row:5}}
        @media(max-width:1000px){.waka-main-grid>div>section:first-child,.waka-main-grid>div>#pemantauan,.waka-main-grid>div>#pengisian-kehadiran,.waka-main-grid>div>#tren-kehadiran{grid-row:auto}.waka-main-grid>.waka-rail{grid-row:2}}
        @media(max-width:900px){.waka-filter{grid-template-columns:minmax(0,1fr) minmax(0,1fr) auto}.waka-filter label:first-child{grid-column:1/-1}.waka-filter button{justify-self:start}}
        @media(max-width:680px){.waka-content{padding:.9rem}.waka-topbar{align-items:flex-start;flex-direction:column;gap:.55rem}.waka-period-context{justify-items:start;text-align:left}.waka-user{font-size:0}.waka-user .waka-logout{font-size:.78rem}.waka-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:.65rem}.waka-filter{grid-template-columns:1fr;align-items:stretch;gap:.6rem}.waka-filter-heading{gap:.45rem}.waka-filter label:first-child{grid-column:auto}.waka-filter label,.waka-filter input,.waka-filter select,.waka-filter button{width:100%}.waka-filter button{align-self:auto;justify-self:stretch;margin-top:0}.waka-filter-note{grid-column:auto;justify-self:start;padding-top:.1rem}.waka-status-grid,.waka-rail{grid-template-columns:1fr}.waka-status + .waka-status{border-left:0;border-top:1px solid #dceae2;padding:1rem 0 0}.waka-card-heading{flex-direction:column;gap:.45rem}.waka-card-heading .waka-link{white-space:normal}.waka-status-value{font-size:1.2rem}.waka-class-row-top{align-items:flex-start}.waka-class-rate{font-size:1rem}.waka-class-meta{text-align:left}.waka-trend-row{grid-template-columns:1fr;gap:.35rem}.waka-trend-detail{grid-column:auto}.waka-trend-metric{grid-template-columns:5.4rem minmax(0,1fr) 4rem;gap:.4rem}.waka-trend-value{font-size:.68rem}}
        @media(max-width:480px){.waka-grid{grid-template-columns:1fr}}
        @media(max-width:680px){.waka-sidebar.is-expanded .waka-brand{justify-content:flex-start;padding-left:.75rem}.waka-sidebar.is-expanded .waka-nav a{padding-left:.75rem;padding-right:.75rem}}
        @media(max-width:680px){.waka-sidebar.is-expanded{width:14rem}}
        .waka-trend-row{padding:.4rem 0}.waka-main-grid>div>section:first-child .waka-card-heading>.waka-link{display:none}.waka-filter input:focus-visible,.waka-filter select:focus-visible,.waka-link:focus-visible,.waka-quick-action:focus-visible,.waka-session-row:focus-visible{outline:3px solid #b9e7cf;outline-offset:3px}.waka-trend-options .waka-link{display:inline-flex;align-items:center;min-height:2.25rem}.waka-trend-options .waka-link:focus-visible{outline:3px solid #b9e7cf;outline-offset:2px}@media(max-width:1000px){.waka-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
        /* Keep the period action aligned with the date controls, even when the month help text wraps. */
        .waka-filter button{align-self:start;margin-top:1.1rem}
        @media(max-width:680px){.waka-filter button{align-self:auto;margin-top:0}}
        .waka-ai-assistant{display:grid;gap:.85rem;margin-bottom:1rem;border:1px solid #cfe5da;border-radius:1rem;padding:1.1rem;background:#f2faf5;box-shadow:0 8px 24px rgba(16,72,51,.04)}.waka-ai-heading{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem}.waka-ai-heading h2{margin:0;font-size:1.08rem}.waka-ai-heading p{margin:.3rem 0 0;color:var(--waka-muted);font-size:.78rem;line-height:1.45}.waka-ai-readonly{display:inline-flex;white-space:nowrap;border:1px solid #b9dfc8;border-radius:999px;padding:.3rem .55rem;color:var(--waka-green);font-size:.68rem;font-weight:800;background:#fff}.waka-ai-form{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:.65rem;align-items:end}.waka-ai-form label{display:grid;gap:.3rem;color:var(--waka-ink);font-size:.75rem;font-weight:800}.waka-ai-form textarea{width:100%;min-height:3.1rem;resize:vertical;padding:.65rem .75rem;border:1px solid #b9d8c6;border-radius:.65rem;background:#fff;color:var(--waka-ink);font:inherit;line-height:1.4}.waka-ai-form textarea:focus-visible{outline:3px solid #b9e7cf;outline-offset:2px}.waka-ai-submit{align-self:end;min-height:2.5rem;padding:0 1rem;border:0;border-radius:.65rem;background:var(--waka-green);color:#fff;font:inherit;font-weight:800;cursor:pointer}.waka-ai-submit:hover,.waka-ai-submit:focus-visible{background:var(--waka-deep)}.waka-ai-submit:focus-visible{outline:3px solid #b9e7cf;outline-offset:2px}.waka-ai-submit:disabled{cursor:wait;opacity:.7}.waka-ai-feedback{display:grid;gap:.45rem}.waka-ai-answer,.waka-ai-warning,.waka-ai-error{border-radius:.65rem;padding:.7rem .8rem;font-size:.78rem;line-height:1.5;white-space:pre-wrap;overflow-wrap:anywhere}.waka-ai-answer{border:1px solid #cfe5da;background:#fff;color:var(--waka-ink)}.waka-ai-warning{border:1px solid #f0d9a8;background:#fffaf0;color:#76520b}.waka-ai-error{border:1px solid #e8c4c4;background:#fff7f7;color:#8a3030}.waka-ai-status{color:var(--waka-muted);font-size:.72rem}.waka-ai-status[hidden],.waka-ai-answer[hidden],.waka-ai-warning[hidden],.waka-ai-error[hidden]{display:none}@media(max-width:680px){.waka-ai-heading{flex-direction:column;gap:.55rem}.waka-ai-form{grid-template-columns:1fr}.waka-ai-submit{width:100%}}
        .wali-home{display:grid;gap:1rem;margin-bottom:1.15rem}.wali-identity{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;border:1px solid #c9e3d5;border-radius:1rem;padding:1rem 1.1rem;background:#fff}.wali-identity h2{margin:.15rem 0 .25rem;font-size:1.35rem;letter-spacing:-.035em}.wali-identity p{margin:0;color:var(--waka-muted);font-size:.78rem}.wali-identity-count{text-align:right;min-width:7rem}.wali-identity-count strong{display:block;color:var(--waka-green);font-size:1.65rem}.wali-identity-count span{color:var(--waka-muted);font-size:.72rem}.wali-urgent{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.7rem}.wali-urgent-item{border:1px solid #ecd8ab;border-radius:.8rem;padding:.8rem;background:#fffaf0}.wali-urgent-item strong{display:block;font-size:1.45rem;color:#8a5a00}.wali-urgent-item span{font-size:.75rem;color:#705b32}.wali-operational-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(15rem,.34fr);gap:1rem}.wali-today,.wali-next{margin:0}.wali-today-progress{display:flex;align-items:center;gap:.55rem;flex-wrap:wrap}.wali-today-progress strong{font-size:1.05rem;color:var(--waka-green)}.wali-work-list{display:grid;gap:.65rem}.wali-work-item{display:grid;grid-template-columns:minmax(9rem,.3fr) minmax(0,1fr) auto;gap:.8rem;align-items:center;border:1px solid #dceae2;border-radius:.8rem;padding:.85rem;background:#fbfefc}.wali-work-item[data-state="DUE_INCOMPLETE"],.wali-work-item[data-state="DUE_NOT_STARTED"]{border-color:#e8cc91;background:#fffaf2}.wali-work-time strong,.wali-work-main strong{display:block}.wali-work-time span,.wali-work-main span,.wali-work-metrics{display:block;color:var(--waka-muted);font-size:.72rem;line-height:1.45}.wali-work-main .waka-session-status{margin-top:.35rem}.wali-work-action{display:inline-flex;align-items:center;justify-content:center;min-height:2.5rem;border:1px solid #9bcbb2;border-radius:.65rem;padding:.5rem .7rem;background:var(--waka-soft);color:var(--waka-green);text-decoration:none;font-size:.76rem;font-weight:900;text-align:center}.wali-work-action:focus-visible{outline:3px solid #b9e7cf;outline-offset:2px}.wali-next-detail{display:grid;gap:.35rem}.wali-next-detail strong{font-size:1.05rem}.wali-next-detail span{color:var(--waka-muted);font-size:.76rem;line-height:1.45}.wali-next-detail .wali-work-action{margin-top:.45rem}.wali-empty{border:1px dashed #cbd9d1;border-radius:.8rem;padding:1rem;background:#f7faf8;color:var(--waka-muted);font-size:.78rem;line-height:1.45}.wali-empty strong{display:block;color:var(--waka-ink);margin-bottom:.25rem}.wali-analytics-label{margin:.2rem 0 -.45rem;color:var(--waka-muted);font-size:.7rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase}
        @media(max-width:900px){.wali-operational-grid{grid-template-columns:1fr}.wali-urgent{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:680px){.wali-identity{display:grid}.wali-identity-count{text-align:left}.wali-urgent{grid-template-columns:1fr}.wali-work-item{grid-template-columns:1fr}.wali-work-action{width:100%}}
    </style>
</head>
<body>
@php
    $roleLabels = ['SUPER_ADMIN' => 'Super Admin', 'WAKA_AKADEMIK' => 'Waka Akademik', 'WALI_KELAS' => 'Wali Kelas'];
    $roleLabel = $roleLabels[$dashboard['role']] ?? $dashboard['role'];
    $classes = $dashboard['classes'];
    $classCount = $classes->count();
    $completedSessions = $classes->sum(fn ($item) => (int) ($item['sessions']['completed_sessions'] ?? 0));
    $countedSessions = $classes->sum(fn ($item) => (int) ($item['sessions']['counted_sessions'] ?? 0));
    $extraSessions = $classes->sum(fn ($item) => (int) ($item['sessions']['extra_sessions'] ?? 0));
    $overview = $dashboard['overview'] ?? [];
    $overviewAttendance = $overview['attendance'] ?? [];
    $physicalPresenceRate = $overviewAttendance['physical_presence_rate'] ?? null;
    $completenessRate = $overviewAttendance['completeness_rate'] ?? null;
    $attendanceEligible = (int) ($overviewAttendance['eligible_opportunities'] ?? 0);
    $attendanceResolved = (int) ($overviewAttendance['resolved_opportunities'] ?? 0);
    $attendanceMissing = max(0, $attendanceEligible - $attendanceResolved);
    $todayAttendance = $dashboard['today_attendance'] ?? [];
    $todayCompletionRate = $todayAttendance['completion_rate'] ?? null;
    $hasAttendanceData = $attendanceEligible > 0;
    $hasDueSessions = (int) ($todayAttendance['due_sessions'] ?? 0) > 0;
    $physicalUnavailableLabel = $hasAttendanceData ? 'Belum ada data kehadiran tervalidasi' : 'Belum ada data wajib yang dapat dihitung';
    $completenessUnavailableLabel = 'Belum ada data wajib yang dapat dihitung';
    $physicalPresenceMeta = $attendanceResolved > 0
        ? 'Hadir + terlambat dari '.$attendanceResolved.' data kehadiran tervalidasi'
        : ($hasAttendanceData ? $attendanceEligible.' data wajib · belum ada yang tervalidasi' : 'Belum ada data wajib yang dapat dihitung');
    $statusUnavailableLabel = $hasDueSessions ? 'Belum tersedia' : 'Belum ada sesi jatuh tempo';
    $teacherAttendance = $dashboard['teacher_attendance'] ?? [];
    $teacherPresenceRate = $teacherAttendance['presence_rate'] ?? null;
    $teacherHasSessions = (int) ($teacherAttendance['eligible_participations'] ?? 0) > 0;
    $teacherLabel = $teacherPresenceRate !== null ? $teacherPresenceRate.'% hadir' : ($teacherHasSessions ? 'Belum diisi' : 'Belum ada data kehadiran guru');
    $weekdayNames = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
    $monthShortNames = ['01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'];
    $monthLongNames = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];
    $formatDashboardDate = static fn ($date, array $months): string => $date->format('d').' '.($months[$date->format('m')] ?? $date->format('M')).' '.$date->format('Y');
    $filterModeLabel = $month !== null ? 'Mode bulan penuh' : 'Mode rentang manual';
    $periodContextTitle = $month !== null
        ? ($monthLongNames[$from->format('m')] ?? $from->format('F')).' '.$from->format('Y')
        : $from->format('d').' '.($monthShortNames[$from->format('m')] ?? $from->format('M')).'–'.$to->format('d').' '.($monthShortNames[$to->format('m')] ?? $to->format('M')).' '.$to->format('Y');
    $periodContextMode = $month !== null ? 'Bulan penuh' : 'Rentang manual';
@endphp
<div class="waka-shell">
    @include('academic.partials.sidebar', ['activeMenu' => 'dashboard', 'roleCodeOverride' => $dashboard['role']])
    <main class="waka-content">
        <header class="waka-topbar">
            <div><p class="waka-kicker">Akademik</p><h1 class="waka-title">Dashboard {{ $roleLabel }}</h1></div>
            <div class="waka-period-context" aria-label="Periode aktif"><strong>{{ $periodContextTitle }}</strong><span>{{ $periodContextMode }}</span></div>
        </header>
        @if ($dashboard['role'] === 'WALI_KELAS')
            @php
                $waliHome = $dashboard['wali_operational'];
                $waliClass = $waliHome['class'];
                $todayWork = $waliHome['today_sessions'];
                $todayProgress = $waliHome['today_completion'];
                $nextSession = $waliHome['next_session'];
            @endphp
            <div class="wali-home" aria-label="Beranda operasional Wali Kelas">
                @if (! $waliHome['has_assignment'])
                    <section class="wali-empty" role="status"><strong>Penugasan Wali Kelas belum tersedia</strong>Akun Anda belum memiliki kelas binaan aktif pada periode ini. Hubungi Waka Akademik untuk memeriksa penugasan.</section>
                @else
                    <section class="wali-identity" aria-labelledby="wali-class-title">
                        <div><p class="waka-kicker">Wali Kelas</p><h2 id="wali-class-title">@uiLabel($waliClass->display_name)</h2><p>{{ $waliClass->gradeLevel?->display_name ?? 'Tingkat belum tersedia' }} · Bagian {{ $waliClass->section_code ?: '—' }} · {{ $waliClass->academicYear?->display_name ?? 'Tahun ajaran belum tersedia' }}{{ $waliHome['semester'] ? ' · '.$waliHome['semester']->display_name : '' }}</p></div>
                        <div class="wali-identity-count"><strong>{{ $waliHome['active_student_count'] }}</strong><span>santri aktif</span></div>
                    </section>
                    <section aria-labelledby="wali-urgent-title"><div class="waka-card-heading"><div><h2 id="wali-urgent-title">Perlu Ditangani</h2><p>Ringkasan sesi pada periode terpilih yang sudah membutuhkan tindakan.</p></div><a class="waka-link" href="{{ request()->fullUrlWithQuery(['attendance_filter' => 'needs_action']) }}#pengisian-kehadiran">Lihat sesi yang perlu ditangani →</a></div><div class="wali-urgent">
                        <div class="wali-urgent-item"><strong>{{ $waliHome['urgent']['occurrence_pending'] }}</strong><span>pelaksanaan KBM belum dicatat</span></div>
                        <div class="wali-urgent-item"><strong>{{ $waliHome['urgent']['due_incomplete'] }}</strong><span>sesi belum lengkap</span></div>
                        <div class="wali-urgent-item"><strong>{{ $waliHome['urgent']['due_not_started'] }}</strong><span>sesi belum dimulai pengisiannya</span></div>
                        <div class="wali-urgent-item"><strong>{{ $waliHome['urgent']['teacher_attendance_missing'] }}</strong><span>kehadiran guru belum dicatat</span></div>
                    </div></section>
                    <div class="wali-operational-grid">
                        <section class="waka-card wali-today" id="sesi-hari-ini" aria-labelledby="wali-today-title">
                            <div class="waka-card-heading"><div><h2 id="wali-today-title">Sesi Hari Ini</h2><p>Semua sesi kelas binaan hari ini, diurutkan berdasarkan urgensi.</p></div><div class="wali-today-progress">@if ($todayProgress['due'] > 0)<strong>{{ $todayProgress['finalized'] }}/{{ $todayProgress['due'] }}</strong><span>jatuh tempo selesai</span>@else<span class="waka-link neutral">Belum ada sesi jatuh tempo</span>@endif</div></div>
                            @if ($todayWork->isEmpty())
                                <div class="wali-empty" role="status"><strong>Tidak ada sesi hari ini</strong>Belum ada sesi terjadwal untuk kelas binaan pada hari ini.</div>
                            @else
                                <div class="wali-work-list" role="list">
                                    @foreach ($todayWork as $work)
                                        @php
                                            $workStart = \App\Shared\Platform\Presentation\AcademicBusinessTime::at($work['session']->planned_start_at);
                                            $workEnd = \App\Shared\Platform\Presentation\AcademicBusinessTime::at($work['session']->planned_end_at);
                                        @endphp
                                        <article class="wali-work-item" role="listitem" data-state="{{ $work['state'] }}">
                                            <div class="wali-work-time"><strong>{{ $workStart->format('H:i') }}–{{ $workEnd->format('H:i') }}</strong><span>{{ $weekdayNames[$workStart->format('l')] ?? $workStart->format('l') }}, {{ $workStart->format('d/m/Y') }}</span><span>@uiLabel($work['class_label'])</span></div>
                                            <div class="wali-work-main"><strong>@uiLabel($work['subject_label'])</strong><span>Guru: @uiLabel($work['teacher_label'])</span><span class="waka-session-status">{{ $work['status_label'] }}</span><span class="wali-work-metrics">Santri {{ $work['resolved'] }}/{{ $work['eligible'] }} terselesaikan · {{ $work['missing'] }} belum diisi · {{ $work['completion_rate'] !== null ? $work['completion_rate'].'%' : 'denominator belum tersedia' }}</span><span class="wali-work-metrics">Kehadiran guru: {{ $work['teacher_attendance_label'] }}</span></div>
                                            <a class="wali-work-action" href="{{ route('academic.attendance.show', $work['session']) }}">{{ $work['action_label'] }} <span aria-hidden="true">→</span></a>
                                        </article>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                        <section class="waka-card wali-next" aria-labelledby="wali-next-title"><div class="waka-card-heading"><div><h2 id="wali-next-title">Sesi Berikutnya</h2><p>Jadwal terdekat kelas binaan.</p></div></div>
                            @if ($nextSession)
                                @php
                                    $nextStart = \App\Shared\Platform\Presentation\AcademicBusinessTime::at($nextSession['session']->planned_start_at);
                                    $nextEnd = \App\Shared\Platform\Presentation\AcademicBusinessTime::at($nextSession['session']->planned_end_at);
                                @endphp
                                <div class="wali-next-detail"><strong>@uiLabel($nextSession['subject_label'])</strong><span>{{ $weekdayNames[$nextStart->format('l')] ?? $nextStart->format('l') }}, {{ $nextStart->format('d/m/Y') }} · {{ $nextStart->format('H:i') }}–{{ $nextEnd->format('H:i') }}</span><span>Guru: @uiLabel($nextSession['teacher_label'])</span><span>{{ $nextSession['status_label'] }}</span><a class="wali-work-action" href="{{ route('academic.attendance.show', $nextSession['session']) }}">Lihat Sesi <span aria-hidden="true">→</span></a></div>
                            @else
                                <div class="wali-empty"><strong>Belum ada sesi berikutnya</strong>Tidak ada jadwal mendatang yang tersedia untuk kelas binaan.</div>
                            @endif
                        </section>
                    </div>
                    <p class="wali-analytics-label">Ringkasan dan analitik kelas</p>
                @endif
            </div>
        @endif
        <form class="waka-filter" method="GET" action="{{ route('academic.dashboard') }}">
            <div class="waka-filter-heading"><strong>Periode</strong><small>Pilih bulan penuh atau rentang tanggal manual.</small></div>
            <label>Bulan<select name="month" title="Jika dipilih, periode bulan mengabaikan tanggal manual" aria-describedby="month-filter-help"><option value="">Pilih bulan</option>@foreach ($months as $availableMonth)<option value="{{ $availableMonth->format('Y-m') }}" @selected(($month ?? null) === $availableMonth->format('Y-m'))>{{ $monthLongNames[$availableMonth->format('m')] ?? $availableMonth->format('F') }} {{ $availableMonth->format('Y') }}</option>@endforeach</select><small id="month-filter-help" class="waka-filter-help">Pilih bulan untuk memakai satu bulan penuh; kosongkan untuk rentang tanggal manual.</small></label>
            <label>Mulai<input lang="id" type="date" name="from" value="{{ $from->format('Y-m-d') }}"></label>
            <label>Sampai<input lang="id" type="date" name="to" value="{{ $to->format('Y-m-d') }}"></label>
            <button class="button" type="submit">Terapkan</button>
            <span class="waka-filter-note">{{ $formatDashboardDate($from, $monthShortNames) }}–{{ $formatDashboardDate($to, $monthShortNames) }} · {{ $filterModeLabel === 'Mode bulan penuh' ? 'Bulan penuh' : 'Rentang manual' }}</span>
        </form>
        @if (in_array($dashboard['role'], ['WAKA_AKADEMIK', 'SUPER_ADMIN'], true) && config('academic.ai.assistant_enabled', false))
            <section class="waka-ai-assistant" aria-labelledby="waka-ai-title" data-ai-assistant>
                <div class="waka-ai-heading"><div><h2 id="waka-ai-title">Asisten Akademik</h2><p>Tanyakan ringkasan kehadiran dari data akademik yang tersedia.</p></div><span class="waka-ai-readonly">Baca saja</span></div>
                <form class="waka-ai-form" data-ai-form action="{{ route('academic.ai-assistant.query') }}" method="POST">
                    @csrf
                    <label for="waka-ai-question">Pertanyaan<textarea id="waka-ai-question" name="question" maxlength="4000" rows="2" required placeholder="Contoh: ringkas kelas yang masih memiliki data kehadiran belum lengkap."></textarea></label>
                    <button class="waka-ai-submit" type="submit" data-ai-submit>Tanyakan</button>
                </form>
                <div class="waka-ai-feedback" data-ai-feedback aria-live="polite" aria-atomic="true">
                    <span class="waka-ai-status" data-ai-status hidden></span>
                    <div class="waka-ai-answer" data-ai-answer hidden></div>
                    <div class="waka-ai-warning" data-ai-warning hidden></div>
                    <div class="waka-ai-error" data-ai-error role="alert" hidden></div>
                </div>
            </section>
        @endif
        <section class="waka-grid" aria-label="Ringkasan angka">
            <div class="waka-kpi primary"><div class="waka-kpi-head"><span class="waka-kpi-label">Kehadiran fisik</span><span class="waka-kpi-icon" aria-hidden="true">✓</span></div><strong class="waka-kpi-value">{{ $physicalPresenceRate !== null ? $physicalPresenceRate.'%' : $physicalUnavailableLabel }}</strong><span class="waka-kpi-meta">{{ $physicalPresenceMeta }}</span></div>
            <div class="waka-kpi data-quality"><div class="waka-kpi-head"><span class="waka-kpi-label">Kelengkapan data</span><span class="waka-kpi-icon" aria-hidden="true">▦</span></div><strong class="waka-kpi-value">{{ $completenessRate !== null ? $completenessRate.'%' : $completenessUnavailableLabel }}</strong><span class="waka-kpi-meta">{{ $attendanceEligible > 0 ? $attendanceResolved.' dari '.$attendanceEligible.' data wajib sudah tervalidasi · '.$attendanceMissing.' belum tervalidasi' : 'Belum ada data wajib yang dapat dihitung' }}</span></div>
            <div class="waka-kpi context"><div class="waka-kpi-head"><span class="waka-kpi-label">Santri aktif</span><span class="waka-kpi-icon" aria-hidden="true">◉</span></div><strong class="waka-kpi-value">{{ $overview['active_student_count'] ?? 0 }}</strong><span class="waka-kpi-meta">Dalam kelas yang terpantau</span></div>
            <div class="waka-kpi context blue"><div class="waka-kpi-head"><span class="waka-kpi-label">Guru aktif</span><span class="waka-kpi-icon" aria-hidden="true">◎</span></div><strong class="waka-kpi-value">{{ $overview['active_teacher_count'] ?? 0 }}</strong><span class="waka-kpi-meta">Memiliki tugas mengajar aktif</span></div>
        </section>
        <div class="waka-main-grid">
            <div>
                <section class="waka-card"><div class="waka-card-heading"><div><h2>Status Operasional</h2><p>Ringkasan sesi dan kehadiran guru pada periode terpilih.</p></div><span class="waka-link {{ $todayCompletionRate === null ? 'neutral' : '' }}">{{ $todayCompletionRate !== null ? $todayCompletionRate.'%' : $statusUnavailableLabel }}</span></div><div class="waka-status-grid"><div class="waka-status"><span class="waka-status-title">Pengesahan sesi</span><div class="waka-status-value {{ $todayCompletionRate === null ? 'neutral' : '' }}">{{ $todayCompletionRate !== null ? $todayCompletionRate.'% sesi disahkan' : $statusUnavailableLabel }}</div><span class="waka-status-note">{{ ($todayAttendance['finalized_sessions'] ?? 0).' disahkan · '.($todayAttendance['due_not_finalized_sessions'] ?? 0).' belum disahkan · '.($todayAttendance['in_progress_sessions'] ?? 0).' berlangsung · '.($todayAttendance['upcoming_sessions'] ?? 0).' akan datang' }}</span></div><div class="waka-status"><span class="waka-status-title">Kehadiran guru</span><div class="waka-status-value {{ $teacherPresenceRate === null ? 'neutral' : '' }}">{{ $teacherLabel }}</div><span class="waka-status-note">{{ ($teacherAttendance['resolved_participations'] ?? 0).' dari '.($teacherAttendance['eligible_participations'] ?? 0).' penugasan tercatat · Hadir '.($teacherAttendance['present'] ?? 0).' · Tidak hadir '.($teacherAttendance['absent'] ?? 0).' · Sakit '.($teacherAttendance['sick'] ?? 0).' · Izin '.($teacherAttendance['izin'] ?? 0).' · Lainnya '.($teacherAttendance['other'] ?? 0) }}</span></div></div></section>
                <section class="waka-card" id="pemantauan"><div class="waka-card-heading"><div><h2>Pemantauan Kelas</h2><p>Kelengkapan kehadiran dari data periode terpilih.</p></div><span class="waka-link">{{ $classCount }} kelas</span></div><div class="waka-class-list">
                    @forelse ($classes as $item)
                        @php
                            $rate = is_numeric($item['attendance']['completeness_rate'] ?? null) ? (float) $item['attendance']['completeness_rate'] : null;
                            $eligible = (int) ($item['attendance']['eligible_opportunities'] ?? 0);
                            $resolved = (int) ($item['attendance']['resolved_opportunities'] ?? 0);
                            $missing = max(0, $eligible - $resolved);
                            $className = (string) $item['class']->display_name;
                        @endphp
                        <div class="waka-class-row"><div class="waka-class-row-top"><div class="waka-class-main"><span class="waka-class-name">@uiLabel($className)</span><span class="waka-class-summary">{{ $eligible > 0 ? $resolved.' dari '.$eligible.' tervalidasi' : 'Belum ada data wajib yang dapat dihitung' }}</span><span class="waka-class-missing">{{ $eligible > 0 ? $missing.' belum tervalidasi' : 'Kelengkapan belum dapat dihitung' }}</span></div><span class="waka-class-rate {{ $rate === null ? 'neutral' : '' }}">{{ $rate !== null ? $rate.'%' : 'Belum tersedia' }}</span></div><div class="waka-progress" role="progressbar" aria-label="Kelengkapan kehadiran {{ $className }}" aria-valuemin="0" aria-valuemax="100" @if ($rate !== null) aria-valuenow="{{ $rate }}" @else aria-valuetext="Belum ada data wajib yang dapat dihitung" @endif><span style="--progress:{{ $rate !== null ? $rate : 0 }}%"></span></div></div>
                    @empty
                        <div class="waka-unavailable"><strong>Tidak ada kelas dalam cakupan</strong>Belum ada data kelas yang dapat ditampilkan.</div>
                    @endforelse
                </div></section>
                @if ($dashboard['role'] === 'WALI_KELAS')
                    @php
                        $attendanceFilter = request('attendance_filter', 'all');
                        $attendanceSessions = $dashboard['attendance_sessions'];
                        $attendanceSessionSummary = $dashboard['attendance_session_summary'];
                        $attendanceSessionCounts = [
                            'all' => $attendanceSessionSummary['total'],
                            'empty' => $attendanceSessionSummary['empty'],
                            'incomplete' => $attendanceSessionSummary['incomplete'],
                            'finalized' => $attendanceSessionSummary['finalized'],
                            'upcoming' => $attendanceSessionSummary['upcoming'],
                            'needs_action' => $attendanceSessionSummary['needs_action'],
                        ];
                        if ($attendanceFilter !== 'all') {
                            $attendanceSessions = $attendanceSessions->filter(fn ($session) => match ($attendanceFilter) {
                                'empty' => $session->attendance_label === 'Belum diisi',
                                'incomplete' => $session->attendance_label === 'Belum lengkap',
                                'finalized' => $session->attendance_label === 'Sudah disahkan',
                                'upcoming' => $session->period_state === 'UPCOMING',
                                'needs_action' => $session->needs_action === true,
                                default => true,
                            });
                        }
                        $attendanceFilterLabels = ['all' => 'Semua', 'empty' => 'Belum diisi', 'incomplete' => 'Belum lengkap', 'finalized' => 'Sudah disahkan', 'upcoming' => 'Akan datang', 'needs_action' => 'Perlu ditangani'];
                    @endphp
                    <section class="waka-card" id="pengisian-kehadiran"><div class="waka-card-heading"><div><h2>Riwayat Sesi Periode</h2><p>Daftar ringkas sesi terdahulu dan mendatang pada periode terpilih.</p></div><span class="waka-link">{{ $attendanceSessionSummary['total'] }} sesi</span></div>
                        <div class="waka-session-filters" aria-label="Filter status pengisian"><span>Status:</span>@foreach ($attendanceFilterLabels as $filter => $label)<a class="waka-link {{ $attendanceFilter === $filter ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['attendance_filter' => $filter]) }}">{{ $label }} ({{ $attendanceSessionCounts[$filter] }})</a>@endforeach</div>
                        @if ($attendanceSessions->isEmpty())
                            <div class="waka-unavailable"><strong>Belum ada sesi</strong>Belum ada sesi dalam periode yang dipilih.</div>
                        @else
                            <div class="waka-session-list">
                                @php
                                    $previousSessionDate = null;
                                @endphp
                                @foreach ($attendanceSessions as $attendanceSession)
                                    @php
                                        $attendanceStart = \App\Shared\Platform\Presentation\AcademicBusinessTime::at($attendanceSession->planned_start_at);
                                        $sessionDate = $attendanceStart->toDateString();
                                    @endphp
                                    @if ($sessionDate !== $previousSessionDate)
                                        <div class="waka-session-day">{{ $weekdayNames[$attendanceStart->format('l')] ?? $attendanceStart->format('l') }}, {{ $attendanceStart->format('d/m/Y') }}</div>
                                        @php
                                            $previousSessionDate = $sessionDate;
                                        @endphp
                                    @endif
                                    <a class="waka-session-row" href="{{ route('academic.attendance.show', $attendanceSession) }}"><span><strong>@uiLabel($attendanceSession->teachingAssignment?->subject?->subject_name ?? 'Pelajaran')</strong><small>{{ $attendanceStart->format('d/m/Y, H:i') }} · {{ $attendanceSession->studentParticipants->count() }} santri</small><span class="waka-session-meta"><span class="waka-session-status">{{ $attendanceSession->attendance_label }}</span></span></span><span class="waka-session-action">{{ $attendanceSession->attendance_action }} →</span></a>
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endif
                <section class="waka-card" id="tren-kehadiran"><div class="waka-card-heading"><div><h2>{{ ($dashboard['attendance_trend_source'] ?? 'daily_transactions') === 'monthly_snapshot' ? 'Ringkasan Kehadiran '.($monthLongNames[$from->format('m')] ?? $from->format('F')).' '.$from->format('Y') : 'Tren Kehadiran Santri' }}</h2><p>{{ ($dashboard['attendance_trend_source'] ?? 'daily_transactions') === 'monthly_snapshot' ? 'Persentase kehadiran berdasarkan snapshot rekap bulanan.' : 'Persentase hadir + terlambat per hari pada rentang terpilih.' }}</p></div><span class="waka-link">{{ ($dashboard['attendance_trend_source'] ?? 'daily_transactions') === 'monthly_snapshot' ? '1 bulan' : count($dashboard['attendance_trend'] ?? []).' hari' }}</span></div>
                    @if (($dashboard['attendance_trend_source'] ?? 'daily_transactions') !== 'monthly_snapshot')<div class="waka-trend-options" aria-label="Rentang grafik"><span>Rentang grafik:</span>@foreach ([7 => '7 hari', 14 => '14 hari', 30 => '30 hari'] as $days => $label)<a class="waka-link {{ (int) request('trend_days', 14) === $days ? 'active' : '' }}" href="{{ route('academic.dashboard', array_merge(request()->except('trend_days'), ['trend_days' => $days])) }}">{{ $label }}</a>@endforeach</div>@endif
                    @if (count($dashboard['attendance_trend'] ?? []))
                        <div class="waka-trend" aria-label="Grafik tren kehadiran santri">
                            @foreach ($dashboard['attendance_trend'] as $trend)
                                @php
                                    $trendDate = \Illuminate\Support\Carbon::parse($trend['date']);
                                @endphp
                                @php
                                    $trendEligible = (int) ($trend['eligible_opportunities'] ?? 0);
                                    $trendResolved = (int) ($trend['resolved_opportunities'] ?? 0);
                                    $trendPhysical = $trend['physical_presence_rate'];
                                    $trendCompleteness = $trend['completeness_rate'];
                                    $trendPhysicalAvailable = $trendPhysical !== null;
                                    $trendCompletenessAvailable = $trendCompleteness !== null;
                                    $trendPhysicalLabel = $trendPhysicalAvailable ? $trendPhysical.'%' : 'Belum tersedia';
                                    $trendCompletenessLabel = $trendCompletenessAvailable ? $trendCompleteness.'%' : 'Belum tersedia';
                                @endphp
                                <div class="waka-trend-row"><span class="waka-trend-date">{{ ($dashboard['attendance_trend_source'] ?? 'daily_transactions') === 'monthly_snapshot' ? 'Juli 2026' : $trendDate->format('d').' '.($monthShortNames[$trendDate->format('m')] ?? $trendDate->format('M')) }}</span><div class="waka-trend-metrics"><div class="waka-trend-metric"><span class="waka-trend-metric-label">Kehadiran</span><div class="waka-trend-track {{ $trendPhysicalAvailable ? '' : 'neutral' }}" role="progressbar" aria-label="Tren kehadiran santri {{ $trendDate->format('d/m/Y') }}" aria-valuemin="0" aria-valuemax="100" @if ($trendPhysicalAvailable) aria-valuenow="{{ $trendPhysical }}" @else aria-valuetext="Belum tersedia" @endif><span style="--progress:{{ $trendPhysicalAvailable ? $trendPhysical : 0 }}%"></span></div><span class="waka-trend-value {{ $trendPhysicalAvailable ? '' : 'neutral' }}">{{ $trendPhysicalLabel }}</span></div><div class="waka-trend-metric"><span class="waka-trend-metric-label">Kelengkapan</span><div class="waka-trend-track completeness" role="progressbar" aria-label="Kelengkapan data santri {{ $trendDate->format('d/m/Y') }}" aria-valuemin="0" aria-valuemax="100" @if ($trendCompletenessAvailable) aria-valuenow="{{ $trendCompleteness }}" @else aria-valuetext="Belum tersedia" @endif><span style="--progress:{{ $trendCompletenessAvailable ? $trendCompleteness : 0 }}%"></span></div><span class="waka-trend-value {{ $trendCompletenessAvailable ? '' : 'neutral' }}">{{ $trendCompletenessLabel }}</span></div><span class="waka-trend-detail">{{ $trendEligible > 0 ? $trendResolved.' dari '.$trendEligible.' data tervalidasi · '.max(0, $trendEligible - $trendResolved).' belum tervalidasi' : 'Belum ada data wajib yang dapat dihitung' }}</span></div></div>
                            @endforeach
                        </div>
                    @else
                        <div class="waka-unavailable"><strong>Grafik belum tersedia</strong>Belum ada kesempatan kehadiran santri pada periode ini.</div>
                    @endif
                </section>
            </div>
            <aside class="waka-rail">
                <section class="waka-card"><div class="waka-card-heading"><div><h2>Aksi Cepat</h2><p>Jalur kerja akademik yang tersedia.</p></div></div><div class="waka-quick-actions">@if ($dashboard['role'] === 'WALI_KELAS')<a class="waka-quick-action primary" href="#sesi-hari-ini"><span>Lihat sesi hari ini</span><span aria-hidden="true">↓</span></a>@else<a class="waka-quick-action primary" href="{{ route('academic.attendance.exceptions') }}"><span>Kontrol kehadiran</span><span aria-hidden="true">→</span></a>@endif<a class="waka-quick-action" href="{{ route('academic.monthly-reports.index') }}"><span>Laporan bulanan</span><span aria-hidden="true">→</span></a><a class="waka-quick-action" href="{{ route('academic.dashboard.export', request()->filled('month') ? request()->only(['month', 'semester_id']) : request()->only(['from', 'to', 'semester_id'])) }}"><span>Unduh rekap CSV</span><span aria-hidden="true">↓</span></a></div></section>
            </aside>
        </div>
    </main>
</div>
@if (in_array($dashboard['role'], ['WAKA_AKADEMIK', 'SUPER_ADMIN'], true) && config('academic.ai.assistant_enabled', false))
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('[data-ai-form]');
    if (!form) return;
    const question = form.querySelector('[name="question"]');
    const submit = form.querySelector('[data-ai-submit]');
    const container = form.closest('[data-ai-assistant]');
    const status = container.querySelector('[data-ai-status]');
    const answer = container.querySelector('[data-ai-answer]');
    const warning = container.querySelector('[data-ai-warning]');
    const error = container.querySelector('[data-ai-error]');
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const endpoint = form.getAttribute('action');

    function clearFeedback() {
        answer.hidden = true; answer.textContent = '';
        warning.hidden = true; warning.textContent = '';
        error.hidden = true; error.textContent = '';
        status.hidden = true; status.textContent = '';
    }
    function warningText(items) {
        return (Array.isArray(items) ? items : []).map(function (item) {
            if (typeof item === 'string') return item;
            return item && item.message ? item.message : 'Data memerlukan perhatian.';
        }).join('\n');
    }
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        if (submit.disabled) return;
        clearFeedback();
        submit.disabled = true;
        question.disabled = true;
        status.hidden = false;
        status.textContent = 'Memeriksa data akademik…';
        fetch(endpoint, {method: 'POST', credentials: 'same-origin', headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf}, body: JSON.stringify({question: question.value})})
            .then(function (response) { return response.json().catch(function () { return {}; }).then(function (payload) { return {response: response, payload: payload}; }); })
            .then(function (result) {
                status.hidden = true;
                if (!result.response.ok || result.payload.status !== 'OK') {
                    error.hidden = false;
                    error.textContent = result.response.status === 429 ? 'Batas permintaan tercapai. Silakan coba lagi nanti.' : (result.payload.answer || 'Asisten Akademik belum tersedia.');
                    return;
                }
                answer.hidden = false;
                answer.textContent = result.payload.answer || 'Tidak ada jawaban yang tersedia.';
                const warnings = warningText(result.payload.warnings);
                if (warnings) { warning.hidden = false; warning.textContent = 'Catatan data:\n' + warnings; }
            })
            .catch(function () { status.hidden = true; error.hidden = false; error.textContent = 'Asisten Akademik belum tersedia. Periksa koneksi lalu coba lagi.'; })
            .finally(function () { submit.disabled = false; question.disabled = false; });
    });
});
</script>
@endif
</body>
</html>
