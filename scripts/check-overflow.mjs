/**
 * Responsive overflow check — run: node scripts/check-overflow.mjs
 * Requires: npx playwright (downloads on first run)
 */
import { chromium, devices } from 'playwright';

const BASE = process.env.BASE_URL || 'http://127.0.0.1:8765';
const PAGES = [
  'index.html',
  'about.html',
  'services.html',
  'ai-lab.html',
  'contact.html',
  'portfolio.html',
  'team.html',
  'project-detail.html?id=1',
];

const VIEWPORTS = [
  { name: '320x568', width: 320, height: 568 },
  { name: '375x812', width: 375, height: 812 },
  { name: '768x1024', width: 768, height: 1024 },
  { name: '1280x800', width: 1280, height: 800 },
];

const browser = await chromium.launch();
const results = [];

for (const vp of VIEWPORTS) {
  const context = await browser.newContext({
    viewport: { width: vp.width, height: vp.height },
    deviceScaleFactor: 2,
  });
  const page = await context.newPage();

  for (const path of PAGES) {
    await page.goto(`${BASE}/${path}`, { waitUntil: 'networkidle' });
    const metrics = await page.evaluate(() => {
      const doc = document.documentElement;
      const pre = document.getElementById('preloader');
      return {
        scrollWidth: doc.scrollWidth,
        clientWidth: doc.clientWidth,
        overflowX: doc.scrollWidth > doc.clientWidth + 1,
        preloaderHidden: !pre || pre.classList.contains('hidden'),
      };
    });
    results.push({ viewport: vp.name, page: path, ...metrics });

    await page.goto(`${BASE}/${path}`, { waitUntil: 'domcontentloaded' });
    const duringLoad = await page.evaluate(() => {
      const doc = document.documentElement;
      const pre = document.getElementById('preloader');
      return {
        scrollWidth: doc.scrollWidth,
        clientWidth: doc.clientWidth,
        overflowX: doc.scrollWidth > doc.clientWidth + 1,
        preloaderVisible: pre && !pre.classList.contains('hidden'),
      };
    });
    results.push({
      viewport: vp.name,
      page: path,
      phase: 'domcontentloaded',
      ...duringLoad,
    });
  }

  await context.close();
}

await browser.close();

const failures = results.filter((r) => r.overflowX);
console.log(JSON.stringify({ total: results.length, failures: failures.length, results, failures }, null, 2));
process.exit(failures.length ? 1 : 0);
