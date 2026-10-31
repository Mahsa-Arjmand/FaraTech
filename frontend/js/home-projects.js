(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    const track = document.getElementById('homeProjects');
    const previousButton = document.getElementById('homeProjectsPrevious');
    const nextButton = document.getElementById('homeProjectsNext');
    if (!track || !window.FaraTechProjectCards) return;

    const apiBase = window.FARATECH_API_BASE || '/api';
    let requestNumber = 0;
    let scrollFrame = 0;

    function currentLang() {
      return (window.FT_I18N && window.FT_I18N.current) || 'fa';
    }

    function text(key, fallback) {
      return (window.FT_I18N && window.FT_I18N.t(key)) || fallback;
    }

    function closestCardIndex() {
      const cards = Array.from(track.querySelectorAll('.work-card'));
      if (!cards.length) return 0;
      const rtl = getComputedStyle(track).direction === 'rtl';
      const trackRect = track.getBoundingClientRect();
      let closest = 0;
      let shortestDistance = Infinity;

      cards.forEach(function (card, index) {
        const rect = card.getBoundingClientRect();
        const distance = Math.abs(rtl ? trackRect.right - rect.right : rect.left - trackRect.left);
        if (distance < shortestDistance) {
          shortestDistance = distance;
          closest = index;
        }
      });
      return closest;
    }

    function updateControls() {
      const cards = track.querySelectorAll('.work-card');
      if (!previousButton || !nextButton) return;
      const index = closestCardIndex();
      const canScroll = track.scrollWidth > track.clientWidth + 2;
      previousButton.disabled = !canScroll || index <= 0;
      nextButton.disabled = !canScroll || index >= cards.length - 1;
      previousButton.hidden = !canScroll || cards.length < 2;
      nextButton.hidden = !canScroll || cards.length < 2;
    }

    function moveBy(step) {
      const cards = Array.from(track.querySelectorAll('.work-card'));
      if (!cards.length) return;
      const index = Math.max(0, Math.min(cards.length - 1, closestCardIndex() + step));
      cards[index].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
      window.setTimeout(updateControls, 280);
    }

    if (previousButton) previousButton.addEventListener('click', function () { moveBy(-1); });
    if (nextButton) nextButton.addEventListener('click', function () { moveBy(1); });

    track.addEventListener('scroll', function () {
      if (scrollFrame) cancelAnimationFrame(scrollFrame);
      scrollFrame = requestAnimationFrame(function () {
        scrollFrame = 0;
        updateControls();
      });
    }, { passive: true });
    window.addEventListener('resize', updateControls, { passive: true });

    // Touch and trackpad scrolling are native; this adds intuitive mouse dragging on desktop.
    let pointerStart = null;
    track.addEventListener('pointerdown', function (event) {
      if (event.pointerType !== 'mouse' || event.button !== 0) return;
      pointerStart = {
        pointerId: event.pointerId,
        x: event.clientX,
        scrollLeft: track.scrollLeft,
        rtl: getComputedStyle(track).direction === 'rtl',
        moved: false,
      };
      track.classList.add('is-dragging');
    });

    window.addEventListener('pointermove', function (event) {
      if (!pointerStart || event.pointerId !== pointerStart.pointerId) return;
      const delta = event.clientX - pointerStart.x;
      if (Math.abs(delta) > 4) pointerStart.moved = true;
      if (pointerStart.moved) {
        event.preventDefault();
        track.scrollLeft = pointerStart.scrollLeft + (pointerStart.rtl ? delta : -delta);
      }
    });

    function endPointerDrag(event) {
      if (!pointerStart || (event && event.pointerId !== pointerStart.pointerId)) return;
      const wasDragged = pointerStart.moved;
      pointerStart = null;
      track.classList.remove('is-dragging');
      if (wasDragged) {
        track.dataset.suppressClick = 'true';
        window.setTimeout(function () { delete track.dataset.suppressClick; }, 400);
      }
    }

    window.addEventListener('pointerup', endPointerDrag);
    window.addEventListener('pointercancel', endPointerDrag);
    track.addEventListener('dragstart', function (event) { event.preventDefault(); });
    track.addEventListener('click', function (event) {
      if (track.dataset.suppressClick === 'true') {
        delete track.dataset.suppressClick;
        event.preventDefault();
        event.stopPropagation();
      }
    }, true);

    async function loadProjects() {
      const thisRequest = ++requestNumber;
      const lang = currentLang();
      track.setAttribute('aria-busy', 'true');
      track.innerHTML = '<p class="portfolio-empty">' +
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
          track.innerHTML = '<p class="portfolio-empty">' + text('portfolio.noProjects', lang === 'fa' ? 'هنوز پروژه‌ای ثبت نشده است.' : 'No projects yet.') + '</p>';
          updateControls();
          return;
        }

        track.innerHTML = projects.map(function (project) {
          return window.FaraTechProjectCards.render(project, lang);
        }).join('');
        track.scrollTo({ left: 0, behavior: 'auto' });
        updateControls();
        if (window.FaraTechReveal && typeof window.FaraTechReveal.refresh === 'function') {
          window.FaraTechReveal.refresh();
        }
      } catch (error) {
        if (thisRequest !== requestNumber) return;
        console.error('[FaraTech] Could not load homepage projects:', error);
        track.innerHTML = '<p class="portfolio-empty">' +
          (lang === 'fa' ? 'نمونه‌کارها در حال حاضر در دسترس نیستند.' : 'Projects are temporarily unavailable.') +
          '</p>';
        updateControls();
      } finally {
        if (thisRequest === requestNumber) track.removeAttribute('aria-busy');
      }
    }

    loadProjects();
    document.addEventListener('ft:langchange', loadProjects);
  });
})();
