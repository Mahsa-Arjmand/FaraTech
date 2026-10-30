/* Temporary alignment verification for inner-page hero titles */
const { chromium } = require('playwright');
const path = require('path');

const pages = ['about.html', 'services.html', 'portfolio.html', 'team.html', 'contact.html', 'project-detail.html', 'ai-lab.html'];
const viewports = [
  { name: 'desktop-1280', width: 1280, height: 800 },
  { name: 'ipad-landscape-1024', width: 1024, height: 768 },
  { name: 'ipad-portrait-768', width: 768, height: 1024 },
];
const langs = ['fa', 'en'];

(async () => {
  const browser = await chromium.launch({ channel: 'chrome' });
  for (const lang of langs) {
    for (const vp of viewports) {
      const ctx = await browser.newContext({ viewport: { width: vp.width, height: vp.height }, locale: 'en-US' });
      await ctx.addInitScript((l) => {
        try { localStorage.setItem('ft_lang', l); } catch (e) {}
      }, lang);
      const page = await ctx.newPage();
      for (const file of pages) {
        const url = 'file:///' + path.resolve(__dirname, 'frontend', file).replace(/\\/g, '/');
        await page.goto(url, { waitUntil: 'load' });
        await page.waitForTimeout(900);
        const m = await page.evaluate(() => {
          const pick = (sel) => {
            const el = document.querySelector(sel);
            if (!el) return null;
            const r = el.getBoundingClientRect();
            const cs = getComputedStyle(el);
            return { l: +r.left.toFixed(1), r: +r.right.toFixed(1), w: +r.width.toFixed(1), c: +((r.left + r.right) / 2).toFixed(1), ta: cs.textAlign, ml: cs.marginLeft, mr: cs.marginRight };
          };
          return {
            dir: document.documentElement.dir,
            lang: document.documentElement.lang,
            h1: pick('.page-header-content h1') || pick('.ai-hero-content h1'),
            p: pick('.page-header-content > p') || pick('.ai-hero-content > p'),
          };
        });
        const h1 = m.h1, par = m.p;
        const delta = h1 && par ? +(h1.c - par.c).toFixed(1) : null;
        console.log(
          `${lang} ${vp.name.padEnd(20)} ${file.padEnd(20)} ${m.dir} h1c=${h1 ? h1.c : '-'} pc=${par ? par.c : '-'} delta=${delta} h1w=${h1 ? h1.w : '-'} pw=${par ? par.w : '-'} ta=${h1 ? h1.ta : '-'} ml=${h1 ? h1.ml : '-'} mr=${h1 ? h1.mr : '-'}`
        );
      }
      await ctx.close();
    }
  }
  await browser.close();
})();
