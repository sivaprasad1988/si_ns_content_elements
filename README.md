# News content elements in the page module (si_ns_content_elements)

Edit the content elements of an EXT:news record in the TYPO3 page module instead of the inline
field "Content elements" of the news record.

## Requirements

- TYPO3 14.3
- EXT:news 14.1+
- Composer mode

## Installation

```bash
composer require woit/si-ns-content-elements
```

No configuration needed.

## Usage

1. Open a saved news record.
2. Click **Edit content in page module** in the doc header.
3. The page module opens on the news folder and shows only the content elements of this news record.
   Content elements created there are added to the news record.
4. **Show all content elements** leaves the news mode.

The news mode is part of the page module URL (`tx_sinscontentelements[news]`), so it survives reloads
and editing or creating content elements. Opening the page via the page tree shows the regular page module.
**Back to news** in the doc header returns to the news record.

## How it works

| Part | Implementation |
|------|----------------|
| Button in the news form | `ModifyButtonBarEvent` |
| Switch news mode, info box | `ModifyPageLayoutContentEvent` |
| Filter page module content | Decorates the EXT:news `ModifyDatabaseQueryForContentEvent` listener |
| Relate new content to the news | `ModifyNewContentElementWizardItemsEvent` (`defaultValues`) |

## License

GPL-2.0-or-later
