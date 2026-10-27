<?php
/**
 * Réception des demandes de devis -> envoi par e-mail à l'équipe commerciale.
 * Chaque demande est aussi conservée 30 jours dans un fichier hors du dossier public :
 * si un e-mail se perd, la demande peut être retrouvée.
 */
declare(strict_types=1);
date_default_timezone_set('Europe/Paris');

// Fichier de configuration HORS du dossier public (www/). Voir config/pourlesgendarmes.example.php.
$cheminConfig = getenv('PLG_CONFIG') ?: dirname(__DIR__, 2) . '/config/pourlesgendarmes.php';
if (!is_file($cheminConfig)) { repondre(500, false, 'Configuration manquante.'); }
$cfg = require $cheminConfig;

$veutJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { repondre(405, false, 'Méthode non autorisée.'); }

// Requête venant bien du site (pas d'envoi depuis un autre domaine)
$origine = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origine !== '' && !in_array($origine, $cfg['origines_autorisees'], true)) { repondre(403, false, 'Origine refusée.'); }

// --- Anti-spam ---------------------------------------------------------------
// 1. Champ piège : un humain ne le voit pas. On fait semblant d'accepter.
if (trim((string)($_POST['site_web'] ?? '')) !== '') { repondre(200, true); }
// 2. Formulaire rempli trop vite (< 4 s) = robot probable.
$ts = (int)($_POST['ts'] ?? 0);
if ($ts > 0 && (microtime(true) * 1000 - $ts) < 4000) { repondre(200, true); }
// 3. Limite de fréquence par adresse IP (hachée, non conservée en clair).
if (!limiteFrequence($cfg)) { repondre(429, false, 'Trop de demandes envoyées depuis cette connexion. Réessayez dans une heure.', 'frequence'); }

// --- Validation --------------------------------------------------------------
$listes = [
  'motif'    => ['mutation', 'retraite', 'autre'],
  'logement' => ['caserne', 'appartement', 'maison'],
  'pieces'   => ['1', '2', '3', '4', '5+'],
  'creneau'  => ['indifferent', 'matin', 'midi', 'apres-midi', 'soir'],
];
// Champs d'une ligne : balises retirées, caractères de contrôle (retours à la ligne, tabulations…) remplacés par des espaces.
$p = fn(string $k, int $max = 200) => mb_substr(trim(preg_replace('/[\x00-\x1F\x7F]+/u', ' ', strip_tags((string)($_POST[$k] ?? '')))), 0, $max);
// Champ multiligne (message) : seuls les retours à la ligne sont conservés.
$pTexte = fn(string $k, int $max = 1000) => mb_substr(trim(preg_replace('/[\x00-\x09\x0B-\x1F\x7F]+/u', ' ', strip_tags((string)($_POST[$k] ?? '')))), 0, $max);

$lead = [
  'motif'          => $p('motif'),
  'date_souhaitee' => $p('date_souhaitee', 7),
  'depart_cp'      => $p('depart_cp', 5),
  'arrivee_cp'     => $p('arrivee_cp', 5),
  'outre_mer'      => ($_POST['outre_mer'] ?? '') === 'oui',
  'logement'       => $p('logement'),
  'pieces'         => $p('pieces'),
  'volume'         => $p('volume', 4),
  'prenom'         => $p('prenom', 80),
  'nom'            => $p('nom', 80),
  'telephone'      => $p('telephone', 20),
  'email'          => $p('email', 160),
  'creneau'        => $p('creneau') ?: 'indifferent',
  'message'        => $pTexte('message', 1000),
  'consentement'   => ($_POST['consentement'] ?? '') === 'oui',
];

$erreurs = [];
foreach ($listes as $champ => $valeurs) { if (!in_array($lead[$champ], $valeurs, true)) $erreurs[] = $champ; }
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $lead['date_souhaitee'])) $erreurs[] = 'date_souhaitee';
if (!preg_match('/^\d{5}$/', $lead['depart_cp'])) $erreurs[] = 'depart_cp';
if ($lead['arrivee_cp'] !== '' && !preg_match('/^\d{5}$/', $lead['arrivee_cp'])) $erreurs[] = 'arrivee_cp';
if ($lead['volume'] !== '' && (!ctype_digit($lead['volume']) || (int)$lead['volume'] < 1 || (int)$lead['volume'] > 300)) $erreurs[] = 'volume';
if ($lead['prenom'] === '') $erreurs[] = 'prenom';
if ($lead['nom'] === '') $erreurs[] = 'nom';
if (!preg_match('/^[+0-9 .()-]{10,20}$/', $lead['telephone'])) $erreurs[] = 'telephone';
if (!filter_var($lead['email'], FILTER_VALIDATE_EMAIL)) $erreurs[] = 'email';
if (!$lead['consentement']) $erreurs[] = 'consentement';
if ($erreurs) { repondre(422, false, "Votre demande n'a pas pu être envoyée. Vérifiez les champs, puis réessayez.", 'envoi', $erreurs); }

// Données de contexte
$source = json_decode((string)($_POST['source'] ?? ''), true) ?: [];
$lead['site']          = $cfg['nom_site'];
$lead['utm_source']    = mb_substr((string)($source['utm_source'] ?? ''), 0, 100);
$lead['utm_medium']    = mb_substr((string)($source['utm_medium'] ?? ''), 0, 100);
$lead['utm_campaign']  = mb_substr((string)($source['utm_campaign'] ?? ''), 0, 100);
$lead['referrer']      = mb_substr((string)($source['referrer'] ?? ''), 0, 300);
$lead['date_demande']  = date('c');
$lead['consentement_texte'] = $cfg['texte_consentement'];

// --- Envoi par e-mail ---------------------------------------------------------
sauvegarderCopie($lead, $cfg);
$envoye = envoyerEmail($lead, $cfg);
if (!$envoye) { journaliserEchec($lead, $cfg); }
// Pour le visiteur, la demande est prise en compte dans tous les cas (une copie est conservée).
repondre(200, true);

// =============================================================================

function libelles(): array {
  return [
    'motif' => ['mutation' => 'Mutation', 'retraite' => 'Départ à la retraite ou fin de contrat', 'autre' => 'Autre déménagement'],
    'logement' => ['caserne' => 'Logement en caserne (concédé)', 'appartement' => 'Appartement', 'maison' => 'Maison'],
    'pieces' => ['1' => 'Studio ou 1 pièce', '2' => '2 pièces', '3' => '3 pièces', '4' => '4 pièces', '5+' => '5 pièces ou plus'],
    'creneau' => ['indifferent' => 'Peu importe', 'matin' => 'Le matin', 'midi' => 'Entre 12 h et 14 h', 'apres-midi' => "L'après-midi", 'soir' => 'Après 18 h'],
  ];
}

function moisFr(string $aaaamm): string {
  $mois = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
  [$a, $m] = array_map('intval', explode('-', $aaaamm));
  return ($mois[$m] ?? '') . ' ' . $a;
}

function lignesEmail(array $lead): array {
  $l = libelles();
  $val = fn(string $k) => $l[$k][$lead[$k]] ?? $lead[$k];
  return [
    'Contact' => [
      'Nom' => trim($lead['prenom'] . ' ' . $lead['nom']),
      'Téléphone' => $lead['telephone'],
      'E-mail' => $lead['email'],
      'Rappel souhaité' => $val('creneau'),
    ],
    'Déménagement' => [
      'Motif' => $val('motif'),
      'Mois souhaité' => moisFr($lead['date_souhaitee']),
      'Départ (code postal)' => $lead['depart_cp'],
      'Arrivée (code postal)' => $lead['arrivee_cp'] !== '' ? $lead['arrivee_cp'] : 'Non connue',
      'Outre-mer ou étranger' => $lead['outre_mer'] ? 'Oui' : 'Non',
    ],
    'Logement actuel' => [
      'Type' => $val('logement'),
      'Nombre de pièces' => $val('pieces'),
      'Volume estimé' => $lead['volume'] !== '' ? $lead['volume'] . ' m³' : 'Non renseigné',
    ],
    'Précisions' => [
      'Message' => $lead['message'] !== '' ? $lead['message'] : '(aucun)',
    ],
    'Suivi' => [
      'Date de la demande' => date('d/m/Y à H:i', strtotime($lead['date_demande'])),
      'Provenance' => trim(implode(' / ', array_filter([$lead['utm_source'], $lead['utm_medium'], $lead['utm_campaign']]))) ?: ($lead['referrer'] ?: 'Accès direct'),
      'Consentement' => 'Oui : « ' . $lead['consentement_texte'] . ' »',
    ],
  ];
}

function envoyerEmail(array $lead, array $cfg): bool {
  $sections = lignesEmail($lead);
  $h = fn($t) => htmlspecialchars((string)$t, ENT_QUOTES, 'UTF-8');

  $texte = "Nouvelle demande de devis reçue sur {$cfg['nom_site']}\n\n";
  $html = '<div style="font-family:Arial,sans-serif;font-size:15px;color:#141B2D;max-width:640px">'
        . '<h2 style="color:#2348C0;margin:0 0 4px">Nouvelle demande de devis</h2>'
        . '<p style="margin:0 0 16px;color:#4A5368">Reçue sur ' . $h($cfg['nom_site']) . '. Répondre à cet e-mail écrit directement au demandeur.</p>';
  foreach ($sections as $titre => $champs) {
    $texte .= mb_strtoupper($titre) . "\n";
    $html .= '<h3 style="margin:18px 0 6px;font-size:15px;color:#2348C0">' . $h($titre) . '</h3><table style="border-collapse:collapse;width:100%">';
    foreach ($champs as $k => $v) {
      $texte .= "  $k : $v\n";
      $valeur = $k === 'Téléphone' ? '<a href="tel:' . $h(preg_replace('/[^0-9+]/', '', $v)) . '">' . $h($v) . '</a>'
              : ($k === 'E-mail' ? '<a href="mailto:' . $h($v) . '">' . $h($v) . '</a>' : nl2br($h($v)));
      $html .= '<tr><td style="padding:6px 10px;border-bottom:1px solid #D9DEEA;color:#4A5368;width:40%;vertical-align:top">' . $h($k)
             . '</td><td style="padding:6px 10px;border-bottom:1px solid #D9DEEA;font-weight:bold">' . $valeur . '</td></tr>';
    }
    $texte .= "\n";
    $html .= '</table>';
  }
  $html .= '</div>';

  $nom = trim($lead['prenom'] . ' ' . $lead['nom']);
  $sujet = 'Demande de devis : ' . $nom . ' (' . $lead['depart_cp'] . ($lead['arrivee_cp'] !== '' ? ' vers ' . $lead['arrivee_cp'] : '') . ', ' . moisFr($lead['date_souhaitee']) . ')';
  $frontiere = 'plg_' . bin2hex(random_bytes(8));
  $entetes = implode("\r\n", [
    'From: =?UTF-8?B?' . base64_encode($cfg['nom_expediteur']) . '?= <' . $cfg['email_expediteur'] . '>',
    'Reply-To: =?UTF-8?B?' . base64_encode($nom) . '?= <' . $lead['email'] . '>',
    'MIME-Version: 1.0',
    'Content-Type: multipart/alternative; boundary="' . $frontiere . '"',
  ]);
  $corps = "--$frontiere\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
         . chunk_split(base64_encode($texte))
         . "--$frontiere\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
         . chunk_split(base64_encode($html))
         . "--$frontiere--";

  return @mail($cfg['email_destinataire'], '=?UTF-8?B?' . base64_encode($sujet) . '?=', $corps, $entetes, '-f' . $cfg['email_expediteur']);
}

function dossierPrive(array $cfg): string {
  $d = $cfg['dossier_prive'];
  if (!is_dir($d)) @mkdir($d, 0700, true);
  return $d;
}

/** Copie de sécurité de chaque demande, purgée automatiquement au-delà de 30 jours. */
function sauvegarderCopie(array $lead, array $cfg): void {
  $fichier = dossierPrive($cfg) . '/demandes-30-jours.jsonl';
  $limite = time() - 30 * 86400;
  $lignes = is_file($fichier) ? file($fichier, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
  $gardees = array_filter($lignes, function ($l) use ($limite) {
    $d = json_decode($l, true);
    return $d && strtotime($d['date_demande'] ?? '') >= $limite;
  });
  $gardees[] = json_encode($lead, JSON_UNESCAPED_UNICODE);
  @file_put_contents($fichier, implode(PHP_EOL, $gardees) . PHP_EOL, LOCK_EX);
}

function journaliserEchec(array $lead, array $cfg): void {
  $ligne = date('c') . " : échec d'envoi de l'e-mail pour la demande du {$lead['date_demande']} (voir demandes-30-jours.jsonl)" . PHP_EOL;
  @file_put_contents(dossierPrive($cfg) . '/envois-echoues.log', $ligne, FILE_APPEND | LOCK_EX);
}

function limiteFrequence(array $cfg): bool {
  $dossier = dossierPrive($cfg) . '/frequence';
  if (!is_dir($dossier)) @mkdir($dossier, 0700, true);
  $cle = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . $cfg['sel']);
  $fichier = "$dossier/$cle";
  $maintenant = time();
  $horodatages = is_file($fichier) ? array_filter(array_map('intval', file($fichier)), fn($t) => $t > $maintenant - 3600) : [];
  if (count($horodatages) >= $cfg['max_par_heure']) return false;
  $horodatages[] = $maintenant;
  @file_put_contents($fichier, implode(PHP_EOL, $horodatages), LOCK_EX);
  return true;
}

function repondre(int $code, bool $ok, string $message = '', string $cleErreur = 'envoi', array $champs = []): never {
  global $veutJson;
  http_response_code($code);
  if ($veutJson ?? false) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => $ok, 'message' => $message, 'champs' => $champs], JSON_UNESCAPED_UNICODE);
  } else {
    // Navigateur sans JavaScript : redirection classique.
    header('Location: ' . ($ok ? '/merci/' : '/demande-de-devis/?erreur=' . $cleErreur), true, 303);
  }
  exit;
}
