Mini Modular Shop 🧩
A modular, API-first e‑commerce backend built with Laravel — designed as a teaching/portfolio-grade project and a reusable foundation for small to medium online shops.

Tech focus: clean modular architecture (per-domain modules), layered design (Controller → Service → Repository → Model), strong domain tests, and a mockable payment pipeline.

Table of Contents
Overview
Architecture
Modules
Layered Design
Routing
Tech Stack
Features
Authentication
Products
Categories
Cart
Orders / Checkout
Payment
Authorization & Roles
Domain Exceptions & Error Handling
API Design & Response Format
Setup & Installation
Requirements
Installation Steps
Running Tests
Seeded Data
Example API Flows
Auth Flow
Shopping Flow
Payment Flow
Project Roadmap & Design Intent
Known Gaps & Future Work
Notes on Localization
License
Overview
Mini Modular Shop یک RESTful backend برای یک فروشگاه آنلاین کوچک است که تمرکزش روی:

ماژولار بودن (هر دامنه یک ماژول مستقل)
معماری لایه‌ای تمیز
تست‌پذیری بالا
آمادگی برای Production (نرخ‌دهی، کش، صف، پرداخت ماک،…)
این پروژه:

فقط API است (UI اصلی ندارد) و انتظار دارد توسط:
اپ موبایل
SPA (React/Vue/etc.)
یا ابزارهایی مثل Postman
مصرف شود.

چه مشکلی را حل می‌کند؟
یک اسکلت تمیز و modular برای فروشگاه آنلاین فراهم می‌کند.
دامنه‌ها را جدا می‌کند: Auth، Catalog، Cart، Order، Payment.
مناسب است برای:
یادگیری معماری ماژولار در Laravel
نشان دادن در رزومه/Portfolio
تبدیل به یک فروشگاه واقعی با کمی توسعه‌ی بیشتر
Architecture
Modules
پروژه با nwidart/laravel-modules ساختاربندی شده و هر دامنه‌ی اصلی، یک ماژول مستقل است:

text
Modules/
├── Auth/ # ثبت‌نام، ورود، خروج، user info (Sanctum)
├── Product/ # کاتالوگ محصولات + CRUD ادمین + جستجو/فیلتر
├── Category/ # دسته‌بندی‌ها + سلسله‌مراتب parent/child
├── Cart/ # سبد خرید کاربر
├── Order/ # checkout + تاریخچه سفارش
└── Payment/ # درگاه پرداخت ماک + abstraction برای درگاه واقعی
Layered Design
هر ماژول الگوی زیر را رعایت می‌کند:

text
routes/api.php # تعریف روت‌ها (فقط API)
Http/Controllers/_ # کنترلرها (thin)
Http/Requests/_ # ولیدیشن + authorization
Services/_ # منطق دامنه
Repositories/_ # دسترسی به داده + کش
Models/_ # Eloquent models
Transformers/_ (Resources) # شکل‌دهی خروجی API
database/migrations/\* # مایگریشن‌های دامنه‌ای
مسیر اجرای یک درخواست نمونه:

text
HTTP Request
→ Route (api.php)
→ Controller
→ Service (business logic)
→ Repository (DB + cache)
→ Model
→ Resource / Transformer
→ JSON Response
Routing
Base prefix: /api/v1
Module Prefix Auth
Auth /auth عمومی + Sanctum
Product /products عمومی خواندن، ادمین نوشتن
Category /categories عمومی خواندن، ادمین نوشتن
Cart /cart نیاز به Sanctum
Order /orders نیاز به Sanctum
Payment /payment نیاز به Sanctum
Rate limiting:

Global: 60 درخواست بر دقیقه
RPS اختصاصی:
ثبت‌نام: 3/min
لاگین: 10/min
Checkout: 5/min
Payment: 10/min
Tech Stack
Layer Technology
Language PHP 8.3+
Framework Laravel 13.8
Modules nwidart/laravel-modules
Auth Laravel Sanctum
DB SQLite (پیش‌فرض) / MySQL-ready
Cache Database driver
Queue Database driver
Session Database driver
Frontend Vite 8 + Tailwind 4 (اسکفولد)
Testing PHPUnit 12 (Feature tests)
Features
Authentication
Module: Modules/Auth

پشتیبانی از:

ثبت‌نام کاربر جدید
ورود (Token-based با Sanctum)
خروج (حذف همه tokens)
دریافت اطلاعات کاربر فعلی
Endpoints (نمونه):

POST /api/v1/auth/register
POST /api/v1/auth/login
POST /api/v1/auth/logout
GET /api/v1/auth/user
Flow ثبت‌نام:

درخواست به POST /auth/register
RegisterRequest ولیدیشن را انجام می‌دهد
AuthService::register() کاربر را می‌سازد
توکن Sanctum صادر می‌شود و در پاسخ برگردانده می‌شود
Products
Module: Modules/Product

لیست محصولات با:
Pagination
جستجو (search)
فیلتر دسته (category_id)
فیلتر قیمت (min_price, max_price)
فیلتر وضعیت (is_active)
مشاهده‌ی محصول تکی
CRUD ادمین (ایجاد، ویرایش، حذف)
تولید slug از title + حل collision (slug‌های یکتا)
Endpoints (نمونه):

GET /api/v1/products
GET /api/v1/products/{id}
POST /api/v1/products (admin)
PUT /api/v1/products/{id} (admin)
DELETE /api/v1/products/{id} (admin)
Caching:

محصول تکی: کش ~10 دقیقه‌ای
invalidation روی mutationها انجام می‌شود
Categories
Module: Modules/Category

لیست دسته‌ها (همراه کودکان)
نمایش دسته‌ی تکی
CRUD ادمین
پشتیبانی از سلسله‌مراتب (parent_id)
ویژگی‌های مهم:

کش لیست دسته‌ها برای ۱ ساعت
جلوگیری از:
self-parent
حلقه‌ی سلسله‌مراتب
حذف امن:
اگر دسته products یا children داشته باشد، اجازه‌ی حذف نمی‌دهد
جلوگیری از orphan شدن داده‌ها
Endpoints نمونه:

GET /api/v1/categories
GET /api/v1/categories/{id}
POST /api/v1/categories (admin)
PUT /api/v1/categories/{id} (admin)
DELETE /api/v1/categories/{id} (admin)
Cart
Module: Modules/Cart

یک سبد فعال برای هر کاربر
اضافه/ویرایش/حذف آیتم‌ها
نگهداری snapshot قیمت در زمان اضافه شدن
Endpoints:

GET /api/v1/cart → مشاهده‌ی سبد + مجموع
POST /api/v1/cart/add → { product_id, quantity }
PUT /api/v1/cart/update/{id} → تغییر تعداد
DELETE /api/v1/cart/item/{id} → حذف آیتم
محافظت‌ها:

جلوگیری از اضافه کردن محصولات غیرفعال
چک کردن موجودی:
هنگام add (با در نظر گرفتن quantity تجمعی)
هنگام update
اطمینان از اینکه cart item متعلق به همان کاربر است (ownership enforcement)
Orders / Checkout
Module: Modules/Order

تبدیل سبد به سفارش (checkout)
نمایش لیست سفارش‌های کاربر
نمایش سفارش تکی
مدیریت status:
pending
paid
canceled (API برای cancel فعلاً پیاده نشده)
Flow checkout:

بارگذاری cart + items
جلوگیری از checkout سبد خالی
چک stock برای هر آیتم
داخل DB::transaction با lockForUpdate():
ساخت Order (status = pending)
ساخت order_items با snapshot قیمت
کاهش stock محصول
پاک کردن cart items
برگشت Order + items + products
Notes:

CheckoutRequest فیلد notes را قبول می‌کند
notes در سفارش ذخیره می‌شود (migration اختصاصی اضافه شده)
Payment
Module: Modules/Payment

درگاه پرداخت ماک با abstraction برای درگاه‌های واقعی (مثلاً Stripe)
Flow دو مرحله‌ای: request → verify
Endpoints:

POST /api/v1/payment/requestBody: { order_id }
POST /api/v1/payment/verifyBody: { payment_id }
Behavior:

فقط سفارش‌های:
متعلق به همان کاربر
در وضعیت pending
از طریق PaymentRepository::findForUserWithLock() مالکیت enforce می‌شود
جلوگیری از چند پرداخت همزمان برای یک سفارش
روی success:
وضعیت payment → success
وضعیت order → paid (داخل transaction)
روی failure:
payment → failed
Gateway Switching:

MockPaymentGateway پیش‌فرض
StripeGateway به صورت placeholder وجود دارد
انتخاب gateway از طریق config/env (مثل PAYMENT_GATEWAY=mock|stripe)
Authorization & Roles
Roles:

admin
customer (پیش‌فرض)
ویژگی‌ها:

ستون users.role
UserRole enum + cast روی مدل User
متدهای کمکی:
User::isAdmin()
user->cart(), user->orders()
Authorization:
استفاده از Policies برای محصولات و دسته‌ها
FormRequest ها از policies استفاده می‌کنند (به جای middleware)
destroy از $this->authorize('delete', $model) استفاده می‌کند
Domain Exceptions & Error Handling
برای استاندارد کردن خطاها، یک base کلاس تعریف شده:

App\Exceptions\ApiDomainException
ویژگی‌ها:

فیلدهایی مثل:
message
statusCode
errorCode
رندر استاندارد JSON در bootstrap/app.php برای api/\*:
json
{
"message": "پیغام خطا",
"error": "error_code"
}
نمونه‌ی exceptions دامنه‌ای:

EmptyCartException
InsufficientStockException
ProductNotFoundException
PaymentFailedException
InactiveProductException
CartItemNotFoundException
OrderNotPayableException
InvalidCategoryHierarchyException
CategoryDeleteException
Controllers خطاهای generic را بالا نمی‌خورند؛ اجازه می‌دهند exceptions دامنه‌ای به handler سراسری برسند.

API Design & Response Format
الگوی پاسخ‌ها (موارد success):

json
{
"message": "پیغام (optional)",
"data": {
// ...
}
}
Auth /auth/user:
json
{ "data": { "id": 1, "name": "...", ... } }
OrderResource شامل notes است
PaymentResource برای پرداخت‌ها اضافه شده
Setup & Installation
Requirements
PHP 8.3+
Composer
SQLite (یا MySQL)
Node.js (برای Vite/Tailwind فقط در صورت نیاز)
Installation Steps
bash

# 1. Clone the repo

git clone https://github.com/NAVIDNTT/mini-shop.git
cd mini-shop

# 2. Install PHP dependencies

composer install

# 3. Copy environment file

cp .env.example .env

# 4. Generate app key

php artisan key:generate

# 5. (Optional) تنظیم DB

# پیش‌فرض روی SQLite تنظیم شده است

# مطمئن شوید database/database.sqlite وجود دارد:

touch database/database.sqlite

# یا برای MySQL:

# در .env مقادیر DB\_\* را تنظیم کنید

# 6. Run migrations + seeders

php artisan migrate:fresh --seed

# 7. Run tests (optional but recommended)

php artisan test

# 8. Run the server

php artisan serve

# API now available at http://127.0.0.1:8000/api/v1

Running Tests
bash
php artisan test
حدود ۴۲ تست Feature برای:
Auth
Product
Category
Cart
Order
Payment
Domain Exceptions
Seeded Data
Seederها:

DatabaseSeeder:
Admin user:
Email: admin@example.com
Password: password
Customer user:
Email: customer@example.com
Password: password
دسته‌ها و محصولات نمونه:
چند دسته با hierarchy
تعدادی محصول (یکی غیرفعال، یکی با stock کم برای تست)
Example API Flows
Auth Flow
Register
http
POST /api/v1/auth/register
Content-Type: application/json

{
"name": "Test User",
"email": "test@example.com",
"password": "password",
"password_confirmation": "password"
}
Login
http
POST /api/v1/auth/login
{
"email": "test@example.com",
"password": "password"
}
→ برمی‌گرداند: token Sanctum (Bearer)

Get current user
http
GET /api/v1/auth/user
Authorization: Bearer {token}
Shopping Flow
دریافت لیست محصولات:
http
GET /api/v1/products?search=phone&min_price=100&max_price=500
اضافه کردن محصول به سبد:
http
POST /api/v1/cart/add
Authorization: Bearer {token}
Content-Type: application/json

{
"product_id": 1,
"quantity": 2
}
مشاهده‌ی سبد:
http
GET /api/v1/cart
Authorization: Bearer {token}
Payment Flow
Checkout:
http
POST /api/v1/orders/checkout
Authorization: Bearer {token}
Content-Type: application/json

{
"notes": "Deliver after 5pm"
}
→ برمی‌گرداند سفارش با status = pending.

درخواست پرداخت:
http
POST /api/v1/payment/request
Authorization: Bearer {token}
Content-Type: application/json

{
"order_id": 1
}
→ برمی‌گرداند payment record (pending) و اطلاعات gateway (ماک).

تأیید پرداخت:
http
POST /api/v1/payment/verify
Authorization: Bearer {token}
Content-Type: application/json

{
"payment_id": 1
}
→ روی موفقیت: payment → success، order → paid.

Project Roadmap & Design Intent
این پروژه براساس یک roadmap 8 فازی (فایل navid.html) ساخته شده:

فاز 1–7:
ماژول‌های اصلی (Auth, Product, Category, Cart, Order, Payment)
Pagination, search, filters, indexes
ساختار معماری تمیز
فاز 8 (Production Polish):
Custom exceptions
standard API responses
بهبودهای امنیتی (stock locking، validation)
Seed داده‌ها
تست‌های دامنه‌ای
هدف نهایی:

یک نمونه‌ی استاندارد از:
Modular Laravel
Domain-oriented design
تست‌پذیری و maintainability
آماده برای:
اضافه کردن UI (SPA / mobile client)
اتصال به درگاه‌های واقعی (Stripe, Zarinpal, …)
توسعه در سطح Production
Known Gaps & Future Work
این موارد عمدی باز گذاشته شده‌اند تا فضا برای توسعه‌ی بعدی وجود داشته باشد:

[ ] Order cancellation API (status canceled هست، endpoint نه)
[ ] Admin order management (لیست همه سفارش‌ها، گزارش‌ها)
[ ] Real Stripe integration + webhooks
[ ] Slug-based category filtering (طبق roadmap: ?category=mobile به جای category_id)
[ ] OpenAPI/Swagger برای مستندسازی رسمی API
[ ] Upload تصاویر محصول
[ ] Email verification & password reset
[ ] Queue-based payment callbacks
[ ] Frontend (SPA / Admin Panel)
(الان پروژه کاملاً API-first است)

[ ] حذف کامل Bladeهای اسکفولد شده (فعلاً harmless هستند)
Notes on Localization
پیام‌های اکثر ماژول‌ها به زبان فارسی هستند (مخصوص بازار ایران / API دو زبانه)
پیام‌های Product admin در ابتدا انگلیسی بودند؛ در نسخه‌ی فعلی به سمت همسان‌سازی به فارسی حرکت شده
ساختار API مستقل از زبان است؛ برای چندزبانه واقعی می‌توان از Laravel Localization / translation files استفاده کرد
License
این پروژه فعلاً برای استفاده‌ی آموزشی و Portfolio طراحی شده است.

در صورت انتشار عمومی، می‌توانید آن را تحت یک لایسنس مثل MIT منتشر کنید.
