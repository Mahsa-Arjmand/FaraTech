const { chromium } = require('playwright');
const path = require('path');

(async () => {
  const browser = await chromium.launch({ channel: 'chrome' });
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 } });
  await ctx.addInitScript(() => { try { localStorage.setItem('ft_lang', 'fa'); } catch (e) {} });
  const page = await ctx.newPage();
  const url = 'file:///' + path.resolve(__dirname, 'frontend', 'contact.html').replace(/\\/g, '/');
  await page.goto(url, { waitUntil: 'load' });
  await page.waitForTimeout(900);
  const info = await page.evaluate(() => {
    const chain = [];
    let el = document.querySelector('.page-header-content h1');
    while (el && el !== document.documentElement) {
      const cs = getComputedStyle(el);
      const r = el.getBoundingClientRect();
      chain.push({
        tag: el.tagName.toLowerCase(),
        cls: el.className && String(el.className).slice(0, 60),
        rect: [+r.left.toFixed(1), +r.right.toFixed(1), +r.width.toFixed(1)],
        display: cs.display,
        maxWidth: cs.maxWidth,
        width: cs.width,
        margin: `${cs.marginTop} ${cs.marginRight} ${cs.marginBottom} ${cs.marginLeft}`,
        justifyContent: cs.justifyContent,
        alignItems: cs.alignItems,
        textAlign: cs.textAlign,
        transform: cs.transform,
        padding: cs.padding,
      });
      el = el.parentElement;
    }
    return {
      innerWidth: window.innerWidth,
      clientWidth: document.documentElement.clientWidth,
      scrollWidth: document.documentElement.scrollWidth,
      bodyScrollWidth: document.body.scrollWidth,
      chain,
    };
  });
  console.log(JSON.stringify(info, null, 1));
  await browser.close();
})();
