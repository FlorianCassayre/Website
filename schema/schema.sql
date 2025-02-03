SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `contact` (
  `id` int NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `name` text NOT NULL,
  `email` text NOT NULL,
  `message` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `enchanting_combinations` (
  `id` int NOT NULL,
  `enchantments` binary(13) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `enchanting_stats` (
  `item` tinyint NOT NULL,
  `levels` tinyint NOT NULL,
  `combination` int NOT NULL,
  `frequency` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `log_access` (
  `id` int NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `site` varchar(16) NOT NULL,
  `method` varchar(8) NOT NULL,
  `url` text NOT NULL,
  `code` int NOT NULL,
  `ipv4` varchar(48) NOT NULL,
  `ipv6` varchar(45) DEFAULT NULL,
  `session` varchar(128) NOT NULL,
  `user_agent` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

CREATE TABLE `log_errors` (
  `id` int NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `site` varchar(16) NOT NULL,
  `method` varchar(8) NOT NULL,
  `url` text NOT NULL,
  `code` int NOT NULL,
  `stacktrace` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

CREATE TABLE `minecraft_heads` (
  `uuid` binary(16) NOT NULL,
  `layer_head` binary(192) NOT NULL,
  `layer_hat` binary(192) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `nfc` (
  `key` varchar(32) NOT NULL,
  `value` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `publibike_bikes` (
  `id` int NOT NULL,
  `name` varchar(16) NOT NULL,
  `type_id` int NOT NULL,
  `type_name` varchar(16) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `publibike_events` (
  `id` int NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `station_id` int NOT NULL,
  `bikes` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

CREATE TABLE `publibike_stations` (
  `id` int NOT NULL,
  `name` varchar(128) NOT NULL,
  `address` varchar(128) NOT NULL,
  `zip` int NOT NULL,
  `city` varchar(128) NOT NULL,
  `latitude` varchar(16) NOT NULL,
  `longitude` varchar(16) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `teachers` (
  `id` int NOT NULL,
  `title` varchar(16) NOT NULL,
  `last_name` varchar(64) NOT NULL,
  `first_name` varchar(64) NOT NULL,
  `subject` varchar(128) NOT NULL,
  `name_original` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `teachers_missing` (
  `teacher_id` int NOT NULL,
  `date` date NOT NULL,
  `is_morning` tinyint(1) NOT NULL,
  `is_afternoon` tinyint(1) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ztracker_events` (
  `id` int NOT NULL,
  `player_id` int NOT NULL,
  `world_id` int NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `x` int NOT NULL,
  `y` int NOT NULL,
  `z` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=FIXED;

CREATE TABLE `ztracker_players` (
  `id` int NOT NULL,
  `uuid` binary(16) NOT NULL,
  `last_name` varchar(16) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

CREATE TABLE `ztracker_worlds` (
  `id` int NOT NULL,
  `name` varchar(32) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;


ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `enchanting_combinations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `enchantments` (`enchantments`);

ALTER TABLE `enchanting_stats`
  ADD UNIQUE KEY `unique_index` (`item`,`levels`,`combination`);

ALTER TABLE `log_access`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `log_errors`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `minecraft_heads`
  ADD PRIMARY KEY (`uuid`);

ALTER TABLE `nfc`
  ADD PRIMARY KEY (`key`);

ALTER TABLE `publibike_bikes`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `publibike_events`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `publibike_stations`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `teachers_missing`
  ADD UNIQUE KEY `teacher_id` (`teacher_id`,`date`);

ALTER TABLE `ztracker_events`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ztracker_players`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ztracker_worlds`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `contact`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `enchanting_combinations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `log_access`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `log_errors`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `publibike_events`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `teachers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `ztracker_events`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `ztracker_players`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `ztracker_worlds`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
