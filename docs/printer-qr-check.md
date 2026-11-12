# Printer QR images: check against the imported data (2026-11-12)

The printer sent, next to the 13 `Printer_Data_Requirement-*.xlsx` workbooks, two folders of QR images:

- `Books/<region>/<print code>.png`: 2,019 book QRs, one per title and edition, named by the code printed under the QR.
- `Package/<region>/<zone>/<zone code>.png`: 113 carton labels, one per zone (for example `ORM-Z-1003`).

Each image was decoded and compared with what the importer stored (`manual_book_codes` for books, `manual_book_packages.package_qr_text` for cartons). The app reads the QR text itself, so the images are not stored in the app; this check is what they are for.

## Result

| | Images | Match | Problem |
|---|---|---|---|
| Book QRs | 2,019 | 2,012 | 7 (below) |
| Carton labels | 113 | 112 | 1 could not be decoded by the checker (`ORM-Z-1017`, Qeellam Wallaggaa); it looks like a valid QR |

Every image name matches a code in the database, and every zone in the database has a carton label.

## Problems to raise with the printer

1. **Wrong QR on `25110-015-1-1711`.** The code belongs to *Oromia Grade 1 English, Teacher guide*, but the QR in the image reads *Oromia Grade 6 Mathematics Teacher guide*. If the books were printed with that image, scanning an English Grade 1 teacher guide will register it as a Mathematics Grade 6 guide. Ask the printer which is right and whether that batch is already printed.
2. **Missing space in six South Ethiopia QRs.** `25102-089-1-0298` to `25102-094-1-0303` (Environmental Science Teacher guide, Grades 1 to 6) read `…Environmental ScienceTeacher guide`. Scanning now ignores spacing as a last resort, so these still match the right title, both online and in the field app.
3. **No image for five Oromia codes:** `25110-032-2-1744`, `25110-033-2-1746`, `25110-033-1-1747`, `25110-034-2-1748`, `25110-034-1-1749`. They are in the workbook but have no QR image.
