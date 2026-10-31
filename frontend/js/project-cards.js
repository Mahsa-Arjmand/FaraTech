(function () {
  'use strict';

  function escapeHtml(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function mediaUrl(value) {
    const url = String(value == null ? '' : value).trim();
    if (!url) return '';

    try {
      const parsed = new URL(url, window.location.href);
      if ((parsed.hostname === 'localhost' || parsed.hostname === '127.0.0.1') && /\/uploads\.php$/i.test(parsed.pathname)) {
        return '/uploads.php' + parsed.search;
      }
      if (parsed.origin === window.location.origin && /^\/uploads\/projects\/[a-z0-9_.-]+$/i.test(parsed.pathname)) {
        return '/uploads.php?file=' + encodeURIComponent(parsed.pathname.replace(/^\//, ''));
      }
      if (parsed.protocol === 'http:' || parsed.protocol === 'https:') return parsed.href;
      return '';
    } catch (error) {
      return '';
    }
  }

  function categoryClass(category) {
    const value = String(category || '').toLowerCase();
    if (/e-commerce|فروشگاه/.test(value)) return 'work-card-category--gold';
    if (/ai|هوش مصنوعی/.test(value)) return 'work-card-category--purple';
    if (/platform|پلتفرم|saas/.test(value)) return 'work-card-category--blue';
    if (/corporate|شرکتی|website|وب.?سایت/.test(value)) return 'work-card-category--green';
    return 'work-card-category--navy';
  }

  function render(project, lang) {
    const language = lang === 'en' ? 'en' : 'fa';
    const id = encodeURIComponent(String(project.id == null ? '' : project.id));
    const title = escapeHtml(project.title || (language === 'fa' ? 'پروژه' : 'Project'));
    const description = escapeHtml(project.description || (language === 'fa'
      ? 'برای آشنایی بیشتر با این پروژه، جزئیات را ببینید.'
      : 'Explore the project to learn more.'));
    const category = String(project.category || (language === 'fa' ? 'پروژه' : 'Project'));
    const categoryClassName = categoryClass(category);
    const image = mediaUrl(project.image_url);
    const alt = escapeHtml(project.title || (language === 'fa' ? 'تصویر پروژه' : 'Project image'));
    const detailsLabel = language === 'fa' ? 'مشاهده جزئیات' : 'View project details';
    const href = 'project-detail.html?id=' + id + '&lang=' + language;
    const media = image
      ? '<img class="work-card-image" src="' + escapeHtml(image) + '" alt="' + alt + '" loading="lazy" decoding="async">'
      : '<span class="work-card-placeholder" aria-hidden="true"><span>F</span></span>';

    return '<a class="work-card" href="' + href + '" aria-label="' + escapeHtml(detailsLabel + ': ' + (project.title || '')) + '">' +
      '<span class="work-card-media">' + media +
        '<span class="work-card-category ' + categoryClassName + '">' + escapeHtml(category) + '</span>' +
        '<span class="work-card-media-shade" aria-hidden="true"></span>' +
      '</span>' +
      '<span class="work-card-body">' +
        '<span class="work-card-kicker">' + escapeHtml(project.client || (language === 'fa' ? 'پروژه منتخب' : 'Selected project')) + '</span>' +
        '<span class="work-card-title">' + title + '</span>' +
        '<span class="work-card-description">' + description + '</span>' +
        '<span class="work-card-footer">' +
          '<span>' + escapeHtml(detailsLabel) + '</span>' +
          '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>' +
        '</span>' +
      '</span>' +
    '</a>';
  }

  window.FaraTechProjectCards = {
    escapeHtml: escapeHtml,
    mediaUrl: mediaUrl,
    render: render,
  };
})();
