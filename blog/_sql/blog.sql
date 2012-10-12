-- phpMyAdmin SQL Dump
-- version 3.3.9.1
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Jeu 11 Octobre 2012 à 17:12
-- Version du serveur: 5.5.9
-- Version de PHP: 5.3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `blog`
--

-- --------------------------------------------------------

--
-- Structure de la table `billets`
--

DROP TABLE IF EXISTS `billets`;
CREATE TABLE IF NOT EXISTS `billets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `contenu` text NOT NULL,
  `date_creation` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Contenu de la table `billets`
--

INSERT INTO `billets` (`id`, `titre`, `contenu`, `date_creation`) VALUES
(1, 'hello World', 'Ceci est mon 1er billet de blog\r\n\r\net je met des sauts de ligne !', '2012-10-11 14:32:25'),
(2, 'Mon ancien billet de blog', 'Bonjour, j''aime le gâteau au chocolat\r\n\r\nEt vous ?', '2012-10-01 14:31:40'),
(3, 'Encore un billet', 'Lorem ipsum', '2012-10-09 15:06:20'),
(4, 'Oh je sais pas quoi dire', 'Je vais arreter de bloguer', '2012-10-08 15:06:35'),
(5, 'Le 5ème élément', 'Ceci est un billet de blog', '2012-10-01 15:07:45');
