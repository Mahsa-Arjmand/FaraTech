(function () {
  'use strict';

  const apiBase = String(window.FARATECH_API_BASE || '/api').replace(/\/$/, '');

  function pageName() {
    const parts = window.location.pathname.split('/').filter(Boolean);
    let file = parts.length ? parts[parts.length - 1] : 'index';
    file = file.replace(/\.html?$/i, '').toLowerCase();
    return file || 'index';
  }

  function recordView() {
    const body = new URLSearchParams();
    body.set('action', 'track');
    body.set('page', pageName());
    const endpoint = apiBase + '/page_views.php?action=track';

    if (navigator.sendBeacon && navigator.sendBeacon(endpoint, body)) return;
    fetch(endpoint, {
      method: 'POST',
      body: body,
      keepalive: true,
      cache: 'no-store',
      credentials: 'same-origin',
      headers: { Accept: 'application/json' },
    }).catch(function () {
      // Analytics must never interrupt the visitor's page experience.
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', recordView, { once: true });
  } else {
    recordView();
  }
  window.addEventListener('pageshow', function (event) {
    if (event.persisted) recordView();
  });
})();
