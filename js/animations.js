// ===== SCROLL REVEAL =====
function initScrollReveal() {
  const revealElements = document.querySelectorAll('[data-reveal]');

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('revealed');
        observer.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px',
  });

  revealElements.forEach(el => observer.observe(el));
}

// ===== STAT COUNTER =====
function initCounters() {
  const statCards = document.querySelectorAll('.stat-card[data-count]');

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const card = entry.target;
        const target = parseInt(card.dataset.count, 10);
        const suffix = card.dataset.suffix || '';
        const numEl = card.querySelector('.num');
        const plusEl = numEl.querySelector('.plus');

        let current = 0;
        const duration = 1500;
        const startTime = performance.now();

        function update(now) {
          const elapsed = now - startTime;
          const progress = Math.min(elapsed / duration, 1);
          const eased = 1 - Math.pow(1 - progress, 3);
          current = Math.floor(eased * target);

          numEl.childNodes[0].nodeValue = current;

          if (progress < 1) {
            requestAnimationFrame(update);
          } else {
            card.classList.add('counted');
          }
        }

        requestAnimationFrame(update);
        observer.unobserve(card);
      }
    });
  }, { threshold: 0.5 });

  statCards.forEach(card => observer.observe(card));
}

// ===== PROGRESS BARS =====
function initProgressBars() {
  const heroFrame = document.querySelector('.hero-frame');
  if (!heroFrame) return;

  heroFrame.querySelectorAll('.progress-fill').forEach(f => f.style.width = '0%');

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const fills = entry.target.querySelectorAll('.progress-fill');
        fills.forEach(fill => {
          const width = fill.classList.contains('gold') ? '92%' : '80%';
          fill.style.width = width;
        });
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.3 });

  observer.observe(heroFrame);
}

document.addEventListener('DOMContentLoaded', () => {
  initScrollReveal();
  initCounters();
  initProgressBars();
});