// Menu principal. Le déménagement est mis en avant en première position, avec un sous-menu.
export const demenagement = {
  href: '/demenagement/',
  label: 'Déménagement',
  sous: [
    { href: '/demenagement/', label: 'Le guide du déménagement', texte: 'Volume, devis, cartons, jour J : toutes les étapes' },
    { href: '/prise-en-charge-demenagement/', label: 'La prise en charge', texte: 'Plateforme multi-déménageurs ou déménageur au choix' },
    { href: '/articles/rubrique/demenagement/', label: 'Conseils déménagement', texte: 'Lettre de voiture, saison des mutations, astuces' },
    { href: '/demande-de-devis/', label: 'Demander un devis', texte: 'Gratuit et sans engagement, en deux minutes' },
  ],
};
export const guides = [
  { href: '/guide-mutation/', label: 'Mutation', titre: 'Les étapes du déménagement, de l’annonce à l’installation', court: 'Mutation' },
  { href: '/logement/', label: 'Logement', titre: 'Quitter son logement et s’installer dans le nouveau', court: 'Logement' },
  { href: '/prise-en-charge-demenagement/', label: 'Prise en charge', titre: 'PFMD ou déménageur de votre choix : comment ça marche', court: 'Prise en charge' },
  { href: '/mutation-en-famille/', label: 'En famille', titre: 'Conjoint, enfants, démarches : muter en famille', court: 'En famille' },
];
export const menu = [
  { href: '/guide-mutation/', label: 'Mutation' },
  { href: '/logement/', label: 'Logement' },
  { href: '/mutation-en-famille/', label: 'En famille' },
  { href: '/articles/', label: 'Articles' },
];
