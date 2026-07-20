<div style="display:flex;align-items:center;gap:10px;text-decoration:none;">
    {{-- Icon badge --}}
    <div style="
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #16a34a 0%, #14532d 100%);
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(22,163,74,0.35), inset 0 1px 0 rgba(255,255,255,0.15);
        flex-shrink: 0;
    ">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
             stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
        </svg>
    </div>

    {{-- Text --}}
    <div style="display:flex;flex-direction:column;line-height:1.15;">
        <span style="
            font-size: 14px;
            font-weight: 800;
            color: #0f1117;
            letter-spacing: -0.3px;
            font-family: 'Outfit', system-ui, sans-serif;
            white-space: nowrap;
        ">{{ config('app.name') }}</span>
        <span style="
            font-size: 9px;
            font-weight: 700;
            color: #16a34a;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            font-family: system-ui, sans-serif;
        ">Fiscal POS</span>
    </div>
</div>
