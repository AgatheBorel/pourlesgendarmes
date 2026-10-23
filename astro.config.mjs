import { defineConfig } from 'astro/config';

// Domaine principal. pour-les-gendarmes.fr est redirigé en 301 vers celui-ci (voir public/.htaccess).
export default defineConfig({
  site: 'https://pourlesgendarmes.fr',
  output: 'static',
  trailingSlash: 'always',
  build: { format: 'directory', inlineStylesheets: 'auto' },
});
