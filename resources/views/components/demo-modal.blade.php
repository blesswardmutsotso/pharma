{{-- ═══════════════════════════════════════════════════════
     FISCTECH – BOOK A DEMO MODAL COMPONENT
     Include on any page: @include('components.demo-modal')
     Also add the trigger button wherever needed:
     <button onclick="openDemoModal()" class="btn-demo">Book a Demo</button>
═══════════════════════════════════════════════════════ --}}

<!-- ══ DEMO MODAL OVERLAY ══ -->
<div id="demoOverlay" class="demo-overlay" onclick="closeDemoModal(event)">
    <div class="demo-modal" id="demoModal">

        <!-- close -->
        <button class="demo-close" onclick="closeDemoModal(true)" aria-label="Close">✕</button>

        <!-- top bar -->
        <div class="demo-modal-bar"></div>

        <!-- header -->
        <div class="demo-modal-header">
            <div class="demo-logo-wrap">
                <div class="demo-logo">FT</div>
            </div>
            <div class="demo-title">Book a Demo</div>
            <div class="demo-subtitle">See FiscTech in action — schedule your personalised walkthrough</div>
            <div class="demo-badge-row">
                <span class="demo-badge">🕐 30-min session</span>
                <span class="demo-badge">📊 Live dashboard</span>
                <span class="demo-badge">🤝 1-on-1 guided tour</span>
            </div>
        </div>

        <!-- form -->
        <form id="demoForm" onsubmit="submitDemo(event)" novalidate>

            <div class="demo-section-label">COMPANY DETAILS</div>

            <div class="demo-row">
                <div class="demo-group">
                    <label for="d_company">Company Name <span class="req">*</span></label>
                    <div class="demo-input-wrap">
                        <span class="demo-icon">🏢</span>
                        <input type="text" id="d_company" name="company" placeholder="Acme Corp" required>
                    </div>
                    <div class="demo-err" id="err_company"></div>
                </div>
                <div class="demo-group">
                    <label for="d_email">Company Email <span class="req">*</span></label>
                    <div class="demo-input-wrap">
                        <span class="demo-icon">✉</span>
                        <input type="email" id="d_email" name="email" placeholder="info@company.com" required>
                    </div>
                    <div class="demo-err" id="err_email"></div>
                </div>
            </div>

            <div class="demo-group">
                <label for="d_address">Company Address <span class="req">*</span></label>
                <div class="demo-input-wrap">
                    <span class="demo-icon">📍</span>
                    <input type="text" id="d_address" name="address" placeholder="123 Business Ave, Harare" required>
                </div>
                <div class="demo-err" id="err_address"></div>
            </div>

            <div class="demo-section-label" style="margin-top:18px;">CONTACT PERSON</div>

            <div class="demo-row">
                <div class="demo-group">
                    <label for="d_contact">Contact Person Name <span class="req">*</span></label>
                    <div class="demo-input-wrap">
                        <span class="demo-icon">👤</span>
                        <input type="text" id="d_contact" name="contact" placeholder="Jane Smith" required>
                    </div>
                    <div class="demo-err" id="err_contact"></div>
                </div>
                <div class="demo-group">
                    <label for="d_contact_phone">Contact Person Phone <span class="req">*</span></label>
                    <div class="demo-input-wrap">
                        <span class="demo-icon">📱</span>
                        <input type="tel" id="d_contact_phone" name="contact_phone" placeholder="+263 77 123 4567" required>
                    </div>
                    <div class="demo-err" id="err_contact_phone"></div>
                </div>
            </div>

            <div class="demo-row">
                <div class="demo-group">
                    <label for="d_primary_phone">Primary Phone Number <span class="req">*</span></label>
                    <div class="demo-input-wrap">
                        <span class="demo-icon">☎</span>
                        <input type="tel" id="d_primary_phone" name="primary_phone" placeholder="+263 24 123 4567" required>
                    </div>
                    <div class="demo-err" id="err_primary_phone"></div>
                </div>
                <div class="demo-group">
                    <label for="d_whatsapp">WhatsApp Number <span class="req">*</span></label>
                    <div class="demo-input-wrap">
                        <span class="demo-icon">💬</span>
                        <input type="tel" id="d_whatsapp" name="whatsapp" placeholder="+263 78 123 4567" required>
                    </div>
                    <div class="demo-err" id="err_whatsapp"></div>
                </div>
            </div>

            <div class="demo-section-label" style="margin-top:18px;">PREFERRED DEMO SCHEDULE</div>

            <div class="demo-row">
                <div class="demo-group">
                    <label for="d_date">Preferred Date <span class="req">*</span></label>
                    <div class="demo-input-wrap">
                        <span class="demo-icon">📅</span>
                        <input type="date" id="d_date" name="date" required>
                    </div>
                    <div class="demo-err" id="err_date"></div>
                </div>
                <div class="demo-group">
                    <label for="d_time">Preferred Time <span class="req">*</span></label>
                    <div class="demo-input-wrap">
                        <span class="demo-icon">🕐</span>
                        <input type="time" id="d_time" name="time" min="08:00" max="19:00" required>
                    </div>
                    <div class="demo-hint">Between 8:00 AM – 7:00 PM</div>
                    <div class="demo-err" id="err_time"></div>
                </div>
            </div>

            <button type="submit" class="demo-submit" id="demoSubmitBtn">
                <span id="demoSubmitText">Send via WhatsApp →</span>
                <span id="demoSubmitSpinner" style="display:none;">Sending…</span>
            </button>

        </form>

        <!-- success state -->
        <div id="demoSuccess" class="demo-success" style="display:none;">
            <div class="demo-success-icon">✅</div>
            <div class="demo-success-title">Request Sent!</div>
            <div class="demo-success-msg">Your demo booking has been sent via WhatsApp. Our team will confirm your slot shortly.</div>
            <button class="demo-success-close" onclick="closeDemoModal(true)">Close</button>
        </div>

    </div>
</div>

<style>
/* ════════════════════════════════════════
   BOOK A DEMO MODAL STYLES
════════════════════════════════════════ */

/* ── Trigger Button (shared) ── */
.btn-demo {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 20px;
    background: transparent;
    color: var(--accent);
    border: 1.5px solid var(--accent2);
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 700;
    font-family: 'Sora', sans-serif;
    cursor: pointer;
    transition: background 0.2s, color 0.2s, box-shadow 0.2s, transform 0.15s;
    white-space: nowrap;
}
.btn-demo::before { content: '📅'; font-size: 14px; }
.btn-demo:hover {
    background: var(--accent);
    color: #fff;
    box-shadow: 0 4px 16px rgba(22,163,74,0.32);
    transform: translateY(-1px);
}

/* ── Overlay ── */
.demo-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(5, 46, 22, 0.55);
    backdrop-filter: blur(6px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: overlayIn 0.2s ease;
}
.demo-overlay.active { display: flex; }

@keyframes overlayIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}

/* ── Modal Box ── */
.demo-modal {
    position: relative;
    background: #ffffff;
    border: 1.5px solid #a7f3d0;
    border-radius: 20px;
    width: 100%;
    max-width: 660px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 36px 36px 32px;
    box-shadow:
        0 0 0 1px rgba(22,163,74,0.06),
        0 32px 80px rgba(22,163,74,0.18),
        0 8px 24px rgba(0,0,0,0.08);
    animation: modalIn 0.35s cubic-bezier(0.16,1,0.3,1) both;
    scrollbar-width: thin;
    scrollbar-color: #a7f3d0 transparent;
}

.demo-modal::-webkit-scrollbar { width: 5px; }
.demo-modal::-webkit-scrollbar-track { background: transparent; }
.demo-modal::-webkit-scrollbar-thumb { background: #a7f3d0; border-radius: 99px; }

@keyframes modalIn {
    from { opacity: 0; transform: scale(0.94) translateY(20px); }
    to   { opacity: 1; transform: scale(1)    translateY(0); }
}

/* top gradient bar */
.demo-modal-bar {
    position: absolute;
    top: 0; left: 28px; right: 28px;
    height: 3px;
    background: linear-gradient(90deg, transparent, #22c55e, #4ade80, #22c55e, transparent);
    border-radius: 0 0 6px 6px;
}

/* close */
.demo-close {
    position: absolute;
    top: 16px; right: 16px;
    width: 30px; height: 30px;
    border: 1.5px solid #d1fae5;
    background: #f0faf4;
    border-radius: 8px;
    font-size: 13px;
    color: #6b7280;
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: background 0.2s, color 0.2s, border-color 0.2s;
    z-index: 10;
}
.demo-close:hover { background: #dc2626; color: #fff; border-color: #dc2626; }

/* ── Modal Header ── */
.demo-modal-header {
    text-align: center;
    margin-bottom: 26px;
}

.demo-logo-wrap {
    position: relative;
    display: inline-block;
    margin-bottom: 14px;
}
.demo-logo {
    width: 54px; height: 54px;
    background: linear-gradient(135deg, #16a34a, #15803d);
    border-radius: 14px;
    display: inline-grid;
    place-items: center;
    font-family: 'JetBrains Mono', monospace;
    font-size: 18px;
    font-weight: 700;
    color: #fff;
    box-shadow: 0 6px 22px rgba(22,163,74,0.32);
    position: relative;
    z-index: 1;
}
.demo-logo-wrap::before {
    content: '';
    position: absolute;
    inset: -5px;
    border-radius: 19px;
    border: 2px dashed rgba(34,197,94,0.35);
    animation: spin 14s linear infinite;
}

.demo-title {
    font-size: 22px;
    font-weight: 800;
    color: #052e16;
    letter-spacing: -0.4px;
    margin-bottom: 5px;
    font-family: 'Sora', sans-serif;
}
.demo-subtitle {
    font-size: 13px;
    color: #6b7280;
    font-family: 'Sora', sans-serif;
    margin-bottom: 14px;
}

.demo-badge-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    flex-wrap: wrap;
}
.demo-badge {
    display: inline-block;
    background: rgba(22,163,74,0.07);
    border: 1.5px solid #d1fae5;
    border-radius: 20px;
    padding: 3px 12px;
    font-size: 11.5px;
    font-weight: 600;
    color: #16a34a;
    font-family: 'JetBrains Mono', monospace;
}

/* ── Section Labels ── */
.demo-section-label {
    font-size: 10px;
    font-weight: 700;
    font-family: 'JetBrains Mono', monospace;
    color: #9ca3af;
    letter-spacing: 1.5px;
    margin-bottom: 12px;
    padding-bottom: 7px;
    border-bottom: 1px solid #d1fae5;
}

/* ── Form Layout ── */
.demo-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.demo-group {
    margin-bottom: 14px;
}

.demo-group label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 5px;
    font-family: 'Sora', sans-serif;
}

.req { color: #dc2626; }

.demo-input-wrap { position: relative; }

.demo-icon {
    position: absolute;
    left: 12px; top: 50%;
    transform: translateY(-50%);
    font-size: 14px;
    pointer-events: none;
    line-height: 1;
}

.demo-input-wrap input {
    width: 100%;
    background: #f7fef9;
    border: 1.5px solid #d1fae5;
    border-radius: 9px;
    padding: 10px 12px 10px 38px;
    font-size: 13.5px;
    font-family: 'Sora', sans-serif;
    color: #1a2e1f;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
}
.demo-input-wrap input:focus {
    border-color: #22c55e;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(34,197,94,0.12);
}
.demo-input-wrap input.field-error { border-color: #dc2626; background: #fff5f5; box-shadow: 0 0 0 3px rgba(220,38,38,0.08); }
.demo-input-wrap input.field-ok    { border-color: #22c55e; background: #f0fdf4; }

.demo-err  { font-size: 11.5px; color: #dc2626; margin-top: 4px; min-height: 16px; font-family: 'Sora', sans-serif; }
.demo-hint { font-size: 11px; color: #9ca3af; margin-top: 3px; font-family: 'JetBrains Mono', monospace; }

/* ── Submit ── */
.demo-submit {
    width: 100%;
    margin-top: 8px;
    padding: 13px;
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 700;
    font-family: 'Sora', sans-serif;
    cursor: pointer;
    transition: transform 0.15s, box-shadow 0.2s, filter 0.2s;
    box-shadow: 0 4px 18px rgba(22,163,74,0.38);
    position: relative;
    overflow: hidden;
}
.demo-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 26px rgba(22,163,74,0.45);
    filter: brightness(1.06);
}
.demo-submit:disabled { opacity: 0.65; cursor: not-allowed; transform: none; }

/* ── Success ── */
.demo-success {
    text-align: center;
    padding: 20px 0 10px;
    animation: slideUp 0.4s cubic-bezier(0.16,1,0.3,1) both;
}
.demo-success-icon { font-size: 52px; margin-bottom: 14px; }
.demo-success-title {
    font-size: 22px; font-weight: 800; color: #052e16;
    font-family: 'Sora', sans-serif; margin-bottom: 8px;
}
.demo-success-msg {
    font-size: 14px; color: #6b7280;
    font-family: 'Sora', sans-serif; line-height: 1.6; margin-bottom: 24px;
}
.demo-success-close {
    padding: 10px 32px;
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: #fff; border: none; border-radius: 10px;
    font-size: 14px; font-weight: 700; font-family: 'Sora', sans-serif;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(22,163,74,0.32);
    transition: filter 0.2s, transform 0.15s;
}
.demo-success-close:hover { filter: brightness(1.07); transform: translateY(-1px); }

/* ── Responsive ── */
@media (max-width: 600px) {
    .demo-modal { padding: 28px 20px 24px; max-width: 100%; }
    .demo-row   { grid-template-columns: 1fr; }
    .demo-badge-row { gap: 6px; }
}
</style>

<script>
/* ══════════════════════════════════
   DEMO MODAL JS
══════════════════════════════════ */

function openDemoModal() {
    const overlay = document.getElementById('demoOverlay');
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
    // set min date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('d_date').min = today;
}

function closeDemoModal(force) {
    if (force === true || force?.target === document.getElementById('demoOverlay')) {
        document.getElementById('demoOverlay').classList.remove('active');
        document.body.style.overflow = '';
    }
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDemoModal(true); });

/* ── Live validation helpers ── */
function setErr(id, msg) {
    const el = document.getElementById('err_' + id);
    const input = document.getElementById('d_' + id);
    if (el) el.textContent = msg;
    if (input) {
        input.classList.toggle('field-error', !!msg);
        input.classList.toggle('field-ok', !msg && input.value.trim() !== '');
    }
}

function clearErr(id) { setErr(id, ''); }

function validatePhone(val) {
    return /^[+\d\s\-().]{7,20}$/.test(val.trim());
}

/* attach live listeners */
document.addEventListener('DOMContentLoaded', () => {

    const fieldMap = {
        d_company:       () => document.getElementById('d_company').value.trim()       ? '' : 'Company name is required.',
        d_email:         () => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(document.getElementById('d_email').value) ? '' : 'Enter a valid email address.',
        d_address:       () => document.getElementById('d_address').value.trim()       ? '' : 'Address is required.',
        d_contact:       () => document.getElementById('d_contact').value.trim()       ? '' : 'Contact person name is required.',
        d_contact_phone: () => validatePhone(document.getElementById('d_contact_phone').value) ? '' : 'Enter a valid phone number.',
        d_primary_phone: () => validatePhone(document.getElementById('d_primary_phone').value) ? '' : 'Enter a valid phone number.',
        d_whatsapp:      () => validatePhone(document.getElementById('d_whatsapp').value)      ? '' : 'Enter a valid WhatsApp number.',
        d_date: () => {
            const v = document.getElementById('d_date').value;
            if (!v) return 'Please select a date.';
            const sel = new Date(v);
            const today = new Date(); today.setHours(0,0,0,0);
            return sel < today ? 'Date must be today or in the future.' : '';
        },
        d_time: () => {
            const v = document.getElementById('d_time').value;
            if (!v) return 'Please select a time.';
            const [h, m] = v.split(':').map(Number);
            const mins = h * 60 + m;
            if (mins < 480) return 'Time must be 8:00 AM or later.';
            if (mins > 1140) return 'Time must be 7:00 PM or earlier.';
            return '';
        },
    };

    Object.entries(fieldMap).forEach(([id, validate]) => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('blur', () => {
                const key = id.replace('d_', '');
                setErr(key, validate());
            });
            el.addEventListener('input', () => {
                const key = id.replace('d_', '');
                if (document.getElementById('err_' + key)?.textContent) {
                    setErr(key, validate());
                }
            });
        }
    });

    /* special: time input change — also recheck immediately */
    document.getElementById('d_time')?.addEventListener('change', () => {
        setErr('time', fieldMap.d_time());
    });
});

/* ── Format phone for WhatsApp link ── */
function formatForWA(num) {
    return num.replace(/[\s\-().]/g, '').replace(/^\+/, '');
}

/* ── Build WhatsApp message ── */
function buildMessage(data) {
    const timeStr = (() => {
        const [h, m] = data.time.split(':').map(Number);
        const ampm   = h >= 12 ? 'PM' : 'AM';
        const hr     = h > 12 ? h - 12 : h === 0 ? 12 : h;
        return `${hr}:${String(m).padStart(2,'0')} ${ampm}`;
    })();

    const dateStr = (() => {
        const d = new Date(data.date + 'T12:00:00');
        return d.toLocaleDateString('en-GB', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    })();

    return `🗓️ *FISCTECH DEMO BOOKING REQUEST*\n\n` +
           `🏢 *Company:* ${data.company}\n` +
           `📍 *Address:* ${data.address}\n` +
           `✉️ *Company Email:* ${data.email}\n\n` +
           `👤 *Contact Person:* ${data.contact}\n` +
           `📱 *Contact Phone:* ${data.contact_phone}\n` +
           `☎️ *Primary Phone:* ${data.primary_phone}\n` +
           `💬 *WhatsApp:* ${data.whatsapp}\n\n` +
           `📅 *Preferred Date:* ${dateStr}\n` +
           `🕐 *Preferred Time:* ${timeStr}\n\n` +
           `_Sent from FiscTech Login Portal_`;
}

/* ── Submit handler ── */
function submitDemo(e) {
    e.preventDefault();

    /* run all validations */
    const fieldMap = {
        company:       () => document.getElementById('d_company').value.trim()       ? '' : 'Company name is required.',
        email:         () => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(document.getElementById('d_email').value) ? '' : 'Enter a valid email address.',
        address:       () => document.getElementById('d_address').value.trim()       ? '' : 'Address is required.',
        contact:       () => document.getElementById('d_contact').value.trim()       ? '' : 'Contact person name is required.',
        contact_phone: () => validatePhone(document.getElementById('d_contact_phone').value) ? '' : 'Enter a valid phone number.',
        primary_phone: () => validatePhone(document.getElementById('d_primary_phone').value) ? '' : 'Enter a valid phone number.',
        whatsapp:      () => validatePhone(document.getElementById('d_whatsapp').value)      ? '' : 'Enter a valid WhatsApp number.',
        date: () => {
            const v = document.getElementById('d_date').value;
            if (!v) return 'Please select a date.';
            const sel = new Date(v); const today = new Date(); today.setHours(0,0,0,0);
            return sel < today ? 'Date must be today or in the future.' : '';
        },
        time: () => {
            const v = document.getElementById('d_time').value;
            if (!v) return 'Please select a time.';
            const [h, m] = v.split(':').map(Number);
            const mins = h * 60 + m;
            if (mins < 480)  return 'Time must be 8:00 AM or later.';
            if (mins > 1140) return 'Time must be 7:00 PM or earlier.';
            return '';
        },
    };

    let valid = true;
    Object.entries(fieldMap).forEach(([key, validate]) => {
        const err = validate();
        setErr(key, err);
        if (err) valid = false;
    });

    if (!valid) {
        /* scroll to first error */
        const firstErr = document.querySelector('.demo-modal .field-error');
        if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    /* collect data */
    const data = {
        company:       document.getElementById('d_company').value.trim(),
        email:         document.getElementById('d_email').value.trim(),
        address:       document.getElementById('d_address').value.trim(),
        contact:       document.getElementById('d_contact').value.trim(),
        contact_phone: document.getElementById('d_contact_phone').value.trim(),
        primary_phone: document.getElementById('d_primary_phone').value.trim(),
        whatsapp:      document.getElementById('d_whatsapp').value.trim(),
        date:          document.getElementById('d_date').value,
        time:          document.getElementById('d_time').value,
    };

    /* show spinner */
    document.getElementById('demoSubmitText').style.display    = 'none';
    document.getElementById('demoSubmitSpinner').style.display = 'inline';
    document.getElementById('demoSubmitBtn').disabled          = true;

    /* short delay for UX, then open WhatsApp */
    setTimeout(() => {
        const msg     = buildMessage(data);
        const encoded = encodeURIComponent(msg);
        const waNum   = '263787780405'; /* destination number */
        const waUrl   = `https://wa.me/${waNum}?text=${encoded}`;

        window.open(waUrl, '_blank');

        /* show success */
        document.getElementById('demoForm').style.display    = 'none';
        document.getElementById('demoSuccess').style.display = 'block';
    }, 900);
}
</script>