(function () {
  'use strict';

  const apiBase = window.FARATECH_API_BASE || '/api';
  const params = new URLSearchParams(window.location.search);
  const projectId = params.get('id');
  const requestedLang = params.get('lang');
  let currentProject = null;
  let requestNumber = 0;
  let viewerImages = [];
  let viewerIndex = 0;
  let focusBeforeViewer = null;

  const el = function (id) { return document.getElementById(id); };

  function currentLang() {
    return (window.FT_I18N && window.FT_I18N.current) || (requestedLang === 'en' ? 'en' : 'fa');
  }

  function translate(key, fallback) {
    return (window.FT_I18N && window.FT_I18N.t(key)) || fallback || key;
  }

  function normalizeMediaUrl(value) {
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
      if (parsed.origin === window.location.origin) return parsed.href;
    } catch (error) {
      return '';
    }
    return '';
  }

  function safeExternalUrl(value) {
    const url = String(value == null ? '' : value).trim();
    if (!url) return '';
    try {
      const parsed = new URL(url, window.location.href);
      return parsed.protocol === 'http:' || parsed.protocol === 'https:' ? parsed.href : '';
    } catch (error) {
      return '';
    }
  }

  function ensureMeta(selector, attribute, value) {
    let node = document.head.querySelector(selector);
    if (!node) {
      node = document.createElement('meta');
      const match = selector.match(/\[(name|property)="([^"]+)"\]/);
      if (match) node.setAttribute(match[1], match[2]);
      document.head.appendChild(node);
    }
    node.setAttribute(attribute, value);
  }

  function ensureCanonical(url) {
    let node = document.head.querySelector('link[rel="canonical"]');
    if (!node) {
      node = document.createElement('link');
      node.rel = 'canonical';
      document.head.appendChild(node);
    }
    node.href = url;
  }

  function updateStructuredData(project, lang, canonical, description, image) {
    let node = document.getElementById('projectStructuredData');
    if (!node) {
      node = document.createElement('script');
      node.type = 'application/ld+json';
      node.id = 'projectStructuredData';
      document.head.appendChild(node);
    }

    const keywords = [project.category].concat(project.technologies || []).filter(Boolean).join(', ');
    const graph = {
      '@context': 'https://schema.org',
      '@graph': [
        {
          '@type': 'CreativeWork',
          '@id': canonical + '#project',
          name: project.title,
          description: description,
          url: canonical,
          image: image,
          creator: { '@type': 'Organization', name: 'FaraTech', url: 'https://faratechlab.ir/' },
          inLanguage: lang,
          dateCreated: project.created_at || undefined,
          keywords: keywords || undefined,
          about: project.category ? { '@type': 'Thing', name: project.category } : undefined,
        },
        {
          '@type': 'BreadcrumbList',
          itemListElement: [
            { '@type': 'ListItem', position: 1, name: translate('nav.home', 'Home'), item: 'https://faratechlab.ir/' },
            { '@type': 'ListItem', position: 2, name: translate('nav.work', 'Portfolio'), item: 'https://faratechlab.ir/portfolio.html' },
            { '@type': 'ListItem', position: 3, name: project.title, item: canonical },
          ],
        },
      ],
    };

    node.textContent = JSON.stringify(graph);
  }

  function updateSeo(project, lang) {
    const fallbackTitle = lang === 'fa' ? 'نمونه‌کار FaraTech' : 'FaraTech project';
    const title = (project.title || fallbackTitle) + ' | FaraTech';
    const rawDescription = project.description || project.details || (lang === 'fa'
      ? 'جزئیات پروژه، فناوری‌های استفاده‌شده و تصاویر نمونه‌کار FaraTech.'
      : 'Project details, technologies and selected work by FaraTech.');
    const description = String(rawDescription).replace(/\s+/g, ' ').trim().slice(0, 200);
    const canonical = 'https://faratechlab.ir/project-detail.html?id=' + encodeURIComponent(String(project.id || projectId));
    const imageUrl = normalizeMediaUrl(project.image_url);
    const socialImage = imageUrl ? new URL(imageUrl, window.location.href).href : 'https://faratechlab.ir/og-image.png';
    const categoryKeywords = [project.category].concat(project.technologies || []).filter(Boolean).join(', ');

    document.title = title;
    document.documentElement.setAttribute('lang', lang);
    document.documentElement.setAttribute('dir', lang === 'fa' ? 'rtl' : 'ltr');
    ensureMeta('meta[name="description"]', 'content', description);
    ensureMeta('meta[name="title"]', 'content', title);
    ensureMeta('meta[name="robots"]', 'content', 'index, follow, max-image-preview:large');
    ensureMeta('meta[name="keywords"]', 'content', categoryKeywords || 'FaraTech, portfolio, web development');
    ensureMeta('meta[property="og:type"]', 'content', 'article');
    ensureMeta('meta[property="og:url"]', 'content', canonical);
    ensureMeta('meta[property="og:title"]', 'content', title);
    ensureMeta('meta[property="og:description"]', 'content', description);
    ensureMeta('meta[property="og:image"]', 'content', socialImage);
    ensureMeta('meta[property="og:image:alt"]', 'content', project.title || fallbackTitle);
    ensureMeta('meta[property="og:locale"]', 'content', lang === 'fa' ? 'fa_IR' : 'en_US');
    ensureMeta('meta[name="twitter:card"]', 'content', 'summary_large_image');
    ensureMeta('meta[name="twitter:url"]', 'content', canonical);
    ensureMeta('meta[name="twitter:title"]', 'content', title);
    ensureMeta('meta[name="twitter:description"]', 'content', description);
    ensureMeta('meta[name="twitter:image"]', 'content', socialImage);
    ensureMeta('meta[name="twitter:image:alt"]', 'content', project.title || fallbackTitle);
    ensureCanonical(canonical);
    updateStructuredData(project, lang, canonical, description, socialImage);
  }

  function renderProject(project, lang) {
    currentProject = project;
    const title = project.title || '';
    el('projectHeaderTitle').textContent = title;
    el('projectHeaderSubtitle').textContent = project.description || '';
    el('breadcrumbTitle').textContent = title;
    el('projectDescription').textContent = project.description || translate('project.noDescription', 'No description available.');
    el('projectClient').textContent = project.client || '—';
    el('projectCategory').textContent = project.category || '—';
    el('projectDetails').textContent = project.details || translate('project.noDetails', 'No additional details available.');

    const technologyContainer = el('projectTechnologies');
    technologyContainer.replaceChildren();
    (Array.isArray(project.technologies) ? project.technologies : []).forEach(function (technology) {
      const chip = document.createElement('span');
      chip.className = 'tech-tag';
      chip.textContent = technology;
      technologyContainer.appendChild(chip);
    });

    const videoContainer = el('videoContainer');
    const video = el('projectVideo');
    const videoUrl = normalizeMediaUrl(project.video_url);
    video.pause();
    video.removeAttribute('src');
    video.load();
    if (videoUrl) {
      video.src = videoUrl;
      video.load();
      videoContainer.hidden = false;
    } else {
      videoContainer.hidden = true;
    }

    const imageContainer = el('imageContainer');
    const image = el('projectImage');
    const imageUrl = normalizeMediaUrl(project.image_url);
    if (imageUrl) {
      image.src = imageUrl;
      image.alt = (lang === 'fa' ? 'تصویر پروژه: ' : 'Project preview: ') + title;
      imageContainer.hidden = false;
      image.onclick = function () { openViewer(imageUrl, image.alt, image); };
    } else {
      image.removeAttribute('src');
      imageContainer.hidden = true;
      image.onclick = null;
    }

    const projectUrl = safeExternalUrl(project.project_url);
    const liveLink = el('projectUrl');
    if (projectUrl) {
      liveLink.href = projectUrl;
      liveLink.hidden = false;
    } else {
      liveLink.removeAttribute('href');
      liveLink.hidden = true;
    }

    updateSeo(project, lang);
    if (window.FaraTechReveal && typeof window.FaraTechReveal.refresh === 'function') {
      window.FaraTechReveal.refresh();
    }
  }

  function setNotFound(message, lang) {
    const title = translate('project.notFound', lang === 'fa' ? 'پروژه پیدا نشد' : 'Project not found');
    el('projectHeaderTitle').textContent = title;
    el('projectHeaderSubtitle').textContent = message || translate('project.notFoundDesc', 'Please return to the portfolio.');
    el('projectDescription').textContent = message || translate('project.notFoundDesc', 'Please return to the portfolio.');
    document.title = title + ' | FaraTech';
    ensureMeta('meta[name="robots"]', 'content', 'noindex, follow');
    ensureCanonical('https://faratechlab.ir/portfolio.html');
  }

  async function loadGallery(project, lang, thisRequest) {
    const galleryContainer = el('galleryContainer');
    const grid = el('galleryGrid');
    const noMediaContainer = el('noMediaContainer');
    const mainImageUrl = normalizeMediaUrl(project.image_url);
    const galleryUrl = apiBase + '/project_images.php?project_id=' + encodeURIComponent(String(project.id));

    galleryContainer.hidden = true;
    grid.replaceChildren();
    viewerImages = [];
    if (mainImageUrl) viewerImages.push({ src: mainImageUrl, alt: (lang === 'fa' ? 'تصویر پروژه: ' : 'Project preview: ') + (project.title || '') });

    let gallery = [];
    try {
      const response = await fetch(galleryUrl, { headers: { Accept: 'application/json' }, cache: 'no-store' });
      if (response.ok) {
        const payload = await response.json();
        if (payload && payload.success && Array.isArray(payload.data)) gallery = payload.data;
      }
    } catch (error) {
      console.warn('[FaraTech] Could not load project gallery:', error);
    }

    if (thisRequest !== requestNumber) return;

    const usableGallery = gallery.map(function (item, index) {
      const src = normalizeMediaUrl(item.image_url);
      if (!src) return null;
      const caption = String(item.caption || '').trim();
      const alt = caption || ((lang === 'fa' ? 'تصویر ' : 'Image ') + (index + 1) + ': ' + (project.title || ''));
      let viewerIndex = viewerImages.findIndex(function (entry) { return entry.src === src; });
      if (viewerIndex === -1) {
        viewerIndex = viewerImages.length;
        viewerImages.push({ src: src, alt: alt });
      }
      return { src: src, alt: alt, caption: caption, viewerIndex: viewerIndex };
    }).filter(Boolean);

    if (usableGallery.length) {
      const fragment = document.createDocumentFragment();
      usableGallery.forEach(function (item) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'gallery-item';
        button.setAttribute('aria-label', (lang === 'fa' ? 'نمایش تصویر: ' : 'View image: ') + item.alt);

        const image = document.createElement('img');
        image.src = item.src;
        image.alt = item.alt;
        image.loading = 'lazy';
        image.decoding = 'async';
        button.appendChild(image);

        const caption = document.createElement('span');
        caption.className = 'gallery-caption';
        caption.textContent = item.caption || item.alt;
        button.appendChild(caption);

        button.addEventListener('click', function () {
          openViewer(item.src, item.alt, button, item.viewerIndex);
        });
        fragment.appendChild(button);
      });
      grid.appendChild(fragment);
      galleryContainer.hidden = false;
    }

    const hasMedia = Boolean(mainImageUrl || normalizeMediaUrl(project.video_url) || usableGallery.length);
    noMediaContainer.hidden = hasMedia;
  }

  async function loadProject() {
    const thisRequest = ++requestNumber;
    const lang = currentLang();
    if (!projectId || !/^\d+$/.test(projectId)) {
      setNotFound(translate('project.notFoundDesc', 'Please return to the portfolio.'), lang);
      return;
    }

    try {
      const url = apiBase + '/public_project.php?id=' + encodeURIComponent(projectId) + '&lang=' + encodeURIComponent(lang);
      const response = await fetch(url, { headers: { Accept: 'application/json' }, cache: 'no-store' });
      const payload = await response.json();
      if (thisRequest !== requestNumber) return;
      if (!response.ok || !payload || !payload.success || !payload.data) {
        setNotFound(payload && payload.message, lang);
        return;
      }

      const project = payload.data;
      renderProject(project, lang);
      await loadGallery(project, lang, thisRequest);
    } catch (error) {
      if (thisRequest !== requestNumber) return;
      console.error('[FaraTech] Could not load project details:', error);
      const title = translate('project.errorLoading', 'Error loading project');
      el('projectHeaderTitle').textContent = title;
      el('projectHeaderSubtitle').textContent = translate('project.errorLoadingDesc', 'Please try again later.');
      el('projectDescription').textContent = translate('project.errorLoadingDesc', 'Please try again later.');
      document.title = title + ' | FaraTech';
      ensureMeta('meta[name="robots"]', 'content', 'noindex, follow');
    }
  }

  function openViewer(src, alt, opener, selectedIndex) {
    if (!viewerImages.length) viewerImages = [{ src: src, alt: alt }];
    let index = Number.isInteger(selectedIndex) ? selectedIndex : viewerImages.findIndex(function (image) { return image.src === src; });
    if (index < 0) {
      viewerImages.push({ src: src, alt: alt });
      index = viewerImages.length - 1;
    }
    viewerIndex = index;
    focusBeforeViewer = opener || document.activeElement;
    renderViewerImage();
    const viewer = el('imageLightbox');
    viewer.hidden = false;
    document.body.style.overflow = 'hidden';
    el('lightboxClose').focus();
  }

  function renderViewerImage() {
    const image = viewerImages[viewerIndex];
    if (!image) return;
    const viewerImage = el('lightboxImage');
    viewerImage.src = image.src;
    viewerImage.alt = image.alt || '';
    el('lightboxPrevious').hidden = viewerImages.length < 2;
    el('lightboxNext').hidden = viewerImages.length < 2;
  }

  function closeViewer() {
    const viewer = el('imageLightbox');
    if (viewer.hidden) return;
    viewer.hidden = true;
    document.body.style.overflow = '';
    el('lightboxImage').removeAttribute('src');
    if (focusBeforeViewer && typeof focusBeforeViewer.focus === 'function') focusBeforeViewer.focus();
  }

  function stepViewer(step) {
    if (!viewerImages.length) return;
    viewerIndex = (viewerIndex + step + viewerImages.length) % viewerImages.length;
    renderViewerImage();
  }

  function bindViewer() {
    el('lightboxClose').addEventListener('click', closeViewer);
    el('lightboxPrevious').addEventListener('click', function () { stepViewer(-1); });
    el('lightboxNext').addEventListener('click', function () { stepViewer(1); });
    el('imageLightbox').addEventListener('click', function (event) {
      if (event.target === el('imageLightbox')) closeViewer();
    });
    document.addEventListener('keydown', function (event) {
      if (el('imageLightbox').hidden) return;
      if (event.key === 'Escape') closeViewer();
      else if (event.key === 'ArrowLeft') stepViewer(-1);
      else if (event.key === 'ArrowRight') stepViewer(1);
    });
  }

  function start() {
    bindViewer();
    loadProject();
  }

  if (requestedLang === 'en' && window.FT_I18N && window.FT_I18N.current !== 'en') {
    let storedLang = null;
    try { storedLang = window.localStorage.getItem('ft_lang'); } catch (error) {}
    if (!storedLang && typeof window.FT_I18N.setLang === 'function') window.FT_I18N.setLang('en');
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
  else start();
  document.addEventListener('ft:langchange', loadProject);
})();
