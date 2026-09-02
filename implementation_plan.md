# Placement Pro Admin Portal — Pixel-Perfect Modern Redesign

Upgrade the Placement Pro Admin Portal into a modern, minimalist, highly professional B2B SaaS interface matching exact pixel-perfect specifications. The design system features an Indigo/Purple primary brand palette (`#4F46E5` / `#4338CA`), soft diffused drop shadows, rounded card containers (12–16px), clean typography (Inter font), and a sidebar + top header layout across 8 key screens.

## User Review Required

> [!IMPORTANT]
> - All 8 requested screens (`login.php`, `dashboard.php`, `sections.php`, `students.php`, `companies.php`, `import.php`, `reports.php`, `settings.php`) will be created or overhauled to match the exact visual, component, card, chart, and metric specifications in your prompt.
> - Full frontend-backend integration with existing Flask REST API will be maintained, with rich dynamic mock data fallbacks for non-DB metrics (like real-time donut charts, section pipeline breakdown, and settings states).

## Open Questions

> [!NOTE]
> No blocking open questions. Implementation will adhere 100% to the provided design system token rules, color codes, component structures, and screen-by-screen prompt specifications.

## Proposed Changes

### Global Design System & Component Layout

#### [MODIFY] [style.css](file:///c:/Users/Sanjay%20G%20L/Desktop/placement-pro/frontend/assets/css/style.css)
- Implement CSS variables for Indigo/Purple primary brand (`#4F46E5`), off-white background (`#F8F9FA`), card backgrounds (`#FFFFFF`), dark slate text (`#111827`), muted text (`#6B7280`), and semantic status badges (Success `#10B981`, Warning `#F59E0B`, Danger `#EF4444`).
- Add standard card shadow (`box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05)`), border radii (12-16px for cards, 8px for buttons/inputs), custom scrollbars, and sidebar/header layout positioning.

#### [NEW] [header.php](file:///c:/Users/Sanjay%20G%20L/Desktop/placement-pro/frontend/partials/header.php)
- Implement 72px fixed top header with 400px global search bar ("Search students, companies, records..."), notification bell with badge dot, help icon, and user profile component with circular avatar, user name, and role badge.

#### [NEW] [sidebar.php](file:///c:/Users/Sanjay%20G%20L/Desktop/placement-pro/frontend/partials/sidebar.php)
- Implement 250px fixed left sidebar with pure white background, logo area ("Placement Pro" bold + "Admin Portal"), icons & text for all 7 navigation links (Dashboard, Students, Companies, Import, Sections, Reports, Settings).
- Active menu indicator with light purple background (`#EEF2FF`) and bold purple vertical left strip.
- Bottom CTA button: full-width purple `+ Post New Job` button.

#### [MODIFY] [nav.php](file:///c:/Users/Sanjay%20G%20L/Desktop/placement-pro/frontend/partials/nav.php)
- Include sidebar & top header partials for consistent layout framing.

---

### Screen Implementations

#### [MODIFY] [login.php](file:///c:/Users/Sanjay%20G%20L/Desktop/placement-pro/frontend/login.php)
- Ambient pastel purple/pink soft gradient background.
- Centered white auth card with soft shadow, logo icon, "Placement Pro", and "Welcome back, Admin".
- WORK EMAIL input with mail icon, PASSWORD input with lock icon, eye icon toggle, "Forgot Password?" link, "Stay logged in for 30 days" checkbox, solid purple "Sign In to Dashboard" CTA, and "Authorized Personnel Only. Support Center" footer text.

#### [MODIFY] [dashboard.php](file:///c:/Users/Sanjay%20G%20L/Desktop/placement-pro/frontend/dashboard.php)
- Header: "Academic Overview" ("Placement Session 2023-2024"), Filter button & solid purple Export PDF button.
- Row of 6 KPI Cards: Total Students (2,400 +12%), Total Companies (45 "New"), Total Selected (1,850, 82% progress bar), Placement % (77%, line chart icon), Highest Package ($42 LPA Goldman Sachs), Avg Package ($8.5 LPA +1.2 LPA vs Prev).
- Middle Section: "Company-wise Hiring" bar chart (Chart.js canvas with "Last 6 Months" dropdown) and "Section-wise" donut chart (1.8k placed, CS/IT, Electronics, Mechanical legend).
- Bottom Section: "Recent Activities" timeline feed, interactive Calendar widget (1st & 7th highlighted, "Amazon Tech Talk"), and "Urgent Alerts" box (light red bg, unresolved document conflicts warning).

#### [NEW] [sections.php](file:///c:/Users/Sanjay%20G%20L/Desktop/placement-pro/frontend/sections.php)
- Header "Sections Overview", segmented control tabs (Section A active, Section B, Section C).
- KPI Cards for section metrics.
- Widgets: Company Distribution donut chart (Product, Service, Fintech, Others) & Department Analytics horizontal progress bars (CS, ECE, IT).
- Placement Pipeline bottom horizontal connected step-tracker: Eligible -> Aptitude -> Technical -> Selected with student drop-off metrics.

#### [MODIFY] [students.php](file:///c:/Users/Sanjay%20G%20L/Desktop/placement-pro/frontend/students.php)
- Header "Students Directory", List vs Grid toggle switch (List active).
- Filter Bar: Department, Status, Batch Year dropdowns, "Showing 1-10 of 2,450 students".
- Data Table: Student (avatar, full name, ID), Department & Section, Company (mini logo + name), Status pill badges, Actions.
- Footer pagination & rows per page selector.
- Floating Action Button (FAB): Circular purple FAB in bottom right with `user +` icon.

#### [MODIFY] [companies.php](file:///c:/Users/Sanjay%20G%20L/Desktop/placement-pro/frontend/companies.php)
- Header "Companies Directory", Filter & Export List buttons, search bar, Industry dropdown, Sort dropdown ("Package: High to Low").
- Grid Layout: Company cards (Logo, status pill Live/Pending/Closed, description, Selected Students & Avg Package metrics, Next Hiring date, full-width "View Details" outline button).
- Empty State Card: Dashed gray border, large plus icon, "Add Company" button.

#### [MODIFY] [import.php](file:///c:/Users/Sanjay%20G%20L/Desktop/placement-pro/frontend/import.php)
- Breadcrumb "Dashboard > Import Student Data", Title "Data Ingestion Center".
- Left/Center: Upload area drag-and-drop zone with dashed border, "Auto-detect Schema" badge, cloud upload icon, "Click or drag files here", gray "Select Excel File" button, purple "Import Google Sheets" button.
- Sidebar: INSTRUCTIONS card (3 steps, template schema link), Last Import status card (success icon, batch number, record count, View Logs link).
- Integrated file parsing & preview table.

#### [MODIFY] [reports.php](file:///c:/Users/Sanjay%20G%20L/Desktop/placement-pro/frontend/reports.php)
- Header "Analytics & Reports", Date range ("Last 30 Days") & Department dropdowns.
- Top Row: Placement Summary 2023 (metrics + PDF/Excel buttons), Student Eligibility (horizontal progress bar + Get Full Report button).
- Middle Row: 3 Advanced Analysis cards (Company Performance, Sector Wise Growth, Skill Gap Analysis) with subtext and PDF/XLS outline buttons.
- Bottom Row: Diversity Dashboard card, Custom Report Builder solid purple promo card with arrow icon.

#### [NEW] [settings.php](file:///c:/Users/Sanjay%20G%20L/Desktop/placement-pro/frontend/settings.php)
- Two-column layout: Left inner-nav menu (User Profile, Appearance, Notifications, Security, Integrations), Right settings panels.
- Panels: User Profile (photo with pencil badge, Name, Email, Bio), Appearance (Light/Dark/System graphical buttons), Notification Hub (toggles), Security & Access (2FA, Sign out all sessions, Change password link), Platform Integrations (LinkedIn Recruiter, Slack, Add Integration).
- Sticky bottom right toolbar: "Discard Changes" link & solid purple "Save Preferences" button.

---

## Verification Plan

### Automated Tests / Syntax Checks
- Verify PHP syntax across all updated and new frontend PHP files: `php -l frontend/*.php`.

### Manual Verification
- Render all 8 screens in browser subagent or local browser to confirm pixel-perfect visual compliance against prompt criteria:
  - Sidebar & Top header present and styled accurately on all portal pages.
  - Login card, inputs, gradient background matching spec.
  - Dashboard stat cards, Chart.js bar & donut graphs, timeline, calendar, and urgent alerts rendering cleanly.
  - Sections overview pipeline tracker and charts.
  - Student directory table, semantic status pills, FAB button.
  - Company directory cards, status badges, and empty state card.
  - Data ingestion drag-and-drop box and instructions sidebar.
  - Reports summary cards and custom report builder promo card.
  - Settings panels, graphical theme selectors, and sticky footer toolbar.
