# News content elements in the page module (si_ns_content_elements)

Edit the content elements of an EXT:news record in the TYPO3 page module instead of the inline
field "Content elements" of the news record.

## Requirements

- TYPO3 14.3
- EXT:news 14.1+
- PHP 8.2 - 8.5
- Composer mode

## Installation

```bash
composer require woit/si-ns-content-elements
```

Flush the caches afterwards. No database changes are needed: the extension uses the field
`tt_content.tx_news_related_news` of EXT:news.

## Configuration

### Column (colPos) of news content elements

Content elements of a news record must be in a column (colPos) of the backend layout of the news folder.
EXT:news creates them with colPos 0 ("Normal"). If the backend layout of the news folder has no column 0,
the colPos field of these content elements shows `Invalid value ("0")` and the page module shows them
in no column.

Set the column in the page TSconfig of the news folder (or of a page above it):

```
tx_sinscontentelements.colPos = 0
```

| Record | Behaviour |
|--------|-----------|
| New content element in the inline field of the news record | Gets the configured column |
| New content element in the page module (news mode) | Keeps the column in which "+ Content" was clicked |
| Content element with a column which is not part of the backend layout | Gets the configured column in the edit form; stored with the next save |
| Content element with a column of the backend layout | Unchanged |

Notes:

- The configured column must be part of the backend layout of the news folder, otherwise the setting is ignored.
  With the backend layout "None" (default layout) only column 0 exists.
- Without this setting nothing is changed (EXT:news behaviour).
- Allowed / disallowed content types of the column (`allowed.CType`, `disallowed.CType`) still apply in the
  page module and in the edit form of single content elements.

Existing content elements can also be moved directly in the database, e.g. to colPos 0 in news folder 123:

```sql
UPDATE tt_content SET colPos = 0 WHERE tx_news_related_news > 0 AND deleted = 0 AND pid = 123;
```

## Usage

1. Open a saved news record.
2. Click **Edit content in page module** in the doc header (or below the field "Content elements").
3. The page module opens on the news folder and shows only the content elements of this news record.
   Content elements created there are added to the news record.
4. **Back to news** in the doc header or **Edit news record** in the info box returns to the news record.
5. **Show all content elements** leaves the news mode.

The news mode is part of the page module URL (`tx_sinscontentelements[news]`), so it survives reloads,
switching the language or view, and editing or creating content elements. Opening the page via the page tree
shows the regular page module.

The news mode is only active if the news record is stored on the page shown in the page module and is a record
in the default language. Otherwise the parameter is ignored.

Outside the news mode the page module behaves like with EXT:news alone: in news folders (page type "Folder",
"Contains plugin: News") content elements of news records are hidden, unless
`tx_news.showContentElementsInNewsSysFolder = 1` is set in page TSconfig.

Translations: content elements are managed on the news record in the default language. Translated content
elements are shown in news mode, too.

## How it works

| Part | Implementation |
|------|----------------|
| Button in the news form (doc header) | `ModifyButtonBarEvent` (`AddPageModuleButton`) |
| Link below the inline field "Content elements" | TCA `customControls` (`Form\PageModuleControl`); replaces the EXT:news control `ce` (hint for EXT:news_content_elements) |
| Switch news mode, info box | `ModifyPageLayoutContentEvent` (`AddNewsModeHeader`) |
| Button "Back to news" in the page module | `ModifyButtonBarEvent` (`AddBackToNewsButton`) |
| Keep the news mode on page module links | JavaScript module `keep-news-mode.js` |
| Filter page module content | Decorates the EXT:news `ModifyDatabaseQueryForContentEvent` listener (`FilterPageModuleContent`) |
| Relate new content to the news | `ModifyNewContentElementWizardItemsEvent` (`defaultValues`, `RelateNewContentToNews`) |
| Column of news content elements | FormEngine data provider `Form\FormDataProvider\NewsContentColPos` (`tcaDatabaseRecord`) |

## Troubleshooting

| Problem | Solution |
|---------|----------|
| `Invalid value ("0")` in the colPos field of news content elements | Backend layout of the news folder has no column 0: set `tx_sinscontentelements.colPos` (see Configuration) or set the backend layout of the news folder to "None" |
| News content elements are missing in the page module | Their colPos is not part of the backend layout: see Configuration |
| Button "Edit content in page module" is missing | Only shown for saved news records opened alone (not for new records or multi-record editing) |
| Page module shows all content elements instead of the news mode | The news record is not stored on this page or is not in the default language: open the page module via the button of the news record |

## Localization

Backend labels: English and German (`Resources/Private/Language/locallang_be.xlf`, `de.locallang_be.xlf`).

## License

GPL-2.0-or-later
