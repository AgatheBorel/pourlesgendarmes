// Génère dist/sitemap.xml à partir des pages construites (hors pages en noindex).
import { readdirSync, statSync, readFileSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
const SITE = 'https://pourlesgendarmes.fr';
const dist = new URL('../dist/', import.meta.url).pathname;
const pages = [];
(function parcourir(dir) {
  for (const f of readdirSync(dir)) {
    const p = join(dir, f);
    if (statSync(p).isDirectory()) parcourir(p);
    else if (f === 'index.html') {
      if (/<meta name="robots" content="noindex/.test(readFileSync(p, 'utf8'))) continue;
      pages.push(p.slice(dist.length - 1).replace(/index\.html$/, ''));
    }
  }
})(dist);
const auj = new Date().toISOString().slice(0, 10);
const xml = `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${pages.sort()
  .map((u) => `  <url><loc>${SITE}${u}</loc><lastmod>${auj}</lastmod></url>`).join('\n')}\n</urlset>\n`;
writeFileSync(join(dist, 'sitemap.xml'), xml);
console.log(`sitemap.xml : ${pages.length} pages`);
