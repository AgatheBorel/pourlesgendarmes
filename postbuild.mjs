// Après la construction du site :
// 1. les liens vers un article pas encore publié (brouillon ou date future) deviennent du texte simple ;
//    ils redeviennent des liens d'eux-mêmes le jour de la publication (reconstruction quotidienne) ;
// 2. génération de dist/sitemap.xml (hors pages en noindex).
import { readdirSync, statSync, readFileSync, writeFileSync, existsSync } from 'node:fs';
import { join } from 'node:path';

const SITE = 'https://pourlesgendarmes.fr';
const dist = new URL('../dist/', import.meta.url).pathname;

const fichiersHtml = [];
(function parcourir(dir) {
  for (const f of readdirSync(dir)) {
    const p = join(dir, f);
    if (statSync(p).isDirectory()) parcourir(p);
    else if (f.endsWith('.html')) fichiersHtml.push(p);
  }
})(dist);

// 1. Liens vers des articles non publiés
let neutralises = 0;
const lienArticle = /<a\b[^>]*\bhref="\/articles\/([^/"#?]+)\/?[^"]*"[^>]*>([\s\S]*?)<\/a>/g;
for (const f of fichiersHtml) {
  const html = readFileSync(f, 'utf8');
  const nouveau = html.replace(lienArticle, (lien, slug, texte) => {
    if (slug === 'rubrique' || existsSync(join(dist, 'articles', slug, 'index.html'))) return lien;
    neutralises++;
    return texte;
  });
  if (nouveau !== html) writeFileSync(f, nouveau);
}
console.log(`liens vers des articles programmés neutralisés : ${neutralises}`);

// 2. Sitemap
const pages = fichiersHtml
  .filter((f) => f.endsWith('index.html') && !/<meta name="robots" content="noindex/.test(readFileSync(f, 'utf8')))
  .map((f) => f.slice(dist.length - 1).replace(/index\.html$/, ''));
const auj = new Date().toISOString().slice(0, 10);
const xml = `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${pages.sort()
  .map((u) => `  <url><loc>${SITE}${u}</loc><lastmod>${auj}</lastmod></url>`).join('\n')}\n</urlset>\n`;
writeFileSync(join(dist, 'sitemap.xml'), xml);
console.log(`sitemap.xml : ${pages.length} pages`);
