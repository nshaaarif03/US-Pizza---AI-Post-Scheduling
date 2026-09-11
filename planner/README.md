# AI Social Media Planner

A working Laravel 12, PHP, Blade, Tailwind CSS and MySQL prototype for a marketing department. The interface uses US Pizza-inspired red accents, locally bundled Inter and Anton fonts, responsive navigation, and fictional pizza marketing examples.

**Campaign brief → AI drafts → human review → approval → internal scheduling → manual platform handoff.**

Version 1 never automatically publishes to a social network. Passing a scheduled time does not change a post to “Published.”

## Requirements

- PHP 8.2 or later, with PDO MySQL, mbstring, OpenSSL, cURL, fileinfo, DOM/XML, tokenizer and standard Laravel extensions. PDO SQLite is used for the default tests.
- Composer 2.
- Node.js 20.19+ or 22.12+ and npm. The frontend uses Vite 6 and Tailwind 4.
- MySQL 8.0+ recommended. The local demonstration and MySQL integration tests were run using the available MySQL-compatible MariaDB 10.4.32 server through Laravel's `mysql` driver.
- A Gemini API key and a supported model for live generation. No key is needed for the explicitly labeled sample mode.

## Installation

From the repository root, open a terminal:

```powershell
cd planner
composer install
npm install
copy .env.example .env
php artisan key:generate
```

On macOS/Linux use `cp .env.example .env` instead of `copy`. Do not replace an existing configured `.env` when updating an installation.

Create an empty database on your MySQL server:

```sql
CREATE DATABASE ai_social_media_planner
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Edit `.env` to match your server:

```dotenv
APP_NAME="AI Social Media Planner"
APP_ENV=local
APP_KEY=base64:generated-by-artisan
APP_DEBUG=false
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_social_media_planner
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

GEMINI_API_KEY=
GEMINI_MODEL=gemini-2.5-flash
```

`APP_KEY` must be generated with Artisan; the example text is not a usable key. Keep the Gemini key only on the server. It is sent in the `x-goog-api-key` request header, never in a URL or frontend JavaScript. Choose a model enabled for your Google project if the configured default is unavailable.

Run migrations, load fictional seed data, build the frontend and start Laravel:

```powershell
php artisan migrate --seed
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Open **http://127.0.0.1:8000**. Use `npm run dev` in a second terminal while editing frontend assets, or rerun `npm run build` after changes. If configuration was cached, run `php artisan config:clear` after changing `.env`.

## This machine's local demo

The project has an ignored `.runtime/mysql` data directory initialized specifically for this prototype. It does not use or overwrite existing XAMPP databases. The current `.env` uses `DB_PORT=3307` and the local development database. This is a local-only demonstration setup, not production database configuration.

If the background database process has stopped, start it from the `planner` directory in PowerShell:

```powershell
& 'C:\xampp\mysql\bin\mysqld.exe' "--defaults-file=$PWD\.runtime\mysql\my.ini" --bind-address=127.0.0.1 --console
```

Leave that terminal running. In another terminal run `php artisan serve --host=127.0.0.1 --port=8000`. Do not start a second database process against the same data directory if it is already running. This machine-specific runtime is excluded from Git; other installations should follow the standard MySQL instructions above.

To stop this isolated database cleanly:

```powershell
& 'C:\xampp\mysql\bin\mysqladmin.exe' --host=127.0.0.1 --port=3307 --user=root shutdown
```

Stop the Laravel development server with Ctrl+C in its terminal.

## Demonstration workflow

1. Open **AI Content Generator**. Enter a goal, audience, product/promotion, platforms, dates, tone and optional instructions.
2. Choose **Generate Content Plan** for Gemini, or **Try Sample Plan** for deterministic fictional content. Sample mode is explicitly labeled and never masquerades as an AI request. Sample copy demonstrates the workflow; it does not tailor itself to every brief instruction.
3. Open a draft in **Content Library**. Edit its idea, platform, hook, caption, CTA, hashtags or proposed time. The preview updates immediately.
4. **Save Changes**, then review the saved content and select **Approve Post**. Unsaved changes prevent approval in the interface. Concurrent/stale actions are rejected on the server using a version counter and row lock.
5. Select a future posting date and time, then **Schedule Post**. All dates and times use **Asia/Kuala_Lumpur (MYT / UTC+8)**.
6. Find the post on the dashboard, calendar or Scheduled Posts screen. You can edit its schedule or cancel the schedule. Cancellation returns it to Approved and keeps the content.
7. **Post to Instagram/Facebook/TikTok** reports that the integration is not connected and provides the corresponding **Open platform** link. **Copy Caption** copies the hook, caption and CTA; **Copy Hashtags** copies hashtags separately. Manually add media and publish through the real platform yourself.

Editing an approved post resets it to Draft. A scheduled post's content cannot be edited until its schedule is cancelled. Only drafts may be deleted. No approval or scheduling status is accepted from arbitrary form input or AI JSON.

## Gemini integration and testing

`app/Services/GeminiService.php` uses Laravel's HTTP client and Google's `generateContent` endpoint with `responseMimeType: application/json` and a JSON schema. It requests one post for each selected date/platform pair, up to 14 days and three platforms.

The prompt includes platform-specific instructions and prohibits inventing official prices, discounts, products, offers, terms, analytics or company facts. Output includes date, platform, content type, idea, hook, caption, CTA, hashtags, suggested time and rationale. The service validates lengths, enums, dates, times, hashtags, completeness and duplicate date/platform pairs before anything is saved. All campaign/post writes are transactional.

**Test with a real API key:**

1. Add `GEMINI_API_KEY` to `.env`; choose `GEMINI_MODEL` if necessary.
2. Run `php artisan config:clear`.
3. Open the generator and enter a three-day, one-platform campaign with a general product description.
4. Click **Generate Content Plan**. Expect three Draft posts, a success message, and no scheduled dates.
5. Review the output manually for factual accuracy. Prompt constraints do not guarantee truthful model output.
6. Settings displays **Connected** after a successful generation for up to one hour. A configured key without a recent result displays **Not verified**, not a fabricated connection status.

Missing keys, connection timeouts, upstream errors, quota limits, blocked/truncated responses, empty responses, invalid JSON and invalid plans produce friendly messages and save no partial campaign. The campaign form preserves inputs on failure and prevents repeated submission while generating. Requests are limited to five per minute per IP.

No live Gemini request was made during implementation because no API key was supplied. Success and failure paths are covered by HTTP fakes, with stray network requests disabled in tests.

Primary references: [Laravel 12 documentation](https://laravel.com/docs/12.x), [Laravel HTTP client](https://laravel.com/docs/12.x/http-client), [Gemini generateContent API](https://ai.google.dev/api/generate-content), [Gemini structured output](https://ai.google.dev/gemini-api/docs/generate-content/structured-output).

## Tests and verification

Default tests use an isolated in-memory SQLite database:

```powershell
php artisan test
```

For MySQL-compatible integration tests, create a **separate disposable test database**:

```sql
CREATE DATABASE ai_social_media_planner_test
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then run:

```powershell
php vendor/bin/phpunit -c phpunit.mysql.xml
```

The MySQL test configuration forces the database name to `ai_social_media_planner_test`. Tests rebuild tables in that database. It must never contain real data. Set `DB_HOST`, `DB_PORT`, `DB_USERNAME` and `DB_PASSWORD` as shell environment variables to override test connection defaults. For this local demo, use `$env:DB_PORT='3307'` before running PHPUnit.

The suite covers seeded and empty screens, generation as Draft, explicit sample mode, missing keys, API failures, malformed plans, invalid brief dates, approval gating, stale edits, past schedules, cancellation, output escaping, safe handoff responses, the five-post dashboard limit, and calendar placement. Browser verification also covers real form submission, preview updates, caption copying, mobile navigation and all seven screens at 375, 768, 1024 and 1440 pixels.

Additional checks:

```powershell
npm run build
php artisan view:cache
php vendor/bin/pint --test
```

## Project structure

```text
app/
  Http/Controllers/
    DashboardController.php       Summary counts and next five posts
    CampaignController.php        Brief submission and draft persistence
    PostController.php            Library, review, editing, approval, deletion
    ScheduleController.php        List, schedule, reschedule, cancellation
    CalendarController.php        Month navigation and effective-date grouping
    SocialMediaController.php     Approved-content manual handoff
    SettingsController.php        Honest integration status
  Http/Requests/
    CampaignRequest.php           Campaign input validation
    PostRequest.php               Editable-content validation
  Models/
    Campaign.php                  hasMany(Post), date/JSON casts
    Post.php                      belongsTo(Campaign), statuses and schedule scope
  Services/
    GeminiService.php             HTTP request, schema and AI validation
    SamplePlanService.php         Explicit deterministic demo generation
    PostWorkflow.php              Transactional status/version guards
    SocialMedia/
      SocialMediaPublisherInterface.php
      InstagramPublisher.php
      FacebookPublisher.php
      TikTokPublisher.php
config/services.php               Gemini key/model configuration
database/migrations/              Campaign/post schema and Laravel defaults
database/seeders/DemoSeeder.php    Eight fictional posts, relative to seed date
resources/views/                  Blade screens and reusable components
resources/css/app.css             Tailwind theme, layout and responsive styles
resources/js/app.js               Drawer, live preview, copy, form feedback
routes/web.php                    Named Laravel routes
tests/Feature/PlannerTest.php      Workflow and integration coverage
public/images/                    Optional supplied brand logo
```

The database stores planned and scheduled dates separately. Laravel casts date fields to dates and hashtags/platforms to arrays. SQL TIME fields stay as normalized `HH:mm:ss` strings; `scheduledAt()` combines them with the date in MYT. The `version` field prevents stale review actions from approving a different revision.

## Branding and sample data

The supplied attachments contained only text; no logo asset was available. The temporary wordmark is plain typography, not an official logo recreation. Place the actual logo at `public/images/us-pizza-logo.png`; the shared sidebar/mobile brand component automatically uses it at its original aspect ratio with `object-contain`.

`DemoSeeder` creates one fictional campaign with three Draft, two Approved and three Scheduled posts. Every demo campaign has `is_demo=true`, and the interface labels its content as Sample. Re-running the seeder does not duplicate an existing demo campaign. Seed dates are relative to the time the seeder first runs. Older scheduled examples remain scheduled and become due for manual posting; they are never marked published.

## Scope and future integration points

- No real social publishing, account connections, uploads, sales/customer data or engagement analytics.
- No login or per-user authorization is included in this local prototype. Before shared or public deployment, add company authentication, role policies and an appropriate production web/database configuration. The provided development server binds to localhost.
- Only `public/` should be served by a web server. `.env` stays outside the web root, CSRF protects mutations, Blade escapes user/AI content, and the API key never appears in the interface.
- Media is a clearly labeled preview placeholder. Image/video storage and upload validation can be added later.
- Publishers currently return `connected=false` and do not change any post. Future Meta/TikTok implementations can implement `SocialMediaPublisherInterface`, add OAuth/token storage, durable publishing receipts and idempotency, and use jobs for actual API work.
- Version 1 needs no background Scheduler job: schedules are persisted dates, and upcoming/due views are calculated from those dates. Later versions can register notification or publishing jobs in `routes/console.php` and run Laravel's Scheduler. No fake publishing job or operating-system automation was installed.
- Notifications, historical engagement analytics and recommendations based on real performance remain future work.

## Troubleshooting

- **Database connection refused:** start MySQL and check the host/port in `.env`. This machine's isolated demo uses 3307, not 3306.
- **Vite manifest missing:** run `npm install` and `npm run build`.
- **419 Page Expired:** reload the form and try again; the CSRF session may have expired.
- **This post has changed:** reload the review page. Another action updated its version.
- **Gemini unavailable:** check the key, model, network and quota. Sample mode is available separately.
- **Clipboard unavailable:** use HTTPS or localhost, or manually select and copy preview text. The interface reports clipboard failures.
- **Windows worker warning:** do not set `PHP_CLI_SERVER_WORKERS`; the local Windows development server runs one worker.
