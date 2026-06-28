{{--
════════════════════════════════════════════════════════════════════
  <x-smartads> — Smart Engagement Engine — Blade Component
════════════════════════════════════════════════════════════════════
  الاستخدام:
    {{-- بانرات الصفحة الرئيسية --}}
    <x-smartads placement="home" />

    {{-- بوب أب قسم معين --}}
    <x-smartads placement="category" type="popup" :category-id="$category->id" />

    {{-- بانر فيديو مع تحديد الجهاز --}}
    <x-smartads placement="product" type="video" device="android" :limit="1" />

    {{-- إشعارات داخلية --}}
    <x-smartads placement="home" type="notification" />
════════════════════════════════════════════════════════════════════
--}}

@if(!empty($ads))

{{-- ══ CSS (مُضمَّن مرة واحدة) ════════════════════════════════════ --}}
@once
<style>
/* ── حاوية عامة ─────────────────────────────────────────────────── */
.sea-wrap { position: relative; margin-bottom: 16px; }

/* ── Banner ─────────────────────────────────────────────────────── */
.sea-banner {
    display: flex;
    align-items: center;
    gap: 14px;
    border-radius: 14px;
    padding: 14px 16px;
    cursor: pointer;
    overflow: hidden;
    position: relative;
    transition: transform .18s ease, box-shadow .18s ease;
}
.sea-banner:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.12); }
.sea-banner img.sea-thumb {
    width: 64px; height: 64px; border-radius: 10px; object-fit: cover; flex-shrink: 0;
}
.sea-banner .sea-body { flex: 1; min-width: 0; }
.sea-banner .sea-title { font-weight: 700; font-size: 15px; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sea-banner .sea-sub   { font-size: 13px; opacity: .75; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sea-banner .sea-btn   { font-size: 13px; font-weight: 600; padding: 7px 16px; border-radius: 22px; border: none; cursor: pointer; white-space: nowrap; flex-shrink: 0; }
.sea-close {
    position: absolute; top: 8px; right: 10px;
    background: rgba(0,0,0,.12); border: none; border-radius: 50%;
    width: 24px; height: 24px; font-size: 14px; cursor: pointer;
    display: flex; align-items: center; justify-content: center; line-height: 1;
}

/* ── Popup (Overlay) ─────────────────────────────────────────────── */
.sea-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.55);
    display: flex; align-items: center; justify-content: center;
    z-index: 9999; padding: 20px;
    animation: seaFadeIn .25s ease;
}
@keyframes seaFadeIn { from { opacity: 0 } to { opacity: 1 } }
.sea-popup {
    background: #fff; border-radius: 20px; overflow: hidden;
    max-width: 420px; width: 100%;
    animation: seaSlideUp .3s cubic-bezier(.34,1.56,.64,1);
    position: relative;
}
@keyframes seaSlideUp { from { transform: translateY(40px); opacity: 0 } to { transform: translateY(0); opacity: 1 } }
.sea-popup img.sea-popup-img { width: 100%; max-height: 220px; object-fit: cover; display: block; }
.sea-popup .sea-popup-body { padding: 20px; }
.sea-popup .sea-popup-title { font-size: 19px; font-weight: 700; margin-bottom: 6px; }
.sea-popup .sea-popup-sub   { font-size: 14px; color: #666; margin-bottom: 16px; }
.sea-popup .sea-popup-desc  { font-size: 13px; color: #888; margin-bottom: 18px; }
.sea-popup .sea-popup-cta {
    display: block; width: 100%; padding: 12px;
    border-radius: 12px; border: none; font-size: 15px;
    font-weight: 700; cursor: pointer; text-align: center;
}
.sea-popup-close {
    position: absolute; top: 12px; right: 12px;
    background: rgba(0,0,0,.25); border: none; color: #fff;
    border-radius: 50%; width: 30px; height: 30px; font-size: 16px;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    z-index: 2;
}

/* ── Native Card ─────────────────────────────────────────────────── */
.sea-native {
    border-radius: 16px; overflow: hidden;
    box-shadow: 0 2px 16px rgba(0,0,0,.08);
    cursor: pointer; transition: transform .18s ease;
}
.sea-native:hover { transform: translateY(-3px); }
.sea-native img.sea-native-img { width: 100%; height: 160px; object-fit: cover; display: block; }
.sea-native .sea-native-body { padding: 14px 16px 16px; }
.sea-native .sea-native-title { font-weight: 700; font-size: 16px; margin-bottom: 4px; }
.sea-native .sea-native-sub   { font-size: 13px; color: #777; margin-bottom: 12px; }
.sea-native .sea-native-desc  { font-size: 12px; color: #999; margin-bottom: 14px; }
.sea-native .sea-native-btn {
    display: inline-block; padding: 9px 20px;
    border-radius: 22px; border: none; font-size: 13px;
    font-weight: 700; cursor: pointer;
}

/* ── Video ───────────────────────────────────────────────────────── */
.sea-video-wrap {
    position: relative; border-radius: 14px; overflow: hidden;
    cursor: pointer;
}
.sea-video-wrap video, .sea-video-wrap iframe {
    width: 100%; display: block; border-radius: 14px;
}
.sea-video-overlay {
    position: absolute; inset: 0; display: flex;
    flex-direction: column; justify-content: flex-end;
    background: linear-gradient(0deg, rgba(0,0,0,.65) 0%, transparent 55%);
    padding: 16px; border-radius: 14px;
}
.sea-video-title { color: #fff; font-size: 16px; font-weight: 700; margin-bottom: 4px; }
.sea-video-btn {
    display: inline-block; padding: 8px 18px; border-radius: 20px;
    border: 2px solid #fff; color: #fff; font-size: 13px; font-weight: 600;
    background: transparent; cursor: pointer;
    align-self: flex-start;
}

/* ── Notification Toast ──────────────────────────────────────────── */
.sea-notif-wrap {
    position: fixed; bottom: 20px; right: 20px;
    z-index: 9990; display: flex; flex-direction: column; gap: 10px;
    pointer-events: none;
}
.sea-notif {
    background: #fff; border-radius: 14px;
    box-shadow: 0 6px 24px rgba(0,0,0,.15);
    padding: 14px 16px; max-width: 340px; width: 340px;
    display: flex; align-items: flex-start; gap: 12px;
    cursor: pointer; pointer-events: all;
    animation: seaSlideInRight .35s cubic-bezier(.34,1.56,.64,1);
    transition: opacity .3s, transform .3s;
}
@keyframes seaSlideInRight {
    from { transform: translateX(120px); opacity: 0 }
    to   { transform: translateX(0);      opacity: 1 }
}
.sea-notif.sea-notif-hiding { opacity: 0; transform: translateX(40px); }
.sea-notif img.sea-notif-img {
    width: 46px; height: 46px; border-radius: 10px; object-fit: cover; flex-shrink: 0;
}
.sea-notif .sea-notif-icon {
    width: 46px; height: 46px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; flex-shrink: 0;
}
.sea-notif-body { flex: 1; }
.sea-notif-title { font-weight: 700; font-size: 14px; margin-bottom: 2px; }
.sea-notif-sub   { font-size: 12px; color: #777; }
.sea-notif-close {
    background: none; border: none; font-size: 16px; color: #bbb;
    cursor: pointer; flex-shrink: 0; line-height: 1; padding: 0;
}

/* ── Progress bar (مدة العرض) ────────────────────────────────────── */
.sea-progress { height: 3px; border-radius: 0 0 14px 14px; background: rgba(255,255,255,.3); overflow: hidden; }
.sea-progress-bar { height: 100%; border-radius: 3px; transition: width linear; }
</style>

<div id="sea-notif-container" class="sea-notif-wrap"></div>
@endonce

{{-- ══ Render كل إعلان حسب نوعه ══════════════════════════════════ --}}
@foreach($ads as $idx => $item)
@php
    $ad      = $item['model'];
    $payload = $item['payload'];
    $disp    = $payload['display'];
    $action  = $payload['action'];
    $bg      = $disp['background_color'] ?? '#ffffff';
    $txt     = $disp['text_color']       ?? '#212121';
    $btnText = $disp['button_text']      ?? 'اعرف أكثر';
    $sub     = $disp['subtitle']         ?? '';
    $desc    = $disp['description']      ?? '';
    $adId    = $ad->id;
    $imgUrl  = $ad->image_url;
    $vidUrl  = $ad->video_url;
@endphp

{{-- ────────────────────────────────────────────────────────────── --}}
{{-- 1. BANNER (شريط أفقي) --}}
{{-- ────────────────────────────────────────────────────────────── --}}
@if($ad->ad_type === 'banner')
<div class="sea-wrap" data-sea-id="{{ $adId }}">
    <div class="sea-banner"
         style="background:{{ $bg }};color:{{ $txt }}"
         onclick="seaHandleClick({{ $adId }}, {{ json_encode($action) }})">
        @if($imgUrl)
            <img src="{{ $imgUrl }}" class="sea-thumb" alt="{{ $ad->title }}">
        @endif
        <div class="sea-body">
            <div class="sea-title" style="color:{{ $txt }}">{{ $ad->title }}</div>
            @if($sub)<div class="sea-sub" style="color:{{ $txt }}">{{ $sub }}</div>@endif
        </div>
        <button class="sea-btn"
                style="background:{{ $txt }};color:{{ $bg }}"
                onclick="event.stopPropagation();seaHandleClick({{ $adId }}, {{ json_encode($action) }})">
            {{ $btnText }}
        </button>
        <button class="sea-close" onclick="event.stopPropagation();seaDismiss({{ $adId }})"
                title="إغلاق">×</button>
    </div>
    <div class="sea-progress" style="margin-top:-4px">
        <div class="sea-progress-bar" id="sea-prog-{{ $adId }}"
             style="background:{{ $txt }};width:100%"></div>
    </div>
</div>

{{-- ────────────────────────────────────────────────────────────── --}}
{{-- 2. POPUP (نافذة منبثقة) --}}
{{-- ────────────────────────────────────────────────────────────── --}}
@elseif($ad->ad_type === 'popup')
<div class="sea-overlay" id="sea-popup-{{ $adId }}"
     onclick="if(event.target===this)seaDismiss({{ $adId }})">
    <div class="sea-popup">
        <button class="sea-popup-close" onclick="seaDismiss({{ $adId }})">×</button>
        @if($imgUrl)
            <img src="{{ $imgUrl }}" class="sea-popup-img" alt="{{ $ad->title }}">
        @endif
        <div class="sea-popup-body" style="background:{{ $bg }}">
            <div class="sea-popup-title" style="color:{{ $txt }}">{{ $ad->title }}</div>
            @if($sub)<div class="sea-popup-sub" style="color:{{ $txt }}">{{ $sub }}</div>@endif
            @if($desc)<div class="sea-popup-desc" style="color:{{ $txt }}">{{ $desc }}</div>@endif
            <button class="sea-popup-cta"
                    style="background:{{ $txt }};color:{{ $bg }}"
                    onclick="seaHandleClick({{ $adId }}, {{ json_encode($action) }})">
                {{ $btnText }}
            </button>
        </div>
        <div class="sea-progress">
            <div class="sea-progress-bar" id="sea-prog-{{ $adId }}"
                 style="background:{{ $txt }};width:100%"></div>
        </div>
    </div>
</div>

{{-- ────────────────────────────────────────────────────────────── --}}
{{-- 3. NATIVE CARD (بطاقة محتوى) --}}
{{-- ────────────────────────────────────────────────────────────── --}}
@elseif($ad->ad_type === 'native')
<div class="sea-wrap" data-sea-id="{{ $adId }}">
    <div class="sea-native" style="background:{{ $bg }}"
         onclick="seaHandleClick({{ $adId }}, {{ json_encode($action) }})">
        @if($imgUrl)
            <img src="{{ $imgUrl }}" class="sea-native-img" alt="{{ $ad->title }}">
        @endif
        <div class="sea-native-body">
            <div class="sea-native-title" style="color:{{ $txt }}">{{ $ad->title }}</div>
            @if($sub)<div class="sea-native-sub">{{ $sub }}</div>@endif
            @if($desc)<div class="sea-native-desc">{{ $desc }}</div>@endif
            <button class="sea-native-btn"
                    style="background:{{ $txt }};color:{{ $bg }}">
                {{ $btnText }}
            </button>
        </div>
    </div>
</div>

{{-- ────────────────────────────────────────────────────────────── --}}
{{-- 4. VIDEO --}}
{{-- ────────────────────────────────────────────────────────────── --}}
@elseif($ad->ad_type === 'video')
<div class="sea-wrap" data-sea-id="{{ $adId }}">
    <div class="sea-video-wrap"
         onclick="seaHandleClick({{ $adId }}, {{ json_encode($action) }})">
        @php
            $isYoutube = $vidUrl && (str_contains($vidUrl,'youtube') || str_contains($vidUrl,'youtu.be'));
            $isExternal = $vidUrl && str_starts_with($vidUrl,'http');
        @endphp
        @if($isYoutube)
            @php
                preg_match('/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $vidUrl, $m);
                $ytId = $m[1] ?? '';
            @endphp
            <div style="position:relative;padding-top:56.25%">
                <iframe src="https://www.youtube.com/embed/{{ $ytId }}?autoplay=0&controls=1&modestbranding=1"
                        style="position:absolute;inset:0;width:100%;height:100%;border:none;border-radius:14px"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen loading="lazy">
                </iframe>
            </div>
        @elseif($vidUrl)
            <video controls preload="metadata" playsinline
                   poster="{{ $imgUrl }}" style="max-height:320px">
                <source src="{{ $vidUrl }}" type="video/mp4">
            </video>
        @elseif($imgUrl)
            <img src="{{ $imgUrl }}" style="width:100%;border-radius:14px" alt="{{ $ad->title }}">
        @endif

        <div class="sea-video-overlay">
            <div class="sea-video-title">{{ $ad->title }}</div>
            @if($btnText !== 'اعرف أكثر' || $action['action_type'] !== 'none')
                <button class="sea-video-btn"
                        onclick="event.stopPropagation();seaHandleClick({{ $adId }}, {{ json_encode($action) }})">
                    {{ $btnText }}
                </button>
            @endif
        </div>
    </div>
</div>

{{-- ────────────────────────────────────────────────────────────── --}}
{{-- 5. NOTIFICATION (Toast يظهر في الزاوية) --}}
{{-- ────────────────────────────────────────────────────────────── --}}
@elseif($ad->ad_type === 'notification')
{{-- يُحقَن تلقائياً في الـ JS container عند تحميل الصفحة --}}
<template id="sea-notif-tpl-{{ $adId }}">
    <div class="sea-notif" id="sea-notif-{{ $adId }}"
         onclick="seaHandleClick({{ $adId }}, {{ json_encode($action) }});seaDismissNotif({{ $adId }})">
        @if($imgUrl)
            <img src="{{ $imgUrl }}" class="sea-notif-img" alt="{{ $ad->title }}">
        @else
            <div class="sea-notif-icon" style="background:{{ $bg }}">🔔</div>
        @endif
        <div class="sea-notif-body">
            <div class="sea-notif-title">{{ $ad->title }}</div>
            @if($sub)<div class="sea-notif-sub">{{ $sub }}</div>@endif
        </div>
        <button class="sea-notif-close"
                onclick="event.stopPropagation();seaDismissNotif({{ $adId }})">×</button>
    </div>
</template>
@endif

@endforeach

{{-- ══ JavaScript Engine ══════════════════════════════════════════ --}}
@once
<script>
(function() {
    // ── تتبع الظهور تلقائياً لكل إعلان مرئي ──────────────────────
    const trackImpression = id =>
        fetch(`/api/v1/smartads/track-impression/${id}`, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''}
        }).catch(() => {});

    const trackClick = id =>
        fetch(`/api/v1/smartads/track-click/${id}`, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''}
        }).catch(() => {});

    // ── إغلاق إعلان ───────────────────────────────────────────────
    window.seaDismiss = function(id) {
        const el = document.querySelector(`[data-sea-id="${id}"]`)
               || document.getElementById(`sea-popup-${id}`);
        if (!el) return;
        el.style.transition = 'opacity .3s, transform .3s';
        el.style.opacity = '0';
        el.style.transform = 'translateY(-8px)';
        setTimeout(() => el.remove(), 320);
    };

    // ── إغلاق toast ───────────────────────────────────────────────
    window.seaDismissNotif = function(id) {
        const el = document.getElementById(`sea-notif-${id}`);
        if (!el) return;
        el.classList.add('sea-notif-hiding');
        setTimeout(() => el.remove(), 300);
    };

    // ── معالجة الإجراء عند الضغط ─────────────────────────────────
    window.seaHandleClick = function(id, action) {
        trackClick(id);

        const type     = action.action_type;
        const payload  = action.payload || {};
        const deepLink = action.deep_link;
        const fallback = action.fallback_url;
        const feedback = action.feedback || {};

        // 1. تنقل داخلي — رابط نسبي
        if (['product','category','brand','deals','wallet','order_tracking','account_settings'].includes(type)) {
            if (fallback) { window.location.href = fallback; return; }
        }

        // 2. نسخ للحافظة
        if (type === 'copy_to_clipboard') {
            navigator.clipboard.writeText(payload.text || '').then(() => {
                seaShowFeedback(feedback);
            });
            return;
        }

        // 3. تطبيق كوبون
        if (type === 'apply_coupon') {
            window.location.href = fallback || `/cart?coupon=${payload.code}`;
            return;
        }

        // 4. deep link → fallback
        if (deepLink) {
            const tryDeepLink = window.open(deepLink, '_blank');
            if (!tryDeepLink || tryDeepLink.closed || typeof tryDeepLink.closed === 'undefined') {
                if (fallback) window.open(fallback, '_blank');
            } else {
                setTimeout(() => {
                    if (fallback) window.open(fallback, '_blank');
                }, 1200);
            }
        } else if (fallback) {
            window.open(fallback, '_blank');
        }

        seaShowFeedback(feedback);
    };

    // ── عرض Feedback Snackbar ─────────────────────────────────────
    window.seaShowFeedback = function(feedback) {
        if (!feedback || !feedback.show_snackbar || !feedback.message) return;
        const snack = document.createElement('div');
        snack.style.cssText = `
            position:fixed;bottom:24px;left:50%;transform:translateX(-50%);
            background:#323232;color:#fff;padding:12px 22px;border-radius:24px;
            font-size:14px;z-index:10000;font-weight:500;
            animation:seaFadeIn .25s ease;box-shadow:0 4px 16px rgba(0,0,0,.25);
            white-space:nowrap;
        `;
        snack.textContent = feedback.message;
        document.body.appendChild(snack);
        setTimeout(() => {
            snack.style.opacity = '0';
            snack.style.transition = 'opacity .3s';
            setTimeout(() => snack.remove(), 300);
        }, feedback.duration_ms || 3000);
    };

    // ── تهيئة عند DOMContentLoaded ───────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        // Intersection Observer لتتبع الظهور
        const observer = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    const id = e.target.dataset.seaId;
                    if (id) {
                        trackImpression(id);
                        observer.unobserve(e.target);
                        // Progress bar countdown
                        startProgressBar(id);
                    }
                }
            });
        }, { threshold: 0.5 });

        document.querySelectorAll('[data-sea-id]').forEach(el => observer.observe(el));

        // Popup: تتبع الظهور فوري
        document.querySelectorAll('[id^="sea-popup-"]').forEach(el => {
            const id = el.id.replace('sea-popup-', '');
            trackImpression(id);
            startProgressBar(id, () => seaDismiss(id));
        });

        // Toast notifications: أضفها للـ container بعد تأخير بسيط
        const container = document.getElementById('sea-notif-container');
        document.querySelectorAll('[id^="sea-notif-tpl-"]').forEach((tpl, idx) => {
            setTimeout(() => {
                if (!container) return;
                const id = tpl.id.replace('sea-notif-tpl-', '');
                const clone = tpl.content.cloneNode(true);
                container.appendChild(clone);
                trackImpression(id);
                // إخفاء تلقائي بعد 6 ثوان
                setTimeout(() => seaDismissNotif(id), 6000);
            }, idx * 800); // تأخير بين كل toast
        });
    });

    // ── Progress Bar countdown ────────────────────────────────────
    function startProgressBar(id, onComplete) {
        const bar = document.getElementById(`sea-prog-${id}`);
        if (!bar) return;
        const duration = 5000;
        bar.style.transition = `width ${duration}ms linear`;
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                bar.style.width = '0%';
            });
        });
        if (onComplete) {
            setTimeout(onComplete, duration);
        }
    }
})();
</script>
@endonce

@endif{{-- end @if(!empty($ads)) --}}
