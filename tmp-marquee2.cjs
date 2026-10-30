const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const shotDir = path.resolve(__dirname, 'tmp-shots3');
fs.mkdirSync(shotDir, { recursive: true });

(async () => {
  const browser = await chromium.launch({ channel: 'chrome' });
  for (const lang of ['fa', 'en']) {
    for (const w of [390, 1280, 2560]) {
      const ctx = await browser.newContext({ viewport: { width: w, height: 800 } });
      await ctx.addInitScript((l) => { try { localStorage.setItem('ft_lang', l); } catch (e) {} }, lang);
      const page = await ctx.newPage();
      await page.goto('file:///' + path.resolve(__dirname, 'frontend', 'index.html').replace(/\\/g, '/'), { waitUntil: 'domcontentloaded' });

      const first = await page.evaluate(() => {
        const t = document.querySelector('.trusted .marquee-track');
        const b = document.querySelector('.trusted .marquee');
        const tr = t.getBoundingClientRect();
        const br = b.getBoundingClientRect();
        return {
          anim: getComputedStyle(t).animationName,
          inlineAnim: t.style.animation,
          inlineTransform: t.style.transform,
          coverage: Math.round(Math.max(0, Math.min(tr.right, br.right) - Math.max(tr.left, br.left))),
          boxW: Math.round(br.width),
        };
      });
      console.log(`${lang} w=${w} FIRST(domcontentloaded) ${JSON.stringify(first)}`);

      // نمونه‌برداری در طول یک دورهٔ کامل (≈۱۲ ثانیه) برای اطمینان از پرش/خالی‌نشدن
      await page.waitForTimeout(800);
      const samples = [];
      for (let i = 0; i < 20; i++) {
        const s = await page.evaluate(() => {
          const t = document.querySelector('.trusted .marquee-track');
          const b = document.querySelector('.trusted .marquee');
          const tr = t.getBoundingClientRect();
          const br = b.getBoundingClientRect();
          return {
            cov: Math.round(Math.max(0, Math.min(tr.right, br.right) - Math.max(tr.left, br.left))),
            box: Math.round(br.width),
            tx: t.style.transform,
          };
        });
        samples.push(s);
        await page.waitForTimeout(650);
      }
      const minCov = Math.min(...samples.map(s => s.cov));
      const maxCov = Math.max(...samples.map(s => s.cov));
      const box = samples[0].box;
      console.log(`${lang} w=${w} coverage min=${minCov} max=${maxCov} box=${box} gapFrames=${samples.filter(s => s.cov < s.box).length}/${samples.length}`);
      console.log(`   transforms: ${samples.map(s => s.tx).join(' | ')}`);

      if (lang === 'fa' && w === 390) {
        const boxEl = await page.$('.trusted');
        const bb = await boxEl.boundingBox();
        await page.screenshot({
          path: path.join(shotDir, `marquee-${lang}-${w}.png`),
          clip: { x: 0, y: Math.max(0, bb.y), width: w, height: Math.min(bb.height + 40, 300) },
          fullPage: false,
        });
      }
      await ctx.close();
    }
  }
  await browser.close();
})();
