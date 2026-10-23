# Publier un article

## Le circuit

1. **Choisir un sujet** dans `docs/plan-editorial.csv`.
2. **Rédiger** : avec Claude (voir `docs/3-PROMPT-REDACTION.md`) ou soi-même à partir de `docs/modele-article.md`.
3. **Relire** avec la checklist ci-dessous.
4. **Publier** : Pages CMS ou GitHub (voir plus bas). Le site se met à jour seul en deux à trois minutes.
5. **Mettre à jour le plan éditorial** : statut « publié » et date.

## Publication programmée

La **date de publication** décide de la mise en ligne : un article daté dans le futur reste invisible
jusqu'à ce jour-là. Le site est reconstruit chaque matin, il apparaît donc automatiquement le jour venu.
Il est ainsi possible de rédiger dix articles en une séance et de les étaler sur un mois.

La case **brouillon** bloque la publication quelle que soit la date.

Un lien vers un article pas encore publié s'affiche en texte simple, puis devient cliquable de lui-même le jour
de la publication de sa cible : on peut donc relier les articles programmés entre eux sans risque de lien cassé.

## Méthode A — Pages CMS (recommandée)

app.pagescms.org > dépôt `pourlesgendarmes` > **Articles** > **Add an entry**.
Remplir les champs, coller le contenu, **Save**. Pour publier, décocher **brouillon**.

## Méthode B — Directement sur GitHub

Dépôt > dossier `src/content/articles` > **Add file** > **Create new file**.

- Nom du fichier : l'adresse de la page, en minuscules, mots séparés par des tirets, sans accents,
  terminé par `.md`. Exemple : `garde-meubles-mutation.md` donnera `pourlesgendarmes.fr/articles/garde-meubles-mutation/`.
- Coller le texte produit avec le modèle ou par Claude, puis **Commit changes**.

## Si la publication échoue

Onglet **Actions** de GitHub : une croix rouge signale une erreur, avec la raison en clair, par exemple
`description: Description : 170 caractères maximum`. Corriger l'article et enregistrer : la publication repart.
Tant qu'il y a une erreur, le site en ligne n'est pas modifié.

## Checklist avant publication

- [ ] Le **titre** contient le mot-clé principal, idéalement au début, et fait moins de 70 caractères.
- [ ] La **description** (70 à 170 caractères) donne envie de cliquer et contient le mot-clé.
- [ ] Le texte répond à la question dès le chapô, puis détaille.
- [ ] Des **intertitres** (`##`) toutes les 150 à 300 mots : ils forment le sommaire automatique.
- [ ] **Au moins 3 liens internes** : vers un guide (mutation, logement, prise en charge, famille),
      vers un autre article, et vers la demande de devis quand c'est naturel.
- [ ] Une **FAQ** de 2 à 4 questions réelles, formulées comme on les tape dans Google.
- [ ] Les **sources** sont des pages officielles dont le lien a été ouvert et vérifié.

## Règles éditoriales (obligatoires)

- **Sources publiques uniquement** : textes officiels, Service-Public.fr, pages de la Gendarmerie ou du ministère.
  Rien qui provienne des documents ou échanges du marché PFMD.
- **Aucun chiffre réglementaire sans source vérifiée** (montants, volumes, délais). En cas de doute :
  « renseignez-vous auprès de votre unité ou de votre service de gestion ».
- **Neutralité** : la plateforme multi-déménageurs et le déménageur au choix sont présentés comme deux options.
  Ne jamais présenter le site comme un canal officiel.
- **Pas de nom d'entreprise** dans les articles : l'éditeur n'apparaît que dans les mentions légales.
- **Vouvoiement**, phrases courtes, vocabulaire du lecteur (« mutation », « caserne », « prise de poste »).

## Mettre en forme le contenu

| Pour obtenir | Écrire |
|---|---|
| Un intertitre (entre dans le sommaire) | `## Mon intertitre` |
| Un sous-intertitre | `### Mon sous-intertitre` |
| Du gras | `**texte**` |
| Une liste | `- élément` |
| Une liste numérotée | `1. élément` |
| Une case à cocher (checklist) | `- [ ] élément` |
| Un lien vers une page du site | `[texte du lien](/logement/)` |
| Un lien vers un autre article | `[texte](/articles/nom-du-fichier/)` |
| Un lien externe | `[texte](https://www.service-public.fr/…)` |
| Une citation ou un exemple | `> texte` |
