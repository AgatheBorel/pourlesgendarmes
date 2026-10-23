# pourlesgendarmes.fr

Site d'information et de génération de demandes de devis pour les gendarmes mutés.
Édité par Muter Loger. Domaine principal : **pourlesgendarmes.fr** (pour-les-gendarmes.fr redirigé en 301).

## Technique
- **Astro** (site statique) : pages HTML légères, rapides, bien référencées. Aucun serveur Node nécessaire chez OVH.
- **PHP** (`public/api/lead.php`) : reçoit le formulaire, l'envoie par e-mail à militaire@muterloger.com (répondre à l'e-mail écrit au demandeur) et en garde une copie 30 jours hors du dossier public.
- Polices auto-hébergées, aucun service tiers chargé chez le visiteur (pas de Google Fonts, pas de reCAPTCHA).

## Documentation
- `docs/1-MISE-EN-PLACE.md` : OVH, GitHub, publication automatique, Pages CMS, Search Console (une seule fois)
- `docs/2-PUBLIER-UN-ARTICLE.md` : le circuit de publication, la checklist SEO et les règles éditoriales
- `docs/3-PROMPT-REDACTION.md` : les instructions à donner à Claude pour produire des articles au bon format
- `docs/modele-article.md` : le modèle d'article
- `docs/plan-editorial.csv` : 38 sujets priorisés (s'ouvre dans Excel)

## Articles
Un article = un fichier Markdown dans `src/content/articles/`. Le site génère la page, le sommaire,
la FAQ structurée, les articles liés, les listes par rubrique et le sitemap. Un article mal rempli bloque
la construction avec un message clair (rien n'est publié). Une date future programme la publication.

## Développer
    npm install
    npm run dev      # aperçu local sur http://localhost:4321
    npm run build    # génère dist/ (+ sitemap.xml)

## Mettre en ligne chez OVH (hébergement web mutualisé)
1. Dans l'espace client OVH > Hébergement > **Multisite** : ajouter `pourlesgendarmes.fr`, `www.pourlesgendarmes.fr`,
   `pour-les-gendarmes.fr` et `www.pour-les-gendarmes.fr`, tous vers le dossier `www`. Activer le **SSL** (Let's Encrypt) pour chacun.
2. Vérifier dans la configuration de l'hébergement : PHP 8.2 ou plus.
3. `npm run build`, puis envoyer **le contenu** de `dist/` dans `www/` (FTP/SFTP), y compris `.htaccess`.
4. Créer `~/config/pourlesgendarmes.php` à partir de `config/pourlesgendarmes.example.php` (voir docs/1-MISE-EN-PLACE.md).
5. Tester une demande réelle et vérifier sa réception sur militaire@muterloger.com.

## Si un e-mail n'arrive pas
Toutes les demandes des 30 derniers jours sont dans `~/prive/demandes-30-jours.jsonl` ; les échecs d'envoi dans `~/prive/envois-echoues.log`.

## Anti-spam
Champ piège invisible, délai minimal de remplissage, 5 envois par heure et par connexion (IP hachée).

## Pages
- `/` : landing page (formulaire rapide dès le premier écran, guides par situation, rétroplanning, sources, FAQ)
- `/guide-mutation/`, `/logement/`, `/prise-en-charge-demenagement/`, `/mutation-en-famille/` : guides
- `/faq/`, `/demande-de-devis/` (3 étapes, pré-rempli depuis le formulaire rapide), `/merci/`
- `/mentions-legales/` (seule page qui nomme l'éditeur), `/confidentialite/`

## Règles éditoriales (issues de l'analyse du CCAP, du CCTP et de l'annexe RGPD)
- Ne publier que des informations issues de sources publiques (textes, pages officielles).
  Rien qui provienne des documents reçus pendant l'exécution du marché.
- Présenter la PFMD et le mode indemnitaire de façon neutre, sans se présenter comme canal officiel.
- Base de leads strictement séparée des données PFMD (aucun échange dans aucun sens).

## À compléter ou valider avant la mise en ligne
- [ ] `src/data/site.ts` : informations légales de l'éditeur (SIREN, adresse, directeur de la publication, e-mail DPO).
- [ ] Page Données personnelles : relecture DPO / juridique, durée de conservation.
- [ ] Relecture des guides par une personne connaissant la Gendarmerie (logement concédé, accès casernes).
- [ ] Engagement de rappel « au créneau choisi » : confirmer que l'équipe peut le tenir.
- [ ] Enregistrement SPF du domaine et test de réception sur militaire@muterloger.com.
- [ ] Image de partage (Open Graph), Google Search Console + envoi du sitemap.
