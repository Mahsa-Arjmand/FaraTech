const { chromium } = require('playwright');
const path = require('path');

(async () => {
  const browser = await chromium.launch({ channel: 'chrome' });
  const ctx = await browser.newContext({ viewport: { width: 1366, height: 800 } });
  await ctx.addInitScript(() => { try { localStorage.setItem('ft_lang', 'fa'); } catch (e) {} });
  const page = await ctx.newPage();
  const errors = [];
  page.on('pageerror', e => errors.push(String(e)));
  page.on('console', m => { if (m.type() === 'error') errors.push('console: ' + m.text()); });

  await page.goto('file:///' + path.resolve(__dirname, 'frontend', 'index.html').replace(/\\/g, '/'), { waitUntil: 'load' });
  await page.waitForTimeout(1500);

  const snap = () => page.evaluate(() => {
    const t = document.querySelector('.trusted .marquee-track');
    const b = document.querySelector('.trusted .marquee');
    const tr = t.getBoundingClientRect();
    const br = b.getBoundingClientRect();
    return {
      dir: document.documentElement.dir,
      lang: document.documentElement.lang,
      box: Math.round(br.width),
      coverage: Math.round(Math.max(0, Math.min(tr.right, br.right) - Math.max(tr.left, br.left))),
      tx: t.style.transform,
      anim: getComputedStyle(t).animationName,
      children: t.children.length,
    };
  });

  console.log('initial   ', JSON.stringify(await snap()));

  // switch to English
  await page.click('[data-lang-switch="en"]');
  await page.waitForTimeout(900);
  console.log('after en  ', JSON.stringify(await snap()));

  // switch back to Persian
  await page.click('[data-lang-switch="fa"]');
  await page.waitForTimeout(900);
  console.log('after fa  ', JSON.stringify(await snap()));

  // confirm it is still advancing after the switches
  const a = (await snap()).tx;
  await page.waitForTimeout(700);
  const b2 = (await snap()).tx;
  console.log('advancing:', a, '->', b2);

  // resize larger then smaller, coverage must hold
  await page.setViewportSize({ width: 3200, height: 900 });
  await page.waitForTimeout(1600);
  console.log('wide 3200 ', JSON.stringify(await snap()));
  await page.setViewportSize({ width: 360, height: 800 });
  await page.waitForTimeout(1600);
  console.log('narrow 360', JSON.stringify(await snap()));

  console.log('errors:', errors.length ? errors : 'none');
  await browser.close();
})();
