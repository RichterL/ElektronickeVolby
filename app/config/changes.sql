ALTER TABLE `question`
DROP `multiple`,
ADD `min` tinyint(2) NOT NULL DEFAULT '1',
ADD `max` tinyint(2) NOT NULL DEFAULT '1' AFTER `min`;