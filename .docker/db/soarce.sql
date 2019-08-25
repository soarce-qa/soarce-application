-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.6.44 - MySQL Community Server (GPL)
-- Server OS:                    Linux
-- HeidiSQL Version:             10.2.0.5626
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

use soarce;

-- Dumping structure for table soarce.application
CREATE TABLE IF NOT EXISTS `application` (
                                             `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
                                             `name` varchar(63) DEFAULT NULL,
                                             PRIMARY KEY (`id`),
                                             UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table soarce.coverage
CREATE TABLE IF NOT EXISTS `coverage` (
                            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                            `file_id` INT(10) UNSIGNED NOT NULL,
                            `request_id` MEDIUMINT(8) UNSIGNED NOT NULL,
                            `line` MEDIUMINT(8) UNSIGNED NOT NULL,
                            `covered` TINYINT(4) NOT NULL DEFAULT '0',
                            PRIMARY KEY (`id`),
                            UNIQUE INDEX `file_id_line` (`file_id`, `request_id`, `line`),
                            INDEX `line` (`line`),
                            INDEX `request` (`request_id`),
                            INDEX `file_line_request_covered` (`file_id`, `request_id`, `line`, `covered`),
                            INDEX `file_line_covered` (`file_id`, `line`, `covered`),
                            CONSTRAINT `file` FOREIGN KEY (`file_id`) REFERENCES `file` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
                            CONSTRAINT `request` FOREIGN KEY (`request_id`) REFERENCES `request` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table soarce.dump
CREATE TABLE IF NOT EXISTS `dump` (
                                      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                      `raw` mediumtext NOT NULL,
                                      `header` mediumtext NOT NULL,
                                      `payload` mediumtext NOT NULL,
                                      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table soarce.file
CREATE TABLE `file` (
                        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `application_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
                        `filename` VARCHAR(510) NULL DEFAULT NULL,
                        `md5` BINARY(16) NULL DEFAULT NULL,
                        `lines` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
                        PRIMARY KEY (`id`),
                        UNIQUE INDEX `application_id_filename` (`application_id`, `filename`),
                        INDEX `filename` (`filename`),
                        CONSTRAINT `application` FOREIGN KEY (`application_id`) REFERENCES `application` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)   COLLATE='utf8mb4_general_ci' ENGINE=InnoDB ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1;


-- Data exporting was unselected.

-- Dumping structure for table soarce.function_call
CREATE TABLE IF NOT EXISTS `function_call` (
                                               `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                               `file_id` int(10) unsigned NOT NULL,
                                               `request_id` mediumint(8) unsigned NOT NULL,
                                               `class` varchar(382) DEFAULT NULL,
                                               `function` varchar(382) NOT NULL,
                                               `type` enum('internal','user-defined') NOT NULL,
                                               `calls` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                                               `walltime` FLOAT UNSIGNED NOT NULL DEFAULT '0',
                                               PRIMARY KEY (`id`),
                                               UNIQUE KEY `file_id_class_function` (`file_id`,`request_id`,`class`,`function`),
                                               KEY `fi__files` (`file_id`),
                                               KEY `FK_function_call_request` (`request_id`),
                                               CONSTRAINT `FK__files` FOREIGN KEY (`file_id`) REFERENCES `file` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                                               CONSTRAINT `FK_function_call_request` FOREIGN KEY (`request_id`) REFERENCES `request` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table soarce.request
CREATE TABLE IF NOT EXISTS `request` (
                                         `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                         `usecase_id` mediumint(8) unsigned NOT NULL,
                                         `application_id` smallint(5) unsigned NOT NULL,
                                         `request_id` varchar(510) NOT NULL,
                                         `request_started` double unsigned NOT NULL DEFAULT '0',
                                         `get` mediumtext,
                                         `post` mediumtext,
                                         `server` mediumtext,
                                         `env` mediumtext,
                                         PRIMARY KEY (`id`),
                                         UNIQUE KEY `request_id` (`request_id`),
                                         KEY `fi_case` (`usecase_id`),
                                         KEY `application_id` (`application_id`),
                                         CONSTRAINT `FK_requests_application` FOREIGN KEY (`application_id`) REFERENCES `application` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                                         CONSTRAINT `usecase` FOREIGN KEY (`usecase_id`) REFERENCES `usecase` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table soarce.usecase
CREATE TABLE IF NOT EXISTS `usecase` (
                                         `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                         `name` varchar(63) NOT NULL,
                                         `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                         `active` tinyint(1) unsigned DEFAULT NULL,
                                         PRIMARY KEY (`id`),
                                         UNIQUE KEY `name` (`name`),
                                         UNIQUE KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
