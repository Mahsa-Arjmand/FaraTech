const { chromium } = require('playwright');
const path = require('path');

const widths = [390, 768, 1024, 1280, 1440, 1920, 2560];

(async () => {
  const browser = await chromium.launch({ channel: 'chrome' });
  for (const lang of ['fa', 'en']) {
    for (const w of widths) {
      const ctx = await browser.newContext({ viewport: { width: w, height: 900 } });
      await ctx.addInitScript((l) => { try { localStorage.setItem('ft_lang', l); } catch (e) {} }, lang);
      const page = await ctx.newPage();
      await page.goto('file:///' + path.resolve(__dirname, 'frontend', 'index.html').replace(/\\/g, '/'), { waitUntil: 'load' });
      await page.waitForTimeout(1200);
      const m = await page.evaluate(() => {
        const t = document.querySelector('.trusted .marquee-track');
        const b = document.querySelector('.trusted .marquee');
        const cs = getComputedStyle(t);
        return {
          trackW: Math.round(t.getBoundingClientRect().width),
          boxW: Math.round(b.getBoundingClientRect().width),
          anim: cs.animationName,
          dir: cs.animationDirection,
          overflowX: document.documentElement.scrollWidth - document.documentElement.clientWidth,
        };
      });
      const shiftMax = m.trackW / 2;
      const covered = m.trackW - m.boxW >= shiftMax; // no blank gap at any phase
      console.log(`${lang} w=${String(w).padEnd(5)} track=${m.trackW} box=${m.boxW} anim=${m.anim}/${m.dir} shiftMax=${Math.round(shiftMax)} noGap=${covered} hOverflow=${m.overflowX}`);
      await ctx.close();
    }
  }
  await browser.close();
})();
