/**
 * FaraTech — Content Sync
 * همگام‌سازی محتوای داینامیک از API با پشتیبانی دوزبانه.
 *
 * API عمومی:
 *   FaraTechSync.fetchPageContent(page, section?)
 *   FaraTechSync.fetchTeamPositions()
 *   FaraTechSync.submitJobApplication(data)
 *   FaraTechSync.applyContentToDOM(container, mapping)
 *
 * با تغییر زبان، از localStorage می‌خوانَد و از API دوباره می‌گیرد.
 */
(function () {
  'use strict';

  // Detect environment and set API base URL
  function getApiBaseUrl() {
    // Relative requests are served by the site host; the dev server proxies /api to PHP.
    return window.FARATECH_API_BASE || '/api';
  }

  const API_BASE = getApiBaseUrl();

  function currentLang() {
    return (window.FT_I18N && window.FT_I18N.current) || 'fa';
  }

  function buildUrl(path, params) {
    const qs = Object.keys(params || {})
      .filter(function (k) { return params[k] != null && params[k] !== ''; })
      .map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]); })
      .join('&');
    return API_BASE + path + (qs ? ('?' + qs) : '');
  }

  // ─────────────────────────────────────────────────────────────
  // Content (page-specific)
  // ─────────────────────────────────────────────────────────────
  async function fetchPageContent(page, section) {
    try {
      const url = buildUrl('/public_content.php', {
        page: page,
        section: section || '',
        lang: currentLang(),
      });
      const r = await fetch(url);
      const data = await r.json();
      return (data && data.success && data.data) ? data.data : {};
    } catch (e) {
      console.warn('[FaraTechSync] fetchPageContent error', e);
      return {};
    }
  }

  // ─────────────────────────────────────────────────────────────
  // Team positions
  // ─────────────────────────────────────────────────────────────
  async function fetchTeamPositions() {
    try {
      const url = buildUrl('/public_team_positions.php', { lang: currentLang() });
      const r = await fetch(url);
      const data = await r.json();
      return (data && data.success && Array.isArray(data.data)) ? data.data : [];
    } catch (e) {
      console.warn('[FaraTechSync] fetchTeamPositions error', e);
      return [];
    }
  }

  // ─────────────────────────────────────────────────────────────
  // Job application
  // ─────────────────────────────────────────────────────────────
  async function submitJobApplication(payload) {
    try {
      const fd = new FormData();
      Object.keys(payload || {}).forEach(function (k) {
        if (payload[k] != null && payload[k] !== '') fd.append(k, payload[k]);
      });
      fd.append('lang', currentLang());

      const r = await fetch(API_BASE + '/job_application.php', {
        method: 'POST',
        body: fd,
      });
      const data = await r.json();
      return !!(data && data.success);
    } catch (e) {
      console.warn('[FaraTechSync] submitJobApplication error', e);
      return false;
    }
  }

  // ─────────────────────────────────────────────────────────────
  // Helper: پر کردن DOM از محتوای content
  //   data-ft-content="section.key"
  // ─────────────────────────────────────────────────────────────
  async function applyContentToDOM(root, page) {
    root = root || document;
    page = page || document.body.getAttribute('data-page') || '';
    if (!page) return;

    const content = await fetchPageContent(page);
    root.querySelectorAll('[data-ft-content]').forEach(function (el) {
      const spec = el.getAttribute('data-ft-content'); // "hero.title"
      const parts = spec.split('.');
      const section = parts[0];
      const key = parts.slice(1).join('.');
      if (content[section] && content[section][key] != null) {
        const val = content[section][key];
        // اگر مقدار HTML دارد، از innerHTML استفاده کن
        if (/<[a-z][\s\S]*>/i.test(val)) el.innerHTML = val;
        else el.textContent = val;
      }
    });
  }

  // ─────────────────────────────────────────────────────────────
  // Public API
  // ─────────────────────────────────────────────────────────────
  window.FaraTechSync = {
    fetchPageContent: fetchPageContent,
    fetchTeamPositions: fetchTeamPositions,
    submitJobApplication: submitJobApplication,
    applyContentToDOM: applyContentToDOM,
    get currentLang() { return currentLang(); },
  };

  // اگر صفحهای data-ft-content دارد، بعد از لود اعمال کن
  document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelector('[data-ft-content]')) {
      applyContentToDOM(document);
    }
  });

  // با تغییر زبان هم دوباره اعمال کن
  document.addEventListener('ft:langchange', function () {
    if (document.querySelector('[data-ft-content]')) {
      applyContentToDOM(document);
    }
  });
})();