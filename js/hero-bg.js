/* Hero background animation — connected particles network */
(function () {
  const canvas = document.getElementById('heroCanvas');
  if (!canvas) return;

  const ctx = canvas.getContext('2d');
  const hero = canvas.parentElement;

  const COLOR_DOT = 'rgba(11, 31, 58, 0.6)';
  const COLOR_DOT_HOVER = 'rgba(201, 162, 39, 0.9)';

  let width, height, dpr;
  let particles = [];
  let mouse = { x: null, y: null, radius: 140 };

  function resize() {
    dpr = window.devicePixelRatio || 1;
    width = hero.offsetWidth;
    height = hero.offsetHeight;
    canvas.width = width * dpr;
    canvas.height = height * dpr;
    canvas.style.width = width + 'px';
    canvas.style.height = height + 'px';
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    initParticles();
  }

  function initParticles() {
    const area = width * height;
    const count = Math.min(Math.floor(area / 12000), 100);

    particles = [];
    for (let i = 0; i < count; i++) {
      particles.push({
        x: Math.random() * width,
        y: Math.random() * height,
        vx: (Math.random() - 0.5) * 0.4,
        vy: (Math.random() - 0.5) * 0.4,
        r: Math.random() * 1.8 + 1.2,
      });
    }
  }

  function draw() {
    ctx.clearRect(0, 0, width, height);

    for (let i = 0; i < particles.length; i++) {
      const p1 = particles[i];

      for (let j = i + 1; j < particles.length; j++) {
        const p2 = particles[j];
        const dx = p1.x - p2.x;
        const dy = p1.y - p2.y;
        const dist = Math.sqrt(dx * dx + dy * dy);

        if (dist < 160) {
          const opacity = 1 - dist / 160;
          const mouseNear = isNearMouse(p1) || isNearMouse(p2);

          if (mouseNear) {
            ctx.strokeStyle = 'rgba(201, 162, 39, ' + (0.5 * opacity) + ')';
          } else {
            ctx.strokeStyle = 'rgba(11, 31, 58, ' + (0.22 * opacity) + ')';
          }

          ctx.lineWidth = 1;
          ctx.beginPath();
          ctx.moveTo(p1.x, p1.y);
          ctx.lineTo(p2.x, p2.y);
          ctx.stroke();
        }
      }
    }

    particles.forEach((p) => {
      const nearMouse = isNearMouse(p);
      ctx.fillStyle = nearMouse ? COLOR_DOT_HOVER : COLOR_DOT;
      ctx.beginPath();
      ctx.arc(p.x, p.y, nearMouse ? p.r * 1.8 : p.r, 0, Math.PI * 2);
      ctx.fill();
    });

    particles.forEach((p) => {
      p.x += p.vx;
      p.y += p.vy;

      if (p.x < 0 || p.x > width) p.vx *= -1;
      if (p.y < 0 || p.y > height) p.vy *= -1;
    });
  }

  function isNearMouse(p) {
    if (mouse.x === null) return false;
    const dx = p.x - mouse.x;
    const dy = p.y - mouse.y;
    return dx * dx + dy * dy < mouse.radius * mouse.radius;
  }

  let rafId;
  function loop() {
    draw();
    rafId = requestAnimationFrame(loop);
  }

  window.addEventListener('resize', resize);

  hero.addEventListener('mousemove', (e) => {
    const rect = hero.getBoundingClientRect();
    mouse.x = e.clientX - rect.left;
    mouse.y = e.clientY - rect.top;
  });

  hero.addEventListener('mouseleave', () => {
    mouse.x = null;
    mouse.y = null;
  });

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduceMotion) {
    draw();
    return;
  }

  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      cancelAnimationFrame(rafId);
    } else {
      loop();
    }
  });

  resize();
  loop();
})();