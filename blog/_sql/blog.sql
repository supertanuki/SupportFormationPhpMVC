-- phpMyAdmin SQL Dump
-- version 3.3.9.1
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Ven 12 Octobre 2012 à 16:50
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

-- --------------------------------------------------------

--
-- Structure de la table `commentaires`
--

DROP TABLE IF EXISTS `commentaires`;
CREATE TABLE IF NOT EXISTS `commentaires` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auteur` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `date_message` datetime NOT NULL,
  `billet_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `billet_id` (`billet_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

--
-- Contenu de la table `commentaires`
--

INSERT INTO `commentaires` (`id`, `auteur`, `message`, `date_message`, `billet_id`) VALUES
(1, 'Jacquot', 'Mangez des pommes', '2012-10-12 10:55:19', 1),
(2, 'Szgolne', 'On m''a pris ma place !!!\r\net je fais des sauts de ligne\r\noui...', '2012-10-10 10:00:00', 1),
(3, 'Tony', 'Bonjour !', '2012-10-12 14:49:36', 1),
(4, 'test', 'test', '2012-10-12 15:04:22', 1),
(5, 'test', 'test', '2012-10-12 15:06:03', 1),
(6, 'bfkjsdhj', 'jkhjkhjkhsd', '2012-10-12 15:06:28', 1),
(7, 'jean', 'jean', '2012-10-12 15:07:43', 1),
(8, 'Jean', 'Hello\r\n<h1>j''ai essayé de mettre du html</h1>', '2012-10-12 15:08:42', 3),
(9, 'Sharko', 'Hello la france !!!', '2012-10-12 15:27:16', 5),
(10, 'Jean', 'hello !', '2012-10-12 15:31:33', 5),
(11, 'Ségolène', 'Bonjour !!!', '2012-10-12 15:35:40', 5),
(12, 'Ségolène', 'Bonjour !!!', '2012-10-12 15:36:22', 5),
(13, 'hello', 'hello', '2012-10-12 15:36:29', 5);
