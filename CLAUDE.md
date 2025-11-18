# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Sistema de Gestión de Gastos de Viaje** - A Laravel 12 + Filament 3.3 application for managing the complete lifecycle of travel expense requests, from initial submission through departmental and travel team approval, advance deposit management, to expense verification and reimbursement.

**Tech Stack:**
- **Backend:** PHP 8.2, Laravel 12
- **Admin Panel:** Filament 3.3 (Livewire, Alpine.js, Tailwind CSS 4)
- **Database:** SQLite (development), MySQL (production)
- **Testing:** PestPHP
- **Auth/Permissions:** Filament Shield (Spatie permissions)
- **Email:** Laravel Mail with custom Mailables

## Development Commands

### Running the Application
```bash
# Start development environment (concurrently runs server, queue, logs, and vite)
composer dev

# Individual services
php artisan serve              # Development server (port 8000)
php artisan queue:listen       # Queue worker
php artisan pail               # Real-time logs
npm run dev                    # Vite dev server
```

### Database Management
```bash
# Run migrations (uses SQLite by default, MySQL in production)
php artisan migrate

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Rollback migrations
php artisan migrate:rollback
```

### Testing
```bash
# Run all tests
composer test
# or
php artisan test

# Run specific test file
php artisan test tests/Feature/TravelRequestTest.php

# Run with coverage
php artisan test --coverage
```

### Code Quality
```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Check formatting without changes
./vendor/bin/pint --test
```

### Filament Commands
```bash
# Create new resource
php artisan make:filament-resource ModelName

# Create relation manager
php artisan make:filament-relation-manager ResourceName relationName

# Clear Filament cache
php artisan filament:cache-components

# Upgrade Filament (runs automatically on composer update)
php artisan filament:upgrade
```

### Queue Management
```bash
# Process queued jobs
php artisan queue:work

# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

## Architecture Overview

### Core Business Models

The application revolves around three primary workflows:

1. **Travel Request Workflow** (`TravelRequest` model)
   - User creates request → Departmental authorization → Travel team review → Treasury advance deposit → Travel completion → Expense verification

2. **Expense Verification Workflow** (`ExpenseVerification` model)
   - User uploads receipts (CFDI XML + non-deductible) → System validates against approved amounts → Travel/accounting team approves → Treasury processes reimbursement if needed

3. **Configuration & Catalogs**
   - Per diems (`PerDiem`) calculated based on position + destination + trip duration
   - Expense concepts/details define allowable expense categories
   - User roles determine authorization flow (department authorizer, travel team, treasury team)

### Key Domain Concepts

**Travel Request Status Flow:**
```
draft → pending → approved → travel_review → travel_approved → pending_verification
   ↓                 ↓              ↓                ↓
revision ← rejected    travel_rejected (terminal)
```

**Special User Roles (via boolean flags on User model):**
- `travel_team`: Can review/approve/reject in travel_review stage, upload attachments, manage expense verifications
- `treasury_team`: Can mark advance deposits as made/paid, process reimbursements

**Authorization Logic:**
- Users inherit authorizer from their Department
- Users can have an `override_authorizer_id` that takes precedence
- Accessed via `$travelRequest->actual_authorizer` (computed property)

### Critical Data Structures

**Travel Request JSON Fields:**
- `per_diem_data`: Snapshot of which per diems are enabled for the trip (keyed by PerDiem ID)
- `custom_expenses_data`: Array of custom expense line items (concept, amount, justification)
- `additional_services`: Array of company-managed services (flights, hotels, etc.)

These are **immutable snapshots** created at submission time to preserve the original request even if catalog data changes.

### File Storage & Attachments

- Default disk: `public` (configurable in `config/filament.php`)
- Travel request attachments: Managed by `TravelRequestAttachment` model
- Expense receipts: Managed by `ExpenseReceipt` model with CFDI XML parsing capability
- Reimbursement attachments: Stored as JSON array in `ExpenseVerification.reimbursement_attachments`

### Email & Notifications

All email templates are in `resources/views/emails/` using Blade. Key Mailables:
- `TravelRequestCreatedMail` - Initial submission notification
- `TravelRequestPendingAuthorizationMail` - To departmental authorizer
- `TravelRequestAuthorizedMail` - To requester on approval
- `TravelRequestPendingTravelReviewMail` - To travel team
- `TravelRequestAdvanceDepositMail` - To requester when treasury deposits advance

Database notifications (bell icon) use standard Laravel Notifications via `TravelRequestNotification`.

### Events & Listeners

- `TravelRequestCreated` event fires on model creation
- Listeners in `app/Listeners/` handle email sending (decoupled from models)

## Filament Panel Configuration

**Admin Panel Route:** `/admin` (configured in `AdminPanelProvider`)

**Key Customizations:**
- Home URL redirects to `/admin/travel-requests` (no dashboard)
- SPA mode enabled for better UX
- Sidebar collapsible on desktop
- Breadcrumbs disabled
- Database notifications enabled
- Custom brand colors (primary: `#857151`)
- Custom font: Raleway

**Resources Location:** `app/Filament/Resources/`

Each resource follows Filament's structure:
```
ResourceName/
├── Pages/
│   ├── ListResourceNames.php
│   ├── CreateResourceName.php
│   ├── EditResourceName.php
│   └── ViewResourceName.php (for complex resources)
└── ResourceName.php (base resource definition)
```

## Important Business Rules

### Travel Requests
1. **Editing:** Only allowed in `draft` or `revision` status
2. **Submission:** Requires all mandatory fields + at least one expense or per diem
3. **Automatic Transition:** `approved` status automatically moves to `travel_review`
4. **Per Diem Calculation:** Based on `Position`, `Country` (domestic/foreign), and trip duration (inclusive of return date)
5. **Advance Deposit:** Only treasury team can mark as made, automatically transitions to `pending_verification`

### Expense Verifications
1. **Creation:** Only allowed for travel requests in `pending_verification` status
2. **CFDI Parsing:** XML files are automatically parsed to extract supplier, amount, date, UUID
3. **Concept Matching:** System tracks which expense details have been proven vs. pending
4. **Reimbursement Logic:** Automatically calculated as `total_verified - advance_deposit_amount`

### Permissions
- Managed by Filament Shield (wraps Spatie Laravel Permission)
- Policy classes in `app/Policies/` define granular access control
- Check user capabilities with: `$user->isTravelTeamMember()`, `$user->isTreasuryTeamMember()`

## Database Schema Notes

- Primary keys: Standard Laravel `id` (BIGINT auto-increment)
- UUIDs: Used for `TravelRequest` and `ExpenseVerification` as public-facing folios
- Soft deletes: Not implemented (explicit `is_archived` flags used instead for audit trail)
- Timestamps: Standard Laravel `created_at`, `updated_at` on all tables
- Status fields: String enums (not database enums for MySQL compatibility)

## Common Patterns in This Codebase

### Model Methods for Business Logic
Models encapsulate business logic (not in controllers). Examples:
- `TravelRequest::submitForAuthorization()` - Handles status change + notifications
- `TravelRequest::approve()` - Approval logic + automatic progression to travel review
- `ExpenseVerification::approve()` - Checks if reimbursement needed

### Filament Form Patterns
- Sections with title + description + `columns(1)` for vertical forms
- Icons on compatible fields: `TextInput`, `Select`, `DatePicker` (NOT on `Textarea`, `FileUpload`)
- All labels in Spanish, placeholders with realistic examples
- Helper texts only when providing genuine value
- Resources redirect to list page after create/edit (configured in resource pages)
- Custom notifications with icon + title + subtitle

### Testing Strategy
- Tests in `tests/Feature/` for integration tests
- Tests in `tests/Unit/` for isolated unit tests
- Use in-memory SQLite for tests (configured in `phpunit.xml`)
- No frontend tests (Livewire components tested via feature tests)

## Routes Structure

- Filament handles most routes automatically
- Custom routes in `routes/web.php`:
  - `/attachments/{attachment}/download` - Protected attachment downloads
  - `/admin/expense-receipts/{receipt}/update-field` - AJAX endpoint for inline receipt editing
  - `/admin/expense-receipts/{receipt}/delete` - AJAX endpoint for receipt deletion
  - `/email-preview/*` - Development-only email preview routes

## Environment Configuration

Key `.env` variables:
- `DB_CONNECTION` - Set to `mysql` for production
- `QUEUE_CONNECTION` - Use `database` (default) or `redis` in production
- `MAIL_MAILER` - Configure SMTP for production emails
- `FILAMENT_FILESYSTEM_DISK` - Defaults to `public`

## Code Style Guidelines

1. **Follow Laravel conventions** - Use standard Laravel naming (StudlyCase for models, snake_case for database)
2. **Spanish for user-facing content** - All Filament labels, notifications, emails in Spanish
3. **English for code** - Comments, variable names, method names in English
4. **Professional code comments** - Document complex business logic, not obvious code
5. **Filament native components** - Always prefer native Filament components over custom JS/CSS
6. **Form redirects** - Resources must redirect to list page after create/edit
7. **Custom notifications** - Use icon + title + subtitle, primary color for icons
8. **Git commits** - NEVER make git commits automatically. Always wait for explicit user authorization before committing changes

## Debugging Tips

- Use `php artisan pail` for real-time logs (better than `tail -f`)
- Check `storage/logs/laravel.log` for historical logs
- Filament debug toolbar: Set `APP_DEBUG=true` in `.env`
- Database queries: Use Laravel Debugbar or `DB::enableQueryLog()`
- Email testing: Set `MAIL_MAILER=log` to write emails to log instead of sending

## External Dependencies

- **Filament Shield:** Permission management - docs at https://filamentphp.com/plugins/bezhansalleh-shield
- **Laravel Mail:** Email functionality - docs at https://laravel.com/docs/mail
- **Spatie Laravel Permission:** Underlying permission system - docs at https://spatie.be/docs/laravel-permission

## Migration Notes

When creating migrations:
- Use descriptive names with date prefix (Laravel convention)
- Always provide `down()` method for rollback capability
- For MySQL compatibility: Avoid using database enums, use string type instead
- Test migrations on both SQLite and MySQL if possible
