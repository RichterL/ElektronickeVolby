-- Adminer 4.8.0 MySQL 5.5.5-10.4.18-MariaDB-1:10.4.18+maria~focal-log dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

INSERT INTO `acl_resource` (`key`, `name`, `parent`) VALUES
('users',	'Users',	NULL),
('roles',	'Roles',	NULL),
('resources',	'Resources',	NULL),
('elections',	'Elections',	NULL),
('rules',	'Rules',	NULL),
('results',	'Results',	NULL),
('questions',	'Questions',	NULL),
('answers',	'Answers',	NULL),
('voterList',	'Voter List',	NULL),
('voterFiles',	'Voter Files',	NULL);

INSERT INTO `acl_resource_privilege` (`resource_id`, `key`, `name`) VALUES
(1,	'view',	'View'),
(2,	'view',	'view'),
(2,	'edit',	'edit'),
(2,	'delete',	'delete'),
(1,	'edit',	'Edit'),
(1,	'delete',	'Delete'),
(1,	'import',	'Import'),
(3,	'view',	'View'),
(3,	'edit',	'Edit'),
(4,	'view',	'View'),
(4,	'edit',	'Edit'),
(4,	'delete',	'Delete'),
(4,	'activate',	'Activate'),
(7,	'view',	'View'),
(4,	'decrypt',	'Decrypt'),
(4,	'importKey',	'Import Key'),
(11,	'import',	'Import'),
(3,	'delete',	'Delete'),
(6,	'delete',	'Delete'),
(6,	'edit',	'Edit'),
(8,	'view',	'View'),
(8,	'edit',	'Edit'),
(8,	'delete',	'Delete'),
(9,	'view',	'View'),
(9,	'delete',	'Delete'),
(10,	'view',	'View'),
(11,	'view',	'View'),
(6,	'add',	'Add');

INSERT INTO `acl_role` (`id`, `key`, `name`, `parent`) VALUES
(1,	'student',	'Student',	NULL),
(2,	'employee',	'Zamestnanec',	NULL),
(3,	'superAdmin',	'superAdmin',	NULL);

INSERT INTO `user` (`id`, `username`, `email`, `password`, `name`, `surname`, `full_name`) VALUES
(1,	'admin',	'admin@volby.l',	'$2y$10$0ZXcrfYPeEOCg4QxSlRKDe2ZyFaaLh.xwADrV8Vj4OiXw9A1PPKtq',	'Administrator',	'Super',	NULL);

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(1,	3);