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

-- Jeu de données pour les utilisateur, ici l'utilisateur Admin
INSERT INTO `participant` (`id`, `campus_id`, `pseudo`, `mot_passe`, `telephone`, `mail`, `administrateur`, `actif`, `nom`, `prenom`) VALUES
    (1, 1, 'admin', '$2y$13$Stn1E.QiL2ZYnJYSHxc6HOOQotfx0xuFdcXGlcJyXCtXNDSTDbeZi', '0102030405', 'admin@sortir.com', 1, 1, 'Sortir', 'Administrateur');

-- Jeu de données de sortie
INSERT INTO `sortie` (`id`, `campus_id`, `organisateur_id`, `etat_id`, `lieu_id`, `nom`, `date_heure_debut`, `duree`, `date_limite_inscription`, `nb_inscriptions_max`, `infos_sortie`) VALUES
                                                                                                                                                                                            (28, 1, 1, 1, 1, 'Après-Midi Piscine', '2022-04-24 14:00:00', 120, '2022-04-17 00:00:00', 12, 'Après-midi à la piscine de la Bourgonnière à Saint Herblain\r\nPrévoir maillot, serviette, et produit de toilette'),
                                                                                                                                                                                            (29, 1, 1, 2, 2, 'Foot entre classe', '2022-04-17 14:30:00', 180, '2022-04-14 00:00:00', 30, 'Tournois de foot entre les différentes classe du campus, au Stade Beauregard'),
                                                                                                                                                                                            (30, 1, 1, 3, 3, 'Visite de Musée', '2022-04-08 15:00:00', 180, '2022-04-12 00:00:00', 12, 'Visite du musée de la libération à Cherbourg, Voyage en bus jusquau musée');