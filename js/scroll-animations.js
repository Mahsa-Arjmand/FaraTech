// ============================================
// SCROLL-DRIVEN ANIMATIONS - The WOW factor
// ============================================

document.addEventListener('DOMContentLoaded', () => {
  initPreloader();
  initScrollProgress();
  initCursor();
  initScrollReveal();
  initSplitText();
  initCounters();
  initProgressBars();
  initTilt();
  initMagneticButtons();
  initParallax();
  initCardShine();
});

// ===== PRELOADER =====
function initPreloader() {
  const preloader = document.getElementById('preloader');
  if (!preloader) return;

  window.addEventListener('load', () => {
    setTimeout(() => {
      preloader.classList.add('hidden');
      // Trigger hero animations after preloader
      document.querySelectorAll('.hero [data-reveal]').forEach(el => {
        el.classList.add('revealed');
      });
    }, 600);
  });

  // Fallback
  setTimeout(() => {
    preloader.classList.add('hidden');
  }, 3000);
}

// ===== SCROLL PROGRESS BAR =====
function initScrollProgress() {
  const bar = document.getElementById('scrollProgress');
  if (!bar) return;

  window.addEventListener('scroll', () => {
    const scrollTop = window.scrollY;
    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
    const progress = (scrollTop / docHeight) * 100;
    bar.style.width = progress + '%';
  });
}

// ===== CUSTOM CURSOR =====
function initCursor() {
  const dot = document.getElementById('cursorDot');
  const ring = document.getElementById('cursorRing');
  if (!dot || !ring) return;

  let mouseX = 0, mouseY = 0;
  let ringX = 0, ringY = 0;

  window.addEventListener('mousemove', (e) => {
    mouseX = e.clientX;
    mouseY = e.clientY;
    dot.style.left = mouseX + 'px';
    dot.style.top = mouseY + 'px';
  });

  function animateRing() {
    ringX += (mouseX - ringX) * 0.15;
    ringY += (mouseY - ringY) * 0.15;
    ring.style.left = ringX + 'px';
    ring.style.top = ringY + 'px';
    requestAnimationFrame(animateRing);
  }
  animateRing();

  // Hover states
  document.querySelectorAll('a, button, [data-tilt], .flip-card, .service-item').forEach(el => {
    el.addEventListener('mouseenter', () => {
      dot.classList.add('hover');
      ring.classList.add('hover');
    });
    el.addEventListener('mouseleave', () => {
      dot.classList.remove('hover');
      ring.classList.remove('hover');
    });
  });
}

// ===== SCROLL REVEAL =====
function initScrollReveal() {
  const elements = document.querySelectorAll('[data-reveal]');

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const delay = entry.target.dataset.delay || 0;
        setTimeout(() => {
          entry.target.classList.add('revealed');
        }, delay);
        observer.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.15,
    rootMargin: '0px 0px -80px 0px',
  });

  elements.forEach(el => observer.observe(el));
}

// ===== SPLIT TEXT ANIMATION =====
function initSplitText() {
  const elements = document.querySelectorAll('[data-split]');

  elements.forEach(el => {
    const html = el.innerHTML;
    // Preserve existing spans (like .accent)
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;

    function wrapWords(node) {
      if (node.nodeType === 3) { // Text node
        const words = node.textContent.split(/(\s+)/);
        const frag = document.createDocumentFragment();
        words.forEach(word => {
          if (word.trim()) {
            const wordSpan = document.createElement('span');
            wordSpan.className = 'word';
            const inner = document.createElement('span');
            inner.className = 'word-inner';
            inner.textContent = word;
            wordSpan.appendChild(inner);
            frag.appendChild(wordSpan);
          } else {
            frag.appendChild(document.createTextNode(word));
          }
        });
        node.parentNode.replaceChild(frag, node);
      } else if (node.nodeType === 1) {
        // Recurse into element children
        Array.from(node.childNodes).forEach(wrapWords);
      }
    }

    Array.from(tempDiv.childNodes).forEach(wrapWords);
    el.innerHTML = tempDiv.innerHTML;

    // Observe for reveal
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          el.classList.add('revealed');
          // Stagger word animations
          el.querySelectorAll('.word-inner').forEach((word, i) => {
            word.style.transitionDelay = `${i * 0.05}s`;
          });
          observer.unobserve(el);
        }
      });
    }, { threshold: 0.3 });

    observer.observe(el);
  });
}

// ===== STAT COUNTERS =====
function initCounters() {
  const cards = document.querySelectorAll('.stat-card[data-count]');

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const card = entry.target;
        const target = parseInt(card.dataset.count, 10);
        const numEl = card.querySelector('.num');
        const duration = 2000;
        const start = performance.now();

        function update(now) {
          const elapsed = now - start;
          const progress = Math.min(elapsed / duration, 1);
          const eased = 1 - Math.pow(1 - progress, 4);
          numEl.childNodes[0].nodeValue = Math.floor(eased * target);

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

  cards.forEach(card => observer.observe(card));
}

// ===== PROGRESS BARS =====
function initProgressBars() {
  const heroFrame = document.querySelector('.hero-frame');
  if (!heroFrame) return;

  heroFrame.querySelectorAll('.progress-fill').forEach(f => f.style.width = '0%');

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.querySelectorAll('.progress-fill').forEach(fill => {
          const width = fill.classList.contains('gold') ? '92%' : '80%';
          setTimeout(() => fill.style.width = width, 800);
        });
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.3 });

  observer.observe(heroFrame);
}

// ===== 3D TILT =====
function initTilt() {
  document.querySelectorAll('[data-tilt]').forEach(el => {
    const maxTilt = parseFloat(el.dataset.tiltMax) || 10;

    el.addEventListener('mousemove', (e) => {
      const rect = el.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      const centerX = rect.width / 2;
      const centerY = rect.height / 2;

      const rotateX = ((y - centerY) / centerY) * -maxTilt;
      const rotateY = ((x - centerX) / centerX) * maxTilt;

      el.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;

      // Update shine position for cards
      const shine = el.querySelector('.card-shine');
      if (shine) {
        shine.style.setProperty('--shine-x', `${(x / rect.width) * 100}%`);
        shine.style.setProperty('--shine-y', `${(y / rect.height) * 100}%`);
      }
    });

    el.addEventListener('mouseleave', () => {
      el.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale3d(1, 1, 1)';
    });
  });
}

// ===== MAGNETIC BUTTONS =====
function initMagneticButtons() {
  document.querySelectorAll('.magnetic').forEach(btn => {
    btn.addEventListener('mousemove', (e) => {
      const rect = btn.getBoundingClientRect();
      const x = e.clientX - rect.left - rect.width / 2;
      const y = e.clientY - rect.top - rect.height / 2;

      btn.style.transform = `translate(${x * 0.15}px, ${y * 0.15}px)`;
    });

    btn.addEventListener('mouseleave', () => {
      btn.style.transform = 'translate(0, 0)';
    });
  });
}

// ===== PARALLAX BG =====
function initParallax() {
  const elements = document.querySelectorAll('[data-parallax]');

  window.addEventListener('scroll', () => {
    elements.forEach(el => {
      const speed = parseFloat(el.dataset.parallax) || 0.2;
      const rect = el.getBoundingClientRect();
      const offset = (window.innerHeight / 2 - rect.top - rect.height / 2) * speed;
      el.style.transform = `translateY(${offset}px)`;
    });
  });
}

// ===== CARD SHINE TRACKING =====
function initCardShine() {
  document.querySelectorAll('.card-3d').forEach(card => {
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const shine = card.querySelector('.card-shine');
      if (!shine) return;
      const x = ((e.clientX - rect.left) / rect.width) * 100;
      const y = ((e.clientY - rect.top) / rect.height) * 100;
      shine.style.setProperty('--shine-x', `${x}%`);
      shine.style.setProperty('--shine-y', `${y}%`);
    });
  });
}