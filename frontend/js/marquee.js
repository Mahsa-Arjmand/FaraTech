/* ============================================================
   MARQUEE — ریل متحرک لوگوی مشتریان
   حرکت با requestAnimationFrame انجام می‌شود تا:
   • از همان لحظهٔ بازشدن سایت، ریل پر و در حال حرکت باشد
   • در هر عرضی (موبایل تا مانیتور عریض) متن همیشه دیده شود
   • حلقه دقیقاً به اندازهٔ یک تکرار جابه‌جا شود و پرش نداشته باشد
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => {
  'use strict';

  const SPEED = 60; // پیکسل در ثانیه
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');

  // کوچک‌ترین واحدی که محتوا با آن تکرار می‌شود (مثلاً ۳ لوگو + ۳ جداکننده)
  function findRepeatUnit(kids) {
    const n = kids.length;
    for (let k = 1; k <= Math.floor(n / 2); k += 1) {
      let same = true;
      for (let i = 0; i + k < n; i += 1) {
        if (kids[i].textContent !== kids[i + k].textContent) { same = false; break; }
      }
      if (same) return k;
    }
    return n;
  }

  function setupMarquee(wrap) {
    const track = wrap.querySelector('.marquee-track');
    if (!track || !track.children.length) return null;

    const kids = Array.from(track.children);
    const unit = findRepeatUnit(kids);
    const unitNodes = kids.slice(0, unit);
    const unitSize = unitNodes.length;

    let period = 0;
    let offset = 0;
    let direction = 1;
    let rafId = 0;
    let lastTime = 0;

    // طول یک تکرار کامل (شامل فاصلهٔ قبل از تکرار بعدی)
    function measurePeriod() {
      const items = track.children;
      if (items.length <= unitSize) return 0;
      const first = items[0].getBoundingClientRect();
      const next = items[unitSize].getBoundingClientRect();
      return Math.abs(next.left - first.left);
    }

    function addUnit() {
      unitNodes.forEach(node => {
        const clone = node.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        track.appendChild(clone);
      });
    }

    // ریل را تا جایی پر کن که در تمام طول حرکت، قاب خالی نشود
    function fill() {
      period = measurePeriod();
      let guard = 0;
      while (period && track.scrollWidth < wrap.clientWidth + period * 2 && guard < 40) {
        addUnit();
        guard += 1;
      }
      period = measurePeriod() || period;
    }

    function render() {
      track.style.transform = `translateX(${Math.round(offset * direction)}px)`;
    }

    function reset() {
      direction = getComputedStyle(track).direction === 'rtl' ? 1 : -1;
      fill();
      if (period) offset %= period;
      render();
    }

    function tick(now) {
      if (!lastTime) lastTime = now;
      const dt = Math.min((now - lastTime) / 1000, 0.1);
      lastTime = now;
      if (period) {
        offset += SPEED * dt;
        if (offset >= period) offset -= period;
        render();
      }
      rafId = requestAnimationFrame(tick);
    }

    function start() {
      cancelAnimationFrame(rafId);
      lastTime = 0;
      if (reduced.matches || !period) return;
      rafId = requestAnimationFrame(tick);
    }

    // انیمیشن CSS (و هر نسخهٔ قدیمی کش‌شدهٔ آن) را کنار بگذار
    track.style.animation = 'none';

    reset();
    offset = 0;
    render();
    start();

    return { reset, start };
  }

  const marquees = Array.from(document.querySelectorAll('.marquee'))
    .map(setupMarquee)
    .filter(Boolean);

  if (!marquees.length) return;

  const restartAll = () => marquees.forEach(marquee => { marquee.reset(); marquee.start(); });

  let resizeTimer = 0;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(restartAll, 150);
  });

  // تغییر زبان یا جهت صفحه: طول متن و جهت حرکت دوباره محاسبه شود
  document.addEventListener('ft:langchange', restartAll);

  // بعد از آماده‌شدن فونت‌ها اندازه‌ها تغییر می‌کند؛ دوباره اندازه‌گیری شود
  if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(() => restartAll()).catch(() => {});
  }
  window.addEventListener('load', () => restartAll());

  if (typeof reduced.addEventListener === 'function') {
    reduced.addEventListener('change', restartAll);
  }
});
