# Mise en place (à faire une seule fois)

Durée : environ 1 heure. À la fin, chaque article enregistré sur GitHub est publié automatiquement sur pourlesgendarmes.fr.

## Étape 1 — Préparer l'hébergement OVH

1. Espace client OVH > Web Cloud > Hébergements > votre hébergement > onglet **Multisite**.
   Ajouter, avec et sans `www`, tous avec le dossier racine `www` et **SSL** coché :
   `pourlesgendarmes.fr` (domaine principal), `pour-les-gendarmes.fr`, `pourlegendarme.fr`, `pourlegendarme.com`,
   `pour-le-gendarme.fr`, `pour-le-gendarme.com` (et, si conservés, les domaines « militaire »).
   Le fichier `.htaccess` redirige automatiquement toute adresse autre que `pourlesgendarmes.fr` vers celle-ci.
2. Onglet **Informations générales** : vérifier que la version de PHP est 8.2 ou plus.
3. Onglet **FTP - SSH** : noter le **serveur FTP** (de la forme `ftp.clusterXXX.hosting.ovh.net`),
   l'**identifiant**, et définir un **mot de passe**.

## Étape 2 — Le fichier de configuration du formulaire

Plus rien à faire à la main : GitHub dépose automatiquement `config/pourlesgendarmes.php` à côté du dossier `www`
à chaque publication, à partir de `config/pourlesgendarmes.example.php` (destinataire : militaire@muterloger.com)
et du secret `CONFIG_SEL` (étape 4).

## Étape 2 bis — Autoriser l'envoi des e-mails (SPF)

Les demandes partent de l'adresse `no-reply@pourlesgendarmes.fr`. Pour qu'elles n'arrivent pas en spam :

1. OVH > Noms de domaine > `pourlesgendarmes.fr` > **Zone DNS**.
2. S'il n'existe pas déjà, ajouter un enregistrement **TXT** (ou SPF) sur le domaine avec la valeur :
   `v=spf1 include:mx.ovh.com ~all`
3. Après la mise en ligne, faire une demande de test et vérifier sa réception sur `militaire@muterloger.com`,
   y compris dans le dossier des indésirables. Si elle y arrive, demander à votre service informatique
   d'autoriser l'expéditeur `no-reply@pourlesgendarmes.fr`.

Chaque demande est aussi conservée 30 jours dans `prive/demandes-30-jours.jsonl` (à côté de `www`, non accessible
depuis internet) : en cas de doute sur un e-mail perdu, la demande s'y trouve. Les échecs d'envoi sont notés dans
`prive/envois-echoues.log`.

## Étape 3 — Créer le dépôt GitHub

1. Sur github.com, bouton **New repository**. Nom : `pourlesgendarmes`. Visibilité : **Private**. Créer.
2. Sur la page du dépôt vide : lien **uploading an existing file**. Glisser **tout le contenu** du dossier du projet
   (pas le dossier lui-même), y compris les dossiers `.github` et les fichiers `.pages.yml` et `.gitignore`.
   Sur Mac, les fichiers commençant par un point sont masqués : `Cmd + Maj + .` pour les afficher.
3. En bas de page, **Commit changes**.

## Étape 4 — Donner à GitHub l'accès FTP à OVH

Dépôt GitHub > **Settings** > **Secrets and variables** > **Actions** > **New repository secret**. Créer trois secrets :

| Nom | Valeur |
|---|---|
| `FTP_SERVER` | le serveur FTP noté à l'étape 1 |
| `FTP_USERNAME` | l'identifiant FTP |
| `FTP_PASSWORD` | le mot de passe FTP |
| `CONFIG_SEL` | une longue chaîne aléatoire (sert à anonymiser les adresses IP de l'anti-spam) |

Ces valeurs sont chiffrées : personne, pas même vous, ne peut les relire ensuite.

## Étape 5 — Première publication

Dépôt GitHub > onglet **Actions** > **Publier le site** > **Run workflow**.
Au bout de deux à trois minutes, une coche verte apparaît : le site est en ligne.

En cas de croix rouge, cliquer dessus pour lire l'erreur. La publication utilise SFTP (port 22, connexion chiffrée)
avec les mêmes identifiants que le FTP. Si la connexion échoue, vérifier le secret `FTP_SERVER` : chez OVH, le serveur
SFTP est en général `ftp.clusterXXX.hosting.ovh.net` ou `ssh.clusterXXX.hosting.ovh.net`.

## Étape 6 — Brancher l'interface d'édition (Pages CMS)

1. Aller sur **app.pagescms.org** et se connecter avec le compte GitHub.
2. Autoriser l'accès au dépôt `pourlesgendarmes`.
3. Le menu **Articles** apparaît, avec la liste des articles et un bouton pour en créer.

Au premier article créé par Pages CMS, vérifier sur GitHub que le fichier est bien nommé
`titre-de-l-article.md` dans `src/content/articles/` et que le contenu est correct. Si un réglage ne convient pas,
me joindre le fichier : j'ajusterai `.pages.yml`.

## Étape 7 — Google Search Console

1. search.google.com/search-console > **Ajouter une propriété** > **Domaine** : `pourlesgendarmes.fr`.
2. Valider via l'enregistrement DNS proposé (OVH > Noms de domaine > Zone DNS > ajouter un enregistrement TXT).
3. Menu **Sitemaps** : envoyer `https://pourlesgendarmes.fr/sitemap.xml`.

Faire de même pour `pour-les-gendarmes.fr` afin que Google voie la redirection.
