const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const shotDir = path.resolve(__dirname, 'tmp-shots');
fs.mkdirSync(shotDir, { recursive: true });

const cases = [
  { file: 'contact.html', lang: 'fa', w: 1280, h: 800, tag: 'contact-fa-1280' },
  { file: 'contact.html', lang: 'fa', w: 768, h: 1024, tag: 'contact-fa-768' },
  { file: 'about.html', lang: 'fa', w: 1024, h: 768, tag: 'about-fa-1024' },
  { file: 'contact.html', lang: 'en', w: 1280, h: 800, tag: 'contact-en-1280' },
];

(async () => {
  const browser = await chromium.launch({ channel: 'chrome' });
  for (const c of cases) {
    for (const mode of ['before', 'after']) {
      const ctx = await browser.newContext({ viewport: { width: c.w, height: c.h } });
      await ctx.addInitScript((l) => { try { localStorage.setItem('ft_lang', l); } catch (e) {} }, c.lang);
      const page = await ctx.newPage();
      await page.goto('file:///' + path.resolve(__dirname, 'frontend', c.file).replace(/\\/g, '/'), { waitUntil: 'load' });
      await page.waitForTimeout(700);
      if (mode === 'before') {
        await page.addStyleTag({ content: '.page-header-3d h1{margin:16px 0 20px !important}' });
      }
      await page.waitForTimeout(500);
      const hero = await page.$('.page-header');
      const box = await hero.boundingBox();
      await page.screenshot({
        path: path.join(shotDir, `${c.tag}-${mode}.png`),
        clip: { x: 0, y: Math.max(0, box.y), width: c.w, height: Math.min(box.height, c.h - Math.max(0, box.y)) },
      });
      await ctx.close();
    }
  }
  await browser.close();
  console.log('done', fs.readdirSync(shotDir).join('\n'));
})();
