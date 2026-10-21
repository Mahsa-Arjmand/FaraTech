/**
 * FaraTech — DOM Binder (Bilingual)
 * عناصر دارای data-ft-render را با محتوای دوزبانه پر می‌کند.
 * با رویداد ft:langchange دوباره اجرا می‌شود.
 */
(function () {
  'use strict';

  const F = window.FARATECH;
  if (!F) {
    return;
  }

  // ─── آیکن‌ها ─────────────────────────────────────────────────
  const icons = {
    monitor: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>',
    code: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>',
    brain: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 1.98-3A2.5 2.5 0 0 1 9.5 2z"/><path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-1.98-3A2.5 2.5 0 0 0 14.5 2z"/></svg>',
    bolt: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>',
  };

  // ─── رندر سرویس‌ها (home — 3 کارت) ──────────────────────────
  function renderServicesHome(container) {
    const lang = F.currentLang;
    const list = F.services.slice(0, 3);
    container.innerHTML = list.map(function (s, i) {
      const title = F.pick(s, 'title');
      const desc = F.pick(s, 'description');
      const items = (lang === 'fa' && s.list_fa) ? s.list_fa : s.list;
      return `
        <article class="service-card">
          <div class="service-icon">${icons[s.icon] || icons.monitor}</div>
          <h3>${title}</h3>
          <p>${desc}</p>
          <ul class="service-list">
            ${items.map(function (it) { return `<li>${it}</li>`; }).join('')}
          </ul>
          <a href="services.html" class="service-link">
            ${lang === 'fa' ? 'بیشتر بدانید' : 'Learn more'}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </a>
        </article>
      `;
    }).join('');
  }

  // ─── رندر سرویس‌ها (services.html — همه) ─────────────────────
  function renderServicesDetailed(container) {
    const lang = F.currentLang;
    container.innerHTML = F.services.map(function (s, i) {
      const title = F.pick(s, 'title');
      const desc = F.pick(s, 'description');
      const items = (lang === 'fa' && s.list_fa) ? s.list_fa : s.list;
      return `
        <article class="service-detail" style="background: white; border: 1px solid #e5e7eb; border-radius: 16px; padding: 36px 30px; display: flex; flex-direction: column;">
          <div class="card-3d-inner" style="padding: 0; height: 100%;">
            <div class="service-detail-icon" style="width: 52px; height: 52px; border-radius: 12px; background: #0b1f3a; color: white; display: flex; align-items: center; justify-content: center; margin-bottom: 22px;">${icons[s.icon] || icons.monitor}</div>
            <h3 style="font-size: 1.1rem; color: #0b1f3a; margin-bottom: 10px; font-weight: 800;">${title}</h3>
            <p style="color: #4a5568; font-size: 0.92rem; line-height: 1.85; margin-bottom: 22px;">${desc}</p>
            <ul class="service-detail-list" style="padding-top: 20px; border-top: 1px solid #f3f4f6; display: flex; flex-direction: column; gap: 10px; margin-top: auto;">
              ${items.map(function (it) { return `<li style="font-size: 0.86rem; color: #4a5568; padding-left: 18px; position: relative;">${it}</li>`; }).join('')}
            </ul>
          </div>
        </article>
      `;
    }).join('');
  }

  // ─── رندر شبکه اجتماعی فوتر ─────────────────────────────────
  function renderSocials(container) {
    const links = [];
    if (F.instagram) {
      links.push({
        href: F.instagram,
        label: 'Instagram',
        icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>',
      });
    }
    if (F.linkedin) {
      links.push({
        href: F.linkedin,
        label: 'LinkedIn',
        icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-4 0v7h-4v-7a6 6 0 016-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>',
      });
    }
    if (F.email) {
      links.push({
        href: 'mailto:' + F.email,
        label: 'Email',
        icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="M22 6l-10 7L2 6"/></svg>',
      });
    }

    container.innerHTML = links.map(function (l) {
      return `<a href="${l.href}" target="_blank" rel="noopener noreferrer" aria-label="${l.label}" class="social-link">${l.icon}</a>`;
    }).join('');
  }

  // ─── Dispatcher ─────────────────────────────────────────────
  function renderAll() {
    document.querySelectorAll('[data-ft-render]').forEach(function (container) {
      const key = container.getAttribute('data-ft-render');
      const variant = container.getAttribute('data-ft-variant') || '';

      switch (key) {
        case 'services-home':
          renderServicesHome(container);
          break;
        case 'services':
          if (variant === 'detailed') renderServicesDetailed(container);
          else renderServicesHome(container);
          break;
        case 'socials':
          renderSocials(container);
          break;
        case 'projects':
          // projects جداگانه از API می‌آید (در inline script صفحه)
          break;
      }
    });

    // بعد از رندر، انیمیشن‌های reveal را دوباره راه‌اندازی کن
    if (window.FaraTechReveal && typeof window.FaraTechReveal.refresh === 'function') {
      window.FaraTechReveal.refresh();
    }
  }

  // اجرای اولیه - منتظر i18n و DOMContentLoaded
  function delayedRender() {
    // مطمئن شویم i18n لود شده است
    if (window.FT_I18N) {
      setTimeout(renderAll, 100); // کمی تاخیر برای اطمینان از مقداردهی اولیه
    } else {
      setTimeout(delayedRender, 50);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', delayedRender);
  } else {
    delayedRender();
  }

  // اجرای مجدد پس از تغییر زبان
  document.addEventListener('ft:langchange', function () {
    renderAll();
  });
})();