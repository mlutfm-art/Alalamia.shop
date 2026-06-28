<?php
namespace Modules\SmartAds\app\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Modules\Alertmarkting\app\Models\NotificationGroup;
use Modules\SmartAds\app\Services\FCMService;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class NotificationGroupController extends Controller
{
    public function index()
    {
        $groups = NotificationGroup::latest()->paginate(15);
        return view('smartads::admin.groups.index', compact('groups'));
    }

    public function create() { return view('smartads::admin.groups.create'); }

    public function store(Request $request)
    {
        $data = $request->validate(['name'=>'required','type'=>'required','description'=>'nullable']);
        NotificationGroup::create($data);
        Toastr::success('تم إنشاء المجموعة');
        return redirect('/admin/smartads/groups');
    }

    public function edit($id)
    {
        $group = NotificationGroup::findOrFail($id);
        return view('smartads::admin.groups.edit', compact('group'));
    }

    public function update(Request $request, $id)
    {
        $group = NotificationGroup::findOrFail($id);
        $data = $request->validate(['name'=>'required','type'=>'required','description'=>'nullable']);
        $group->update($data);
        Toastr::success('تم تحديث المجموعة');
        return redirect('/admin/smartads/groups');
    }

    public function destroy($id)
    {
        NotificationGroup::findOrFail($id)->delete();
        Toastr::success('تم حذف المجموعة');
        return back();
    }

    public function members($id)
    {
        $group = NotificationGroup::with('users')->findOrFail($id);
        $allUsers = User::select('id','f_name','l_name','email','phone')->get();
        return view('smartads::admin.groups.members', compact('group','allUsers'));
    }

    public function addMember(Request $request, $id)
    {
        $group = NotificationGroup::findOrFail($id);
        $request->validate(['user_id'=>'required|exists:users,id']);
        $group->users()->syncWithoutDetaching([$request->user_id]);
        Toastr::success('تمت الإضافة');
        return back();
    }

    public function removeMember(Request $request, $id)
    {
        $group = NotificationGroup::findOrFail($id);
        $request->validate(['user_id'=>'required|exists:users,id']);
        $group->users()->detach($request->user_id);
        Toastr::success('تمت الإزالة');
        return back();
    }

    public function sendForm($id)
    {
        $group = NotificationGroup::findOrFail($id);
        return view('smartads::admin.groups.send', compact('group'));
    }

    public function sendNotification(Request $request, $id)
    {
        $group = NotificationGroup::with('users')->findOrFail($id);
        $validated = $request->validate(['title'=>'required','body'=>'required']);
        $tokens = $group->users->pluck('cm_firebase_token')->filter()->toArray();
        if (empty($tokens)) { Toastr::error('لا يوجد أعضاء لديهم توكن صالح'); return back(); }
        $fcm = new FCMService();
        $result = $fcm->sendToMultiple($tokens, $validated['title'], $validated['body']);
        Toastr::success('تم الإرسال. نجح: '.($result['success']??0).'، فشل: '.($result['failure']??0));
        return back();
    }
}
