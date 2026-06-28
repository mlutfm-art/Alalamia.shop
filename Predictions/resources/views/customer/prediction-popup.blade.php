{{-- =====================================================
     Predictions Pop Banner v5
     الإصلاح الحقيقي: نقل User ID من Session عبر Blade إلى JS
     @include('predictions::customer.prediction-popup')
===================================================== --}}

{{-- ── حقن User ID من الجلسة (يعمل بدون Bearer Token) ── --}}
@php
    $__predUid = null;
    foreach (['web','customer','api'] as $__g) {
        try {
            $__predUid = auth($__g)->id();
            if ($__predUid) break;
        } catch (\Throwable $__e) {}
    }
@endphp

<style>
#__po{display:none;position:fixed;inset:0;z-index:99999;align-items:center;justify-content:center;
  background:rgba(5,8,25,.75);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px)}
#__po.open{display:flex}
#__pm{width:500px;max-width:96vw;border-radius:32px;overflow:hidden;direction:rtl;
  font-family:inherit;
  box-shadow:0 50px 120px rgba(0,0,0,.7),0 0 0 1px rgba(255,255,255,.07);
  animation:__popin .45s cubic-bezier(.34,1.56,.64,1)}
#__pt{background:linear-gradient(160deg,#1e1b4b 0%,#312e81 45%,#1e3a5f 100%);
  padding:30px 30px 26px;position:relative;border-bottom:1px solid rgba(255,255,255,.07)}
#__pb{background:linear-gradient(160deg,#0f0c29 0%,#1e1b4b 100%);padding:22px 30px 28px}
#__px{position:absolute;top:16px;left:16px;background:rgba(255,255,255,.1);
  border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.8);border-radius:12px;
  width:38px;height:38px;font-size:16px;cursor:pointer;line-height:38px;text-align:center}
#__px:hover{background:rgba(255,255,255,.2)}
#__badge{display:inline-flex;align-items:center;gap:7px;background:rgba(239,68,68,.14);
  border:1px solid rgba(239,68,68,.32);border-radius:100px;padding:5px 16px;
  color:#fca5a5;font-size:13px;font-weight:700}
#__dot{width:8px;height:8px;border-radius:50%;background:#ef4444;animation:__pulse 1.4s infinite}
.po-team{text-align:center;flex:1}
.po-circle{width:80px;height:80px;border-radius:50%;margin:0 auto 10px;
  display:flex;align-items:center;justify-content:center;font-size:32px;
  box-shadow:0 4px 20px rgba(0,0,0,.35)}
.po-logo{width:80px;height:80px;object-fit:contain;margin-bottom:10px;
  filter:drop-shadow(0 4px 16px rgba(0,0,0,.4))}
.po-tname{font-weight:800;font-size:17px;color:#fff;text-shadow:0 2px 8px rgba(0,0,0,.4)}
#__scores{display:flex;align-items:center;gap:10px}
.po-inp{width:88px;height:88px;border:2.5px solid rgba(255,255,255,.28);border-radius:20px;
  background:rgba(255,255,255,.1);color:#fff;font-size:36px;font-weight:900;text-align:center;
  outline:none;backdrop-filter:blur(10px);transition:all .2s;-moz-appearance:textfield}
.po-inp::placeholder{color:rgba(255,255,255,.3)}
.po-inp:focus{border-color:rgba(255,255,255,.8);background:rgba(255,255,255,.18);
  box-shadow:0 0 0 4px rgba(255,255,255,.1),0 0 30px rgba(99,102,241,.4)}
.po-inp::-webkit-outer-spin-button,.po-inp::-webkit-inner-spin-button{-webkit-appearance:none}
.po-cd{background:rgba(0,0,0,.32);border:1px solid rgba(255,255,255,.11);
  border-radius:14px;padding:8px 14px;text-align:center;min-width:62px;backdrop-filter:blur(8px)}
.po-pt{flex:1;border-radius:16px;padding:12px 8px;text-align:center}
#__sbtn{width:100%;padding:17px;border:none;border-radius:18px;
  background:linear-gradient(270deg,#6366f1,#8b5cf6,#6366f1);background-size:300%;
  animation:__shimmer 3s linear infinite;color:#fff;font-size:18px;font-weight:800;
  cursor:pointer;box-shadow:0 6px 30px rgba(99,102,241,.5);transition:transform .15s,box-shadow .15s}
#__sbtn:hover{transform:translateY(-3px);box-shadow:0 12px 40px rgba(99,102,241,.6)}
#__sbtn:active{transform:translateY(0)}
#__sbtn:disabled{opacity:.65;cursor:not-allowed;transform:none;animation:none}
#__login-msg{display:none;text-align:center;padding:6px 0}
#__login-btn{display:inline-block;margin-top:14px;padding:15px 40px;
  background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;
  border:none;border-radius:16px;font-size:16px;font-weight:700;
  cursor:pointer;text-decoration:none}
#__done{display:none;text-align:center;padding:8px 0}
#__done-btn{width:100%;padding:16px;border:none;border-radius:18px;
  background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;
  font-size:17px;font-weight:800;cursor:pointer}
.__shake{animation:__shk .4s}
@keyframes __popin{from{transform:scale(.78) translateY(55px);opacity:0}to{transform:scale(1) translateY(0);opacity:1}}
@keyframes __pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(.8)}}
@keyframes __shimmer{0%{background-position:200% center}100%{background-position:-200% center}}
@keyframes __shk{0%,100%{transform:translateX(0)}20%,60%{transform:translateX(-12px)}40%,80%{transform:translateX(12px)}}
</style>

<div id="__po" role="dialog" aria-modal="true">
 <div id="__pm">
  <div id="__pt">
   <button id="__px" aria-label="إغلاق">✕</button>
   <div style="display:flex;justify-content:center;margin-bottom:22px">
    <div id="__badge"><span id="__dot"></span>⚽ مباراة نشطة — توقّع الآن!</div>
   </div>
   <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
    <div class="po-team" id="__t1area">
     <div class="po-circle" style="background:linear-gradient(135deg,#4f46e5,#818cf8)" id="__t1icon">⚽</div>
     <div class="po-tname" id="__t1name">—</div>
    </div>
    <div style="text-align:center;flex-shrink:0">
     <div id="__scores">
      <input class="po-inp" id="__s1" type="number" min="0" max="99" placeholder="0" inputmode="numeric">
      <span style="color:rgba(255,255,255,.4);font-size:28px;font-weight:900">:</span>
      <input class="po-inp" id="__s2" type="number" min="0" max="99" placeholder="0" inputmode="numeric">
     </div>
     <div style="color:rgba(255,255,255,.4);font-size:12px;margin-top:10px">أدخل توقعك للنتيجة</div>
    </div>
    <div class="po-team" id="__t2area">
     <div class="po-circle" style="background:linear-gradient(135deg,#059669,#34d399)" id="__t2icon">⚽</div>
     <div class="po-tname" id="__t2name">—</div>
    </div>
   </div>
   <div id="__submitted-score" style="display:none;text-align:center;margin-top:8px">
    <div style="background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.32);border-radius:18px;padding:12px 24px;display:inline-block">
     <div style="color:#4ade80;font-size:36px;font-weight:900;line-height:1" id="__score-display"></div>
     <div style="color:rgba(255,255,255,.5);font-size:12px;margin-top:6px">توقعك المُرسَل ✓</div>
    </div>
   </div>
   <div style="display:flex;justify-content:center;gap:10px;margin-top:24px">
    <div class="po-cd"><div style="font-size:22px;font-weight:900;color:#fff;line-height:1" id="__h">00</div><div style="font-size:11px;color:rgba(255,255,255,.4);margin-top:4px">ساعة</div></div>
    <div class="po-cd"><div style="font-size:22px;font-weight:900;color:#fff;line-height:1" id="__m">00</div><div style="font-size:11px;color:rgba(255,255,255,.4);margin-top:4px">دقيقة</div></div>
    <div class="po-cd"><div style="font-size:22px;font-weight:900;color:#fff;line-height:1" id="__s">00</div><div style="font-size:11px;color:rgba(255,255,255,.4);margin-top:4px">ثانية</div></div>
   </div>
   <div style="text-align:center;color:rgba(255,255,255,.32);font-size:12px;margin-top:10px">⏰ ينتهي وقت التوقع بعد</div>
  </div>

  <div id="__pb">
   <div id="__form-area">
    <div style="display:flex;gap:10px;margin-bottom:18px">
     <div class="po-pt" style="background:rgba(251,191,36,.09);border:1px solid rgba(251,191,36,.22)">
      <div style="font-size:22px">🥇</div>
      <div style="color:#fbbf24;font-weight:900;font-size:16px;margin-top:4px" id="__full-pts">— نقطة</div>
      <div style="color:rgba(255,255,255,.38);font-size:11px;margin-top:3px">إصابة دقيقة</div>
     </div>
     <div class="po-pt" style="background:rgba(148,163,184,.07);border:1px solid rgba(148,163,184,.18)">
      <div style="font-size:22px">🥈</div>
      <div style="color:#94a3b8;font-weight:900;font-size:16px;margin-top:4px" id="__half-pts">— نقطة</div>
      <div style="color:rgba(255,255,255,.38);font-size:11px;margin-top:3px">توقع قريب</div>
     </div>
    </div>
    <button id="__sbtn" type="button">✅ أرسل توقعي الآن</button>
    <div style="text-align:center;margin-top:11px;color:rgba(255,255,255,.28);font-size:12px">توقع واحد فقط لكل مباراة</div>
   </div>

   <div id="__login-msg">
    <div style="font-size:44px;margin-bottom:10px">🔐</div>
    <div style="color:#fff;font-weight:800;font-size:18px;margin-bottom:8px">سجّل دخولك أولاً</div>
    <div style="color:rgba(255,255,255,.45);font-size:14px;margin-bottom:4px">للمشاركة في توقعات المباريات وربح النقاط</div>
    <a id="__login-btn" href="/customer/auth/login">🔑 تسجيل الدخول</a>
   </div>

   <div id="__done">
    <div style="font-size:56px;margin-bottom:10px">🎉</div>
    <div style="color:#fff;font-weight:800;font-size:19px;margin-bottom:6px">تم إرسال توقعك بنجاح!</div>
    <div style="color:rgba(255,255,255,.42);font-size:13px;margin-bottom:22px">سيتم منح النقاط بعد انتهاء المباراة وإدخال النتيجة</div>
    <button id="__done-btn" type="button">رائع، أغلق النافذة 🏆</button>
   </div>
  </div>
 </div>
</div>

{{-- ── User ID من Blade (Session) يُمرَّر إلى JS ── --}}
<script>
window.__PRED_UID = {{ $__predUid ? (int)$__predUid : 'null' }};
</script>

<script>
(function(){
  var API_ACTIVE = '/api/v1/predictions/matches/active';
  var API_SUBMIT = '/api/v1/predictions/submit';

  function p2(n){return String(n).padStart(2,'0');}
  function clamp(v){return Math.min(99,Math.max(0,parseInt(v)||0));}

  function dismiss(key){
    if(key) sessionStorage.setItem('__po_'+key,'1');
    document.getElementById('__po').classList.remove('open');
  }

  /* ── جمع التوكن من كل المصادر الممكنة ── */
  function getToken(){
    var keys=['auth_token','token','customer_token','access_token',
              'user_token','bearer_token','customerToken','authToken'];
    for(var i=0;i<keys.length;i++){
      var v=localStorage.getItem(keys[i])||sessionStorage.getItem(keys[i]);
      if(v&&v.length>10) return v;
    }
    return null;
  }

  function setTeam(n,name,logo){
    document.getElementById('__t'+n+'name').textContent=name;
    if(logo){
      var img=document.createElement('img');
      img.src=logo; img.className='po-logo'; img.alt=name;
      img.onerror=function(){this.style.display='none';};
      var el=document.getElementById('__t'+n+'icon');
      el.parentNode.insertBefore(img,el);
      el.style.display='none';
    }
  }

  function startCD(closeTime){
    var tick=setInterval(function(){
      var diff=Math.max(0,new Date(closeTime)-Date.now());
      if(diff===0){clearInterval(tick);dismiss('_time');return;}
      document.getElementById('__h').textContent=p2(Math.floor(diff/3600000));
      document.getElementById('__m').textContent=p2(Math.floor((diff%3600000)/60000));
      document.getElementById('__s').textContent=p2(Math.floor((diff%60000)/1000));
    },1000);
  }

  function showLoginRequired(){
    document.getElementById('__form-area').style.display='none';
    document.getElementById('__login-msg').style.display='block';
  }

  function launch(data){
    var key=data.match_id;
    if(sessionStorage.getItem('__po_'+key)) return;

    setTeam(1,data.team1,data.team1_logo);
    setTeam(2,data.team2,data.team2_logo);
    document.getElementById('__full-pts').textContent=data.reward_points+' نقطة';
    document.getElementById('__half-pts').textContent=Math.round(data.reward_points*.5)+' نقطة';
    startCD(data.prediction_close_time);

    setTimeout(function(){document.getElementById('__po').classList.add('open');},2000);

    document.getElementById('__px').onclick=function(){dismiss(key);};
    document.getElementById('__po').onclick=function(e){if(e.target===this)dismiss(key);};
    document.addEventListener('keydown',function(e){if(e.key==='Escape')dismiss(key);});

    ['__s1','__s2'].forEach(function(id){
      document.getElementById(id).addEventListener('input',function(){
        if(this.value!=='') this.value=clamp(this.value);
      });
    });

    document.getElementById('__sbtn').onclick=function(){
      var v1=document.getElementById('__s1').value;
      var v2=document.getElementById('__s2').value;
      if(v1===''||v2===''){
        var sc=document.getElementById('__scores');
        sc.classList.add('__shake');
        setTimeout(function(){sc.classList.remove('__shake');},500);
        return;
      }

      var btn=this; btn.disabled=true; btn.textContent='⏳ جاري الإرسال...';

      /* ── بناء الـ Headers ── */
      var h={
        'Content-Type':'application/json',
        'Accept':'application/json',
        'X-Requested-With':'XMLHttpRequest'
      };

      var csrf=document.querySelector('meta[name="csrf-token"]');
      if(csrf) h['X-CSRF-TOKEN']=csrf.getAttribute('content');

      /* 1. User ID من Blade (الأولوية الأولى — الأموثق) */
      if(window.__PRED_UID){
        h['X-User-Id']=String(window.__PRED_UID);
      }

      /* 2. Bearer Token من localStorage (للتطبيق) */
      var tok=getToken();
      if(tok) h['Authorization']='Bearer '+tok;

      fetch(API_SUBMIT,{
        method:'POST',
        headers:h,
        credentials:'include',
        body:JSON.stringify({
          match_id:key,
          predicted_team1:parseInt(v1),
          predicted_team2:parseInt(v2)
        })
      })
      .then(function(r){
        if(r.status===401){showLoginRequired();return null;}
        return r.json();
      })
      .then(function(res){
        if(!res) return;
        if(res.success){
          document.getElementById('__score-display').textContent=
            data.team1+' '+v1+' : '+v2+' '+data.team2;
          document.getElementById('__scores').parentNode.style.display='none';
          document.getElementById('__submitted-score').style.display='block';
          document.getElementById('__form-area').style.display='none';
          document.getElementById('__done').style.display='block';
          sessionStorage.setItem('__po_'+key,'1');
        } else {
          var msg=res.error||'حدث خطأ، حاول مجدداً';
          if(msg==='already_predicted') msg='لقد قمت بالتوقع على هذه المباراة مسبقاً ✓';
          if(msg==='prediction_closed') msg='انتهى وقت التوقع على هذه المباراة';
          alert(msg);
          btn.disabled=false; btn.textContent='✅ أرسل توقعي الآن';
        }
      })
      .catch(function(){
        alert('خطأ في الاتصال، حاول مجدداً');
        btn.disabled=false; btn.textContent='✅ أرسل توقعي الآن';
      });
    };

    document.getElementById('__done-btn').onclick=function(){dismiss(key);};
  }

  fetch(API_ACTIVE,{headers:{'Accept':'application/json'},credentials:'include'})
    .then(function(r){return r.json();})
    .then(function(d){if(d&&d.show_popup) launch(d);})
    .catch(function(){});
})();
</script>
