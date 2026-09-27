# Baran catalog categorization design

## Goal and scope

Correct the category of every currently visible Baran product on felfelisaveh.ir, and keep later Baran refreshes from recreating the same errors. The observed live snapshot contains 380 visible products in 14 categories. This work changes category assignment and selected category labels only; it must not change product names, prices, stock, photos, order flow, or Baran identifiers.

## Evidence and root cause

- The live menu has 380 visible products. Confirmed errors include olive oil under pickles, pickle spice under pickles, chicken-and-mushroom ham under vegetables, sumac under tea, fresh green beans under legumes, and fruit leather under pickles.
- `CategoryClassifier` uses ordered substring rules. The pickles rule includes `زیتون` and `ترشی` and precedes the oil and spice rules, so composite names are captured by the wrong category.
- `CatalogSynchronizer` assigns a category only on product creation. Refreshing an existing product leaves its category unchanged.
- The existing enrichment fixture contains 371 unique SKUs, not the 380 visible products, and includes demonstrably wrong labels. It is a hint for research, not a trusted assignment source.
- Felfeli's public WooCommerce catalog exposes 23 categories including subcategories and an uncategorized bucket. Its taxonomy can inform review, but cannot safely replace the store's flat customer-facing taxonomy or uniquely match every Baran SKU.

## Customer-facing taxonomy

Keep the 14 existing stable slugs and ordering so menu links continue to work. Rename only misleading labels: `nabat` to «نبات و تنقلات», `amade` to «غذا و مواد آماده», and `rob-torshi` to «ترشی، شور و چاشنی». The other category names remain unchanged. Use `rob-torshi` for pickles, olives, preserves, pastes, vinegar and acidic liquid condiments; `adviye` for dry spices and seasoning mixes; `roghan` for all edible oils and tahini. Fruit leather and fruit snacks belong in `nabat`, not pickles. If manual review reveals a coherent product group that genuinely does not fit these 14, stop and request a taxonomy decision before adding a new category.

## Assignment source and refresh behavior

1. Capture a fresh read-only snapshot of all 380 visible live products by Baran SKU, name, and current category. Review every row against the product name, the Felfeli category where an unambiguous product match exists, and the proposed customer-facing taxonomy. Do not infer a match from a photo alone.
2. Save an explicit reviewed `SKU → expected normalized name → category slug` assignment for this snapshot. Require unique SKUs, known slugs, and complete coverage of the snapshot. A name guard prevents an old SKU mapping from silently classifying an unrelated replacement product.
3. Use the reviewed assignment for both newly imported and already existing Baran products. For a new or renamed product without a guarded assignment, use conservative ordered rules for unmistakable product types; otherwise use `sayer` and include it in an operator review report. Do not silently guess among two plausible categories.
4. Do not modify categories of manually created products. Baran product editing remains photo-only, as previously agreed; the admin does not gain a manual override for Baran names, prices, stock, or categories in this change.
5. Add a one-time, preview-first recategorization operation for existing Baran products. Its preview reports old/new category counts, each changed SKU, missing SKUs, unknown slugs, and name-guard failures. Applying it updates only `category_id`. Block application if any live SKU is added or removed, or any guarded product name differs from the reviewed snapshot; review that drift before applying.
6. Before applying on production, save a private recoverable snapshot of `SKU → category_id`. A reverse operation can restore only those assignments if the new menu arrangement proves wrong. Never put this snapshot, credentials, or private paths in a public document root.

## Examples that must be correct

| Product name | Required category |
| --- | --- |
| روغن زیتون با بو ۲ لیتری | `roghan` |
| ادویه ترشی | `adviye` |
| ترشی فلفل | `rob-torshi` |
| ژامبون مرغ و قارچ | `amade` |
| لوبیا سبز تازه | `sabzijat` |
| سماق قهوه‌ای | `adviye` |
| انجیر خشک | `khoshkbar` |
| ترشک آلو جنگلی | `nabat` |

## Verification and release

- Test the named regressions, assignment uniqueness/coverage, unknown SKU/name behavior, and the one-time operation's preview, apply, and rollback with real database records.
- Run the full Laravel test suite. Inspect the preview before production application; if any assignment is questionable, resolve it before applying.
- Deploy the reviewed code and fixture, back up category assignments privately, apply once, then verify menu count remains 380 unless Baran changed independently, category totals add up to the product total, named regressions appear in their intended categories, and a subsequent Baran refresh does not undo them.
- Roll back category assignments from the private snapshot if the live verification fails; do not roll back prices, stock, images, or orders.

## Out of scope

Photo matching and the known 16 Persian-URL photo rejects are separate work. Order submission, payment, and category administration UI changes are also out of scope.
