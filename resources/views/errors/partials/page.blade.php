{{--
    One error page for every status. Self-contained on purpose: inline styles, no Vite build,
    no database and no session work that could fail, so it still renders when the app cannot.

    Expects: $code, $title, $message, $status (short label), $tone (info|warn|danger),
    $icon (search|lock|clock|gauge|alert|tool), $actions (list of back|home|login|reload).
--}}
@php
    $tone = $tone ?? 'info';
    $actions = $actions ?? ['back', 'home'];

    // Where "home" goes: the dashboard for a signed-in user, otherwise the start page
    try {
        $signedIn = auth()->check();
    } catch (\Throwable $e) {
        $signedIn = false;
    }
    $homeHref = $signedIn && Route::has('dashboard') ? route('dashboard') : url('/');
    $homeLabel = $signedIn ? 'Go to dashboard' : 'Go to start page';
    $loginHref = Route::has('login') ? route('login') : url('/login');

    // A reason given with abort(403, 'Only regional users…') helps more than the generic text. Other
    // statuses carry framework text (routes, model classes), which is never shown.
    $detail = null;
    if (isset($exception) && in_array((int) $code, [401, 403], true)) {
        $raw = trim((string) $exception->getMessage());
        $generic = ['', 'Forbidden', 'Not Found', 'Unauthorized', 'This action is unauthorized.', 'Page Expired', 'Too Many Requests', 'Too Many Attempts.'];
        if (! in_array($raw, $generic, true) && mb_strlen($raw) <= 240) {
            $detail = $raw;
        }
    }

    $path = '/' . ltrim(request()->path(), '/');
    $icons = [
        'search' => '<circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.2-4.2"/><path d="M8.5 11h5"/>',
        'lock' => '<rect x="4.5" y="10.5" width="15" height="10" rx="2.5"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3"/><path d="M12 14.5v2.5"/>',
        'clock' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
        'gauge' => '<path d="M4.5 16a7.5 7.5 0 1 1 15 0"/><path d="m12 16 3.5-4.5"/><path d="M4.5 19.5h15"/>',
        'alert' => '<path d="M10.3 4.2 2.9 17.5A2 2 0 0 0 4.6 20.5h14.8a2 2 0 0 0 1.7-3L13.7 4.2a2 2 0 0 0-3.4 0Z"/><path d="M12 9.5v4"/><path d="M12 17h.01"/>',
        'tool' => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L4 17l3 3 5.3-5.3a4 4 0 0 0 5.4-5.4l-2.4 2.4-2.6-.6-.6-2.6Z"/>',
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="#16305d">
    <link rel="icon" href="/logom.png">
    <title>{{ $code }} · {{ $title }} · {{ config('app.name', 'Track & Trace') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Noto+Sans+Ethiopic:wght@400;600;700&display=swap">
    <style>
        :root {
            --brand-50: #f0f5fc; --brand-100: #dde8f8; --brand-200: #c3d6f2; --brand-300: #9abbe8;
            --brand-500: #4474cb; --brand-600: #1e4ea2; --brand-700: #1a4289; --brand-900: #16305d; --brand-950: #0d1d3b;
            --gold-300: #fbd647; --gold-400: #f6ba12; --ember-500: #cc4e3c;

            --page: #0d1d3b;
            --card: #ffffff;
            --card-soft: #f8fafc;
            --line: #e2e8f0;
            --ink: #0f172a;
            --ink-2: #475569;
            --ink-3: #94a3b8;
            --focus: #f6ba12;

            --tone-info-bg: #f0f5fc; --tone-info-ink: #1a4289; --tone-info-dot: #4474cb;
            --tone-warn-bg: #fefbe8; --tone-warn-ink: #9e5706; --tone-warn-dot: #e6a106;
            --tone-danger-bg: #fdf4f2; --tone-danger-ink: #9a3225; --tone-danger-dot: #cc4e3c;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --page: #060e1f;
                --card: #0f1a30;
                --card-soft: #13213b;
                --line: #22324f;
                --ink: #f1f5f9;
                --ink-2: #b6c3d6;
                --ink-3: #7486a3;

                --tone-info-bg: rgba(68, 116, 203, .16); --tone-info-ink: #9abbe8; --tone-info-dot: #6a97da;
                --tone-warn-bg: rgba(246, 186, 18, .13); --tone-warn-ink: #fbd647; --tone-warn-dot: #f6ba12;
                --tone-danger-bg: rgba(204, 78, 60, .16); --tone-danger-ink: #f1aea3; --tone-danger-dot: #e57f6e;
            }
        }

        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
            background: var(--page);
            color: var(--ink);
            font-family: 'Nunito', 'Noto Sans Ethiopic', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
            -webkit-font-smoothing: antialiased;
            position: relative;
            overflow-x: hidden;
        }

        /* Backdrop: the sign-in panel's blue, glow and grid */
        .backdrop { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
        .backdrop::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(1200px 600px at 85% -10%, rgba(68, 116, 203, .35), transparent 60%),
                        radial-gradient(900px 520px at -10% 110%, rgba(246, 186, 18, .14), transparent 60%),
                        linear-gradient(160deg, #1a4289 0%, #16305d 45%, var(--page) 100%);
        }
        .backdrop svg { position: absolute; inset: 0; width: 100%; height: 100%; opacity: .07; }
        @media (prefers-color-scheme: dark) {
            .backdrop::before {
                background: radial-gradient(1100px 560px at 85% -10%, rgba(68, 116, 203, .22), transparent 60%),
                            radial-gradient(900px 520px at -10% 110%, rgba(246, 186, 18, .08), transparent 60%),
                            linear-gradient(160deg, #10254b 0%, #0b1a35 50%, var(--page) 100%);
            }
        }

        .shell { position: relative; z-index: 1; width: 100%; max-width: 920px; }

        .card {
            display: grid;
            grid-template-columns: 340px 1fr;
            background: var(--card);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 30px 80px -20px rgba(2, 8, 23, .55), 0 0 0 1px rgba(255, 255, 255, .06);
        }

        /* Left: the "label" with the status code and the interrupted route */
        .label {
            position: relative;
            padding: 32px;
            color: #fff;
            background: linear-gradient(155deg, var(--brand-600) 0%, var(--brand-900) 70%, var(--brand-950) 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 28px;
            overflow: hidden;
        }
        .label::after {
            content: ''; position: absolute; width: 260px; height: 260px; right: -110px; top: -110px;
            border-radius: 50%; background: rgba(246, 186, 18, .18); filter: blur(40px);
        }
        .brand { position: relative; display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit; }
        .brand-mark {
            flex: none; width: 44px; height: 44px; border-radius: 50%; background: #fff;
            display: grid; place-items: center; box-shadow: 0 0 0 4px rgba(255, 255, 255, .12);
        }
        .brand-mark img { width: 40px; height: 40px; }
        .brand-text { display: flex; flex-direction: column; line-height: 1.15; }
        .brand-text small { font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--brand-200); }
        .brand-text strong { font-size: 17px; font-weight: 800; }
        .brand-text strong span { color: var(--gold-400); }

        .code {
            position: relative;
            margin: 0;
            font-size: 104px;
            line-height: .9;
            font-weight: 900;
            letter-spacing: -.04em;
            font-variant-numeric: tabular-nums;
            background: linear-gradient(180deg, #ffffff 0%, var(--brand-200) 100%);
            -webkit-background-clip: text; background-clip: text; color: transparent;
        }
        .code-caption { margin: 10px 0 0; font-size: 12px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--gold-300); }

        .route { position: relative; width: 100%; height: auto; display: block; }
        .route-legend { display: flex; justify-content: space-between; margin-top: 6px; font-size: 11px; font-weight: 600; color: var(--brand-200); }

        .stripe { height: 4px; border-radius: 999px; width: 120px;
            background: linear-gradient(90deg, #f6ba12 0 30%, #cc4e3c 30% 60%, #3c8ac0 60% 78%, #9abbe8 78% 100%); }

        /* Right: what happened and what to do */
        .body { padding: 44px 44px 36px; display: flex; flex-direction: column; }
        .status {
            align-self: flex-start;
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 12px 6px 10px; border-radius: 999px;
            font-size: 12px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase;
        }
        .status::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: currentColor; box-shadow: 0 0 0 3px color-mix(in srgb, currentColor 22%, transparent); }
        .tone-info .status { background: var(--tone-info-bg); color: var(--tone-info-ink); }
        .tone-warn .status { background: var(--tone-warn-bg); color: var(--tone-warn-ink); }
        .tone-danger .status { background: var(--tone-danger-bg); color: var(--tone-danger-ink); }

        .heading { display: flex; align-items: flex-start; gap: 16px; margin-top: 22px; }
        .icon {
            flex: none; width: 52px; height: 52px; border-radius: 16px;
            display: grid; place-items: center;
            background: var(--card-soft); border: 1px solid var(--line);
        }
        .tone-info .icon { color: var(--tone-info-dot); }
        .tone-warn .icon { color: var(--tone-warn-dot); }
        .tone-danger .icon { color: var(--tone-danger-dot); }
        .icon svg { width: 26px; height: 26px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }

        h1 { margin: 2px 0 0; font-size: 30px; line-height: 1.15; font-weight: 800; letter-spacing: -.02em; text-wrap: balance; }
        .message { margin: 14px 0 0; font-size: 16px; line-height: 1.6; color: var(--ink-2); max-width: 46ch; }
        .detail {
            margin: 18px 0 0; padding: 12px 14px; border-radius: 12px;
            background: var(--card-soft); border: 1px solid var(--line);
            font-size: 14px; line-height: 1.5; color: var(--ink-2);
        }
        .detail strong { color: var(--ink); }

        .actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 28px; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            min-height: 44px; padding: 0 18px; border-radius: 12px;
            font: inherit; font-size: 15px; font-weight: 700; text-decoration: none; cursor: pointer;
            border: 1px solid transparent; transition: background-color .15s, border-color .15s, transform .15s;
        }
        .btn svg { width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .btn:active { transform: translateY(1px); }
        .btn:focus-visible { outline: 3px solid var(--focus); outline-offset: 2px; }
        .btn-primary { background: var(--brand-600); color: #fff; box-shadow: 0 8px 20px -8px rgba(30, 78, 162, .7); }
        .btn-primary:hover { background: var(--brand-700); }
        .btn-ghost { background: transparent; color: var(--ink); border-color: var(--line); }
        .btn-ghost:hover { background: var(--card-soft); }

        .slip {
            margin: 32px 0 0; padding: 16px 0 0; border-top: 1px dashed var(--line);
            display: grid; grid-template-columns: auto 1fr; gap: 6px 18px;
            font-size: 13px;
        }
        .slip dt { color: var(--ink-3); font-weight: 600; }
        .slip dd { margin: 0; color: var(--ink-2); font-weight: 600; overflow-wrap: anywhere; font-variant-numeric: tabular-nums; }
        .slip code { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12.5px; }

        .footer { position: relative; z-index: 1; margin: 22px 0 0; text-align: center; font-size: 12px; color: rgba(221, 232, 248, .7); }

        @media (max-width: 760px) {
            body { justify-content: flex-start; padding-top: 16px; }
            .card { grid-template-columns: 1fr; border-radius: 20px; }
            .label { padding: 22px 22px 24px; gap: 18px; }
            .label-route { display: none; }
            .code { font-size: 76px; }
            .body { padding: 28px 22px 26px; }
            h1 { font-size: 24px; }
            .heading { gap: 14px; }
            .icon { width: 44px; height: 44px; border-radius: 14px; }
            .btn { flex: 1 1 auto; }
        }
        @media (prefers-reduced-motion: no-preference) {
            .route-dash { animation: travel 1.6s linear infinite; }
            @keyframes travel { to { stroke-dashoffset: -16; } }
            .card { animation: rise .45s cubic-bezier(.2, .7, .2, 1) both; }
            @keyframes rise { from { opacity: 0; transform: translateY(10px); } }
        }
    </style>
</head>
<body>
    <div class="backdrop" aria-hidden="true">
        <svg>
            <defs>
                <pattern id="err-grid" width="32" height="32" patternUnits="userSpaceOnUse">
                    <path d="M32 0H0v32" fill="none" stroke="white" stroke-width="1" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#err-grid)" />
        </svg>
    </div>

    <main class="shell">
        <div class="card tone-{{ $tone }}">
            <aside class="label">
                <a class="brand" href="{{ url('/') }}">
                    <span class="brand-mark"><img src="/logom.png" alt=""></span>
                    <span class="brand-text">
                        <small>Ministry of Education</small>
                        <strong>Track <span>&amp;</span> Trace</strong>
                    </span>
                </a>

                <div>
                    <p class="code" aria-hidden="true">{{ $code }}</p>
                    <p class="code-caption">Error code {{ $code }}</p>
                </div>

                {{-- The journey from press to pupil, stopped short: the request did not arrive --}}
                <div class="label-route" aria-hidden="true">
                    <svg class="route" viewBox="0 0 276 92">
                        <path d="M14 70 C 70 70, 70 24, 128 24 S 196 66, 236 46" fill="none" stroke="rgba(255,255,255,.22)" stroke-width="2" stroke-dasharray="2 6" stroke-linecap="round" />
                        <path class="route-dash" d="M14 70 C 70 70, 70 24, 128 24 S 180 58, 196 58" fill="none" stroke="#9abbe8" stroke-width="2.5" stroke-dasharray="8 8" stroke-linecap="round" />
                        <circle cx="14" cy="70" r="7" fill="#fff" />
                        <circle cx="14" cy="70" r="3" fill="#1e4ea2" />
                        <circle cx="128" cy="24" r="5" fill="#16305d" stroke="#9abbe8" stroke-width="2" />
                        <g transform="translate(196 58)">
                            <circle r="13" fill="#f6ba12" opacity=".25" />
                            <circle r="8" fill="#f6ba12" />
                            <path d="M0 -3.5v4" stroke="#16305d" stroke-width="2" stroke-linecap="round" />
                            <circle cy="3.8" r="1.2" fill="#16305d" />
                        </g>
                        <g transform="translate(250 40)" opacity=".55">
                            <rect x="-11" y="-4" width="22" height="16" rx="2.5" fill="none" stroke="#fff" stroke-width="1.6" />
                            <path d="M-13 -4 0 -12 13 -4" fill="none" stroke="#fff" stroke-width="1.6" stroke-linejoin="round" />
                        </g>
                    </svg>
                    <div class="route-legend"><span>Press</span><span>Your page</span></div>
                </div>

                <span class="stripe" aria-hidden="true"></span>
            </aside>

            <section class="body">
                <span class="status">{{ $status }}</span>

                <div class="heading">
                    <span class="icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">{!! $icons[$icon ?? 'alert'] ?? $icons['alert'] !!}</svg>
                    </span>
                    <div>
                        <h1>{{ $title }}</h1>
                    </div>
                </div>

                <p class="message">{{ $message }}</p>

                @if ($detail)
                    <p class="detail"><strong>Reason:</strong> {{ $detail }}</p>
                @endif

                <div class="actions">
                    @foreach ($actions as $action)
                        @php $primary = $loop->first; @endphp
                        @switch($action)
                            @case('reload')
                                <a class="btn {{ $primary ? 'btn-primary' : 'btn-ghost' }}" href="{{ url()->current() }}">
                                    <svg viewBox="0 0 24 24"><path d="M20 12a8 8 0 1 1-2.3-5.6"/><path d="M20 4v4.5h-4.5"/></svg>
                                    Refresh the page
                                </a>
                                @break
                            @case('login')
                                <a class="btn {{ $primary ? 'btn-primary' : 'btn-ghost' }}" href="{{ $loginHref }}">
                                    <svg viewBox="0 0 24 24"><path d="M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4"/><path d="m10 16 4-4-4-4"/><path d="M14 12H4"/></svg>
                                    Sign in
                                </a>
                                @break
                            @case('home')
                                <a class="btn {{ $primary ? 'btn-primary' : 'btn-ghost' }}" href="{{ $homeHref }}">
                                    <svg viewBox="0 0 24 24"><path d="M4 11.5 12 5l8 6.5"/><path d="M6 10v9h12v-9"/></svg>
                                    {{ $homeLabel }}
                                </a>
                                @break
                            @case('back')
                                <a class="btn {{ $primary ? 'btn-primary' : 'btn-ghost' }}" href="{{ url()->previous() !== url()->current() ? url()->previous() : $homeHref }}" onclick="if (history.length > 1) { history.back(); return false; }">
                                    <svg viewBox="0 0 24 24"><path d="M19 12H5"/><path d="m11 18-6-6 6-6"/></svg>
                                    Go back
                                </a>
                                @break
                        @endswitch
                    @endforeach
                </div>

                <dl class="slip">
                    <dt>Requested</dt>
                    <dd><code>{{ \Illuminate\Support\Str::limit($path, 90) }}</code></dd>
                    <dt>Time</dt>
                    <dd>{{ now()->format('j M Y, H:i') }}</dd>
                    <dt>Status</dt>
                    <dd>{{ $code }} · {{ $status }}</dd>
                </dl>
            </section>
        </div>

        <p class="footer">© {{ date('Y') }} Ministry of Education · Federal Democratic Republic of Ethiopia</p>
    </main>
</body>
</html>
