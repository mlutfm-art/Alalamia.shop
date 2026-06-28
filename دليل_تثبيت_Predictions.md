# 📦 دليل تثبيت إضافة Predictions (التوقعات الرياضية)

## ✅ المتطلبات
- مشروع Laravel 10 / 11 / 12
- حزمة `nWidart/laravel-modules` مُثبَّتة ومُفعَّلة
- قاعدة بيانات MySQL أو PostgreSQL

---

## 🚀 خطوات التثبيت

### الخطوة 1 — فكّ ضغط الملف في جذر المشروع

افتح ملف `Predictions_Module_Final.zip` وافكّه **مباشرة داخل جذر مشروع Laravel**:

```
project-root/
├── Modules/
│   └── Predictions/          ← مجلد الإضافة الجديد
├── resources/
│   └── views/layouts/admin/partials/v2/
│       └── _side-bar.blade.php   ← ملف Sidebar محدَّث تلقائياً
└── INSTALLATION.md
```

> ⚠️ إذا طُلب منك استبدال `_side-bar.blade.php` — اضغط **Yes / نعم**

---

### الخطوة 2 — تفعيل الإضافة

```bash
php artisan module:enable Predictions
```

---

### الخطوة 3 — تشغيل Migrations

```bash
php artisan migrate
```

سيُنشئ هذا ثلاثة جداول جديدة:
- `prediction_matches` — بيانات المباريات
- `match_predictions` — توقعات المستخدمين
- `prediction_settings` — إعدادات الإضافة

---

### الخطوة 4 — تحديث ملف `modules_statuses.json`

افتح الملف في جذر المشروع وأضف السطر الجديد:

```json
{
    "Blog": true,
    "AI": true,
    "BotCenter": true,
    "Alertmarkting": true,
    "Predictions": true
}
```

---

### الخطوة 5 — مسح الكاش

```bash
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan cache:clear
```

---

## 🗂 الصفحات المتاحة بعد التثبيت

| الصفحة | الرابط |
|--------|--------|
| 📊 لوحة التحكم | `/admin/predictions` |
| 🏟️ إدارة المباريات | `/admin/predictions/matches` |
| 🎯 قائمة التوقعات | `/admin/predictions/list` |
| 🏆 المتصدرون | `/admin/predictions/leaderboard` |
| ⚙️ الإعدادات | `/admin/predictions/settings` |

---

## 🔌 API Endpoints

| Method | Endpoint | الوصف | Auth مطلوب |
|--------|----------|-------|------------|
| GET | `/api/v1/predictions/matches/active` | المباراة النشطة | ❌ |
| GET | `/api/v1/predictions/matches/{id}` | تفاصيل مباراة | ❌ |
| GET | `/api/v1/predictions/leaderboard` | قائمة المتصدرين | ❌ |
| POST | `/api/v1/predictions/submit` | إرسال توقع | ✅ |
| GET | `/api/v1/predictions/my-predictions` | توقعاتي | ✅ |

### مثال — إرسال توقع (POST /submit):
```json
{
    "match_id": 1,
    "predicted_team1": 2,
    "predicted_team2": 1
}
```

---

## ⚙️ منطق توزيع النقاط

```
distance = |actual1 - predicted1| + |actual2 - predicted2|

distance = 0        → 100 نقطة (صحيح تماماً)
distance ≤ 2        → 50 نقطة  (قريب)
distance > 2        → 0 نقطة
```

> يمكن تغيير هذه القيم من لوحة التحكم → **الإعدادات**

---

## 🗂 بنية ملفات الإضافة

```
Modules/Predictions/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/PredictionController.php
│   │   └── API/PredictionApiController.php
│   ├── Models/
│   │   ├── PredictionMatch.php
│   │   ├── MatchPrediction.php
│   │   └── PredictionSetting.php
│   ├── Services/PredictionService.php
│   └── Providers/
│       ├── PredictionsServiceProvider.php
│       └── RouteServiceProvider.php
├── config/config.php
├── database/migrations/
│   └── 2026_06_14_000000_create_predictions_tables.php
├── module.json
├── resources/views/admin/
│   ├── index.blade.php
│   ├── matches.blade.php
│   ├── predictions-list.blade.php
│   ├── leaderboard.blade.php
│   ├── settings.blade.php
│   └── partials/match-form.blade.php
└── routes/
    ├── web.php
    └── api.php
```

---

## ❗ ملاحظات مهمة

1. **الـ Sidebar**: ملف `_side-bar.blade.php` داخل الـ ZIP هو نسخة كاملة من الملف الأصلي مع إضافة قسم Predictions — استبدله مباشرة.

2. **نموذج المستخدم**: الإضافة تستخدم `App\Models\User` — إذا كان مشروعك يستخدم `Customer` فعدّل في ملف `MatchPrediction.php`.

3. **الصلاحيات**: الإضافة محمية بـ middleware `admin` + `actch:admin_panel` — نفس حماية باقي الإضافات في المشروع.

4. **الـ API Auth**: مُهيَّأة لـ `auth:api,customer` — اضبطها حسب الـ guard المستخدم في مشروعك.
