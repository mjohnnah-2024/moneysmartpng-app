# MoneySmart PNG

A personal finance management application built for Papua New Guineans. Track spending, set budgets and savings goals, get AI-powered financial advice, and build better money habits — all in English and Tok Pisin.

## Features

- **Dashboard** — Safe-to-Spend daily amount, spending summary, streaks, and recommendations
- **Transactions** — Log income and expenses with PNG-specific categories
- **Budgets** — Set monthly spending limits per category with alerts
- **Savings Goals** — Track progress toward financial targets with deadline pacing
- **Recurring Bills** — Manage rent, school fees, utilities with due-date reminders
- **AI Budget Coach** — Chat with an AI advisor that understands your finances (OpenAI gpt-4o-mini)
- **Insights & Recommendations** — Actionable advice based on spending patterns
- **Streaks & Achievements** — Gamified habit building with 11+ badges and a points system
- **Social Savings Squads** — Form groups, set shared challenges, and track progress together
- **Freemium Model** — Free tier with premium upgrades via Stripe or mobile money
- **Admin Panel** — User management (CRUD), payment approvals, AI usage monitoring
- **Tok Pisin Localisation** — Full i18n support with 97+ translation keys
- **API** — Versioned REST API (`/api/v1/`) with Sanctum authentication

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 13 (PHP 8.3) |
| Frontend | React 19, TypeScript, Inertia.js v3 |
| Styling | Tailwind CSS v4, shadcn/ui |
| AI | Laravel AI SDK (Prism) + OpenAI |
| Auth | Laravel Fortify (email/password + 2FA), Sanctum (API) |
| Payments | Stripe + manual mobile money (admin approval) |
| Testing | Pest v4 (423 PHP tests), Vitest (32 frontend tests) |
| Code Quality | Laravel Pint, ESLint, Prettier |

## Requirements

- PHP 8.3+
- Node.js 20+
- Composer 2+
- MySQL 8+ (or SQLite for local development)

## Installation

```bash
# Clone the repository
git clone https://github.com/mjohnnah-2024/moneysmartpng-app.git
cd moneysmartpng-app

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure your .env file with database, OpenAI, and Stripe credentials

# Run migrations and seed
php artisan migrate
php artisan db:seed

# Build frontend assets
npm run build

# Start the development server
composer run dev
```

## Development

```bash
# Start dev server with hot reload
npm run dev

# Run PHP tests
php artisan test --compact

# Run frontend tests
npm test

# Lint and format
vendor/bin/pint          # PHP formatting
npm run lint             # ESLint
npm run format           # Prettier

# Generate Wayfinder routes (after adding/changing controllers)
php artisan wayfinder:generate
```

## Project Structure

```
app/
├── Http/Controllers/       # Web and API controllers
│   ├── Admin/              # Admin panel controllers
│   └── Api/                # API v1 controllers
├── Models/                 # Eloquent models
├── Services/               # Business logic (SafeToSpend, Recommendations, Streaks)
├── Policies/               # Authorization policies
└── Ai/Agents/              # AI agent definitions (BudgetCoach)
resources/js/
├── pages/                  # Inertia React pages
│   ├── admin/              # Admin panel pages
│   ├── bills/              # Recurring expenses
│   ├── squads/             # Social savings squads
│   └── settings/           # User settings
├── components/ui/          # shadcn/ui components
├── layouts/                # App and admin layouts
└── lib/i18n/               # Translation files (en.json, tpi.json)
tests/
├── Feature/                # Integration tests (Pest)
└── Unit/                   # Unit tests (Pest)
```

## License

MIT
