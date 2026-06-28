<button id="subscribeBtn">تفعيل الإشعارات</button>

<script type="module">
import { getFcmToken } from "/js/fcm.js";

document.getElementById('subscribeBtn').onclick = async () => {
    const token = await getFcmToken();

    if (!token) return;

    await fetch('/api/device/token', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            token: token,
            browser: navigator.userAgent
        })
    });

    alert("تم الاشتراك في الإشعارات");
};
</script>