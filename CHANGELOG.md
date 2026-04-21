# Changelog

All notable changes to this project will be documented in this file.

This changelog follows a release-style summary based on major project milestones and significant git history, using release dates without time stamps.

## [Unreleased]

### Changed
- Redesigned the public login page into a cleaner institutional layout while preserving the existing authentication, session, CSRF, language, and SSO-related behavior.
- Redesigned the forgot-password, reset-password, and forced password change pages so the full pre-login authentication flow now uses a consistent visual system.
- Updated pre-login pages to follow the global sidebar theme configuration from `tbl_m_config`, including header gradients, primary buttons, focus states, and supporting accent treatments.
- Simplified pre-login copy and support surfaces by removing selected nonessential text blocks and banner labels from the login and forgot-password pages.
- Updated README and CHANGELOG naming to use the active runtime identity `Student Management System (e-HEPA)`.

## [1.7.1] - 2026-04-19

### Added
- Added request-scoped general application log file routing so runtime logs can be written to entrypoint-specific files such as `page_index.log` and `ajax_user-delete.log` instead of a single shared log file.

### Changed
- Updated project documentation to use the active runtime project identity `Student Management System (e-HEPA)` instead of the previous mismatched project naming.
- Updated README to reflect the active `Student Management System (e-HEPA)` runtime identity, current folder structure, and currently implemented feature set.
- Reorganized README feature documentation into `Features Utama` and `Features Tambahan` so the current Student Management System (e-HEPA) build can be used as a benchmark baseline for future systems.
- Aligned static sidebar quick-access items such as Dashboard, User Manual, and Logout with the same menu height and spacing used by database-driven module menus.
- Updated base fallback settings and authentication-page labels so static defaults now match the active Student Management System (e-HEPA) identity when database overrides are unavailable.
- Finalized the project version source of truth to `1.7.1` and added a web-runtime `public/VERSION` source so shared UI surfaces such as the footer read the correct version consistently.

### Fixed
- Fixed logout confirmation handling in sidebar and topbar so opening or cancelling the confirmation dialog no longer triggers page reload or premature navigation.
- Fixed footer and login-page version rendering so displayed version now uses the shared application version source instead of stale fallback values.

## [1.7.0] - 2026-04-11

### Added
- Added HAT Mizan staff mapping SQL scripts and multi-site migration documentation for production rollout.
- Added `public/log/.htaccess` to block direct browser access to project log files on Apache deployments.

### Changed
- Updated temporary BDR/API/access/SSO/tetapan debug logs to be disabled by default and gated behind explicit environment flags.
- Updated SweetAlert behavior so standard modal alerts require a user click to close, while toast alerts continue to auto-close.
- Updated URL helper and login form link generation to handle both subfolder deployment and production root-domain deployment cleanly.

### Fixed
- Fixed stale logout alert behavior on repeated SSO login attempts after logout.
- Fixed AJAX/access-denied responses so JSON-like requests receive JSON instead of an HTML error response.
- Fixed duplicate-slash URL generation for production root deployment.
- Fixed SSO SP client redirects so production root-domain SSO handoff exits immediately after redirect and uses proxy-aware current URL matching.

## [1.6.0] - 2026-04-08

### Added
- Expanded the System Template Generator creation modal into a tabbed flow with separate sections for template form input, page icon selection, and access mode selection.
- Increased generated page icon choices to 48 selectable icons.
- Added protected-account policy support for the special account `0530-09`, including protected badge display and stricter delete/edit governance.
- Added policy-driven SSO auto provisioning for Staff and Student identities, including configurable default group assignment and first-login application record creation.
- Added auto-provision visibility in `senarai-pengguna.php` so SSO-created accounts can be identified directly from the user listing.

### Changed
- Redesigned the System Template Generator modal to be more compact, centered, and viewport-friendly, with internal panel scrolling instead of full modal scrolling.
- Simplified `Page Name` guidance to clarify that users should enter the page name only, without `.php`.
- Refined multiple administrative UX surfaces, including audit metadata visibility, sidebar toggle behavior, modal presentation, and protected-account interaction handling.
- Extended Login Policy configuration with a dedicated `SSO Auto Provisioning` section for Staff and Student controls.
- Refined login flow handling so Staff and Student first-access behavior is distinguished more clearly between SSO provisioning, manual login readiness, and Public-user access rules.

### Fixed
- Restored DataTables behavior in `senarai-pengguna.php` after earlier initialization regressions.
- Stabilized sidebar collapsible menu behavior so parent menus can open and close reliably without unwanted page refreshes.
- Reduced generic login failures by mapping SSO auto-provision edge cases to clearer outcomes, including invalid default group configuration and unavailable source identity data.

### Security
- Hardened request-level access control for pages, AJAX endpoints, and actions using centralized access policy handling.
- Restricted audit metadata visibility to Super Admin on profile audit flows.
- Kept Public-user access strictly dependent on existing `tbl_m_user` records while limiting Staff and Student automatic record creation to SSO-only first access.

## [1.5.0] - 2026-04-07

### Added
- Added centralized access governance with support for public, super-admin-only, custom-guard, and group-menu-based request policies.
- Added current system module documentation coverage in the project README.

### Changed
- Refined administrative UX across access-protected pages, modals, audit views, and generated page workflows.

### Security
- Hardened direct URL access so page authorization is enforced server-side instead of relying only on menu visibility.
- Introduced safer handling for unavailable or unauthorized destinations with neutral user-facing messaging.

## [1.4.0] - 2026-04-03

### Added
- Added full email template management, including template listing, preview, test send, seed generation, and delivery integration.
- Expanded system template generation capabilities with DB-backed template records, modal-based generation flows, and template variants.

### Changed
- Improved generated page scaffolding with cleaner management flow and access mode support for generated pages.

## [1.3.0] - 2026-03-31

### Added
- Added login policy management and stronger OneID SSO integration flow.
- Added password lifecycle enforcement, password history controls, reset/change flows, and database-backed throttling and lockout controls.
- Added Audit Center enhancements covering events, requests, sessions, changes, security insights, and advanced filtering.

### Changed
- Refined login UX and strengthened session termination handling across admin-facing flows.
- Improved system settings behavior for OneID SSO configuration and related authentication settings.

### Security
- Hardened authentication, session control, and SSO handoff behavior.

## [1.2.0] - 2026-03-22

### Added
- Added DB-backed general system settings with language-aware runtime fallback for footer, mail notes, and shared layout content.
- Added role-based manual management and better user manual access handling.
- Added safer runtime configuration support for system-wide operational settings.

### Changed
- Migrated the external runtime architecture from a legacy single-active Sybase model to a clearer staff-and-student multi-domain runtime model.
- Moved database secrets and sensitive runtime values to environment-driven configuration for safer deployment.
- Rewrote core project documentation around architecture, features, setup, security, and deployment.

### Fixed
- Cleaned up legacy configuration, unused helpers, demo remnants, and orphaned assets.

## [1.1.0] - 2026-03-20

### Added
- Added richer profile management features, including audit trail display, audit modal improvements, device/duration helpers, and better login activity visibility.
- Added stricter group/module management flows, including module reorder support, strict delete behavior, and in-modal access refinements.
- Added layout and application configuration centralization for branding, mail, behavior, and manual access surfaces.

### Changed
- Standardized page headers, profile localization, dashboard login display, topbar language handling, and group access UI behavior.
- Extended session idle timeout handling and refined multiple administrative UI surfaces.

## [1.0.0] - 2026-02-07

### Added
- Established the locked base platform with core login, layout, session handling, theme support, translation support, user foundation, and administrative scaffolding.
- Added early user, group, role, and access-management capabilities.

### Changed
- Migrated identity handling from `f_groupKod` toward `f_groupID`-based access flow.
- Introduced hardening work for group management UI gating and non-blocking audit coverage during base platform stabilization.

### Security
- Locked the hardened base platform as the first stable baseline for subsequent feature work.

## [Pre-1.0 Foundation] - 2025-07-14

### Added
- Established the original project structure, login foundation, layout framework, theme settings, translation groundwork, and early administrative pages.
- Added early system settings, database configuration, email configuration, dashboard loading improvements, group/menu access work, and initial user management capabilities.

### Changed
- Iteratively refined UI layout, dashboard loading behavior, sidebar/menu behavior, and AJAX-based page interactions during the early build phase.
