# Manual Tracking: test findings and fixes (2026-10-29)

How it was tested: every manual-tracking page was loaded as all 58 seeded accounts (every role). Each flow was then run in the browser as the role that owns it: titles, opening stock, dispatch, scan and receive, discrepancies, sort and scan, misroutes, school enrolment, register, issue and return, teacher guides, visits, reports and CSV exports, and the field app. Tables were measured at a 1440px screen width in light and dark mode.

## Errors (crashes and wrong messages)

| # | Where | Problem | Fix |
|---|-------|---------|-----|
| 1 | Titles, Stock, Discrepancies | Typing in the table's search box threw *"Public property [$search] not found"* | Search added to all three (title, grade or subject; location or title; shipment reference or title) |
| 2 | Stock, Discrepancies, Schools | Changing "Show N entries" threw *"Public property [$recordes] not found"* | Page size added. `x-form.table` now hides search and page size on any component that lacks them, so the crash cannot come back in other modules |
| 3 | Every form using `x-form.select` | The error toast showed the raw field key (`error: region_id`, `codes.student.payload`) | The toast now shows the real message ("The region field is required.") |
| 4 | Shipments list | "1 open" counted titles with problems rather than discrepancies (2 open showed as 1) | Now counts the discrepancies themselves |
| 5 | Dark mode, whole app | `body.dark` never matched the `dark:` variant, so text without its own dark colour stayed dark on a dark background (names hard to see) | Added a `body.dark` text and background rule |
| 6 | Reports, dark mode | The KPI numbers (`text-slate-900`) were invisible, and the chart axis and legend text was faint | Dark colours added; charts use readable label and grid colours |

## Workflow problems

| # | Where | Problem | Fix |
|---|-------|---------|-----|
| 7 | Discrepancies | The sender or receiver was shown a Resolve form, and submitting it always failed (they may not close their own hand-off) | They now see "Awaiting review by someone other than the sender and receiver" |
| 8 | Misroutes | The scanner was shown a Close form for their own alert, which always failed | They now see a short note instead |
| 9 | New Shipment | A woreda or zone officer with only one possible source still had to choose it, and forgetting it failed with only a small inline error | The single source is selected automatically |
| 10 | Titles | The trash button deleted a title at once, with no confirmation | A confirmation is now asked first |
| 11 | Record visit | Saving an untouched form recorded "no student could show a book" (0%) | A confirmation appears when nothing was counted or ticked |
| 12 | Shipments, Schools | Two search boxes were bound to the same field (duplicate `id="search"`) | The duplicate header search box was removed |
| 13 | Shipment and visit pages | There was no way back to the list | "← All shipments" and "← All visits" links added |

## Table layout (horizontal scrolling and View buttons)

At 1440px these tables used to scroll sideways: Shipments (1541px wide in a 1113px area), Discrepancies (1560px), Stock (1204px), School coverage (1278px) and New Shipment (1222px). All of them now fit.

- New `cell-wrap` (wraps long names) and `th-wrap` (two-line headings) utilities.
- New `<x-ui.clamp>`: text stops after 3 lines. A **View all** button opens the full text, and it only appears when the text is cut off. Used for QR codes, shipment titles, From → To, misroute details and resolution notes.
- New `<x-ui.view-modal>` (an eye button that opens a details dialog): on Titles, it shows the full title details.
- New `<x-action.view-link>` (an eye button for each row) on Shipments, Schools, Visits, Discrepancies and Misroutes.
- Shipments: "Reference" and "Dispatched" are merged into one column. Discrepancies: the resolve form is stacked so it fits.

## Noted, not changed

- `bole.woreda.01@tracktrace.test` has both **Woreda Officer** and **School User** roles, so it is treated as a school: it cannot dispatch or sort. Check whether this is intended in the seed data.
- Opening stock can be recorded at a school, not only at a warehouse.
- `books/add?edit=<unknown id>` silently opens an empty "Add" form.
- For national users, New Shipment lists titles from every region, even when the source is a regional bureau.

## Tests

- New: `tests/Feature/ManualTrackingTableControlsTest.php` (5 tests).
- Full suite: 136 passed, 6 skipped, 0 failed.
