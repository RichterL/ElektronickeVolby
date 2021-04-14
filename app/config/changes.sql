ALTER TABLE `question`
DROP `multiple`,
ADD `min` tinyint(2) NOT NULL DEFAULT '1',
ADD `max` tinyint(2) NOT NULL DEFAULT '1' AFTER `min`;

ALTER TABLE `voter_file`
    CHANGE `content` `content` mediumblob NOT NULL AFTER `filename`;

DROP TABLE `vote`;

CREATE TABLE `ballot` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `election_id` int(11) NOT NULL,
    `encrypted_data` text NOT NULL,
    `encrypted_key` text NOT NULL,
    `hash` varchar(100) NOT NULL,
    `signature` text NOT NULL,
    `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `decrypted_data` text DEFAULT NULL,
    `decrypted_key` text DEFAULT NULL,
    `decrypted_at` datetime DEFAULT NULL,
    `decrypted_by` int(11) DEFAULT NULL,
    `counted_at` timestamp NULL DEFAULT NULL,
    `counted_by` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `election_id` (`election_id`),
    KEY `decrypted_by` (`decrypted_by`),
    KEY `counted_by` (`counted_by`),
    CONSTRAINT `ballot_ibfk_1` FOREIGN KEY (`election_id`) REFERENCES `election` (`id`) ON DELETE CASCADE,
    CONSTRAINT `ballot_ibfk_2` FOREIGN KEY (`decrypted_by`) REFERENCES `user` (`id`),
    CONSTRAINT `ballot_ibfk_3` FOREIGN KEY (`counted_by`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;