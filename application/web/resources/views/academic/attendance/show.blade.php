<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kehadiran {{ $attendanceScope['class_label'] ?? ($session->academicClass?->display_name ?? 'Santri') }}</title>
    <style>@include('academic.partials.sidebar-styles') .attendance-meta{display:flex;flex-wrap:wrap;gap:.65rem 1.2rem;color:#527066}.attendance-meta span{display:inline-flex;gap:.3rem}.summary{display:grid;grid-template-columns:repeat(auto-fit,minmax(9rem,1fr));gap:.75rem;margin-bottom:1rem}.summary-card{background:#fff;border:1px solid #dce8e1;border-radius:.8rem;padding:.85rem}.summary-card strong{display:block;font-size:1.5rem;color:#176b4d}.attendance-table{min-width:48rem}.attendance-table select,.attendance-table textarea{border:1px solid #bfd3c7;border-radius:.5rem;padding:.55rem;font:inherit;background:#fff}.attendance-table textarea{min-height:2.5rem;resize:vertical}.attendance-table select:focus,.attendance-table textarea:focus{outline:3px solid #cce8d8;border-color:#176b4d}.workflow{color:#6d8279;font-size:.85rem}.button-row{display:flex;flex-wrap:wrap;gap:.65rem;align-items:center;margin-top:1rem}.button-row button{border:0;border-radius:.55rem;padding:.7rem 1rem;font:inherit;font-weight:700;cursor:pointer;background:#176b45;color:#fff}.button-row button.secondary{background:#e5efe9;color:#17352b}.info{background:#f3f8f5;border:1px solid #dce8e1;border-radius:.65rem;padding:.75rem;margin-bottom:1rem}.substitution-form{display:grid;gap:.75rem}.substitution-form label{display:grid;gap:.35rem;color:#345b4e;font-weight:700}.substitution-form select,.substitution-form textarea{width:100%;box-sizing:border-box;border:1px solid #bfd3c7;border-radius:.5rem;padding:.7rem;font:inherit;background:#fff}.substitution-form textarea{min-height:4.2rem;resize:vertical}.substitution-form select:focus,.substitution-form textarea:focus{outline:3px solid #cce8d8;border-color:#176b4d}.substitution-form .button{justify-self:start}@media(max-width:640px){.button-row button{width:100%}.substitution-form .button{width:100%}}</style>
    <style>.cancellation-form{border-color:#efd7bd;background:#fffaf4}.cancellation-form textarea{width:100%;box-sizing:border-box;border:1px solid #d9b995;border-radius:.5rem;padding:.7rem;font:inherit;min-height:4.2rem;resize:vertical}.cancellation-form .button{justify-self:start;background:#a94b1b}@media(max-width:640px){.cancellation-form .button{width:100%}}</style>
    <style>
        .attendance-page{max-width:1120px;margin:0 auto;width:100%;min-width:0;overflow-x:hidden}
        .attendance-page,.session-header,.attendance-card,.info,.substitution-form,.table-wrap{min-width:0;max-width:100%;box-sizing:border-box}
        .attendance-page .card{box-shadow:0 10px 28px rgba(20,77,57,.05)}
        .session-header{padding:1.35rem 1.5rem;margin-bottom:1rem}
        .session-header h1{margin:.2rem 0 .85rem;font-size:clamp(1.55rem,3vw,2.25rem)}
        .attendance-back-link{display:inline-flex;margin-bottom:.85rem;color:#176b4d;font-weight:800;text-decoration:none}
        .attendance-back-link:hover,.attendance-back-link:focus-visible{text-decoration:underline}
        .session-header .eyebrow{margin:0;color:#176b4d}
        .session-header h1,.attendance-meta span,.info,.substitution-form{overflow-wrap:anywhere}
        .attendance-card{padding:1.25rem}
        .attendance-card>.info{margin-bottom:1.15rem}
        .summary-card{min-height:4.6rem;display:flex;flex-direction:column;justify-content:space-between}
        .summary-card .muted{font-size:.88rem}
        .attendance-table th{white-space:nowrap}
        .attendance-table td{vertical-align:top;padding:1rem .7rem}
        .attendance-table td:first-child{min-width:12rem}
        .attendance-table td:nth-child(2){min-width:8rem}
        .attendance-table td:nth-child(3){min-width:13rem}
        .attendance-table td:nth-child(4){min-width:15rem}
        .attendance-table td:nth-child(5){min-width:8rem}
        .attendance-table select,.attendance-table textarea{width:100%;box-sizing:border-box}
        .attendance-table textarea{margin-top:.45rem}
        .grooming-details{min-width:0}
        .grooming-details summary{display:inline-flex;align-items:center;gap:.35rem;cursor:pointer;color:#176b4d;font-weight:800;list-style:none}
        .grooming-details summary::-webkit-details-marker{display:none}
        .grooming-details summary::before{content:'+';display:inline-grid;place-items:center;width:1.25rem;height:1.25rem;border:1px solid #b9dac7;border-radius:50%;font-size:1rem;line-height:1}
        .grooming-details[open] summary::before{content:'−'}
        .grooming-details[open] summary{margin-bottom:.45rem}
        .grooming-fields{display:grid;gap:.45rem}
        .grooming-fields select,.grooming-fields textarea{width:100%;box-sizing:border-box;border:1px solid #bfd3c7;border-radius:.5rem;padding:.55rem;font:inherit;background:#fff}
        .grooming-fields textarea{min-height:2.5rem;resize:vertical}
        .attendance-workflow-status{display:inline-flex;align-items:center;margin-top:.4rem;padding:.18rem .5rem;border:1px solid #dce8e1;border-radius:999px;color:#6d8279;font-size:.76rem;font-weight:700;line-height:1.2}
        .attendance-page .table-wrap{width:100%;overflow-x:auto;overscroll-behavior-x:contain;-webkit-overflow-scrolling:touch}
        .results-table td{min-width:0!important}
        .attendance-card>.button-row + .table-wrap{margin-top:1rem}
        .attendance-actions{display:flex;flex-wrap:wrap;gap:.65rem;align-items:center;padding:.2rem 0 .55rem}
        .attendance-actions .muted{flex:1 1 18rem}
        .substitution-form,.cancellation-form{padding:1.15rem 1.2rem}
        .correction-panel{border-color:#c9dced;background:#f6faff;min-width:0;overflow:hidden}.correction-form{display:grid;gap:.5rem;min-width:0;padding:.8rem 0;border-top:1px solid #d8e6f0}.correction-form label{display:grid;gap:.3rem;min-width:0}.correction-form select,.correction-form textarea{width:100%;box-sizing:border-box;border:1px solid #bfd3c7;border-radius:.5rem;padding:.65rem;font:inherit;background:#fff;min-width:0}.correction-form textarea{min-height:3.2rem;resize:vertical}.correction-form .button{justify-self:start}
        @media(max-width:1100px){
            .attendance-page{max-width:none}
            .session-header,.attendance-card{padding:1.1rem}
            .summary{grid-template-columns:repeat(2,minmax(0,1fr))}
            .attendance-meta{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.55rem .9rem}
            .attendance-meta span{min-width:0;display:block;overflow-wrap:anywhere}
            .results-table{min-width:0;width:100%}
            .results-table thead{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
            .results-table tbody{display:grid;gap:.75rem}
            .results-table tr{display:block;border:1px solid #dce8e1;border-radius:.75rem;background:#fbfefc;padding:.45rem .8rem}
            .results-table td{display:grid;grid-template-columns:minmax(9rem,32%) minmax(0,1fr);gap:.75rem;padding:.65rem .1rem;border-bottom:1px solid #e4eee8}
            .results-table td:last-child{border-bottom:0}
            .results-table td::before{content:attr(data-label);color:#6b8177;font-size:.72rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
            .results-table td:first-child{min-width:0!important}
            .input-table{min-width:0;width:100%}
            .input-table thead{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
            .input-table tbody{display:grid;gap:.75rem}
            .input-table tr{display:block;border:1px solid #dce8e1;border-radius:.75rem;background:#fbfefc;padding:.45rem .8rem}
            .input-table td{display:grid;grid-template-columns:minmax(9rem,32%) minmax(0,1fr);gap:.75rem;align-items:start;min-width:0!important;padding:.65rem .1rem;border-bottom:1px solid #e4eee8}
            .input-table td:last-child{border-bottom:0}
            .input-table td::before{content:attr(data-label);color:#6b8177;font-size:.72rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
            .input-table td:first-child{min-width:0!important}
            .input-table select,.input-table textarea{min-width:0}
        }
        @media(max-width:680px){
            .attendance-page{max-width:none}
            .session-header,.attendance-card{padding:1rem}
            .session-header h1{font-size:1.5rem}
        .attendance-meta{flex-direction:column;gap:.45rem}
            .attendance-meta span{display:flex;min-width:0}
            .summary{grid-template-columns:1fr}
            .attendance-actions .muted{flex-basis:100%}
            .attendance-meta{display:flex;flex-direction:column;gap:.45rem}
            .results-table td{grid-template-columns:1fr;gap:.2rem}
            .results-table td::before{font-size:.68rem}
            .input-table td{grid-template-columns:1fr;gap:.2rem}
            .input-table td::before{font-size:.68rem}
        }
        @media(max-width:900px){
            .attendance-page{max-width:none}
            .session-header,.attendance-card{padding:.95rem;box-sizing:border-box;width:100%;max-width:100%}
            .session-header h1{font-size:clamp(1.35rem,5vw,1.8rem)}
            .attendance-meta{gap:.5rem}
            .attendance-meta span{min-width:0;display:flex;flex-wrap:wrap}
            .attendance-card>.info{padding:.8rem}
            .substitution-form{padding:1rem}
            .correction-panel{padding:.8rem}
        }
        @media(max-width:680px){
            .waka-content.attendance-page{margin-left:4.4rem;padding-left:1.1rem;padding-right:.8rem;overflow-x:hidden}
            .session-header,.attendance-card{width:100%;max-width:100%}
            .attendance-meta span{width:100%;display:flex;flex-wrap:wrap}
            .attendance-back-link{max-width:100%;line-height:1.35}
            .correction-form{padding:.9rem 0;gap:.65rem}
            .correction-form .button{width:100%;justify-self:stretch}
        }
        .session-header .attendance-meta span:first-child{font-size:1.05rem;color:#17352b}
        .session-header .attendance-meta span:nth-child(2){font-weight:700;color:#345b4e}
        .summary{grid-template-columns:repeat(4,minmax(0,1fr));gap:.55rem;margin-bottom:.85rem}
        .summary-card{min-height:3.65rem;padding:.65rem .75rem;border-radius:.65rem}
        .summary-card strong{font-size:1.3rem}
        .summary-card .muted{font-size:.78rem;line-height:1.2}
        .teacher-attendance-form{padding:.55rem 0;gap:.35rem}
        .teacher-attendance-form label{margin-top:.45rem}
        .teacher-attendance-form textarea{min-height:2.8rem}
        .session-actions{margin:0 0 1rem;border:1px solid #dce8e1;border-radius:.7rem;background:#fbfdfc}
        .session-actions summary{padding:.75rem 1rem;color:#345b4e;font-weight:800;cursor:pointer;list-style-position:inside}
        .session-actions[open] summary{border-bottom:1px solid #dce8e1}
        .session-actions .substitution-form,.session-actions .cancellation-form{margin:0!important;border:0;border-radius:0;box-shadow:none}
        .session-actions .cancellation-form{border-top:1px solid #efd7bd!important}
        .attendance-section-title{margin:1rem 0 .65rem;font-size:1.25rem}
        @media(max-width:900px){.summary{grid-template-columns:repeat(4,minmax(0,1fr))}.summary-card{padding:.55rem}.summary-card strong{font-size:1.15rem}}
        @media(max-width:680px){.summary{grid-template-columns:repeat(2,minmax(0,1fr));gap:.5rem}.summary-card{min-height:3.35rem}.session-header .attendance-meta span:first-child{font-size:.98rem}}
    </style>
    <style>
        .attendance-toast{position:fixed;right:1.25rem;bottom:1.25rem;z-index:30;display:flex;align-items:flex-start;gap:.7rem;max-width:min(28rem,calc(100vw - 2rem));padding:.85rem 1rem;border:1px solid #a9d8bb;border-radius:.75rem;background:#effaf3;color:#174d38;box-shadow:0 12px 28px rgba(20,77,57,.18);animation:attendance-toast-in .2s ease-out}
        .attendance-toast-icon{display:grid;place-items:center;flex:none;width:1.45rem;height:1.45rem;border-radius:50%;background:#176b4d;color:#fff;font-size:.85rem;font-weight:900}
        .attendance-toast-message{line-height:1.4}.attendance-toast-close{flex:none;border:0;background:transparent;color:#176b4d;padding:0 .1rem;font:inherit;font-size:1.2rem;line-height:1;cursor:pointer}.attendance-toast-close:hover,.attendance-toast-close:focus-visible{color:#0d573a}
        @keyframes attendance-toast-in{from{opacity:0;transform:translateY(.5rem)}to{opacity:1;transform:translateY(0)}}
        @media(max-width:640px){.attendance-toast{right:.8rem;bottom:.8rem;left:4.9rem;max-width:none}}
    </style>
    <style>
        .attendance-page{color:#243b33}
        .attendance-page .card{box-shadow:none;border-color:#e2ebe6;border-radius:.85rem}
        .attendance-page .session-header{background:#fff;padding:1.25rem 1.4rem}
        .attendance-page .session-header h1{letter-spacing:-.035em;color:#123d30}
        .attendance-page .session-header .eyebrow{letter-spacing:.12em;font-size:.7rem}
        .attendance-page .attendance-meta{color:#62766e;gap:.45rem 1rem;font-size:.9rem}
        .attendance-page .joint-scope-summary{display:grid;gap:.35rem;margin-top:.45rem;color:#62766e;font-size:.78rem;line-height:1.4}
        .attendance-page .joint-scope-summary strong{color:#176b4d;font-size:.85rem}
        .attendance-page .attendance-class-badge{display:inline-flex;width:max-content;max-width:100%;margin-top:.4rem;padding:.16rem .45rem;border:1px solid #cfe1d7;border-radius:999px;background:#f3f8f5;color:#176b4d;font-size:.68rem;font-weight:800;line-height:1.2}
        .attendance-page .attendance-class-badge--unmapped{border-color:#e8d6a8;background:#fff9e9;color:#8a6511}
        .attendance-page .attendance-meta span:first-child{color:#243b33}
        .attendance-page .summary-card{background:#fff;border-color:#e2ebe6;border-radius:.7rem;padding:.6rem .75rem;min-height:3.45rem}
        .attendance-page .summary-card strong{color:#176b4d;font-size:1.35rem;line-height:1.05}
        .attendance-page .summary-card .muted{color:#71827b;font-size:.76rem}
        .attendance-page .attendance-card{padding:1.15rem;background:#fff}
        .attendance-page .info{background:#f7faf8;border-color:#e5ede8;color:#526a60}
        .attendance-page .button-row>button,.attendance-page .button-row>.button{border-radius:.55rem!important;box-shadow:none!important;transition:background-color .15s ease,border-color .15s ease,color .15s ease,transform .15s ease!important}
        .attendance-page .button-row>button:not(.secondary),.attendance-page .button-row>.button{background:#176b4d!important;color:#fff!important}
        .attendance-page .button-row>button:not(.secondary):hover,.attendance-page .button-row>button:not(.secondary):focus-visible,.attendance-page .button-row>.button:hover,.attendance-page .button-row>.button:focus-visible{background:#0f6044!important;transform:translateY(-1px)}
        .attendance-page .button-row>button.secondary{background:#edf5f0!important;color:#176b4d!important;border-color:#cfe1d7!important}
        .attendance-page .button-row>button.secondary:hover,.attendance-page .button-row>button.secondary:focus-visible{background:#e1efe7!important}
        .attendance-page .cancellation-form .button{background:#a94b1b!important}
        .attendance-page .session-actions{border-color:#e2ebe6;background:#fafcfb}
        .attendance-page .input-table tr{background:transparent;border-color:#e5ede8}
        .attendance-page .input-table tr:hover{background:#f8fbf9}
        .attendance-page .input-table td{padding:.75rem .65rem;border-bottom-color:#edf2ef}
        .attendance-page .input-table select,.attendance-page .input-table textarea,.attendance-page .substitution-form select,.attendance-page .substitution-form textarea{border-color:#d2e0d8;border-radius:.5rem;background:#fcfefd;color:#243b33}
        .attendance-page .input-table select:focus,.attendance-page .input-table textarea:focus,.attendance-page .substitution-form select:focus,.attendance-page .substitution-form textarea:focus{outline:3px solid #d9eee1;border-color:#176b4d}
        .attendance-page .grooming-details summary{border-radius:.35rem;padding:.2rem .3rem;margin:-.2rem -.3rem;color:#176b4d}
        .attendance-page .grooming-details summary:hover,.attendance-page .grooming-details summary:focus-visible{background:#edf6f0;outline:2px solid #b9dac7;outline-offset:1px}
        .attendance-page .attendance-workflow-status{background:#f3f7f5;border-color:#dfe9e3;color:#63776e}
        .attendance-page .attendance-note-details{min-width:0}
        .attendance-page .attendance-note-details summary{display:inline-flex;align-items:center;gap:.35rem;padding:.2rem .3rem;margin:-.2rem -.3rem;border-radius:.35rem;color:#60776d;font-size:.86rem;font-weight:700;cursor:pointer;list-style:none}
        .attendance-page .attendance-note-details summary::-webkit-details-marker{display:none}
        .attendance-page .attendance-note-details summary::before{content:'+';display:inline-grid;place-items:center;width:1.15rem;height:1.15rem;border:1px solid #cfe1d7;border-radius:50%;font-size:.9rem;line-height:1}
        .attendance-page .attendance-note-details[open] summary::before{content:'−'}
        .attendance-page .attendance-note-details[open] summary{margin-bottom:.4rem;color:#176b4d}
        .attendance-page .attendance-note-details summary:hover,.attendance-page .attendance-note-details summary:focus-visible{background:#edf6f0;outline:2px solid #b9dac7;outline-offset:1px}
        .attendance-page .attendance-note-details textarea{margin-top:0}
        .attendance-page .attendance-action-bar{position:sticky;bottom:.75rem;z-index:4;display:flex;align-items:center;justify-content:space-between;gap:1rem;margin:1.25rem 0 0;padding:.75rem .9rem;border:1px solid #dce8e1;border-radius:.7rem;background:rgba(255,255,255,.98);box-shadow:0 8px 20px rgba(25,65,49,.1)}
        .attendance-page .attendance-action-copy{min-width:0;display:grid;gap:.2rem}
        .attendance-page .attendance-action-progress{display:flex;flex-wrap:wrap;align-items:center;gap:.35rem .65rem;color:#526a60;font-size:.82rem}
        .attendance-page .attendance-action-progress strong{color:#243b33;font-size:.9rem}
        .attendance-page .attendance-action-status{color:#71827b;font-size:.78rem;line-height:1.35}
        .attendance-page .attendance-action-status[data-state="ready"]{color:#176b4d;font-weight:800}
        .attendance-page .attendance-action-status[data-state="blocked"]{color:#8a6511}
        .attendance-page .attendance-action-group{display:flex;align-items:center;gap:.55rem;flex:none}
        .attendance-page .attendance-action-bar button:disabled{cursor:not-allowed;opacity:.62;transform:none!important;box-shadow:none!important;background:#d7e4dc!important;color:#526a60!important;border-color:#c4d4ca!important}
        @media(max-width:680px){.attendance-page .attendance-action-bar{align-items:stretch;flex-direction:column;gap:.55rem;padding:.7rem}.attendance-page .attendance-action-group{width:100%}.attendance-page .attendance-action-group button{flex:1;width:100%}}
        .attendance-page a:focus-visible{outline:3px solid #b9dac7;outline-offset:2px;border-radius:.25rem}
        .attendance-page .session-header{padding:1.35rem 1.5rem;background:#fff}
        .attendance-page .session-header h1{margin:.15rem 0 .55rem;font-size:clamp(1.7rem,3vw,2.35rem);color:#123d30}
        .attendance-page .attendance-back-link{margin-bottom:1.15rem;font-size:.92rem}
        .attendance-page .hero-meta-grid{display:grid;grid-template-columns:1.35fr 1.15fr 1.2fr 1.1fr auto;gap:0;margin-top:.15rem;padding-top:1rem;border-top:1px solid #edf2ef}
        .attendance-page .hero-meta-block{min-width:0;padding:0 1rem;border-left:1px solid #edf2ef}
        .attendance-page .hero-meta-block:first-child{padding-left:0;border-left:0}
        .attendance-page .hero-meta-label{display:block;margin-bottom:.28rem;color:#789087;font-size:.7rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
        .attendance-page .hero-meta-value{display:block;color:#243b33;font-size:.92rem;font-weight:700;overflow-wrap:anywhere}
        .attendance-page .hero-meta-secondary{display:block;margin-top:.18rem;color:#71827b;font-size:.78rem;font-weight:500}
        .attendance-page .hero-status-badge{display:inline-flex;align-items:center;min-height:1.7rem;padding:.22rem .65rem;border:1px solid #cfe1d7;border-radius:999px;background:#edf6f0;color:#176b4d;font-size:.78rem;font-weight:800;white-space:nowrap}
        @media(max-width:1000px){.attendance-page .hero-meta-grid{grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem 0}.attendance-page .hero-meta-block:nth-child(4){padding-left:0;border-left:0}}
        @media(max-width:680px){.attendance-page .session-header{padding:1rem}.attendance-page .hero-meta-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:.8rem 0;padding-top:.85rem}.attendance-page .hero-meta-block{padding:0 .7rem}.attendance-page .hero-meta-block:nth-child(odd){padding-left:0;border-left:0}.attendance-page .hero-meta-block:nth-child(even){padding-right:0}.attendance-page .hero-meta-label{font-size:.66rem}.attendance-page .hero-meta-value{font-size:.86rem}.attendance-page .hero-status-badge{font-size:.72rem}}
        .attendance-page .summary{grid-template-columns:repeat(8,minmax(0,1fr));gap:.65rem;margin-bottom:1rem}
        .attendance-page .summary-card{position:relative;min-height:4.25rem;justify-content:flex-start;gap:.25rem;padding:.7rem .75rem .65rem;border-radius:.75rem;overflow:hidden}
        .attendance-page .summary-card::before{content:"";position:absolute;inset:0 auto 0 0;width:.22rem;background:var(--summary-accent,#176b4d)}
        .attendance-page .summary-accent{display:inline-grid;place-items:center;width:1.35rem;height:1.35rem;margin-bottom:.05rem;border:1px solid color-mix(in srgb,var(--summary-accent,#176b4d) 28%,#fff);border-radius:.42rem;background:color-mix(in srgb,var(--summary-accent,#176b4d) 10%,#fff);color:var(--summary-accent,#176b4d);font-size:.82rem;font-weight:900;line-height:1}
        .attendance-page .summary-card .muted{color:#71827b;font-size:.74rem;line-height:1.15}
        .attendance-page .summary-card strong{color:#243b33;font-size:1.55rem;line-height:1;font-variant-numeric:tabular-nums}
        .attendance-page .summary-card--total{--summary-accent:#176b4d}.attendance-page .summary-card--present{--summary-accent:#16805a}.attendance-page .summary-card--late{--summary-accent:#b57618}.attendance-page .summary-card--sick{--summary-accent:#b55368}.attendance-page .summary-card--izin{--summary-accent:#3978aa}.attendance-page .summary-card--excused{--summary-accent:#7654a6}.attendance-page .summary-card--absent{--summary-accent:#b85d2a}.attendance-page .summary-card--pending{--summary-accent:#687d76}
        .attendance-page .summary-card--pending{border-color:#cbdcd2;background:#fbfdfc}.attendance-page .summary-card--pending-active{border-color:#a9cbbb;background:#f5faf7}
        @media(max-width:1100px){.attendance-page .summary{grid-template-columns:repeat(4,minmax(0,1fr))}}
        @media(max-width:680px){.attendance-page .summary{grid-template-columns:repeat(2,minmax(0,1fr));gap:.5rem}.attendance-page .summary-card{min-height:3.65rem;padding:.58rem .65rem .6rem}.attendance-page .summary-card strong{font-size:1.35rem}}
        .attendance-page .roster-toolbar{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin:0 0 .65rem}
        .attendance-page .roster-toolbar .attendance-actions{flex:1;min-width:0;padding:0}
        .attendance-page .roster-search{display:grid;gap:.25rem;flex:0 1 18rem;min-width:12rem}
        .attendance-page .roster-search label{color:#63776e;font-size:.7rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
        .attendance-page .roster-search-control{display:flex;align-items:center;gap:.45rem;border:1px solid #d2e0d8;border-radius:.5rem;background:#fcfefd;padding:0 .65rem;color:#63776e}
        .attendance-page .roster-search-control span{font-size:.95rem;line-height:1}
        .attendance-page .roster-search-control input{width:100%;min-width:0;border:0;outline:0;padding:.58rem 0;background:transparent;color:#243b33;font:inherit}
        .attendance-page .roster-search-control:focus-within{outline:3px solid #d9eee1;border-color:#176b4d}
        .attendance-page .roster-empty-state{margin:.7rem 0 0;padding:.75rem;border:1px dashed #cbdcd2;border-radius:.55rem;background:#fbfdfc;color:#63776e;text-align:center;font-size:.86rem}
        .attendance-page [data-student-row][hidden]{display:none}
        @media(max-width:680px){.attendance-page .roster-toolbar{align-items:stretch;flex-direction:column;gap:.65rem}.attendance-page .roster-search{flex-basis:auto;width:100%}.attendance-page .roster-search-control{min-height:2.55rem}}
        .attendance-page .teacher-attendance-section{margin-bottom:1rem;padding:1.15rem 1.2rem}
        .attendance-page .teacher-attendance-section>h2{margin:0 0 .3rem}
        .attendance-page .teacher-attendance-section>.muted{margin:0 0 .85rem;font-size:.86rem;line-height:1.45}
        .attendance-page .teacher-attendance-row{display:grid;grid-template-columns:minmax(12rem,1.15fr) minmax(9rem,.8fr) minmax(14rem,1.55fr) auto;gap:1rem;align-items:end;padding:1rem 0;border-top:1px solid #edf2ef}
        .attendance-page .teacher-identity{display:flex;align-items:center;gap:.7rem;min-width:0;align-self:center}
        .attendance-page .teacher-avatar{display:grid;place-items:center;flex:none;width:2.5rem;height:2.5rem;border:1px solid #cfe1d7;border-radius:50%;background:#edf6f0;color:#176b4d;font-size:.82rem;font-weight:900;letter-spacing:.03em}
        .attendance-page .teacher-identity-copy{display:grid;gap:.18rem;min-width:0}
        .attendance-page .teacher-name{color:#243b33;font-size:.95rem;font-weight:800;overflow-wrap:anywhere}
        .attendance-page .teacher-role{display:inline-flex;width:max-content;max-width:100%;padding:.18rem .48rem;border:1px solid #d7e6dd;border-radius:999px;background:#f5faf7;color:#63776e;font-size:.68rem;font-weight:800;line-height:1.15}
        .attendance-page .teacher-attendance-field{display:grid;gap:.3rem;min-width:0}
        .attendance-page .teacher-attendance-field>span{color:#63776e;font-size:.7rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
        .attendance-page .teacher-attendance-field select,.attendance-page .teacher-attendance-field textarea{width:100%;box-sizing:border-box;border:1px solid #d2e0d8;border-radius:.5rem;padding:.62rem;font:inherit;background:#fcfefd;color:#243b33}
        .attendance-page .teacher-attendance-field textarea{min-height:2.65rem;resize:vertical}
        .attendance-page .teacher-attendance-field select:focus,.attendance-page .teacher-attendance-field textarea:focus{outline:3px solid #d9eee1;border-color:#176b4d}
        .attendance-page .teacher-attendance-row .button{white-space:nowrap;align-self:end}
        .attendance-page .teacher-attendance-readonly{display:grid;gap:.7rem;margin-top:1rem;padding:1rem 1.15rem;border:1px solid #e2ebe6;border-radius:.75rem;background:#fbfdfc}
        .attendance-page .teacher-attendance-readonly h2{margin:0;font-size:1.15rem}
        .attendance-page .teacher-readonly-row{display:grid;grid-template-columns:minmax(12rem,1.3fr) minmax(9rem,.8fr) minmax(12rem,1.4fr);gap:1rem;align-items:center;padding-top:.75rem;border-top:1px solid #edf2ef}
        .attendance-page .teacher-readonly-value{color:#243b33;font-size:.88rem;font-weight:700;overflow-wrap:anywhere}
        .attendance-page .teacher-readonly-label{display:block;margin-bottom:.18rem;color:#71827b;font-size:.68rem;font-weight:800;letter-spacing:.05em;text-transform:uppercase}
        .attendance-page .teacher-status{display:inline-flex;width:max-content;padding:.25rem .55rem;border:1px solid #d7e6dd;border-radius:999px;background:#f3f7f5;color:#63776e;font-size:.75rem;font-weight:800}
        @media(max-width:900px){.attendance-page .teacher-attendance-row{grid-template-columns:minmax(12rem,1fr) minmax(9rem,1fr);gap:.75rem}.attendance-page .teacher-attendance-row .teacher-identity{grid-column:1/-1}.attendance-page .teacher-attendance-field--note{grid-column:1/-1}.attendance-page .teacher-attendance-row .button{justify-self:start}.attendance-page .teacher-readonly-row{grid-template-columns:repeat(2,minmax(0,1fr))}.attendance-page .teacher-readonly-row>div:last-child{grid-column:1/-1}}
        @media(max-width:680px){.attendance-page .teacher-attendance-section{padding:1rem}.attendance-page .teacher-attendance-row{grid-template-columns:1fr;gap:.7rem}.attendance-page .teacher-attendance-row .teacher-identity,.attendance-page .teacher-attendance-field--note{grid-column:auto}.attendance-page .teacher-attendance-row .button{width:100%;justify-self:stretch}.attendance-page .teacher-readonly-row{grid-template-columns:1fr;gap:.65rem}.attendance-page .teacher-readonly-row>div:last-child{grid-column:auto}}
        @media(max-width:1100px){.attendance-page .teacher-attendance-row{grid-template-columns:minmax(12rem,1fr) minmax(9rem,1fr);gap:.75rem}.attendance-page .teacher-attendance-row .teacher-identity{grid-column:1/-1}.attendance-page .teacher-attendance-field--note{grid-column:1/-1}.attendance-page .attendance-action-bar{flex-wrap:wrap}.attendance-page .attendance-action-copy{flex:1 1 18rem}.attendance-page .attendance-action-group{margin-left:auto}}
        @media(max-width:680px){.attendance-page .teacher-attendance-row{grid-template-columns:1fr}.attendance-page .attendance-action-group{margin-left:0}}
        .attendance-page .top-attendance-actions{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin:0 0 .7rem;padding:.25rem 0 .7rem;border-bottom:1px solid #edf2ef}
        .attendance-page .top-attendance-bulk{display:flex;align-items:center;gap:.65rem;min-width:0}
        .attendance-page .top-attendance-bulk .muted{font-size:.78rem;line-height:1.3}
        .attendance-page .top-attendance-utility{display:flex;align-items:end;gap:.5rem;flex:0 1 auto}
        .attendance-page .top-attendance-utility .roster-search{flex-basis:14rem;min-width:11rem}
        .attendance-page .top-attendance-utility button{min-height:2.55rem;white-space:nowrap}
        .attendance-page .top-attendance-utility button.secondary{background:#f3f8f5!important;border-color:#a9cbbb!important;color:#176b4d!important}
        .attendance-page .top-attendance-utility button:not(.secondary){background:#176b4d!important;color:#fff!important}
        .attendance-page .top-attendance-utility button:disabled{opacity:1!important;background:#dcebe3!important;border-color:#c1d8ca!important;color:#5d756a!important}
        @media(max-width:1100px){.attendance-page .top-attendance-actions{align-items:stretch;flex-wrap:wrap}.attendance-page .top-attendance-bulk{flex:1 1 100%}.attendance-page .top-attendance-utility{width:100%;justify-content:flex-end}.attendance-page .top-attendance-utility .roster-search{flex:1 1 14rem}}
        @media(max-width:680px){.attendance-page .top-attendance-actions{gap:.6rem}.attendance-page .top-attendance-bulk{align-items:flex-start;flex-direction:column;gap:.35rem}.attendance-page .top-attendance-utility{align-items:stretch;flex-wrap:wrap}.attendance-page .top-attendance-utility .roster-search{flex-basis:100%;order:-1}.attendance-page .top-attendance-utility button{flex:1;min-width:0}}
        .attendance-page .top-attendance-actions button{appearance:none;-webkit-appearance:none;min-height:2.55rem;padding:.58rem .9rem;border:1px solid transparent;border-radius:.65rem;font:inherit;font-size:.84rem;font-weight:800;line-height:1.2;cursor:pointer;transition:background-color .15s ease,border-color .15s ease,color .15s ease,transform .15s ease,box-shadow .15s ease}
        .attendance-page .top-attendance-bulk button{flex:none;background:#e8f5ed;color:#176b4d;border-color:#a9cfb8;box-shadow:0 1px 2px rgba(25,65,49,.06)}
        .attendance-page .top-attendance-bulk button:hover,.attendance-page .top-attendance-bulk button:focus-visible{background:#dcefe3;border-color:#86b79d;transform:translateY(-1px);box-shadow:0 4px 10px rgba(25,65,49,.1)}
        .attendance-page .top-attendance-utility button.secondary{background:#f3f8f5!important;color:#176b4d!important;border-color:#a9cbbb!important;box-shadow:0 1px 2px rgba(25,65,49,.06)}
        .attendance-page .top-attendance-utility button.secondary:hover,.attendance-page .top-attendance-utility button.secondary:focus-visible{background:#e7f3ec!important;border-color:#86b79d!important;transform:translateY(-1px);box-shadow:0 4px 10px rgba(25,65,49,.1)}
        .attendance-page .top-attendance-utility button:not(.secondary){background:#176b4d!important;color:#fff!important;border-color:#0f6044!important;box-shadow:0 3px 8px rgba(25,65,49,.12)}
        .attendance-page .top-attendance-utility button:not(.secondary):hover,.attendance-page .top-attendance-utility button:not(.secondary):focus-visible{background:#0f6044!important;transform:translateY(-1px);box-shadow:0 5px 12px rgba(25,65,49,.16)}
        .attendance-page .top-attendance-actions button:focus-visible{outline:3px solid #b9dac7;outline-offset:2px}
        .attendance-page .top-attendance-actions button:disabled{opacity:1!important;background:#dcebe3!important;color:#5d756a!important;border-color:#c1d8ca!important;box-shadow:none!important;cursor:not-allowed;transform:none}
        .attendance-page .top-attendance-utility .roster-search{flex-basis:13.5rem}
        @media(max-width:680px){.attendance-page .top-attendance-utility .roster-search{flex-basis:100%}}
        .attendance-page .attendance-action-bar{border:0;border-top:1px solid #dfeae3;border-radius:.65rem;background:rgba(255,255,255,.98);box-shadow:0 8px 20px rgba(25,65,49,.08);padding:.8rem 1rem;scroll-margin-bottom:5rem}
        .attendance-page .attendance-action-copy{display:grid;grid-template-columns:auto minmax(0,1fr);column-gap:.7rem;align-items:center}
        .attendance-page .attendance-progress-meter{--attendance-progress:0%;display:grid;place-items:center;width:2.35rem;height:2.35rem;border-radius:50%;background:conic-gradient(#1b8a62 var(--attendance-progress),#e8f0eb 0);position:relative;color:#176b4d;font-size:.58rem;font-weight:900;line-height:1}
        .attendance-page .attendance-progress-meter::before{content:"";position:absolute;inset:.25rem;border-radius:50%;background:#fff}
        .attendance-page .attendance-progress-meter span{position:relative;z-index:1}
        .attendance-page .attendance-action-progress{display:grid;gap:.08rem;align-items:start}
        .attendance-page .attendance-action-progress strong{font-size:.92rem}
        .attendance-page .attendance-action-progress span{color:#71827b;font-size:.76rem}
        .attendance-page .attendance-action-status{font-size:.76rem;line-height:1.4}
        .attendance-page .attendance-action-status[data-state="blocked"]{color:#7b681f}
        .attendance-page .attendance-action-group button{appearance:none;-webkit-appearance:none;min-height:2.35rem;padding:.58rem .85rem!important;border:1px solid transparent!important;border-radius:.65rem!important;font-size:.84rem;line-height:1.2;transition:background-color .15s ease,border-color .15s ease,color .15s ease,transform .15s ease,box-shadow .15s ease}
        .attendance-page .attendance-action-group button.secondary{background:#f3f8f5!important;border-color:#a9cbbb!important;color:#176b4d!important;box-shadow:0 1px 2px rgba(25,65,49,.06)!important}
        .attendance-page .attendance-action-group button.secondary:hover{background:#e7f3ec!important;border-color:#86b79d!important;transform:translateY(-1px);box-shadow:0 4px 10px rgba(25,65,49,.1)!important}
        .attendance-page .attendance-action-group button:focus-visible{outline:3px solid #b9dac7;outline-offset:2px}
        .attendance-page .attendance-action-group button:disabled{opacity:1!important;background:#dcebe3!important;border-color:#c1d8ca!important;color:#5d756a!important;box-shadow:none!important}
        @media(max-width:680px){.attendance-page .attendance-action-bar{padding:.7rem .8rem}.attendance-page .attendance-action-copy{column-gap:.55rem}.attendance-page .attendance-progress-meter{width:2.15rem;height:2.15rem}.attendance-page .attendance-action-group{gap:.5rem}.attendance-page .attendance-action-group button{padding:.55rem .65rem!important}}
        .attendance-page .input-table{border-collapse:separate;border-spacing:0;table-layout:fixed}
        .attendance-page .input-table thead th{padding:.6rem .65rem;border-bottom:1px solid #dfeae3;background:#fbfdfc;color:#71827b;font-size:.7rem;letter-spacing:.08em}
        .attendance-page .input-table thead th:nth-child(1){width:27%}.attendance-page .input-table thead th:nth-child(2){width:19%}.attendance-page .input-table thead th:nth-child(3){width:27%}.attendance-page .input-table thead th:nth-child(4){width:27%}
        .attendance-page .input-table tbody tr{background:#fff;transition:background-color .15s ease}
        .attendance-page .input-table tbody tr:hover,.attendance-page .input-table tbody tr:focus-within{background:#f8fbf9}
        .attendance-page .input-table tbody td{padding:.85rem .65rem;border-bottom:1px solid #edf2ef;vertical-align:middle}
        .attendance-page .input-table tbody tr:last-child td{border-bottom:0}
        .attendance-page .input-table tbody td:first-child strong{display:block;color:#243b33;font-size:.94rem;line-height:1.25;text-transform:none;overflow-wrap:anywhere}
        .attendance-page .input-table tbody td:first-child>.muted{display:block;margin-top:.18rem;font-size:.74rem}
        .attendance-page .input-table .attendance-workflow-status{display:flex;width:max-content;max-width:100%;margin-top:.42rem;padding:.18rem .48rem;border-radius:999px;background:#f3f7f5;color:#63776e;font-size:.68rem;font-weight:800;line-height:1.15}
        .attendance-page .input-table .attendance-status-select{min-height:2.45rem;border-radius:.55rem;border-color:#cbded2;font-size:.86rem}
        .attendance-page .input-table .attendance-note-details summary,.attendance-page .input-table .grooming-details summary{padding:.28rem .38rem;border:1px solid transparent;border-radius:.45rem;color:#63776e;font-size:.82rem;font-weight:800;transition:background-color .15s ease,border-color .15s ease,color .15s ease}
        .attendance-page .input-table .attendance-note-details summary:hover,.attendance-page .input-table .attendance-note-details summary:focus-visible,.attendance-page .input-table .grooming-details summary:hover,.attendance-page .input-table .grooming-details summary:focus-visible{border-color:#cfe1d7;background:#f3f8f5;color:#176b4d;outline:0}
        @media(max-width:1100px){.attendance-page .input-table{table-layout:auto}.attendance-page .input-table thead th{width:auto!important}}
        @media(max-width:680px){.attendance-page .input-table tbody tr{border-color:#dfeae3;background:#fff}.attendance-page .input-table tbody td{padding:.62rem .1rem}.attendance-page .input-table tbody td:first-child strong{font-size:.92rem}}
    </style>
</head>
<body>
@php($dayNames = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'])
@php($attendanceDay = ($dayNames[$session->planned_start_at->format('l')] ?? $session->planned_start_at->format('l')).', '.$session->planned_start_at->format('d M Y'))
@php($isReviewRoute = request()->routeIs('academic.attendance.review'))
@php($attendanceReturnUrl = $isReviewRoute ? route('academic.attendance.reviews') : route('academic.attendance.exceptions', $attendanceScope['is_joint'] ? ['list_month' => $session->planned_start_at->format('Y-m'), 'list_sort' => 'newest'] : ['list_month' => $session->planned_start_at->format('Y-m'), 'list_class_id' => $session->class_id, 'list_sort' => 'newest']))
@php($attendanceReturnLabel = $isReviewRoute || $attendanceScope['is_joint'] ? '← Kembali ke daftar sesi' : '← Kembali ke daftar sesi kelas ini')
<div class="waka-shell">@include('academic.partials.sidebar', ['activeMenu' => 'attendance', 'attendanceUrl' => $attendanceReturnUrl])<main class="waka-content attendance-page">
    <header class="card session-header">
        <p class="eyebrow">{{ ($readOnly ?? false) ? 'Pemeriksaan Waka Akademik' : 'Operasional kelas' }}</p><h1>{{ ($readOnly ?? false) ? 'Hasil Kehadiran Santri' : 'Kehadiran Santri' }}</h1><a class="attendance-back-link" href="{{ $attendanceReturnUrl }}">{{ $attendanceReturnLabel }}</a>
        <div class="hero-meta-grid">
            <div class="hero-meta-block"><span class="hero-meta-label">{{ $attendanceScope['is_joint'] ? 'Kelas gabungan · Mata pelajaran' : 'Kelas · Mata pelajaran' }}</span><strong class="hero-meta-value">{{ $attendanceScope['is_joint'] ? 'Kelas gabungan · '.$attendanceScope['class_label'] : ($session->academicClass?->display_name ?? 'Kelas belum ditentukan') }} · {{ $session->teachingAssignment?->subject?->subject_name ?? 'Mata pelajaran belum ditentukan' }}</strong><span class="hero-meta-secondary">Sesi Kehadiran</span>@if ($attendanceScope['is_joint'])<div class="joint-scope-summary"><strong>Breakdown roster</strong><span>{{ $attendanceScope['counts']->map(fn ($item) => $item['label'].' · '.$item['count'].' santri')->implode(' · ') }}@if ($attendanceScope['unmapped_count'] > 0) · Belum terpetakan · {{ $attendanceScope['unmapped_count'] }} santri @endif</span></div>@endif</div>
            <div class="hero-meta-block"><span class="hero-meta-label">Tanggal · Waktu</span><strong class="hero-meta-value">{{ $attendanceDay }}</strong><span class="hero-meta-secondary">{{ $session->planned_start_at->format('H:i') }}–{{ $session->planned_end_at->format('H:i') }}</span></div>
            <div class="hero-meta-block"><span class="hero-meta-label">Guru terjadwal</span><strong class="hero-meta-value">{{ $session->teachingAssignment?->teacher?->full_name ?? 'Belum ditentukan' }}</strong></div>
            <div class="hero-meta-block"><span class="hero-meta-label">Dicatat oleh</span><strong class="hero-meta-value">{{ $staff?->full_name ?? auth()->user()->name }}</strong></div>
            <div class="hero-meta-block"><span class="hero-meta-label">Status sesi</span><span class="hero-status-badge">{{ ['PLANNED' => 'Dijadwalkan', 'CONFIRMED' => 'Dikonfirmasi', 'COMPLETED' => 'Selesai', 'CANCELLED' => 'Dibatalkan', 'RESCHEDULED' => 'Dijadwal ulang'][$session->session_status] ?? $session->session_status }}</span></div>
        </div>
    </header>

    @if (session('status')) <div class="attendance-toast" role="status" aria-live="polite" data-auto-dismiss="5000"><span class="attendance-toast-icon" aria-hidden="true">✓</span><span class="attendance-toast-message">{{ session('status') }}</span><button class="attendance-toast-close" type="button" aria-label="Tutup notifikasi">×</button></div> @endif
    @if ($errors->any()) <div class="error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif

    @if (($occurrenceFeatureEnabled ?? false) && ($occurrenceCanonicalRegime ?? false))
        <section class="card occurrence-workflow-panel" aria-labelledby="occurrence-heading">
            <h2 id="occurrence-heading">Kejadian sesi kanonik</h2>
            @if ($effectiveOccurrence)
                <p class="muted">Status kanonik saat ini: <strong>{{ $effectiveOccurrence->occurrence_status }}</strong>. Status legacy sesi tetap ditampilkan terpisah.</p>
            @else
                <p class="muted">Kejadian kanonik belum dicatat. Status legacy tidak otomatis dianggap sebagai SCHEDULED atau HELD.</p>
            @endif
            @if (($canManageOccurrence ?? false) && ! ($readOnly ?? false))
                <div class="button-row">
                    <form method="POST" action="{{ route('academic.attendance.occurrence.record', $session) }}">@csrf<input type="hidden" name="action" value="HELD"><button class="button" type="submit">Catat KBM berlangsung</button></form>
                    <form method="POST" action="{{ route('academic.attendance.occurrence.record', $session) }}">@csrf<input type="hidden" name="action" value="PARTIAL_HELD"><input name="partial_reason" required maxlength="1000" placeholder="Alasan KBM sebagian"><button class="secondary" type="submit">KBM berlangsung sebagian</button></form>
                    <form method="POST" action="{{ route('academic.attendance.occurrence.record', $session) }}">@csrf<input type="hidden" name="action" value="CANCELLED"><input name="reason" required maxlength="1000" placeholder="Alasan pembatalan"><button class="secondary" type="submit">Batalkan KBM</button></form>
                    <form method="POST" action="{{ route('academic.attendance.occurrence.record', $session) }}">@csrf<input type="hidden" name="action" value="RESCHEDULED"><input type="datetime-local" name="new_start_at" required><input type="datetime-local" name="new_end_at" required><input name="reason" required maxlength="1000" placeholder="Alasan jadwal ulang"><button class="secondary" type="submit">Jadwal ulang</button></form>
                </div>
            @endif
            @if (($occurrenceHistory ?? collect())->isNotEmpty())
                <details class="occurrence-history"><summary>Riwayat kejadian sesi</summary>
                    <ol>
                        @foreach ($occurrenceHistory as $history)
                            <li>
                                <strong>Versi {{ $history->version_no }} · {{ $history->occurrence_status }}</strong>
                                @if ($history->id === $effectiveOccurrence?->id)<span>(efektif)</span>@endif
                                <span>{{ $history->recordedBy?->name ?? 'Pengguna' }} · {{ $history->recorded_at?->format('d/m/Y H:i') }}</span>
                                @if ($history->reason)<span>{{ $history->reason }}</span>@endif
                                @if ($history->is_partial)<span>KBM sebagian: {{ $history->partial_reason }}</span>@endif
                            </li>
                        @endforeach
                    </ol>
                </details>
            @endif
            @if (($canCorrectOccurrence ?? false))
                <form method="POST" action="{{ route('academic.attendance.occurrence.correction', $session) }}" class="occurrence-correction-form">
                    @csrf
                    <label>Status koreksi<select name="occurrence_status" required>@foreach(['SCHEDULED' => 'SCHEDULED', 'HELD' => 'HELD', 'CANCELLED' => 'CANCELLED', 'RESCHEDULED' => 'RESCHEDULED'] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
                    <label>Alasan koreksi<textarea name="reason" required maxlength="1000"></textarea></label>
                    <button class="secondary" type="submit">Tambah koreksi versi baru</button>
                </form>
            @endif
        </section>
    @endif

    @php($summary = $participants->map(fn ($participant) => $participant->attendance?->attendance_status ?? 'PENDING')->countBy())
    <section class="summary" aria-label="Ringkasan kehadiran santri">
        <div class="summary-card summary-card--total"><span class="summary-accent" aria-hidden="true">●</span><span class="muted">Total santri</span><strong>{{ $participants->count() }}</strong></div>
        <div class="summary-card summary-card--present"><span class="summary-accent" aria-hidden="true">✓</span><span class="muted">Hadir</span><strong>{{ $summary->get('PRESENT', 0) }}</strong></div>
        <div class="summary-card summary-card--late"><span class="summary-accent" aria-hidden="true">◷</span><span class="muted">Terlambat</span><strong>{{ $summary->get('LATE', 0) }}</strong></div>
        <div class="summary-card summary-card--sick"><span class="summary-accent" aria-hidden="true">+</span><span class="muted">Sakit</span><strong>{{ $summary->get('SICK', 0) }}</strong></div>
        <div class="summary-card summary-card--izin"><span class="summary-accent" aria-hidden="true">▤</span><span class="muted">Izin</span><strong>{{ $summary->get('IZIN', 0) }}</strong></div>
        <div class="summary-card summary-card--excused"><span class="summary-accent" aria-hidden="true">−</span><span class="muted">Dikecualikan</span><strong>{{ $summary->get('EXCUSED', 0) }}</strong></div>
        <div class="summary-card summary-card--absent"><span class="summary-accent" aria-hidden="true">×</span><span class="muted">Tidak hadir</span><strong>{{ $summary->get('ABSENT', 0) }}</strong></div>
        <div class="summary-card summary-card--pending @if ($summary->get('PENDING', 0) > 0) summary-card--pending-active @endif"><span class="summary-accent" aria-hidden="true">…</span><span class="muted">Belum diisi</span><strong>{{ $summary->get('PENDING', 0) }}</strong></div>
    </section>
    <section class="card attendance-card">@if (($readOnly ?? false))<div class="info"><strong>Hasil sudah disahkan.</strong> Halaman ini hanya untuk melihat hasil dan catatan kehadiran.</div>@endif
        @if (($readOnly ?? false))
            <div class="button-row" style="margin-top:0">
                <a class="button" href="{{ route('academic.attendance.review.csv', $session) }}">Unduh detail CSV</a>
                <a class="button" href="{{ route('academic.attendance.review.pdf', $session) }}">Unduh detail PDF</a>
            </div>
            <div class="table-wrap">
                <table class="attendance-table results-table">
                    <thead><tr><th>Santri</th><th>Status</th><th>Catatan kehadiran</th><th>Catatan ketertiban</th><th>Status pemeriksaan</th></tr></thead>
                    <tbody>
                    @foreach ($participants as $participant)
                        <tr>
                            <td data-label="Santri"><strong>{{ $participant->student->full_name }}</strong><br><span class="muted">{{ $participant->student->student_code }}</span>@if ($attendanceScope['is_joint'])<span class="attendance-class-badge @if (! $attendanceScope['participant_labels'][(string) $participant->id]) attendance-class-badge--unmapped @endif">{{ $attendanceScope['participant_labels'][(string) $participant->id] ?? 'Belum terpetakan' }}</span>@endif</td>
                            <td data-label="Status">{{ ['PRESENT' => 'Hadir', 'ABSENT' => 'Tidak hadir', 'SICK' => 'Sakit', 'IZIN' => 'Izin', 'LATE' => 'Terlambat', 'EXCUSED' => 'Dikecualikan'][$participant->attendance?->attendance_status ?? ''] ?? 'Belum diisi' }}</td>
                            <td data-label="Catatan kehadiran">{{ $participant->attendance?->notes ?: '—' }}</td>
                            <td data-label="Catatan ketertiban">{{ ['RAPI' => 'Rapi', 'TIDAK_BERSERAGAM' => 'Tidak berseragam', 'SERAGAM_TIDAK_LENGKAP' => 'Seragam tidak lengkap', 'TIDAK_MEMBAWA_BUKU' => 'Tidak membawa buku', 'TIDAK_BERPECI' => 'Tidak berpeci', 'CATATAN_TAMBAHAN' => 'Catatan tambahan'][$participant->groomingNote?->discipline_code ?? ''] ?? '—' }}{{ $participant->groomingNote?->note_text ? ': '.$participant->groomingNote->note_text : '' }}</td>
                            <td data-label="Status pemeriksaan">{{ ($participant->attendance?->workflow_status === 'VALIDATED') ? 'Sudah diperiksa' : 'Belum diperiksa' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <section id="rekap-guru" class="teacher-attendance-readonly" aria-labelledby="rekap-guru-heading">
                <h2 id="rekap-guru-heading">Rekap kehadiran guru</h2>
                @foreach ($session->teacherParticipations as $teacherParticipation)
                    @php($teacherName = $teacherParticipation->teacher?->full_name ?? 'Guru terjadwal')
                    <div class="teacher-readonly-row">
                        <div><span class="teacher-readonly-label">Guru</span><strong class="teacher-readonly-value">{{ $teacherName }}</strong><span class="teacher-role">{{ $teacherParticipation->role === 'SUBSTITUTE' ? 'Guru pengganti' : 'Guru utama' }}</span></div>
                        <div><span class="teacher-readonly-label">Kehadiran pada sesi</span><span class="teacher-status">{{ ['PRESENT' => 'Hadir', 'ABSENT' => 'Tidak hadir', 'SICK' => 'Sakit', 'IZIN' => 'Izin', 'OTHER' => 'Lainnya'][$teacherParticipation->attendance_status ?? ''] ?? 'Belum dicatat' }}</span></div>
                        <div><span class="teacher-readonly-label">Alasan/catatan</span><span class="teacher-readonly-value">{{ $teacherParticipation->reason ?: '—' }}</span></div>
                    </div>
                @endforeach
            </section>
            @if ($canRequestCorrection ?? false)
                <section class="info correction-panel">
                    <strong>{{ isset($correctionRoute) ? 'Koreksi hasil kehadiran' : 'Ajukan koreksi hasil' }}</strong>
                    <p class="muted">Gunakan bila ada kesalahan setelah pengesahan. {{ isset($correctionRoute) ? 'Perubahan Waka Akademik langsung diaudit.' : 'Permintaan akan ditinjau Waka Akademik.' }}</p>
                    @foreach ($correctionCandidates as $candidate)
                        <form class="correction-form" method="POST" action="{{ $correctionRoute ?? route('academic.attendance.corrections.request', $session) }}">
                            @csrf
                            <input type="hidden" name="attendance_id" value="{{ $candidate->attendance->id }}">
                            <input type="hidden" name="expected_version" value="{{ $candidate->attendance->version_no }}">
                            <strong>{{ $candidate->student->full_name }}</strong>
                            <label>Status baru<select name="attendance_status" required><option value="PRESENT">Hadir</option><option value="ABSENT">Tidak hadir</option><option value="SICK">Sakit</option><option value="IZIN">Izin</option><option value="LATE">Terlambat</option><option value="EXCUSED">Dikecualikan</option></select></label>
                            <label>Alasan koreksi<textarea name="reason" required maxlength="1000" placeholder="Contoh: Status izin belum tercatat"></textarea></label>
                            <button class="button" type="submit">{{ isset($correctionRoute) ? 'Terapkan koreksi' : 'Ajukan koreksi' }}</button>
                        </form>
                    @endforeach
                </section>
            @endif
        @else
        @if ($session->session_status === 'CANCELLED')
            <div class="info"><strong>Sesi dibatalkan.</strong> Sesi ini ditiadakan dan tidak dapat diisi sebagai kehadiran akademik.</div>
        @else
        @if ($participants->isEmpty())<div class="info" style="border-color:#efd7bd;background:#fffaf4"><strong>Roster santri belum dibuat.</strong> Buat snapshot roster kelas sesuai tanggal sesi sebelum mengisi kehadiran.@if (($canCancel ?? false))<form method="POST" action="{{ route('academic.attendance.snapshot-participants', $session) }}" style="margin-top:.75rem">@csrf<button class="button" type="submit">Buat snapshot roster santri</button></form>@endif</div>@endif
        @if ($canRecordTeacherAttendance ?? false)
            <section id="rekap-guru" class="card teacher-attendance-section" aria-labelledby="rekap-guru-heading"><h2 id="rekap-guru-heading">Rekap kehadiran guru</h2><p class="muted">Catat kehadiran guru pada sesi ini. Jika berhalangan, gunakan guru pengganti atau wali kelas agar santri tetap diabsen.</p>
                @foreach ($session->teacherParticipations as $teacherParticipation)
                    @php($teacherName = $teacherParticipation->teacher?->full_name ?? 'Guru terjadwal')
                    @php($teacherWords = preg_split('/\s+/', trim($teacherName)))
                    @php($teacherInitials = count($teacherWords) > 1 ? mb_strtoupper(mb_substr($teacherWords[0], 0, 1).mb_substr($teacherWords[1], 0, 1)) : mb_strtoupper(mb_substr($teacherName, 0, 2)))
                    <form class="teacher-attendance-row" method="POST" action="{{ route('academic.attendance.teacher-attendance', $session) }}">
                        @csrf
                        <input type="hidden" name="participation_id" value="{{ $teacherParticipation->id }}">
                        <div class="teacher-identity"><span class="teacher-avatar" aria-hidden="true">{{ $teacherInitials ?: 'G' }}</span><div class="teacher-identity-copy"><strong class="teacher-name">{{ $teacherName }}</strong><span class="teacher-role">{{ $teacherParticipation->role === 'SUBSTITUTE' ? 'Guru pengganti' : 'Guru utama' }}</span></div></div>
                        <label class="teacher-attendance-field"><span>Status kehadiran pada sesi</span><select name="attendance_status" required><option value="" @selected($teacherParticipation->attendance_status === null)>Belum dicatat</option><option value="PRESENT" @selected($teacherParticipation->attendance_status === 'PRESENT')>Hadir</option><option value="ABSENT" @selected($teacherParticipation->attendance_status === 'ABSENT')>Tidak hadir</option><option value="SICK" @selected($teacherParticipation->attendance_status === 'SICK')>Sakit</option><option value="IZIN" @selected($teacherParticipation->attendance_status === 'IZIN')>Izin</option><option value="OTHER" @selected($teacherParticipation->attendance_status === 'OTHER')>Lainnya</option></select></label>
                        <label class="teacher-attendance-field teacher-attendance-field--note"><span>Alasan/catatan</span><textarea name="reason" maxlength="1000" placeholder="Wajib diisi jika guru tidak hadir, sakit, izin, atau lainnya">{{ $teacherParticipation->reason }}</textarea></label>
                        <button class="button" type="submit">Simpan rekap guru</button>
                    </form>
                @endforeach
            </section>
        @endif
        @if (($replacementTeachers->isNotEmpty()) || ($canCancel ?? false))<details class="session-actions"><summary>Tindakan sesi</summary>@if ($replacementTeachers->isNotEmpty())<form class="card substitution-form" method="POST" action="{{ route('academic.attendance.substitution', $session) }}"><h2 style="margin-top:0">Ganti Guru Sesi Ini</h2><p class="muted" style="margin:0">Default pengganti adalah Wali Kelas aktif. Catat kegiatan pengganti di alasan.</p><label>Guru pengganti<select name="replacement_teacher_id"><option value="">Wali Kelas aktif (default){{ $homeroomStaff ? ' — '.$homeroomStaff->full_name : '' }}</option>@foreach($replacementTeachers as $teacher)<option value="{{ $teacher->id }}" @selected(old('replacement_teacher_id') === (string) $teacher->id)>{{ $teacher->full_name }}</option>@endforeach</select></label><label>Alasan/catatan sesi pengganti<textarea name="reason" required maxlength="1000" placeholder="Contoh: Ngaji di auditorium karena guru berhalangan hadir">{{ old('reason') }}</textarea></label>@csrf<button class="button" type="submit">Simpan guru pengganti</button></form>@endif @if ($canCancel ?? false)<form class="card substitution-form cancellation-form" method="POST" action="{{ route('academic.attendance.cancel', $session) }}"><h2 style="margin-top:0">Batalkan Sesi</h2><p class="muted" style="margin:0">Gunakan bila kegiatan akademik ditiadakan dan diganti kegiatan lain.</p><label>Alasan pembatalan<textarea name="reason" required maxlength="1000" placeholder="Contoh: Kegiatan akademik ditiadakan, diganti Tahfizh">{{ old('reason') }}</textarea></label>@csrf<button class="button" type="submit" onclick="return window.confirm('Batalkan sesi ini? Sesi tanpa data kehadiran akan ditandai sebagai Dibatalkan.')">Batalkan sesi</button></form>@endif</details>@endif
        <h2 class="attendance-section-title">Kehadiran santri</h2>
        @php($requiredParticipants = $participants->filter(fn ($participant) => $participant->participant_status === 'EXPECTED' && $participant->is_required))
        @php($filledParticipants = $requiredParticipants->filter(fn ($participant) => in_array($participant->attendance?->attendance_status, ['PRESENT', 'LATE', 'SICK', 'IZIN', 'EXCUSED', 'ABSENT'], true)))
        @php($primaryTeacherParticipation = $session->teacherParticipations->first(fn ($participation) => $participation->role === 'PRIMARY'))
        @php($teacherHasCoverage = $session->teacherParticipations->contains(fn ($participation) => $participation->role === 'SUBSTITUTE' && $participation->attendance_status === 'PRESENT'))
        @php($teacherGateState = $primaryTeacherParticipation === null || $primaryTeacherParticipation->attendance_status === null ? 'unresolved' : ($primaryTeacherParticipation->attendance_status === 'PRESENT' || $teacherHasCoverage ? 'ready' : 'coverage'))
        @php($initialReady = $filledParticipants->count() === $requiredParticipants->count() && $teacherGateState === 'ready')
        @php($attendanceProgress = $requiredParticipants->count() > 0 ? round($filledParticipants->count() / $requiredParticipants->count() * 100) : 0)
        <form id="attendance-form" method="POST" action="{{ route('academic.attendance.draft', $session) }}">
            @csrf
            <div class="top-attendance-actions">
                <div class="top-attendance-bulk"><button class="secondary" type="button" id="mark-all-present">Tandai semua hadir</button><span class="muted" id="bulk-action-feedback" role="status" aria-live="polite">Tandai kondisi umum terlebih dahulu, lalu ubah santri yang memiliki catatan khusus.</span></div>
                <div class="top-attendance-utility"><div class="roster-search"><label for="student-search">Cari santri</label><div class="roster-search-control"><span aria-hidden="true">⌕</span><input id="student-search" type="search" data-student-search placeholder="Cari nama santri..." autocomplete="off"></div></div><button class="secondary" type="submit" form="attendance-form">Simpan draf</button><button type="submit" form="attendance-form" formaction="{{ route('academic.attendance.finalize', $session) }}" formmethod="POST" data-finalize-button aria-disabled="{{ $initialReady ? 'false' : 'true' }}" @disabled(! $initialReady) onclick="return validateAttendanceBeforeFinalize(event)">Sahkan kehadiran</button></div>
            </div>
            <p class="muted" style="margin:0 0 .6rem">Pada layar kecil, setiap santri ditampilkan sebagai kartu agar semua isian tetap mudah diakses.</p><div class="table-wrap">
                <table class="attendance-table input-table">
                    <thead><tr><th>Santri</th><th>Status</th><th>Catatan</th><th>Ketertiban</th></tr></thead>
                    <tbody>
                    @foreach ($participants as $participant)
                        @php($hasGroomingRecord = $participant->groomingNote !== null)
                        @php($oldDiscipline = old('participants.'.$participant->id.'.discipline_code'))
                        @php($oldGrooming = old('participants.'.$participant->id.'.grooming_note'))
                        @php($hasAttendanceNote = trim((string) ($participant->attendance?->notes ?? '')) !== '')
                        @php($oldAttendanceNote = old('participants.'.$participant->id.'.notes'))
                        @php($attendanceNoteOpen = $hasAttendanceNote || trim((string) $oldAttendanceNote) !== '')
                        @php($groomingOpen = $hasGroomingRecord || trim((string) $oldDiscipline) !== '' || trim((string) $oldGrooming) !== '')
                        <tr data-student-row data-student-name="{{ $participant->student->full_name }}">
                            <td data-label="Santri"><strong>{{ $participant->student->full_name }}</strong><br><span class="muted">{{ $participant->student->student_code }}</span>@if ($attendanceScope['is_joint'])<span class="attendance-class-badge @if (! $attendanceScope['participant_labels'][(string) $participant->id]) attendance-class-badge--unmapped @endif">{{ $attendanceScope['participant_labels'][(string) $participant->id] ?? 'Belum terpetakan' }}</span>@endif<span class="attendance-workflow-status">{{ ['DRAFT' => 'Draf', 'VALIDATED' => 'Sudah diperiksa'][$participant->attendance?->workflow_status ?? ''] ?? 'Belum dibuat' }}</span></td>
                            <td data-label="Status">
                                <select class="attendance-status-select" @if($participant->participant_status === 'EXPECTED' && $participant->is_required) data-attendance-select @endif name="participants[{{ $participant->id }}][attendance_status]">
                                    <option value="">Belum diisi</option>
                                    @foreach (['PRESENT' => 'Hadir', 'ABSENT' => 'Tidak hadir', 'SICK' => 'Sakit', 'IZIN' => 'Izin', 'LATE' => 'Terlambat', 'EXCUSED' => 'Dikecualikan'] as $value => $label)
                                        <option value="{{ $value }}" @selected($participant->attendance?->attendance_status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td data-label="Catatan"><details class="attendance-note-details" @if($attendanceNoteOpen) open @endif><summary>Catatan</summary><textarea name="participants[{{ $participant->id }}][notes]" placeholder="Catatan opsional">{{ old('participants.'.$participant->id.'.notes', $participant->attendance?->notes) }}</textarea></details></td>
                            <td data-label="Ketertiban"><details class="grooming-details" @if($groomingOpen) open @endif><summary>{{ $hasGroomingRecord ? 'Edit catatan ketertiban' : 'Ketertiban' }}</summary><div class="grooming-fields"><select name="participants[{{ $participant->id }}][discipline_code]" class="discipline-select"><option value="">Belum dicatat</option>@foreach (['RAPI' => 'Rapi', 'TIDAK_BERSERAGAM' => 'Tidak berseragam', 'SERAGAM_TIDAK_LENGKAP' => 'Seragam tidak lengkap', 'TIDAK_MEMBAWA_BUKU' => 'Tidak membawa buku', 'TIDAK_BERPECI' => 'Tidak berpeci', 'CATATAN_TAMBAHAN' => 'Catatan tambahan'] as $value => $label)<option value="{{ $value }}" @selected(old('participants.'.$participant->id.'.discipline_code', $participant->groomingNote?->discipline_code) === $value)>{{ $label }}</option>@endforeach</select><textarea name="participants[{{ $participant->id }}][grooming_note]" placeholder="Keterangan tambahan (opsional)">{{ old('participants.'.$participant->id.'.grooming_note', $participant->groomingNote?->note_text) }}</textarea></div></details></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <p class="roster-empty-state" data-student-empty-state hidden>Tidak ada santri yang cocok.</p>
            </div>
        </form>
        @if ($participants->isNotEmpty())
            <div class="attendance-action-bar" data-attendance-action-bar data-student-total="{{ $requiredParticipants->count() }}" data-teacher-state="{{ $teacherGateState }}">
                <div class="attendance-action-copy"><div class="attendance-progress-meter" data-attendance-meter style="--attendance-progress:{{ $attendanceProgress }}%" aria-hidden="true"><span data-attendance-percent>{{ $attendanceProgress }}%</span></div><div class="attendance-action-progress" data-attendance-progress aria-live="polite"><strong>{{ $filledParticipants->count() }}/{{ $requiredParticipants->count() }} terisi</strong><span data-attendance-missing>{{ $requiredParticipants->count() - $filledParticipants->count() }} belum</span><div class="attendance-action-status" data-finalize-status data-state="{{ $initialReady ? 'ready' : 'blocked' }}" aria-live="polite">@if ($initialReady) Siap disahkan @elseif ($requiredParticipants->count() - $filledParticipants->count() > 0 && $teacherGateState !== 'ready') Belum siap disahkan · {{ $requiredParticipants->count() - $filledParticipants->count() }} santri belum diisi · kehadiran guru belum dicatat @elseif ($requiredParticipants->count() - $filledParticipants->count() > 0) {{ $requiredParticipants->count() - $filledParticipants->count() }} santri belum diisi @elseif ($teacherGateState === 'unresolved') Kehadiran guru belum dicatat @else Guru pengganti hadir diperlukan @endif</div></div></div>
                <div class="attendance-action-group"><button class="secondary" type="submit" form="attendance-form">Simpan draf</button><button type="submit" form="attendance-form" formaction="{{ route('academic.attendance.finalize', $session) }}" formmethod="POST" data-finalize-button aria-disabled="{{ $initialReady ? 'false' : 'true' }}" @disabled(! $initialReady) onclick="return validateAttendanceBeforeFinalize(event)">Sahkan kehadiran</button></div>
            </div>
        @endif
        @endif
        @endif
    </section>
</main></div>
</body>
<script>
    const studentSearch = document.querySelector('[data-student-search]');
    const studentRows = [...document.querySelectorAll('[data-student-row]')];
    const studentEmptyState = document.querySelector('[data-student-empty-state]');
    studentSearch?.addEventListener('input', function () {
        const query = this.value.trim().toLocaleLowerCase();
        let visibleRows = 0;
        studentRows.forEach((row) => {
            const matches = !query || (row.dataset.studentName || '').toLocaleLowerCase().includes(query);
            row.hidden = !matches;
            if (matches) visibleRows += 1;
        });
        if (studentEmptyState) studentEmptyState.hidden = visibleRows !== 0;
    });

    document.getElementById('mark-all-present')?.addEventListener('click', function () {
        const selects = document.querySelectorAll('.attendance-status-select');
        selects.forEach((select) => {
            select.value = 'PRESENT';
            select.dispatchEvent(new Event('change', { bubbles: true }));
        });
        const feedback = document.getElementById('bulk-action-feedback');
        if (feedback) feedback.textContent = `${selects.length} santri ditandai Hadir. Anda masih dapat mengubah status tertentu.`;
    });
    const actionBar = document.querySelector('[data-attendance-action-bar]');
    const attendanceSelects = [...document.querySelectorAll('[data-attendance-select]')];
    const progress = actionBar?.querySelector('[data-attendance-progress] strong');
    const missing = actionBar?.querySelector('[data-attendance-missing]');
    const progressMeter = actionBar?.querySelector('[data-attendance-meter]');
    const progressPercent = actionBar?.querySelector('[data-attendance-percent]');
    const finalizeStatus = actionBar?.querySelector('[data-finalize-status]');
    const finalizeButtons = [...document.querySelectorAll('[data-finalize-button]')];
    const studentTotal = Number(actionBar?.dataset.studentTotal || attendanceSelects.length);
    const teacherState = actionBar?.dataset.teacherState || 'unresolved';

    function updateFinalizeReadiness() {
        const filled = attendanceSelects.filter((select) => select.value).length;
        const incomplete = Math.max(studentTotal - filled, 0);
        const teacherReady = teacherState === 'ready';
        const ready = incomplete === 0 && teacherReady;
        if (progress) progress.textContent = `${filled}/${studentTotal} terisi`;
        if (missing) missing.textContent = `${incomplete} belum`;
        const percentage = studentTotal > 0 ? Math.round((filled / studentTotal) * 100) : 0;
        if (progressMeter) progressMeter.style.setProperty('--attendance-progress', `${percentage}%`);
        if (progressPercent) progressPercent.textContent = `${percentage}%`;
        finalizeButtons.forEach((button) => {
            button.disabled = !ready;
            button.setAttribute('aria-disabled', ready ? 'false' : 'true');
        });
        if (!finalizeStatus) return;
        finalizeStatus.dataset.state = ready ? 'ready' : 'blocked';
        if (ready) {
            finalizeStatus.textContent = 'Siap disahkan';
        } else if (incomplete > 0 && !teacherReady) {
            finalizeStatus.textContent = `Belum siap disahkan · ${incomplete} santri belum diisi · kehadiran guru belum dicatat`;
        } else if (incomplete > 0) {
            finalizeStatus.textContent = `${incomplete} santri belum diisi`;
        } else if (teacherState === 'unresolved') {
            finalizeStatus.textContent = 'Kehadiran guru belum dicatat';
        } else {
            finalizeStatus.textContent = 'Guru pengganti hadir diperlukan';
        }
    }

    document.querySelectorAll('.attendance-status-select').forEach((select) => {
        select.addEventListener('change', function () {
            const status = this.closest('tr')?.querySelector('.attendance-workflow-status');
            if (status && this.value) status.textContent = 'Draf (belum disimpan)';
            updateFinalizeReadiness();
        });
    });
    updateFinalizeReadiness();
    function validateAttendanceBeforeFinalize(event) {
        const incomplete = [...document.querySelectorAll('.attendance-status-select')]
            .filter((select) => !select.value).length;
        if (incomplete === 0) return true;
        event.preventDefault();
        window.alert(`Lengkapi status kehadiran ${incomplete} santri terlebih dahulu.`);
        return false;
    }
</script>
<script>
    document.querySelectorAll('[data-auto-dismiss]').forEach((toast) => {
        const close = () => toast.remove();
        toast.querySelector('.attendance-toast-close')?.addEventListener('click', close);
        window.setTimeout(close, Number(toast.dataset.autoDismiss) || 5000);
    });
</script>
</html>
