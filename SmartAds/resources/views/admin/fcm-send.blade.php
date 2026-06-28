@extends("layouts.admin.app")
@section("title", "إرسال إشعارات Push")
@section("content")
<div class="content container-fluid">
    <div class="page-header mb-4">
        <h1 class="page-header-title">
            <i class="tio-notifications mr-2 text-primary"></i> إرسال إشعارات Push
        </h1>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">📤 إرسال لجميع الأجهزة المشتركة</h5>
                </div>
                <div class="card-body">
                    <div id="fcmAlert"></div>
                    <div class="form-group">
                        <label class="font-weight-bold">العنوان *</label>
                        <input type="text" id="fcmTitle" class="form-control" placeholder="مثال: عرض خاص">
                    </div>
                    <div class="form-group mt-3">
                        <label class="font-weight-bold">النص *</label>
                        <textarea id="fcmBody" class="form-control" rows="3" placeholder="نص الإشعار..."></textarea>
                    </div>
                    <div class="mt-3 p-3 bg-light rounded">
                        <small>عدد الأجهزة المشتركة: <strong>{{ $totalTokens }}</strong></small>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button id="fcmSendBtn" class="btn btn-primary px-5">إرسال</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById("fcmSendBtn").addEventListener("click", function() {
    var t = document.getElementById("fcmTitle").value.trim();
    var b = document.getElementById("fcmBody").value.trim();
    var a = document.getElementById("fcmAlert");
    this.disabled = true;
    this.textContent = "جاري الإرسال...";
    var btn = this;
    fetch("/api/v1/smartads/fcm/send", {
        method: "POST",
        headers: {"Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}"},
        body: JSON.stringify({title: t, body: b})
    }).then(function(r){ return r.json(); }).then(function(d){
        if (d.success) {
            a.innerHTML = "<div class=alert alert-success>تم الإرسال لـ " + (d.result ? d.result.success : 0) + " جهاز</div>";
        } else {
            a.innerHTML = "<div class=alert alert-danger>فشل</div>";
        }
    }).catch(function(e){
        a.innerHTML = "<div class=alert alert-danger>خطأ: " + e.message + "</div>";
    }).finally(function(){
        btn.disabled = false;
        btn.textContent = "إرسال";
    });
});
</script>
@endsection