-- Adminer 4.8.0 MySQL 5.5.5-10.4.18-MariaDB-1:10.4.18+maria~focal-log dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

CREATE DATABASE `volby` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `volby`;

CREATE TABLE `acl_resource` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `key` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
   `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
   `parent` int(11) DEFAULT NULL,
   PRIMARY KEY (`id`),
   KEY `parent` (`parent`),
   CONSTRAINT `acl_resource_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `acl_resource` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `acl_resource_privilege` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `resource_id` int(11) NOT NULL,
     `key` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
     `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
     PRIMARY KEY (`id`),
     KEY `resource_id` (`resource_id`),
     CONSTRAINT `acl_resource_privilege_ibfk_1` FOREIGN KEY (`resource_id`) REFERENCES `acl_resource` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `acl_role` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `key` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
   `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
   `parent` int(11) DEFAULT NULL,
   PRIMARY KEY (`id`),
   KEY `parent` (`parent`),
   CONSTRAINT `acl_role_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `acl_role` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `acl_rule` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `role_id` int(11) NOT NULL,
   `resource_id` int(11) NOT NULL,
   `privilege_id` int(11) NOT NULL,
   `type` tinyint(1) NOT NULL,
   PRIMARY KEY (`id`),
   KEY `role_id` (`role_id`),
   KEY `resource_id` (`resource_id`),
   KEY `privilege_id` (`privilege_id`),
   CONSTRAINT `acl_rule_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `acl_role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
   CONSTRAINT `acl_rule_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `acl_resource` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
   CONSTRAINT `acl_rule_ibfk_3` FOREIGN KEY (`privilege_id`) REFERENCES `acl_resource_privilege` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `answer` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `question_id` int(11) NOT NULL,
   `value` varchar(100) NOT NULL,
   PRIMARY KEY (`id`),
   KEY `answer_FK` (`question_id`),
   CONSTRAINT `answer_FK` FOREIGN KEY (`question_id`) REFERENCES `question` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `election` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `title` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
   `description` text COLLATE utf8_unicode_ci DEFAULT NULL,
   `active` tinyint(1) DEFAULT 0,
   `secret` tinyint(1) DEFAULT 0,
   `start` datetime DEFAULT NULL,
   `end` datetime DEFAULT NULL,
   `created_at` datetime DEFAULT current_timestamp(),
   `created_by` int(11) DEFAULT NULL,
   PRIMARY KEY (`id`),
   KEY `created_by` (`created_by`),
   CONSTRAINT `election_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `question` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `election_id` int(11) NOT NULL,
   `name` varchar(100) NOT NULL,
   `question` varchar(500) NOT NULL,
   `required` tinyint(1) DEFAULT 0,
   `multiple` tinyint(1) DEFAULT 0,
   PRIMARY KEY (`id`),
   KEY `election_id` (`election_id`),
   CONSTRAINT `question_ibfk_1` FOREIGN KEY (`election_id`) REFERENCES `election` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `user` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `username` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
   `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
   `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
   `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
   `surname` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
   `full_name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `user_roles` (
   `user_id` int(11) NOT NULL,
   `role_id` int(11) NOT NULL,
   KEY `FK_users_roles_acl_roles` (`role_id`),
   KEY `FK_users_roles_users` (`user_id`),
   CONSTRAINT `user_roles_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
   CONSTRAINT `user_roles_ibfk_6` FOREIGN KEY (`role_id`) REFERENCES `acl_role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `vote` (
   `id_vote` int(11) NOT NULL,
   `id_election` int(11) NOT NULL,
   `value` varchar(500) NOT NULL,
   KEY `id_election` (`id_election`),
   CONSTRAINT `vote_ibfk_1` FOREIGN KEY (`id_election`) REFERENCES `election` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `voter` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `election_id` int(11) NOT NULL,
   `email` varchar(100) NOT NULL,
   `timestamp` datetime DEFAULT NULL,
   `voted` tinyint(1) GENERATED ALWAYS AS (if(ifnull(`timestamp`,0),1,0)) STORED,
   PRIMARY KEY (`id`),
   KEY `election_id` (`election_id`),
   CONSTRAINT `voter_ibfk_1` FOREIGN KEY (`election_id`) REFERENCES `election` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `voter_file` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `election_id` int(11) NOT NULL,
   `filename` varchar(100) NOT NULL,
   `content` mediumblob NOT NULL,
   `created_at` datetime NOT NULL,
   `created_by` int(11) NOT NULL,
   PRIMARY KEY (`id`),
   KEY `election_id` (`election_id`),
   CONSTRAINT `voter_file_ibfk_1` FOREIGN KEY (`election_id`) REFERENCES `election` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2021-04-07 19:41:46