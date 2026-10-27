/**
 * Page module in news mode: links to the same page in the page module (language selector, view mode, ...)
 * are built by TYPO3 without the news parameter. Add it on click, so the news mode is kept.
 */
const parameter = 'tx_sinscontentelements[news]';
const current = new URL(window.location.href);
const newsUid = current.searchParams.get(parameter);

if (newsUid) {
  document.addEventListener('click', (event) => {
    const link = event.composedPath().find((element) => element instanceof HTMLAnchorElement && element.href);
    // "Show all content elements" leaves the news mode
    if (!link || link.dataset.siNsLeave) {
      return;
    }
    const target = new URL(link.href, window.location.href);
    if (target.origin !== current.origin
      || target.pathname !== current.pathname
      || target.searchParams.get('id') !== current.searchParams.get('id')
      || target.searchParams.has(parameter)
      || link.dataset.siNsLeave
    ) {
      return;
    }
    target.searchParams.set(parameter, newsUid);
    link.href = target.toString();
  }, true);
}
