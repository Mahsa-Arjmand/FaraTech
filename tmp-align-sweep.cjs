const { chromium } = require('playwright');
const path = require('path');

const pages = ['about.html', 'services.html', 'portfolio.html', 'team.html', 'contact.html', 'project-detail.html'];
const widths = [360, 390, 430, 768, 834, 1024, 1180, 1280, 1366, 1440, 1600, 1920];

(async () => {
  const browser = await chromium.launch({ channel: 'chrome' });
  for (const lang of ['fa', 'en']) {
    for (const w of widths) {
      const ctx = await browser.newContext({ viewport: { width: w, height: 900 } });
      await ctx.addInitScript((l) => { try { localStorage.setItem('ft_lang', l); } catch (e) {} }, lang);
      const page = await ctx.newPage();
      const rows = [];
      for (const f of pages) {
        await page.goto('file:///' + path.resolve(__dirname, 'frontend', f).replace(/\\/g, '/'), { waitUntil: 'load' });
        await page.waitForTimeout(400);
        const m = await page.evaluate(() => {
          const h = document.querySelector('.page-header-content h1').getBoundingClientRect();
          const p = document.querySelector('.page-header-content > p').getBoundingClientRect();
          const e = document.querySelector('.page-header-content .eyebrow');
          const b = document.querySelector('.page-header-content .breadcrumb').getBoundingClientRect();
          const eb = e ? e.getBoundingClientRect() : null;
          return {
            dh: +((h.left + h.right) / 2 - (p.left + p.right) / 2).toFixed(1),
            db: +((h.left + h.right) / 2 - (b.left + b.right) / 2).toFixed(1),
            oe: document.documentElement.scrollWidth - document.documentElement.clientWidth,
          };
        });
        rows.push(`${f.replace('.html', '')}:dH=${m.dh} dBc=${m.db}`);
        if (m.oe > 0) rows.push(`OVERFLOW ${f} ${m.oe}`);
      }
      console.log(`${lang} w=${w}  ${rows.join('  ')}`);
      await ctx.close();
    }
  }
  await browser.close();
})();
