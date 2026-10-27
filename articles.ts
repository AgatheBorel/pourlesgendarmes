import { getCollection, type CollectionEntry } from 'astro:content';

export type ArticleEntry = CollectionEntry<'articles'>;

/** Articles publiés, du plus récent au plus ancien. En local (npm run dev), tout est visible. */
export async function articlesPublies(): Promise<ArticleEntry[]> {
  const maintenant = new Date();
  // En ligne : ni brouillon, ni date de publication future (publication programmée).
  const tous = await getCollection('articles', ({ data }) => import.meta.env.DEV || (!data.brouillon && data.date <= maintenant));
  return tous.sort((a, b) => b.data.date.getTime() - a.data.date.getTime());
}

export const dateFr = (d: Date) => new Intl.DateTimeFormat('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }).format(d);
export const dateIso = (d: Date) => d.toISOString().slice(0, 10);
