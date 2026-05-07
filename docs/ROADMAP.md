# Hi.Events Multi-Tenant SaaS — Linear-Ready Roadmap

Drop each `###` heading into Linear as one issue. Project name suggestion:
**"Hi.Events Church SaaS"**. Suggested labels: `phase-0a`, `phase-0`,
`phase-1`, `phase-2`, `phase-3`, `phase-4`, `billing`, `tax`,
`infra`, `security`, `docs`, `investigation`, `research`,
`onboarding`, `ux`, `lyrics-integration`.

Status legend: 🟢 Done, 🟡 In flight, ⚪ Backlog.

---

## Phase 0a — Production hardening (DONE)

### 🟢 Octane tenant-state-leak fix
Reset `User::$currentAccountId` and `BaseResource::$additionalData` on every Octane request. Listener wired into `RequestReceived`. 6 unit tests, full suite green.
Commits: `a920be5e`. Labels: `phase-0a`, `security`.

### 🟢 Container hardening
Multi-stage Dockerfile, FrankenPHP 1.4-php8.3-alpine pin, `apk upgrade`, `USER www-data`, HEALTHCHECK, `--max-requests=500`, OPcache+JIT config.
Commits: `a920be5e`, `df4154a4`. Labels: `phase-0a`, `infra`.

### 🟢 `/api/health` + Fly split-deploy config
Health endpoint with DB ping. New `backend/fly.toml.example` with web/worker/scheduler processes. All-in-one fly.toml.example given `release_command` + HTTP check.
Labels: `phase-0a`, `infra`.

### 🟢 CI security workflow
`composer audit --locked` + Pint on changed files (PR-only) + Trivy image scan failing on HIGH/CRITICAL.
Labels: `phase-0a`, `security`.

### 🟢 Sentry env wiring
SDK + `Handler` already in code. Documented `SENTRY_LARAVEL_DSN` and friends in `.env.example`.
Labels: `phase-0a`, `security`.

### ⚪ Lock down public registration
**Estimate:** 0.5d. **Priority:** High.
Set `APP_DISABLE_REGISTRATION=true` as a Fly secret immediately after creating the master account. Verify `/auth/register` returns the disabled-registration error and `/auth/invitation/{token}` still works for invites.
Optional follow-up: add an admin UI toggle on `AccountConfiguration` so the flag flips without a redeploy.
Labels: `phase-0a`, `security`, `ux`.

---

## Phase 0 — Multi-region foundations

### ⚪ Default timezone UTC
**Estimate:** 0.5d.
Change `backend/config/app.php` line 13 from `America/Vancouver` to `UTC`. Verify nothing in the codebase relies on the Vancouver default. Per-account timezone already exists on `Account.timezone`.
Labels: `phase-0`.

### ⚪ Stripe AU + US platform enum
**Estimate:** 1d.
Add `AUSTRALIA = 'au'` and `UNITED_STATES = 'us'` to `backend/app/DomainObjects/Enums/StripePlatform.php`. Extend `StripeConfigurationService::getSecretKey()` match to AU/US. Add `STRIPE_AU_*` and `STRIPE_US_*` env keys in `backend/config/services.php`. Unit tests for the resolver.
Labels: `phase-0`, `billing`.

### ⚪ Set primary Stripe platform
**Estimate:** 0.5d.
Add `STRIPE_PRIMARY_PLATFORM=au` env. CO tenants use AU platform initially (cards-only). Ensures all new churches default to AU until US Atlas opens.
Labels: `phase-0`, `billing`.

---

## Phase 1 — Embedded admin + SSO + branding (AU pilot launch unblocked at end)

### ⚪ OIDC/JWT SSO from parent (lyrics) app
**Estimate:** 3–4d.
New `OIDCLoginAction` validating a JWT signed by the parent app via JWKS. Upserts `User`, attaches to `Account` via `account_users` pivot with the right `Role`, issues hi.events JWT. Mirrors existing `LoginAction` shape.
Labels: `phase-1`, `lyrics-integration`.

### ⚪ Per-church branding via `AccountConfiguration.theme`
**Estimate:** 2–3d.
Add `theme` JSON column to `AccountConfiguration` (primary color, logo URL, font, custom domain). Frontend Mantine provider reads at boot, sets CSS variables. Reuse existing `feature_flags` JSON column shape.
Labels: `phase-1`, `ux`.

### ⚪ Custom subdomain per church
**Estimate:** 2–3d.
Fly.io wildcard cert + subdomain middleware that resolves `{church}.events.yourdomain.com` → `Account`. Hooks into existing `SetAccountContext`. Fallback to `app.yourdomain.com/{church}` for non-DNS setups.
Labels: `phase-1`, `infra`.

### ⚪ OpenAPI spec via dedoc/scramble
**Estimate:** 1–2d.
Add `dedoc/scramble` to composer. Generate spec from existing action classes. Generate TS client for parent app to consume. Public routes already grouped under `/api/public/*`.
Labels: `phase-1`, `lyrics-integration`, `docs`.

### ⚪ Feature flags on AccountConfiguration
**Estimate:** 1d.
Wire the existing `feature_flags` JSON column to a `Feature::for($account)->enabled('foo')` helper. Used to hide non-church UI (waitlist, capacity, affiliate codes) and to gate country-specific features (DIAN, GST).
Labels: `phase-1`, `ux`.

### ⚪ Webhook event types for parent-app sync
**Estimate:** 1–2d.
Add `event.created`, `attendee.created`, `attendee.checked_in`, `order.completed` to the Spatie webhook-server config. Direct overlap with upstream discussion (notflip Feb 2026). Consider upstreaming via PR.
Labels: `phase-1`, `lyrics-integration`.

### ⚪ Account switcher in admin nav
**Estimate:** 1d.
Direct overlap with upstream issue #1181. Watch for upstream merge; if not in 2 weeks, build it locally and PR upstream.
Labels: `phase-1`, `ux`.

### ⚪ Per-organizer team-member scoping
**Estimate:** 2–3d.
Watch upstream issues #1180 / #1178. If they merge, adopt; if not in 4 weeks, build locally so a church admin can grant a worship-team member access to one event series only.
Labels: `phase-1`, `ux`.

---

## Phase 2 — Tax + invoicing (AU GST + CO IVA + DIAN)

### ⚪ Generalize tax determination
**Estimate:** 4–5d.
Introduce `TaxRateResolverInterface` in `backend/app/Services/Domain/Order/Tax/`. Implementations: `EuVatResolver` (wrap existing), `AuGstResolver` (10% if GST-registered), `CoIvaResolver` (19%), `UsSalesTaxResolver` (no-op stub). Dispatch in current `VatRateDeterminationService` by `AccountTaxSetting.country_code`.
Labels: `phase-2`, `tax`.

### ⚪ Rename `AccountVatSetting` → `AccountTaxSetting`
**Estimate:** 2d.
Migration adds `abn`, `nit`, `ein`, `tax_scheme` enum (`VAT`, `GST`, `IVA`, `NONE`) alongside existing EU fields. Update repo + handlers + frontend. Permanent fork divergence.
Labels: `phase-2`, `tax`.

### ⚪ Invoice model upgrade — per-country sequences + itemized tax
**Estimate:** 3–4d.
Replace `Invoice.invoice_number` VARCHAR(50) with per-account per-country monotonic sequence (no gaps — ATO/DIAN requirement). Replace `taxes_and_fees` JSON blob with `invoice_tax_lines` table. Store buyer + issuer tax IDs.
Labels: `phase-2`, `tax`.

### ⚪ Auto-email invoice on order paid
**Estimate:** 1d.
Listener on order-paid event dispatches invoice email via existing `EmailTemplate` + Liquid engine. No code emails invoices today.
Labels: `phase-2`, `tax`.

### 🔬 Investigation — DIAN electronic invoicing provider
**Estimate:** 2d research + 4–5d integration.
Compare Alegra, Siigo, Factus APIs for Colombian electronic invoice. Decide one. Sandbox-integrate via webhook from order-paid listener. Mandatory for any CO sale.
Labels: `phase-2`, `tax`, `investigation`.

---

## Phase 3 — US launch (no Atlas required)

### ⚪ Maintenance-only monetization model for US accounts
**Estimate:** 2d.
Add `monetization_model` enum (`HYBRID` | `MAINTENANCE_ONLY`) on `AccountConfiguration`, derived from country. `StripePaymentIntentCreationService` skips `application_fee_amount` when `MAINTENANCE_ONLY`. US churches keep 100% of ticket revenue; we charge a flat monthly fee only.
Labels: `phase-3`, `billing`.

### ⚪ Platform Stripe Billing — Subscription on Account
**Estimate:** 4–5d.
New `Subscription` model attached to `Account`: `plan`, `currency` (AUD/USD), `status` (trial/active/past_due/canceled). Stripe Billing on YOUR platform Stripe account (separate from Connect, separate from per-region Stripe Connect platforms). Tiers per church size.
Labels: `phase-3`, `billing`.

### ⚪ Stripe Customer Portal integration
**Estimate:** 1–2d.
Magic-link button in `/manage` that opens the Stripe-hosted Customer Portal — card updates, invoice download, plan change, cancel. Saves you building those screens.
Labels: `phase-3`, `billing`, `ux`.

### ⚪ Account lifecycle gating middleware
**Estimate:** 2d.
Middleware that blocks event-creation endpoints when `Subscription.status` ∈ {past_due, canceled}. Keeps read-only access for 30 days post-cancel (data preservation).
Labels: `phase-3`, `billing`.

### 🔬 Investigation — Stripe nonprofit pricing
**Estimate:** 1–2d.
Stripe US offers reduced rates for verified 501(c)(3) (typically 2.2% + $0.30 vs 2.9% + $0.30). AU has case-by-case discount via charity application. CO no formal program. Document the application path per country and add to onboarding guide. Outcome: a one-pager per country with the Stripe form link, required docs, expected approval time.
Labels: `phase-3`, `billing`, `investigation`, `docs`.

### 🔬 Investigation — your platform fee strategy
**Estimate:** 0.5d.
Question: charge 3–5% on top of Stripe (so attendee pays 2.9% Stripe + 3–5% you + flat fee = 5.9–7.9%) or absorb? Decide per market. Recommendation: AU/CO hybrid model = 3% on top + low monthly fee. US = monthly fee only, 0% on top (the differentiator vs. competitors). Codify in `AccountConfiguration.application_fees`.
Labels: `phase-3`, `billing`, `investigation`.

---

## Phase 3b — Stripe Atlas (deferred, opportunistic)

### 🔬 Open Stripe Atlas LLC when triggered
**Estimate:** 6–8 weeks Stripe-side, ~3d our work.
Triggers: (a) want a US bank account to avoid FX, (b) cross $100k revenue in any US state, (c) want US Stripe Connect rates. Migration is non-disruptive — `AccountStripePlatform` model already region-aware.
Labels: `phase-3`, `billing`, `investigation`.

---

## Phase 4 — Church-specific product features

### ⚪ Recurring event series (RRULE)
**Estimate:** 4–5d.
New `event_series` table with RRULE field. Scheduled job spawns child `Event` rows. Critical for weekly services. Direct overlap with upstream discussion "Multi-session Events" (Midas1989). Consider upstreaming.
Labels: `phase-4`.

### ⚪ Donation product type
**Estimate:** 2d.
Add `product_type = DONATION` to `Product` enum. Different UI label, receipt copy, no capacity logic, no quantity input. Tax-receipt template differs per country (AU DGR status, US 501(c)(3) number).
Labels: `phase-4`.

### ⚪ Family/household ticket
**Estimate:** 2–3d.
Group registration where one adult books N attendees. Extends `ProductQuestion` flow to repeat per attendee.
Labels: `phase-4`, `ux`.

### ⚪ Scheduled reminders (T-7d, T-1d, T-1h)
**Estimate:** 2d.
Cron-scheduled reminders emitted at event-creation time using existing `Message` model.
Labels: `phase-4`, `ux`.

---

## Onboarding & docs (church-facing)

### 📚 Tutorial — Stripe Connect setup for churches
**Estimate:** 2d.
Step-by-step guide (with screenshots) on connecting a Stripe Connect Standard account: business details, owner verification, bank account, tax info, charity nonprofit application link. Per-country variant for AU / CO / US.
Deliverable: Markdown in `/docs/onboarding/stripe-au.md` (and `-co.md`, `-us.md`), embedded in the onboarding wizard as a side panel.
Labels: `onboarding`, `docs`.

### ⚪ Onboarding wizard (5 steps)
**Estimate:** 4–5d.
Wizard in `/manage` after first login: (1) church profile + branding, (2) Stripe Connect connect, (3) tax-ID input, (4) first event creation, (5) team member invites. Skippable steps; resumable. Drives the friction that kills church adoption.
Labels: `onboarding`, `ux`.

### 🔬 Investigation + product offer — concierge Stripe setup
**Estimate:** 1d research.
For churches that find Stripe Connect onboarding too painful, offer a one-time **setup fee** (e.g. AUD $200) where you do the Stripe paperwork on a screen-share. Document the SOP, the legal disclaimers (the church still owns the Stripe account and signs the ToS), and the fee invoice template. Up-sell during the wizard if the church bounces from step 2.
Labels: `onboarding`, `billing`, `investigation`.

### 📚 Tutorial — creating recurring services
**Estimate:** 1d.
After Phase 4 ships RRULE: docs on setting up Sunday service, kids' camp, weekly youth group as recurring events. Markdown + 2–3 screenshots.
Labels: `onboarding`, `docs`.

### 📚 Tutorial — DIAN setup for CO churches
**Estimate:** 1d.
Required for CO sales. Steps: provider selection, NIT entry, certificate upload, sandbox test. Depends on Phase 2 DIAN integration shipping.
Labels: `onboarding`, `docs`.

---

## Lyrics integration (don't ship until first paid event customer exists)

### 🔬 Investigation — unified billing model
**Estimate:** 1d.
One Stripe customer per church with TWO subscription items (lyrics + events) on a single invoice — vs. two separate Stripe customers with two invoices. Recommendation: single customer + multiple Stripe Prices. Cleaner UX, one card update flow, one invoice. Codify the data model spanning both products' DBs (probably a shared `customer_id` foreign key from a billing service).
Labels: `lyrics-integration`, `billing`, `investigation`.

### ⚪ Shared identity layer (already covered by Phase 1 SSO)
Same JWT issuer authenticates both lyrics and events. No extra issue beyond Phase 1's OIDC SSO.
Labels: `lyrics-integration`.

### ⚪ Cross-product navigation in shell
**Estimate:** 2d.
Top nav of the parent app exposes "Lyrics" and "Events" tabs that pivot to the appropriate iframe/route. Shared user identity, shared tenant context, separate data planes.
Labels: `lyrics-integration`, `ux`.

### ⚪ Bundled-plan pricing in admin UI
**Estimate:** 2–3d.
After unified billing exists: a single "Plans" page showing combined value. Tier examples: "Worship plan" (lyrics-only), "Worship+Events" (both), "Enterprise" (both + custom domain + extra seats).
Labels: `lyrics-integration`, `billing`, `ux`.

---

## Cross-cutting / risk-mitigation

### ⚪ Per-tenant rate limiting
**Estimate:** 2d.
Today the app rate-limits globally. One church running a 10k-attendee ticket release shouldn't degrade another's. Use Laravel rate limiter scoped to `Account.id`.
Labels: `infra`, `security`.

### ⚪ Audit log per Account
**Estimate:** 3d.
Append-only `audit_logs` table: account_id, actor_user_id, action, target_type, target_id, ip, user_agent, occurred_at. Listener on key domain events. Compliance + support cushion.
Labels: `security`.

### ⚪ Storage signed URLs for private files
**Estimate:** 2d.
Tickets/PDFs should be served via `Storage::temporaryUrl()`, not public links. Event banners stay public. Audit current storage paths.
Labels: `security`.

### ⚪ Backup/DR runbook + restore drill
**Estimate:** 1d setup + quarterly drills.
Supabase has PITR but you need a tested restore. Document: how to restore to a specific point in time, expected RTO/RPO, who has the credentials. Drill quarterly.
Labels: `infra`, `security`, `docs`.

### ⚪ Static-state regression guard in CI
**Estimate:** 0.5d.
Add a CI step that greps `app/Models/` and `app/Resources/` for new mutable static properties not in an allowlist. Prevents future Octane leaks.
Labels: `infra`, `security`.

### 🔬 Investigation — observability beyond Sentry
**Estimate:** 1d.
Decide: Datadog APM? Honeycomb? Plain Sentry traces? For Octane perf characterization. Pick what's cheap at our scale.
Labels: `infra`, `investigation`.

---

## Suggested cycles

| Cycle (2 weeks) | Issues |
|---|---|
| 1 | Lock registration, Phase 0 (TZ + Stripe enum), open Phase 1 SSO |
| 2 | Phase 1 SSO + branding + custom domains |
| 3 | Phase 1 OpenAPI + feature flags + webhooks + account switcher |
| 4 | AU pilot launch + Phase 2 tax start |
| 5 | Phase 2 invoice model + GST + auto-email |
| 6 | DIAN investigation + Phase 3 maintenance-fee model |
| 7 | Phase 3 Stripe Billing + Customer Portal + lifecycle |
| 8 | Phase 3 nonprofit-pricing investigation + onboarding wizard |
| 9 | Phase 4 RRULE + donation + reminders |
| 10 | Cross-cutting (rate limit, audit log, signed URLs), Stripe Atlas trigger evaluation |

After cycle 10: revisit lyrics+events unification.
