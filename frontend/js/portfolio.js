(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('portfolioContainer');
    if (!container || !window.FaraTechProjectCards) return;

    const apiBase = window.FARATECH_API_BASE || '/api';
    let requestNumber = 0;

    function currentLang() {
      return (window.FT_I18N && window.FT_I18N.current) || 'fa';
    }

    function messageFor(key, fallback) {
      return (window.FT_I18N && window.FT_I18N.t(key)) || fallback;
    }

    async function loadProjects() {
      const thisRequest = ++requestNumber;
      const lang = currentLang();
      container.setAttribute('aria-busy', 'true');
      container.innerHTML = '<p class="portfolio-empty">' +
        (lang === 'fa' ? 'در حال بارگذاری نمونه‌کارها…' : 'Loading projects…') + '</p>';

      try {
        const response = await fetch(apiBase + '/public_projects.php?lang=' + encodeURIComponent(lang) + '&limit=100', {
          headers: { Accept: 'application/json' },
          cache: 'no-store',
        });
        if (!response.ok) throw new Error('Project API returned ' + response.status);
        const payload = await response.json();
        if (thisRequest !== requestNumber) return;

        const projects = payload && payload.success && Array.isArray(payload.data) ? payload.data : [];
        if (!projects.length) {
          container.innerHTML = '<p class="portfolio-empty">' +
            messageFor('portfolio.noProjects', lang === 'fa' ? 'هنوز پروژه‌ای ثبت نشده است.' : 'No projects yet.') +
            '</p>';
          return;
        }

        container.innerHTML = projects.map(function (project) {
          return window.FaraTechProjectCards.render(project, lang);
        }).join('');
        if (window.FaraTechReveal && typeof window.FaraTechReveal.refresh === 'function') {
          window.FaraTechReveal.refresh();
        }
      } catch (error) {
        if (thisRequest !== requestNumber) return;
        console.error('[FaraTech] Could not load portfolio:', error);
        container.innerHTML = '<p class="portfolio-empty">' +
          (lang === 'fa' ? 'نمونه‌کارها در حال حاضر در دسترس نیستند.' : 'Projects are temporarily unavailable.') +
          '</p>';
      } finally {
        if (thisRequest === requestNumber) container.removeAttribute('aria-busy');
      }
    }

    loadProjects();
    document.addEventListener('ft:langchange', loadProjects);
  });
})();
