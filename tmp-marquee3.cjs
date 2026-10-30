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

      const info = await page.evaluate(() => {
        const t = document.querySelector('.trusted .marquee-track');
        const b = document.querySelector('.trusted .marquee');
        const kids = Array.from(t.children);
        const first = kids[0].getBoundingClientRect();
        const sixth = kids[6] ? kids[6].getBoundingClientRect() : null;
        return {
          anim: getComputedStyle(t).animationName,
          inlineTransform: t.style.transform,
          children: kids.length,
          trackW: Math.round(t.getBoundingClientRect().width),
          boxW: Math.round(b.getBoundingClientRect().width),
          period6: sixth ? Math.round(Math.abs(sixth.left - first.left)) : null,
          direction: getComputedStyle(t).direction,
        };
      });
      console.log(`${lang} w=${w} INFO ${JSON.stringify(info)}`);

      await page.waitForTimeout(600);
      const samples = [];
      for (let i = 0; i < 24; i++) {
        const s = await page.evaluate(() => {
          const t = document.querySelector('.trusted .marquee-track');
          const b = document.querySelector('.trusted .marquee');
          const tr = t.getBoundingClientRect();
          const br = b.getBoundingClientRect();
          return {
            cov: Math.round(Math.max(0, Math.min(tr.right, br.right) - Math.max(tr.left, br.left))),
            box: Math.round(br.width),
            tx: parseInt(t.style.transform.replace(/[^-\d]/g, ''), 10),
          };
        });
        samples.push(s);
        await page.waitForTimeout(650);
      }
      const box = samples[0].box;
      const jumps = [];
      for (let i = 1; i < samples.length; i++) {
        const d = samples[i].tx - samples[i - 1].tx;
        if (d < 0) jumps.push(`${samples[i - 1].tx}->${samples[i].tx}`);
      }
      console.log(`${lang} w=${w} coverage min=${Math.min(...samples.map(s => s.cov))} box=${box} emptyFrames=${samples.filter(s => s.cov < s.box).length} wraps=${jumps.join(',') || 'none'}`);
      console.log(`   tx: ${samples.map(s => s.tx).join(' ')}`);

      if (lang === 'fa' && (w === 390 || w === 1280)) {
        await page.evaluate(() => {
          const s = document.querySelector('.trusted');
          window.scrollTo(0, s.getBoundingClientRect().top + window.scrollY - 100);
        });
        await page.waitForTimeout(400);
        const el = await page.$('.trusted');
        await el.screenshot({ path: path.join(shotDir, `marquee-${lang}-${w}.png`) });
      }
      await ctx.close();
    }
  }
  await browser.close();
})();
