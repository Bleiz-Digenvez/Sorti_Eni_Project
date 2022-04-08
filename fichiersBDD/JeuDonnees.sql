-- Merci d'éxecuter ce script dans la table sorti_db
-- Après la création des tables
-- N'hésitez pas à vider les tables de leurs données


-- Jeu de données pour les état !!OBLIGATOIRE
INSERT INTO `etat` (`id`, `libelle`) VALUES
(1, 'Créée'),
(2, 'Ouverte'),
(3, 'Clôturée'),
(4, 'Activité en cours'),
(5, 'Passée'),
(6, 'Annulée');

-- Jeu de données pour les villes
INSERT INTO `ville` (`id`, `nom`, `code_postal`) VALUES
(1, 'Saint Herblain', '44800'),
(2, 'Herblay', '95220'),
(3, 'Cherbourg', '44410');


--  Jeu de données pour les campus 
INSERT INTO `campus` (`id`, `nom`) VALUES
(2, 'Chartres de Bretagne'),
(3, 'La Roche sur Yon'),
(1, 'Saint Herblain');


-- Jeu de donnés pour les lieux (Avec de véritable coordonnées pour le test des cartes)
INSERT INTO `lieu` (`id`, `ville_id`, `nom`, `rue`, `latitude`, `longitude`) VALUES
(1, 1, 'Piscine de la Bourgonnière', '34 All. de la Bourgonnière', 47.207670484810315, -1.6568902138830155),
(2, 2, 'Stade Beauregard', '135 Rte de Pierrelaye', 49.00418529304927, 2.15706964021917),
(3, 3, 'Musée de la Libération', 'Mnt des Résistants', 49.63142510160654, -1.6133076088375082);