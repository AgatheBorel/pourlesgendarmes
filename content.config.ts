import { defineCollection } from 'astro:content';
import { glob } from 'astro/loaders';
import { z } from 'astro/zod';

// Rubriques des articles : la valeur sert dans l'URL, le libellé est affiché.
export const rubriques = {
  mutation: 'Mutation',
  logement: 'Logement',
  demenagement: 'Déménagement',
  famille: 'Famille',
  'outre-mer': 'Outre-mer',
  carriere: 'Carrière et retraite',
} as const;

const articles = defineCollection({
  loader: glob({ pattern: '**/*.md', base: './src/content/articles' }),
  schema: z.object({
    title: z.string().max(90, 'Titre : 90 caractères maximum'),
    description: z.string().min(70, 'Description : 70 caractères minimum').max(170, 'Description : 170 caractères maximum'),
    date: z.coerce.date(),
    maj: z.coerce.date().optional(),
    rubrique: z.enum(Object.keys(rubriques) as [keyof typeof rubriques, ...(keyof typeof rubriques)[]]),
    motCle: z.string().optional(),
    chapo: z.string().optional(),
    brouillon: z.boolean().default(false),
    faq: z.array(z.object({ q: z.string(), r: z.string() })).default([]),
    sources: z.array(z.object({ titre: z.string(), url: z.string().url() })).default([]),
  }),
});

export const collections = { articles };
