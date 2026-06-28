<script>
fetch('/admin/notification-templates/api')
.then(r=>r.json())
.then(data=>{
    let opt='<option value="">-- اختر قالبا --</option>';
    data.forEach(t=>opt+='<option value="'+t.id+'">'+t.name+'</option>');
    document.getElementById('templateSelect').innerHTML=opt;
});
function loadTemplate(){
    const id=document.getElementById('templateSelect').value;
    if(!id)return;
    fetch('/admin/notification-templates/api')
    .then(r=>r.json())
    .then(data=>{
        const t=data.find(x=>x.id==id);
        if(t){ document.getElementById('title').value=t.title; document.getElementById('body').value=t.body; }
    });
}
function saveAsTemplate(){
    const name=prompt('اسم القالب:');
    if(!name)return;
    fetch('/admin/notification-templates',{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body:JSON.stringify({name:name, title:document.getElementById('title').value, body:document.getElementById('body').value})
    }).then(r=>r.json()).then(d=>{ if(d.success)alert('تم الحفظ'); });
}
</script>
