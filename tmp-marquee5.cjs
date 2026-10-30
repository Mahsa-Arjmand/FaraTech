const { chromium } = require('playwright');
const path = require('path');

(async () => {
  const browser = await chromium.launch({ channel: 'chrome' });
  for (const lang of ['fa', 'en']) {
    const ctx = await browser.newContext({ viewport: { width: 1366, height: 800 } });
    await ctx.addInitScript((l) => { try { localStorage.setItem('ft_lang', l); } catch (e) {} }, lang);
    const page = await ctx.newPage();
    const errs = [];
    page.on('pageerror', e => errs.push(String(e)));
    await page.goto('file:///' + path.resolve(__dirname, 'frontend', 'index.html').replace(/\\/g, '/'), { waitUntil: 'domcontentloaded' });

    const first = await page.evaluate(() => {
      const t = document.querySelector('.trusted .marquee-track');
      return { tx: t.style.transform, anim: getComputedStyle(t).animationName };
    });

    const covs = [];
    const txs = [];
    for (let i = 0; i < 20; i++) {
      await page.waitForTimeout(650);
      const s = await page.evaluate(() => {
        const t = document.querySelector('.trusted .marquee-track');
        const b = document.querySelector('.trusted .marquee');
        const tr = t.getBoundingClientRect(); const br = b.getBoundingClientRect();
        return {
          box: Math.round(br.width),
          cov: Math.round(Math.max(0, Math.min(tr.right, br.right) - Math.max(tr.left, br.left))),
          tx: parseInt(t.style.transform.replace(/[^-\d]/g, ''), 10),
        };
      });
      covs.push(s.cov); txs.push(s.tx);
    }
    const box = await page.evaluate(() => Math.round(document.querySelector('.trusted .marquee').getBoundingClientRect().width));
    console.log(`${lang}: firstFrame=${first.tx} anim=${first.anim} box=${box} minCoverage=${Math.min(...covs)} emptyFrames=${covs.filter(c => c < box).length} pageErrors=${errs.length}`);
    console.log(`   tx over ${(20 * 0.65).toFixed(1)}s: ${txs.join(' ')}`);
    await ctx.close();
  }
  await browser.close();
})();
