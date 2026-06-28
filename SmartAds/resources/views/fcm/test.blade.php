<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>SmartAds FCM Test</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width:600px">

<div class="card shadow-sm mb-3">
<div class="card-header bg-primary text-white">
<h5 class="mb-0">🔔 SmartAds — اختبار الإشعارات</h5>
<small>الأجهزة المشتركة: <strong>{{ $totalTokens }}</strong></small>
</div>
<div class="card-body">
<div id="msg" class="alert alert-info">⏳ جاري تهيئة Firebase...</div>
<button id="btnSub" class="btn btn-success w-100 mb-2" disabled>✅ اشترك في الإشعارات</button>
<button id="btnUnsub" class="btn btn-outline-danger w-100 mb-3" disabled>🔕 إلغاء الاشتراك</button>
<hr>
<h6>📤 إرسال تجريبي</h6>
<input type="text" id="nTitle" class="form-control mb-2" value="إشعار تجريبي 🔔">
<textarea id="nBody" class="form-control mb-2" rows="2">رسالة تجريبية من SmartAds</textarea>
<button id="btnSend" class="btn btn-primary w-100">إرسال للجميع</button>
<div id="sendResult" class="mt-2"></div>
</div>
</div>

<div class="card shadow-sm">
<div class="card-body">
<h6>📋 السجل</h6>
<div id="log" style="background:#1a1d27;color:#cdd;border-radius:8px;padding:10px;font-size:12px;min-height:100px;font-family:monospace;overflow-y:auto;max-height:200px"></div>
</div>
</div>

</div>

<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js"></script>
<script>
var CFG  = {!! json_encode(config('smartads-fcm')) !!};
var CSRF = document.querySelector('meta[name=csrf-token]').content;
var msg_el = document.getElementById('msg');
var log_el = document.getElementById('log');
var messaging = null;
var myToken = null;

function addLog(text, color) {
    log_el.innerHTML += '<div style="color:' + (color || '#9cdcfe') + '">' + new Date().toLocaleTimeString() + ' — ' + text + '</div>';
    log_el.scrollTop = log_el.scrollHeight;
}
function setMsg(text, type) {
    msg_el.className = 'alert alert-' + (type || 'info');
    msg_el.innerHTML = text;
}

try {
    firebase.initializeApp({
        apiKey: CFG.api_key,
        authDomain: CFG.auth_domain,
        projectId: CFG.project_id,
        storageBucket: CFG.storage_bucket,
        messagingSenderId: CFG.messaging_sender_id,
        appId: CFG.app_id
    });
    messaging = firebase.messaging();
    addLog('Firebase جاهز ✅', '#4ec9b0');
} catch(e) {
    addLog('خطأ Firebase: ' + e.message, '#f48771');
    setMsg('❌ خطأ Firebase: ' + e.message, 'danger');
}

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/firebase-messaging-sw.js').then(function(reg) {
        addLog('Service Worker مسجّل ✅', '#4ec9b0');
        document.getElementById('btnSub').disabled = false;
        document.getElementById('btnUnsub').disabled = false;
        setMsg('✅ جاهز — اضغط زر الاشتراك', 'success');
    }).catch(function(e) {
        addLog('خطأ SW: ' + e.message, '#f48771');
        setMsg('❌ فشل Service Worker: ' + e.message, 'danger');
    });
} else {
    setMsg('❌ المتصفح لا يدعم Service Worker', 'danger');
}

document.getElementById('btnSub').onclick = function() {
    addLog('طلب إذن الإشعارات...', '#dcdcaa');
    Notification.requestPermission().then(function(perm) {
        addLog('الإذن: ' + perm, perm === 'granted' ? '#4ec9b0' : '#f48771');
        if (perm !== 'granted') { setMsg('❌ تم رفض الإذن', 'warning'); return; }
        return messaging.getToken({ vapidKey: CFG.vapid_key }).then(function(token) {
            if (!token) { setMsg('❌ لم يُنشأ توكن', 'danger'); return; }
            myToken = token;
            addLog('توكن: ' + token.substring(0, 40) + '...', '#4ec9b0');
            return fetch('/api/v1/smartads/fcm/token/save', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ token: token, browser: navigator.userAgent.substring(0, 80) })
            }).then(function(r) { return r.json(); }).then(function(d) {
                setMsg(d.success ? '✅ تم الاشتراك — يمكنك الآن استقبال الإشعارات' : '❌ فشل الحفظ', d.success ? 'success' : 'danger');
            });
        });
    }).catch(function(e) {
        addLog('خطأ: ' + e.message, '#f48771');
        setMsg('❌ ' + e.message, 'danger');
    });
};

document.getElementById('btnUnsub').onclick = function() {
    if (!myToken) { setMsg('لا يوجد توكن محفوظ', 'warning'); return; }
    fetch('/api/v1/smartads/fcm/token/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ token: myToken })
    }).then(function() { setMsg('✅ تم إلغاء الاشتراك', 'info'); myToken = null; });
};

document.getElementById('btnSend').onclick = function() {
    var t = document.getElementById('nTitle').value;
    var b = document.getElementById('nBody').value;
    fetch('/api/v1/smartads/fcm/send', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ title: t, body: b })
    }).then(function(r) { return r.json(); }).then(function(d) {
        document.getElementById('sendResult').innerHTML = d.success
            ? '<div class="alert alert-success mt-2">✅ أُرسل لـ ' + (d.result && d.result.success ? d.result.success : 0) + ' جهاز</div>'
            : '<div class="alert alert-danger mt-2">❌ فشل الإرسال</div>';
    });
};
</script>
</body>
</html>