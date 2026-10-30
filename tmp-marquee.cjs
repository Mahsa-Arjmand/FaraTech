const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const shotDir = path.resolve(__dirname, 'tmp-shots2');
fs.mkdirSync(shotDir, { recursive: true });

(async () => {
  const browser = await chromium.launch({ channel: 'chrome' });
  for (const vp of [{ w: 390, h: 844, tag: 'mobile' }, { w: 1280, h: 800, tag: 'desktop' }]) {
    for (const lang of ['fa', 'en']) {
      const ctx = await browser.newContext({ viewport: { width: vp.w, height: vp.h } });
      await ctx.addInitScript((l) => { try { localStorage.setItem('ft_lang', l); } catch (e) {} }, lang);
      const page = await ctx.newPage();
      await page.goto('file:///' + path.resolve(__dirname, 'frontend', 'index.html').replace(/\\/g, '/'), { waitUntil: 'load' });
      await page.waitForTimeout(3200); // let the preloader finish
      await page.evaluate(() => {
        const s = document.querySelector('.trusted');
        window.scrollTo(0, s.getBoundingClientRect().top + window.scrollY - 120);
      });
      await page.waitForTimeout(300);

      const samples = [];
      const t0 = Date.now();
      for (let i = 0; i < 7; i++) {
        const snap = await page.evaluate(() => {
          const track = document.querySelector('.trusted .marquee-track');
          const box = document.querySelector('.trusted .marquee');
          const cs = getComputedStyle(track);
          const r = track.getBoundingClientRect();
          const b = box.getBoundingClientRect();
          return {
            trackL: +r.left.toFixed(0),
            trackR: +r.right.toFixed(0),
            trackW: +r.width.toFixed(0),
            boxL: +b.left.toFixed(0),
            boxR: +b.right.toFixed(0),
            vw: window.innerWidth,
            anim: cs.animationName,
            dir: cs.animationDirection,
            dur: cs.animationDuration,
            delay: cs.animationDelay,
            transform: cs.transform,
            visibleInBox: Math.max(0, Math.min(r.right, b.right) - Math.max(r.left, b.left)),
          };
        });
        samples.push({ t: Date.now() - t0, ...snap });
        if (i === 0) {
          const el = await page.$('.trusted');
          const bb = await el.boundingBox();
          await page.screenshot({
            path: path.join(shotDir, `trusted-${vp.tag}-${lang}-t0.png`),
            clip: { x: 0, y: Math.max(0, bb.y), width: vp.w, height: Math.min(bb.height + 60, vp.h) },
          });
        }
        await page.waitForTimeout(1000);
      }
      console.log(`\n=== ${vp.tag} ${lang} ===`);
      samples.forEach(s => console.log(JSON.stringify(s)));
      await ctx.close();
    }
  }
  await browser.close();
})();
