-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.7.26-0ubuntu0.18.04.1-log - (Ubuntu)
-- Server OS:                    Linux
-- HeidiSQL Version:             10.2.0.5626
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

use soarce;

-- Dumping structure for table old_trace_analyzer.application
CREATE TABLE IF NOT EXISTS `application` (
                                             `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
                                             `name` varchar(63) DEFAULT NULL,
                                             PRIMARY KEY (`id`),
                                             UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table old_trace_analyzer.coverage
CREATE TABLE IF NOT EXISTS `coverage` (
                                          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                          `application_id` smallint(5) unsigned NOT NULL,
                                          `file_id` bigint(20) unsigned NOT NULL,
                                          `line` mediumint(8) unsigned NOT NULL,
                                          PRIMARY KEY (`id`),
                                          KEY `fi__files2` (`file_id`),
                                          KEY `application_id` (`application_id`),
                                          CONSTRAINT `FK__files2` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                                          CONSTRAINT `FK_application` FOREIGN KEY (`application_id`) REFERENCES `application` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table old_trace_analyzer.files
CREATE TABLE IF NOT EXISTS `files` (
                                       `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                       `application_id` smallint(5) unsigned NOT NULL,
                                       `request_id` int(10) unsigned NOT NULL,
                                       `filename` varchar(510) DEFAULT NULL,
                                       `md5` binary(16) NOT NULL,
                                       PRIMARY KEY (`id`),
                                       KEY `fi__requests` (`request_id`),
                                       KEY `application_id` (`application_id`),
                                       CONSTRAINT `FK__requests` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                                       CONSTRAINT `FK_application2` FOREIGN KEY (`application_id`) REFERENCES `application` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table old_trace_analyzer.function_calls
CREATE TABLE IF NOT EXISTS `function_calls` (
                                                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                                `file_id` bigint(20) unsigned NOT NULL DEFAULT '0',
                                                `class` varchar(510) DEFAULT NULL,
                                                `function` varchar(510) NOT NULL DEFAULT '0',
                                                `type` enum('internal','user-defined') NOT NULL,
                                                PRIMARY KEY (`id`),
                                                KEY `fi__files` (`file_id`),
                                                CONSTRAINT `FK__files` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table old_trace_analyzer.requests
CREATE TABLE `requests` (
                            `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                            `usecase_id` MEDIUMINT(8) UNSIGNED NOT NULL,
                            `application_id` SMALLINT(5) UNSIGNED NOT NULL,
                            `filename` VARCHAR(510) NOT NULL,
                            `request_started` DOUBLE UNSIGNED NOT NULL DEFAULT '0',
                            `get` MEDIUMTEXT NULL,
                            `post` MEDIUMTEXT NULL,
                            `server` MEDIUMTEXT NULL,
                            `env` MEDIUMTEXT NULL,
                            PRIMARY KEY (`id`),
                            INDEX `fi_case` (`usecase_id`),
                            INDEX `application_id` (`application_id`),
                            CONSTRAINT `FK_application3` FOREIGN KEY (`application_id`) REFERENCES `application` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
                            CONSTRAINT `usecase` FOREIGN KEY (`usecase_id`) REFERENCES `usecases` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
    COLLATE='utf8mb4_general_ci'
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table old_trace_analyzer.usecases
CREATE TABLE IF NOT EXISTS `usecases` (
                                          `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                                          `name` varchar(63) NOT NULL,
                                          `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                          PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

CREATE TABLE `dump` (
                        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `raw` MEDIUMTEXT NOT NULL,
                        `header` MEDIUMTEXT NOT NULL,
                        `payload` MEDIUMTEXT NOT NULL,
                        PRIMARY KEY (`id`)
)
    COLLATE='utf8mb4_general_ci'
    ENGINE=InnoDB;


/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
