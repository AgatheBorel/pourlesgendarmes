<?php
// À copier sur le serveur OVH dans ~/config/pourlesgendarmes.php (au même niveau que www/, PAS dedans),
// puis à compléter. Ne jamais mettre ce fichier dans le dossier public ni dans GitHub.
return [
  'nom_site' => 'pourlesgendarmes.fr',
  'origines_autorisees' => ['https://pourlesgendarmes.fr'],

  // Destinataire des demandes de devis.
  'email_destinataire' => 'militaire@muterloger.com',

  // Expéditeur : une adresse du domaine du site (voir docs/1-MISE-EN-PLACE.md, réglage SPF).
  'email_expediteur' => 'no-reply@pourlesgendarmes.fr',
  'nom_expediteur' => 'Pour les gendarmes',

  // Dossier privé (hors www/) : copie des demandes sur 30 jours, limite de fréquence, journal d'erreurs.
  'dossier_prive' => __DIR__ . '/../prive',
  'sel' => 'REMPLACER-PAR-UNE-CHAINE-ALEATOIRE-LONGUE',
  'max_par_heure' => 5,

  // Texte exact de la case de consentement, joint à chaque demande (preuve RGPD).
  'texte_consentement' => "J'accepte que l'éditeur du site (voir les mentions légales) utilise ces informations pour me recontacter et établir mon devis.",
];
