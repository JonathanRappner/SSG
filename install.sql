-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Värd: 127.0.0.1
-- Tid vid skapande: 15 jan 2019 kl 12:54
-- Serverversion: 10.1.31-MariaDB
-- PHP-version: 7.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databas: `ssg`
--

-- --------------------------------------------------------

--
-- Tabellstruktur `ssg_events`
--

CREATE TABLE `ssg_events` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci NOT NULL,
  `author` mediumint(8) UNSIGNED DEFAULT NULL COMMENT 'Skapare i ssg_members',
  `start_datetime` datetime NOT NULL,
  `length_time` time NOT NULL,
  `type_id` tinyint(3) UNSIGNED NOT NULL,
  `forum_link` text,
  `preview_image` text,
  `archived` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Förflutna events som inte är arkiverade blir processade och arkiverade automatiskt.'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `ssg_event_types`
--

CREATE TABLE `ssg_event_types` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `title` text NOT NULL,
  `obligatory` tinyint(1) NOT NULL COMMENT 'Ej frivillig'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Event-typer (OP, Träning, TvT, osv.)';

-- --------------------------------------------------------

--
-- Tabellstruktur `ssg_groups`
--

CREATE TABLE `ssg_groups` (
  `id` tinyint(4) UNSIGNED NOT NULL,
  `name` text NOT NULL COMMENT 'Gruppens fulla namn',
  `code` varchar(6) DEFAULT NULL COMMENT 'Gruppens förkortning',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Är gruppen aktiv?',
  `dummy` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Ingen riktig grupp. Bara ett alternativ vid signup.',
  `sorting` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Spelargrupper (FA, GA, TL osv.)';

-- --------------------------------------------------------

--
-- Tabellstruktur `ssg_intervals`
--

CREATE TABLE `ssg_intervals` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `interval_length_days` smallint(5) UNSIGNED NOT NULL,
  `last_performed_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Hanterar tid för php-kod som körs med jämna intervaller';

-- --------------------------------------------------------

--
-- Tabellstruktur `ssg_members`
--

CREATE TABLE `ssg_members` (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT 'Samma id som i smf_members',
  `name` text NOT NULL,
  `rank_id` smallint(5) UNSIGNED DEFAULT NULL,
  `group_id` tinyint(4) UNSIGNED DEFAULT NULL,
  `role_id` smallint(5) UNSIGNED DEFAULT NULL,
  `registered_date` date DEFAULT NULL COMMENT 'Datum då medlemmen registrerade sig.',
  `uid` varchar(20) DEFAULT NULL COMMENT 'Arma 3 UID',
  `is_active` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Extra SSG-medlemsdata. Huvuddatan ligger i smf_members';

-- --------------------------------------------------------

--
-- Tabellstruktur `ssg_permission_groups`
--

CREATE TABLE `ssg_permission_groups` (
  `id` smallint(6) UNSIGNED NOT NULL,
  `code` varchar(10) NOT NULL COMMENT 'Kod som används för att referera till grupperna i php.',
  `title` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Rättighetsgrupper (S1, Gruppchefer, Super Admin osv.)';

-- --------------------------------------------------------

--
-- Tabellstruktur `ssg_permission_groups_members`
--

CREATE TABLE `ssg_permission_groups_members` (
  `member_id` mediumint(9) UNSIGNED NOT NULL,
  `permission_group_id` smallint(6) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Koppling mellan ssg_permission_groups och ssg_members';

-- --------------------------------------------------------

--
-- Tabellstruktur `ssg_promotions`
--

CREATE TABLE `ssg_promotions` (
  `id` int(11) NOT NULL,
  `member_id` mediumint(8) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `from_rank` smallint(5) UNSIGNED DEFAULT NULL,
  `to_rank` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Tillfällen då medlemmars ranker ändrades';

-- --------------------------------------------------------

--
-- Tabellstruktur `ssg_ranks`
--

CREATE TABLE `ssg_ranks` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `icon` text NOT NULL,
  `sorting` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Ranker (Menig kl. 1, Fänrik osv.)';

-- --------------------------------------------------------

--
-- Tabellstruktur `ssg_recesses`
--

CREATE TABLE `ssg_recesses` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `length_days` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Uppehållsperioder där automatiska events inte skapas';

-- --------------------------------------------------------

--
-- Tabellstruktur `ssg_roles`
--

CREATE TABLE `ssg_roles` (
  `id` smallint(6) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `name_long` text,
  `dummy` tinyint(1) NOT NULL DEFAULT '0',
  `sorting` smallint(5) UNSIGNED NOT NULL COMMENT 'Sortering'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Befattningar/Roller (Gruppchef, KSP, osv.)';

-- --------------------------------------------------------

--
-- Tabellstruktur `ssg_roles_groups`
--

CREATE TABLE `ssg_roles_groups` (
  `role_id` smallint(5) UNSIGNED NOT NULL,
  `group_id` tinyint(4) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Koppling mellan ssg_roles och ssg_groups';

-- --------------------------------------------------------

--
-- Tabellstruktur `ssg_sessions`
--

CREATE TABLE `ssg_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `data` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `ssg_signups`
--

CREATE TABLE `ssg_signups` (
  `event_id` smallint(5) UNSIGNED NOT NULL,
  `member_id` mediumint(8) UNSIGNED NOT NULL,
  `group_id` tinyint(4) UNSIGNED NOT NULL,
  `role_id` smallint(6) UNSIGNED NOT NULL,
  `attendance` enum('Ja','JIP','QIP','NOSHOW','Ej anmäld','Oanmäld frånvaro') NOT NULL,
  `signed_datetime` datetime NOT NULL,
  `last_changed_datetime` datetime NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Index för dumpade tabeller
--

--
-- Index för tabell `ssg_events`
--
ALTER TABLE `ssg_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author` (`author`),
  ADD KEY `type_id` (`type_id`);

--
-- Index för tabell `ssg_event_types`
--
ALTER TABLE `ssg_event_types`
  ADD PRIMARY KEY (`id`);

--
-- Index för tabell `ssg_groups`
--
ALTER TABLE `ssg_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sorting` (`sorting`);

--
-- Index för tabell `ssg_intervals`
--
ALTER TABLE `ssg_intervals`
  ADD PRIMARY KEY (`id`);

--
-- Index för tabell `ssg_members`
--
ALTER TABLE `ssg_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ssg_members_ibfk_4` (`group_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `rank_id` (`rank_id`);

--
-- Index för tabell `ssg_permission_groups`
--
ALTER TABLE `ssg_permission_groups`
  ADD PRIMARY KEY (`id`);

--
-- Index för tabell `ssg_permission_groups_members`
--
ALTER TABLE `ssg_permission_groups_members`
  ADD PRIMARY KEY (`member_id`,`permission_group_id`),
  ADD KEY `permission_group_id` (`permission_group_id`);

--
-- Index för tabell `ssg_promotions`
--
ALTER TABLE `ssg_promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ssg_promotion_events_ibfk_1` (`member_id`),
  ADD KEY `from_rank` (`from_rank`),
  ADD KEY `to_rank` (`to_rank`);

--
-- Index för tabell `ssg_ranks`
--
ALTER TABLE `ssg_ranks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sorting` (`sorting`);

--
-- Index för tabell `ssg_recesses`
--
ALTER TABLE `ssg_recesses`
  ADD PRIMARY KEY (`id`);

--
-- Index för tabell `ssg_roles`
--
ALTER TABLE `ssg_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sorting` (`sorting`);

--
-- Index för tabell `ssg_roles_groups`
--
ALTER TABLE `ssg_roles_groups`
  ADD PRIMARY KEY (`role_id`,`group_id`),
  ADD KEY `ssg_roles_groups_ibfk_2` (`group_id`);

--
-- Index för tabell `ssg_sessions`
--
ALTER TABLE `ssg_sessions`
  ADD KEY `ci_sessions_timestamp` (`timestamp`);

--
-- Index för tabell `ssg_signups`
--
ALTER TABLE `ssg_signups`
  ADD PRIMARY KEY (`event_id`,`member_id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT för dumpade tabeller
--

--
-- AUTO_INCREMENT för tabell `ssg_events`
--
ALTER TABLE `ssg_events`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT för tabell `ssg_event_types`
--
ALTER TABLE `ssg_event_types`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT för tabell `ssg_groups`
--
ALTER TABLE `ssg_groups`
  MODIFY `id` tinyint(4) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT för tabell `ssg_intervals`
--
ALTER TABLE `ssg_intervals`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT för tabell `ssg_permission_groups`
--
ALTER TABLE `ssg_permission_groups`
  MODIFY `id` smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT för tabell `ssg_ranks`
--
ALTER TABLE `ssg_ranks`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT för tabell `ssg_recesses`
--
ALTER TABLE `ssg_recesses`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT för tabell `ssg_roles`
--
ALTER TABLE `ssg_roles`
  MODIFY `id` smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restriktioner för dumpade tabeller
--

--
-- Restriktioner för tabell `ssg_events`
--
ALTER TABLE `ssg_events`
  ADD CONSTRAINT `ssg_events_ibfk_1` FOREIGN KEY (`author`) REFERENCES `ssg_members` (`id`),
  ADD CONSTRAINT `ssg_events_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `ssg_event_types` (`id`) ON UPDATE CASCADE;

--
-- Restriktioner för tabell `ssg_permission_groups_members`
--
ALTER TABLE `ssg_permission_groups_members`
  ADD CONSTRAINT `ssg_permission_groups_members_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `ssg_members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ssg_permission_groups_members_ibfk_2` FOREIGN KEY (`permission_group_id`) REFERENCES `ssg_permission_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restriktioner för tabell `ssg_roles_groups`
--
ALTER TABLE `ssg_roles_groups`
  ADD CONSTRAINT `ssg_roles_groups_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `ssg_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ssg_roles_groups_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `ssg_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restriktioner för tabell `ssg_signups`
--
ALTER TABLE `ssg_signups`
  ADD CONSTRAINT `ssg_signups_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `ssg_events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ssg_signups_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `ssg_members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ssg_signups_ibfk_3` FOREIGN KEY (`group_id`) REFERENCES `ssg_groups` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ssg_signups_ibfk_4` FOREIGN KEY (`role_id`) REFERENCES `ssg_roles` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
