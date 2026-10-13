# Gilas restaurant feature audit and backend scope

Audit date: 2026-10-13. This review compares the current main tree with open feature branches and pull requests. It distinguishes existing data models from workflows that are actually callable and tested.

## Workflows already present on main
- Public brand/story/location/experience pages and menu/category/item browsing.
- QR-token table menu and dine-in order submission.
- Server-side menu pricing, variants/options validation, order idempotency, public tracking tokens and status polling.
- Restaurant settings, weekly opening hours, tables and QR tokens.
- Customer and address models, reservations, couriers, delivery zones, payments and delivery records.
- Order status domain service/history, role permissions and new-order staff notifications.
- Payment record creation and cashier settlement authorization.

## Workflows added by this branch
- Public pickup checkout with server-side pricing, customer persistence, pending payment creation and idempotency.
- Public delivery checkout with active restaurant-owned zone validation, zone minimum, server-calculated delivery fee, order-to-zone relation and delivery address snapshot.
- Reservation submission with customer persistence, pending status, estimated 90-minute duration, configured opening-hours checks and Persian/Arabic digit normalization.
- Role-protected order status endpoint using the existing transition service and audit history.
- Role-protected reservation status endpoint with explicit lifecycle transitions, table capacity checks and overlapping-booking detection.
- Regression tests for checkout math, idempotency, cross-restaurant IDs, reservation hours/settings, role boundaries, transition guards and rollback/no-orphan records.

## Integrations intentionally not faked
- Online payments are not enabled by checkout until a payment provider/session, signed callback/webhook and failure/reconciliation path are configured.
- Delivery zones are named operating zones, not GIS polygons or postal coverage rules. The selected zone must be active and belong to the restaurant.
- Reservation requests begin as pending, for staff confirmation. A calendar UI and automatic seating allocation are not represented as complete.
- This change does not redesign Blade, CSS or JavaScript UI; UI/UX is intentionally left for the dedicated redesign.

## Remaining production capabilities to build
1. Admin CRUD for menu categories/items/variants/options with authorization and image validation.
2. Courier assignment and delivery lifecycle actions (assigned, dispatched, picked up, delivered), scoped to each restaurant.
3. Reservation calendar/list/filter UI and staff operational surfaces.
4. Payment provider integration, reconciliation, receipts and refunds as required.
5. Restaurant settings/hours management, holidays, timezone-aware cutoffs and pause/resume ordering.
6. Public endpoint rate limits/abuse controls, queued notification reliability, backups, error reporting and production smoke tests.
7. Optional: loyalty/club points, coupons, reviews, stock, analytics exports and delivery-provider integrations.

## Branch disposition
Older feature branches are divergent from main. Do not merge PRs #4, #6 or #7 wholesale: they contain legacy presentation changes and portions of pre-cleanup GSAP/tree assets. The useful backend changes have been ported selectively; retain the tree-free main branch as the source of truth and check path-level diffs before any further integration.
