# Gilas feature inventory and backend scope

Audit date: 2026-10-13. This review compares `main` with the available feature branches and open pull requests. It distinguishes data models from end-to-end workflows.

## Customer-facing capabilities
- Public brand/story/location/experience pages and menu/category/item browsing.
- QR table ordering with server-side price resolution, variants/options, idempotency, order status tracking and public tracking tokens.
- Public pickup/delivery checkout with customer/address creation, server-side total, restaurant-owned active delivery zone and minimum order, delivery fee, pending payment record and delivery-address snapshot.
- Reservation request submission with Persian/Arabic digit normalization, configured opening hours, pending state and staff confirmation.
- Online payment intentionally disabled until a real gateway/session, signed callback/webhook and reconciliation/failure flow are integrated.

## Staff/operations capabilities
- Restaurant role/permission checks; owner/manager access separated from kitchen, waiter, cashier, courier and staff roles.
- Staff order status transitions use the existing domain service and status history.
- Reservation status transitions, table assignment, table capacity and overlap validation.
- Restaurant menu/category/item CRUD, image replacement/removal, variant and option/value CRUD, with restaurant scoping and authorization.
- Courier management, delivery assignment, availability checks, dispatch/pick-up/delivered lifecycle, timestamps, status history and synchronized order transition to `out_for_delivery` and `delivered`.
- Payment records and authorized cashier settlement.

## Scope boundaries (do not pretend these integrations exist)
- No real online gateway is configured; cashier settlement only.
- Delivery zone is a named operating zone, not a GIS boundary or postcode coverage map.
- Reservation requests are pending and confirmed by staff; there is not yet a customer-facing availability calendar/automatic table allocation.
- This branch exposes backend routes/APIs only. Blade/CSS/JS UI/UX remains for the separate redesign.
- Older PRs #4, #6 and #7 are diverged/stale and include legacy presentation/tree-GSAP work; useful logic was ported selectively, so do not merge those branches wholesale.

## Remaining production backlog
1. Restaurant profile/settings and opening-hours/holiday management screens and services, including timezone-aware cutoffs and scheduled pause/resume.
2. Reservation list/calendar/search screens and outbound confirmation/cancellation messages.
3. Online payment provider integration, refunds, receipts and reconciliation.
4. Inventory/stock, coupons/loyalty, customer reviews and analytics exports if required by product scope.
5. Operational safeguards: per-endpoint rate limiting, spam/abuse handling, durable queued notifications, backup/restore exercises, observability/error reporting, and production smoke tests.
6. Deployment and migration verification on the target environment before release.
