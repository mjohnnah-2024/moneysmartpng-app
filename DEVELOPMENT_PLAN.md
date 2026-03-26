# MoneySmart PNG — Development Plan (Updated)

> Last updated: 26 March 2026
> Status: Phases 1–9 complete | 325 PHP tests + 32 frontend tests passing

---

## Completed Phases (1–9)

| Phase | Title | Status |
|-------|-------|--------|
| 1 | Foundation – Auth, Dashboard, Transactions, Navigation | ✅ Complete |
| 2 | Budget Planner, Smart Alerts, Savings Goals, Insights | ✅ Complete |
| 3 | AI Budget Coach – Chat, Context Injection, OpenAI API | ✅ Complete |
| 4 | Freemium Gating, Referral System, Settings, Polish | ✅ Complete |
| 5 | Security – Policies, Rate Limiting, Input Sanitisation | ✅ Complete |
| 6 | Payments – Stripe, Mobile Money, Subscriptions | ✅ Complete |
| 7 | Admin Dashboard & Business Analytics | ✅ Complete |
| 8 | Testing & QA – Pest, Vitest, Manual | ✅ Complete |
| 9 | Tok Pisin Localisation – i18n, Formatters, AI Language | ✅ Complete |

### Current Architecture

- **Backend:** Laravel 13 (PHP 8.3), MySQL, Pest v4
- **Frontend:** React 19 + TypeScript + Inertia.js v3 + Tailwind CSS v4 + shadcn/ui
- **AI:** Laravel AI SDK (Prism) with OpenAI gpt-4o-mini via BudgetCoach agent
- **Payments:** Stripe + manual mobile money (admin approval)
- **i18n:** English + Tok Pisin (97 translation keys), centralized formatters (Kina/dates)
- **Auth:** Laravel Fortify (email/password + 2FA)

### Current Database Tables

| Table | Purpose |
|-------|---------|
| users | Auth, is_admin flag |
| profiles | Language, income, plan, referral_code |
| transactions | Income/expense with PNG categories |
| budgets | Monthly spending limits per category |
| goals | Savings goals with target/current amounts |
| chat_messages | AI conversation history |
| subscriptions | Premium plan management |
| usage_tracking | Feature usage limits enforcement |
| manual_payments | Mobile money payment submissions |

---

## Phase 10: Production Deployment

> **Status:** Not started
> **Priority:** High — required before public launch

### 10.1 Environment Configuration
- Create `.env.production` with production values (database, Stripe live keys, OpenAI key)
- Audit all secrets — ensure nothing hardcoded
- Set `APP_DEBUG=false`, `APP_ENV=production`

### 10.2 Deployment Platform
- Provision server via Laravel Forge (or Vapor for serverless)
- Set up MySQL, queue workers, cron jobs (`php artisan schedule:run`)
- Configure Vite production build (`npm run build`)
- Set up SSL via Let's Encrypt
- Configure `.htaccess` / Nginx for SPA routing

### 10.3 Error Monitoring
- Install Sentry for Laravel (`sentry/sentry-laravel`) and React (`@sentry/react`)
- Configure DSN in `.env`
- Set up error alerting (email/Slack)

### 10.4 PWA Finalisation
- Create `public/manifest.json` with correct icons (192px, 512px)
- Register service worker (`public/sw.js`) for offline caching
- Add PWA install prompt banner
- Ensure manifest uses production domain

### 10.5 Launch Checklist Script
- Verify all env variables set
- No `console.log` in production build
- No TODO comments in committed code
- All required files exist
- SSL configured
- Print "READY FOR LAUNCH" when all checks pass

### 10.6 Uptime & Cost Monitoring
- Set up UptimeRobot to monitor `/api/health`
- Set monthly spending cap on OpenAI dashboard
- Configure Laravel Telescope or Pulse for production monitoring

### 10.7 Soft Launch Procedure
- Share with 10–20 trusted users in Port Moresby
- Monitor Sentry for 48 hours, fix any errors
- Verify at least 5 successful signups and 1 successful payment
- Then announce publicly

### Deliverable
- App live on production domain with SSL
- Error monitoring active
- PWA installable on mobile devices
- Health endpoint monitored

---

## Phase 11: Safe-to-Spend Engine

> **Status:** Not started
> **Priority:** Critical — primary daily engagement hook
> **Depends on:** Phase 10 (can be developed pre-deployment)

### Goal
Give users instant clarity on how much they can safely spend today without compromising bills or savings goals. This is the **core hook feature** — the single most important element for daily app opens.

### 11.1 Backend: Safe-to-Spend Calculation Service

**Create** `app/Services/SafeToSpendService.php`

```
Safe-to-Spend = (Available Balance - Pending Bills - Savings Goals) / Remaining Days in Cycle
```

**Logic breakdown:**
- **Available Balance** = `profile.monthly_income` + this month's income transactions − this month's expense transactions
- **Pending Bills** = sum of remaining budget allocations not yet spent (compare `budgets.amount_limit` vs actual spending per category for recurring categories)
- **Savings Goals** = sum of `(goals.target_amount - goals.current_amount)` for active goals, prorated to monthly contribution needed to hit deadline
- **Remaining Days** = days left in current calendar month (or user-defined pay cycle)

**Files to create/modify:**
- `app/Services/SafeToSpendService.php` — NEW: core calculation logic
- `app/Http/Controllers/DashboardController.php` — MODIFY: inject safe-to-spend data
- `database/migrations/xxxx_create_recurring_expenses_table.php` — NEW: track recurring bills (rent, school fees, utilities)
- `app/Models/RecurringExpense.php` — NEW: model for recurring bills

**New migration: `recurring_expenses`**

| Column | Type | Purpose |
|--------|------|---------|
| id | bigint | PK |
| user_id | foreignId | Owner |
| name | string | Bill name (e.g., "Rent", "School Fees") |
| amount | decimal(12,2) | Expected amount |
| category | string | Maps to transaction categories |
| frequency | enum | monthly, fortnightly, weekly |
| due_day | integer | Day of month (1-31) |
| is_paid | boolean | Whether paid this cycle |
| last_paid_at | date | Last payment date |
| is_active | boolean | Soft toggle |

### 11.2 Backend: Pay Cycle Support

**Create** `database/migrations/xxxx_add_pay_cycle_to_profiles.php`

Add to `profiles` table:
- `pay_cycle_type` — enum: `monthly`, `fortnightly`, `weekly` (default: `monthly`)
- `pay_cycle_start_day` — integer: day of month income arrives (default: 1)

This allows the Safe-to-Spend calculation to use the user's actual pay cycle rather than calendar month.

### 11.3 Frontend: Dashboard Safe-to-Spend Card

**Modify** `resources/js/pages/dashboard.tsx`

- Replace current monthly summary as secondary info
- Add **primary hero card** at top of dashboard:
  ```
  ┌─────────────────────────────────┐
  │  Today, you can safely spend:   │
  │         K 45.50                 │
  │  ████████████░░░░  67% of daily │
  │  ──────────────────────────────  │
  │  Remaining this cycle: K 682.00 │
  │  Next bill: School Fees (3 days)│
  └─────────────────────────────────┘
  ```
- **Color-coded alerts:**
  - Green (`text-emerald-600`): > 70% of average daily budget remaining
  - Yellow (`text-amber-500`): 30–70% remaining
  - Red (`text-red-600`): < 30% or negative (overspending)
- Progress bar showing daily spend vs safe-to-spend amount
- Tap card to see breakdown (Available Balance, Pending Bills, Goal Contributions)

### 11.4 Frontend: Recurring Bills Management

**Create** `resources/js/pages/bills/index.tsx` and `bills/create.tsx`

- List recurring bills with due dates and paid/unpaid status
- Quick "Mark as Paid" toggle (creates matching transaction)
- Add new recurring bill form
- Link from dashboard "Next bill" widget

**Routes:**
- `GET /bills` — list recurring expenses
- `POST /bills` — create new recurring expense
- `PATCH /bills/{id}` — update
- `DELETE /bills/{id}` — delete
- `POST /bills/{id}/mark-paid` — mark as paid for current cycle

### 11.5 Real-Time Updates

- Safe-to-Spend recalculates on every transaction create/edit/delete
- Use Inertia partial reloads to update dashboard without full page refresh
- BudgetCoach AI prompt updated to include safe-to-spend context

### 11.6 Tests

- `tests/Feature/SafeToSpendTest.php` — calculation accuracy with various scenarios
- `tests/Feature/RecurringExpenseTest.php` — CRUD + mark-paid + policy
- `resources/js/__tests__/safe-to-spend-card.test.ts` — color thresholds, display formatting

### Deliverable
- Safe-to-Spend amount prominently displayed on dashboard
- Color-coded alerts (green/yellow/red)
- Recurring bills tracking with due dates
- Pay cycle configuration in profile
- Real-time recalculation on data changes

---

## Phase 12: Actionable Insights & Recommendations Engine

> **Status:** Not started
> **Priority:** High — drives daily value perception
> **Depends on:** Phase 11 (uses Safe-to-Spend data)

### Goal
Transform the existing Insights page from historical data display into a proactive recommendation engine that gives users clear, actionable advice.

### 12.1 Backend: Recommendations Service

**Create** `app/Services/RecommendationsService.php`

Generate personalized recommendations based on spending patterns:

| Rule | Trigger | Message Example |
|------|---------|-----------------|
| `daily_limit_warning` | Today's spending > 80% of safe-to-spend | "You're close to exceeding your daily limit." |
| `category_reduction` | Category spending > 120% of budget | "Reduce food spending to save K50 this week." |
| `unused_subscription` | Same recurring expense, no clear benefit | "Consider reviewing your [X] subscription." |
| `savings_opportunity` | Consistent underspend in category | "You've been under budget on Transport — move K30 to savings?" |
| `goal_pace_warning` | Goal contribution pace won't meet deadline | "Increase weekly savings by K15 to reach [Goal] on time." |
| `streak_encouragement` | User was under budget N consecutive days | "5-day streak! Keep it up to earn a badge." |
| `income_reminder` | No income logged this month after day 5 | "Have you received your pay this month? Log your income." |
| `bill_reminder` | Recurring bill due within 3 days | "School Fees (K500) due in 3 days." |

### 12.2 Backend: User Notification Preferences

**Create** `database/migrations/xxxx_create_notification_preferences_table.php`

| Column | Type | Purpose |
|--------|------|---------|
| id | bigint | PK |
| user_id | foreignId | Owner |
| type | string | Notification type (e.g., `daily_limit_warning`) |
| in_app | boolean | Show in-app (default: true) |
| push | boolean | Send push notification (default: false) |
| enabled | boolean | Master toggle (default: true) |

### 12.3 Frontend: Insights Page Enhancement

**Modify** `resources/js/pages/insights.tsx`

- Add **"Recommendations"** section at top of Insights page (above charts)
- Each recommendation is a dismissible card with:
  - Icon (info/warning/success)
  - Message text
  - Optional CTA button ("View Budget", "Add to Savings", "Dismiss")
- Dismissed recommendations stored in localStorage (reset monthly)

### 12.4 Frontend: Dashboard Recommendation Widget

**Modify** `resources/js/pages/dashboard.tsx`

- Below Safe-to-Spend card, show top 1–2 most urgent recommendations
- "See all insights →" link to full Insights page

### 12.5 Offline Support (Cached Recommendations)

- Cache last-fetched recommendations in localStorage
- On app load, show cached recommendations immediately
- Refresh from server when online
- Useful for users with intermittent connectivity (common in PNG)

### 12.6 Settings: Notification Preferences

**Create** `resources/js/pages/settings/notifications.tsx`

- Toggle switches for each recommendation type
- In-app vs push notification toggles
- Master "Pause all notifications" switch
- Route: `GET/PATCH /settings/notifications`

### 12.7 Tests

- `tests/Feature/RecommendationsTest.php` — each rule trigger scenario
- `tests/Feature/NotificationPreferencesTest.php` — CRUD + defaults
- Frontend tests for recommendation card rendering and dismiss logic

### Deliverable
- Personalized, actionable recommendations on dashboard and insights page
- Configurable notification preferences
- Offline-capable cached recommendations
- Bill reminders before due dates

---

## Phase 13: Goal-Based Saving + Streaks (Habit Building)

> **Status:** Not started
> **Priority:** High — drives retention via habit formation
> **Depends on:** Phase 11 (Safe-to-Spend), Phase 12 (streak triggers)

### Goal
Motivate users to build consistent financial habits through gamification: streaks, badges, points, and visual progress.

### 13.1 Backend: Streaks & Achievements System

**Create** `database/migrations/xxxx_create_streaks_table.php`

| Column | Type | Purpose |
|--------|------|---------|
| id | bigint | PK |
| user_id | foreignId | Owner |
| type | string | `under_budget`, `daily_login`, `savings_contribution` |
| current_count | integer | Current consecutive days |
| longest_count | integer | Personal best |
| last_recorded_at | date | Last day streak was active |

**Create** `database/migrations/xxxx_create_achievements_table.php`

| Column | Type | Purpose |
|--------|------|---------|
| id | bigint | PK |
| user_id | foreignId | Owner |
| badge_key | string | e.g., `first_transaction`, `7_day_streak`, `first_goal_completed` |
| earned_at | timestamp | When badge was earned |
| points | integer | Points awarded |

### 13.2 Badge Definitions

| Badge Key | Name | Condition | Points |
|-----------|------|-----------|--------|
| `first_transaction` | First Step | Log first transaction | 10 |
| `budget_creator` | Planner | Create first budget | 10 |
| `goal_setter` | Dreamer | Create first savings goal | 10 |
| `goal_completed` | Achievement Unlocked | Complete a savings goal | 50 |
| `3_day_streak` | Getting Started | 3 consecutive days under budget | 15 |
| `7_day_streak` | Steady Saver | 7-day streak | 30 |
| `30_day_streak` | Money Master | 30-day streak | 100 |
| `savings_50` | Halfway There | 50% of any goal reached | 25 |
| `first_referral` | Social Saver | First successful referral | 30 |
| `10_transactions` | Tracker | 10 transactions logged | 15 |
| `safe_spender` | Under Control | 7 days within safe-to-spend | 40 |

### 13.3 Backend: Streak Calculation Service

**Create** `app/Services/StreakService.php`

- `updateStreak($userId, $type)` — called after transactions/daily cron
- `checkAchievements($userId)` — evaluates all badge conditions, awards new ones
- Daily artisan command: `app:update-streaks` — runs at midnight to check daily streaks

### 13.4 Frontend: Goals Page Enhancement

**Modify** `resources/js/pages/goals/index.tsx`

- Enhanced progress bars with milestone markers (25%, 50%, 75%, 100%)
- Animated progress when contributing to goal
- "On track" / "Behind" indicator based on deadline pacing
- Streak counter display: "🔥 5-day streak — under budget!"

### 13.5 Frontend: Achievements Page

**Create** `resources/js/pages/achievements.tsx`

- Grid of all badges (earned = full color, unearned = greyed with lock icon)
- Total points counter
- Progress toward next badge
- Shareable achievement cards (for social features in Phase 14)
- Route: `GET /achievements`

### 13.6 Frontend: Dashboard Streak Widget

**Modify** `resources/js/pages/dashboard.tsx`

- Small streak indicator near Safe-to-Spend card
- "5-day streak! Keep going!" with fire emoji
- Tap to view achievements page

### 13.7 Bottom Navigation Update

- Add Achievements icon to bottom nav (or as sub-item under Goals)
- Badge count indicator for new unread achievements

### 13.8 Tests

- `tests/Feature/StreakTest.php` — streak incrementing, breaking, personal best
- `tests/Feature/AchievementTest.php` — each badge trigger, no duplicates
- Frontend tests for badge grid, streak counter display

### Deliverable
- Streak tracking (under budget, daily login, savings)
- 11+ badge types with point values
- Achievements page with visual badge grid
- Dashboard streak widget
- Daily cron job for streak updates

---

## Phase 14: Social Savings Squads

> **Status:** Not started
> **Priority:** Medium — drives viral growth and user acquisition
> **Depends on:** Phase 13 (achievements for sharing)
> **Feature gate:** Premium only (squads creation), free users can join

### Goal
Turn saving into a community activity. Users form small groups with shared progress visibility, optional challenges, and invite mechanics for viral growth.

### 14.1 Backend: Squad Data Model

**Create migrations:**

**`squads` table:**

| Column | Type | Purpose |
|--------|------|---------|
| id | bigint | PK |
| name | string | Squad name |
| description | text | Optional description |
| creator_id | foreignId | Squad creator |
| invite_code | string(8) | Unique join code |
| max_members | integer | Default: 10 |
| is_active | boolean | Active status |

**`squad_members` table:**

| Column | Type | Purpose |
|--------|------|---------|
| id | bigint | PK |
| squad_id | foreignId | Squad |
| user_id | foreignId | Member |
| role | enum | `admin`, `member` |
| joined_at | timestamp | Join date |

**`squad_challenges` table:**

| Column | Type | Purpose |
|--------|------|---------|
| id | bigint | PK |
| squad_id | foreignId | Squad |
| name | string | Challenge name (e.g., "Save K100 this week") |
| target_amount | decimal(12,2) | Target per member |
| starts_at | date | Challenge start |
| ends_at | date | Challenge end |
| is_active | boolean | Status |

**`squad_challenge_progress` table:**

| Column | Type | Purpose |
|--------|------|---------|
| id | bigint | PK |
| challenge_id | foreignId | Challenge |
| user_id | foreignId | Member |
| current_amount | decimal(12,2) | Progress |
| updated_at | timestamp | Last update |

### 14.2 Privacy: Shared Progress (No Raw Balances)

**Critical privacy rule:** Squad members NEVER see each other's actual balances, transaction amounts, or income. They only see:
- Percentage progress toward challenges (e.g., "72% complete")
- Streak counts (e.g., "5-day streak")
- Badge achievements
- Whether they're "on track" or "behind" on shared challenges

### 14.3 Backend: Squad Controller

**Create** `app/Http/Controllers/SquadController.php`

- `index()` — list user's squads
- `create()` / `store()` — create a squad (premium only)
- `show($squad)` — squad detail with members, challenges, leaderboard
- `join()` — join via invite code
- `leave($squad)` — leave squad
- `invite($squad)` — generate shareable invite link

**Create** `app/Http/Controllers/SquadChallengeController.php`

- `store($squad)` — create challenge (squad admin only)
- `updateProgress($challenge)` — update own progress
- `show($challenge)` — challenge detail with member progress

### 14.4 Frontend: Squads Pages

**Create** `resources/js/pages/squads/index.tsx`
- List of user's squads with member count and active challenge count
- "Create Squad" button (premium) / "Join Squad" button (all users)
- Join by entering invite code

**Create** `resources/js/pages/squads/show.tsx`
- Squad detail page
- Members list with streak counts and badge counts
- Active challenges with group progress bar
- "Invite Friends" button → generates shareable link

**Create** `resources/js/pages/squads/challenge.tsx`
- Challenge detail with per-member progress (percentage only)
- Leaderboard sorted by progress percentage
- "Update My Progress" button

### 14.5 Invite & Sharing Mechanics

- Generate invite links: `https://app.moneysmartpng.com/squads/join/{invite_code}`
- Share via: WhatsApp (primary for PNG), Facebook, Copy Link
- When invited user joins and isn't registered → redirect to signup with squad auto-join
- Track squad invites as a referral vector (counts toward referral rewards)

### 14.6 Routes

```
GET     /squads                         — list squads
POST    /squads                         — create squad (premium)
GET     /squads/{squad}                 — squad detail
DELETE  /squads/{squad}                 — delete squad (admin only)
POST    /squads/{squad}/leave           — leave squad
POST    /squads/join                    — join via invite code
POST    /squads/{squad}/challenges      — create challenge
GET     /squads/{squad}/challenges/{id} — challenge detail
POST    /squads/{squad}/challenges/{id}/progress — update progress
```

### 14.7 Feature Gating

- **Creating** a squad → Premium only
- **Joining** a squad → Free and Premium
- **Creating** challenges → Squad admin only (the creator)
- Max squads per user: Free = 2, Premium = 10

### 14.8 Tests

- `tests/Feature/SquadTest.php` — CRUD, join/leave, invite codes, premium gating
- `tests/Feature/SquadChallengeTest.php` — create, progress, leaderboard
- Privacy test: ensure no raw amounts leak through API responses

### Deliverable
- Squad creation and management
- Invite system with shareable links (WhatsApp/Facebook/copy)
- Squad challenges with percentage-based leaderboards
- Privacy-preserving progress sharing (no raw balances visible)
- Premium gating for squad creation

---

## Phase 15: UX Polish & Performance

> **Status:** Not started
> **Priority:** Medium — drives engagement quality
> **Depends on:** Phases 11–14 (polish all new features)

### Goal
Ensure the app delivers its value proposition within 30 seconds of opening. Fast load times, low cognitive load, delightful micro-interactions.

### 15.1 Dashboard Hierarchy Redesign

Reorder dashboard to prioritize daily value:

```
┌─ Safe-to-Spend Hero Card (Phase 11) ──────────┐
│  "Today, you can safely spend: K 45.50"        │
│  [Green/Yellow/Red indicator]                   │
│  Next bill: School Fees in 3 days              │
└────────────────────────────────────────────────┘

┌─ Top Recommendation ──────────────────────────┐
│  ⚡ "Reduce food spending to save K50/week"    │
│  [View Budget →]                               │
└────────────────────────────────────────────────┘

┌─ Streak + Quick Stats ─────────────────────────┐
│  🔥 5-day streak │ K2,450 income │ K1,768 spent │
└────────────────────────────────────────────────┘

┌─ Monthly Summary (existing) ───────────────────┐
│  [Donut chart] [Category breakdown]            │
└────────────────────────────────────────────────┘

┌─ Recent Transactions (existing) ───────────────┐
└────────────────────────────────────────────────┘
```

### 15.2 Loading & Empty States

- Skeleton loaders for all data-fetching components
- Meaningful empty states with illustrations and CTAs:
  - No transactions → "Start tracking your spending" → [Add Transaction]
  - No goals → "Set your first savings goal" → [Create Goal]
  - No streaks → "Stay under budget today to start your streak!"

### 15.3 Animations & Micro-interactions

- Fade/slide entrance animations for cards (CSS transitions, not heavy libraries)
- Progress bar fill animations on goals page
- Number count-up animation for Safe-to-Spend amount
- Haptic feedback on mobile (navigator.vibrate) for:
  - Transaction added
  - Goal contribution
  - Badge earned
- Confetti animation when completing a goal or earning major badge

### 15.4 Offline Capabilities

- Cache critical data in localStorage:
  - Last Safe-to-Spend amount
  - Recent recommendations
  - Streak count
  - Badge list
- Show "Offline" banner when connection lost
- Queue transaction entries for sync when back online (service worker)

### 15.5 Performance Optimization

- Lazy load route components (`React.lazy` + `Suspense`)
- Memoize expensive calculations (Safe-to-Spend, recommendations)
- Paginate transaction history (already done)
- Image optimization for badge icons (SVG preferred)
- Inertia partial reloads for dashboard components

### 15.6 Accessibility

- Ensure all color-coded elements have text alternatives
- ARIA labels on all interactive elements
- Keyboard navigation support
- Screen reader friendly financial data presentation

### 15.7 Tests

- Lighthouse audit targets: Performance > 90, Accessibility > 90
- Frontend tests for skeleton loaders and empty states
- Offline behavior tests

### Deliverable
- Redesigned dashboard with clear information hierarchy
- Skeleton loaders and meaningful empty states
- Smooth animations and micro-interactions
- Offline-capable with cached critical data
- Performance optimized (lazy loading, memoization)

---

## Phase 16: Architecture Hardening & API-First Design

> **Status:** Not started
> **Priority:** Medium — sets up scalability for future growth
> **Depends on:** Phase 15

### Goal
Ensure the architecture is modular, secure, and ready for future integrations (bank sync, push notifications, mobile apps).

### 16.1 API-First Routes

- Create versioned API routes (`routes/api.php` with `/api/v1/` prefix)
- Expose key endpoints for future mobile app consumption:
  - `GET /api/v1/safe-to-spend`
  - `GET /api/v1/recommendations`
  - `GET /api/v1/streaks`
  - `GET /api/v1/achievements`
  - `GET /api/v1/squads`
- Use Laravel Sanctum for API token authentication
- Rate limit API endpoints appropriately

### 16.2 Bank Sync Preparation (Future)

- Design `bank_connections` table schema (provider, access_token, last_synced)
- Create `BankSyncService` interface (for Plaid, Yodlee, or local PNG bank APIs)
- Auto-categorize imported transactions using AI
- **Note:** Actual bank API integration deferred until PNG banking partners identified

### 16.3 Push Notification Infrastructure

- Install Laravel notification channels (database + FCM for web push)
- Create `notifications` table via Laravel's built-in migration
- Wire up recommendation triggers to notification dispatch
- FCM setup for PWA push notifications
- Respect user notification preferences (Phase 12)

### 16.4 Data Security Hardening

- Encrypt sensitive fields at rest (phone numbers, bank references)
- Audit logging for admin actions (grant/revoke premium, delete user)
- CSRF protection already in place (Laravel default)
- Content Security Policy headers
- Regular dependency audit (`composer audit`, `npm audit`)

### 16.5 Modular Service Architecture

Ensure each major feature is a self-contained service:

```
app/Services/
├── SafeToSpendService.php      (Phase 11)
├── RecommendationsService.php  (Phase 12)
├── StreakService.php            (Phase 13)
├── AchievementService.php      (Phase 13)
├── SquadService.php             (Phase 14)
└── BankSyncService.php          (Phase 16 — interface only)
```

### 16.6 Tests

- API endpoint tests with Sanctum token auth
- Security audit tests (no data leakage across users)
- Service isolation tests

### Deliverable
- Versioned API endpoints for future mobile app
- Push notification infrastructure
- Encrypted sensitive data
- Modular service architecture
- Bank sync interface (ready for future integration)

---

## Implementation Priority & Sequencing

### Immediate (Pre-Launch)
1. **Phase 10** — Production Deployment (required for public access)

### Sprint 1 (Post-Launch, Weeks 1–2)
2. **Phase 11** — Safe-to-Spend Engine (core daily hook)
3. **Phase 12** — Actionable Insights (daily value)

### Sprint 2 (Weeks 3–4)
4. **Phase 13** — Streaks & Achievements (retention)
5. **Phase 15** — UX Polish (engagement quality)

### Sprint 3 (Weeks 5–6)
6. **Phase 14** — Social Savings Squads (viral growth)
7. **Phase 16** — Architecture Hardening (scalability)

---

## Success Criteria

| Metric | Target |
|--------|--------|
| Time to value | Users see clear, actionable value within **30 seconds** of opening |
| Daily engagement | Users check app minimum **once/day** |
| 7-day retention | First-time user retention **> 60%** |
| Feature adoption | **> 50%** of users interact with Safe-to-Spend daily |
| Social growth | **> 20%** of users join at least one squad |
| Streak participation | **> 40%** of active users maintain a streak |
| Referral rate | **> 15%** of users share their referral code |

---

## Database Schema Summary (All Phases)

### New Tables (Phases 11–16)

| Table | Phase | Purpose |
|-------|-------|---------|
| `recurring_expenses` | 11 | Recurring bills tracking |
| `notification_preferences` | 12 | Per-type notification toggles |
| `streaks` | 13 | Consecutive day tracking |
| `achievements` | 13 | Earned badges with points |
| `squads` | 14 | Social savings groups |
| `squad_members` | 14 | Squad membership |
| `squad_challenges` | 14 | Group savings challenges |
| `squad_challenge_progress` | 14 | Per-member challenge progress |

### Modified Tables (Phases 11–16)

| Table | Phase | Changes |
|-------|-------|---------|
| `profiles` | 11 | Add `pay_cycle_type`, `pay_cycle_start_day` |

---

## New Routes Summary (Phases 11–16)

```
# Phase 11 — Bills
GET/POST      /bills
PATCH/DELETE  /bills/{id}
POST          /bills/{id}/mark-paid

# Phase 12 — Notifications
GET/PATCH     /settings/notifications

# Phase 13 — Achievements
GET           /achievements

# Phase 14 — Squads
GET/POST      /squads
GET/DELETE    /squads/{squad}
POST          /squads/{squad}/leave
POST          /squads/join
POST          /squads/{squad}/challenges
GET           /squads/{squad}/challenges/{id}
POST          /squads/{squad}/challenges/{id}/progress

# Phase 16 — API
GET           /api/v1/safe-to-spend
GET           /api/v1/recommendations
GET           /api/v1/streaks
GET           /api/v1/achievements
GET           /api/v1/squads
```

---

## Bottom Navigation Update

Current: **Home** | **Track** | **Budget** | **Goals** | **Coach**

Proposed (Phase 14+): **Home** | **Track** | **Budget** | **Goals** | **Squads**

Coach moves to a FAB (floating action button) or accessible from Home header — since Safe-to-Spend + recommendations reduce the need for frequent AI chat.

---

## Translation Keys Required (Phases 11–16)

Each phase must add corresponding entries to both `en.json` and `tpi.json`:
- Phase 11: ~15 keys (safe_to_spend, available_balance, pending_bills, remaining_days, etc.)
- Phase 12: ~12 keys (recommendations, notification types, dismiss, etc.)
- Phase 13: ~20 keys (streak, achievement, badge names, points, etc.)
- Phase 14: ~18 keys (squad, invite, challenge, progress, leaderboard, etc.)
- Phase 15: ~5 keys (offline banner, empty states)
- Phase 16: ~3 keys (api, sync, notifications)

Estimated total: **~73 new translation keys** (bringing total to ~170)
