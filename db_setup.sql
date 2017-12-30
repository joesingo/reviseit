USE reviseit;

CREATE TABLE `games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `min_terms` int(11) DEFAULT NULL,
  `save_scores` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `privacy_link` (
  `setid` int(11) DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  UNIQUE KEY `privacy_link_uk` (`setid`,`userid`)
);

CREATE TABLE `scores` (
  `userid` int(11) DEFAULT NULL,
  `setid` int(11) DEFAULT NULL,
  `gameid` int(11) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `date` datetime DEFAULT NULL
);

CREATE TABLE `sessions` (
  `userid` int(11) DEFAULT NULL,
  `time` varchar(255) DEFAULT NULL,
  UNIQUE KEY `userid` (`userid`)
);

CREATE TABLE `sets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT NULL,
  `privacy` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `edited` datetime DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_index` (`userid`,`name`)
);

CREATE TABLE `tag_link` (
  `setid` int(11) DEFAULT NULL,
  `tagid` int(11) DEFAULT NULL
);

CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `terms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setid` int(11) DEFAULT NULL,
  `term` varchar(255) DEFAULT NULL,
  `def` text,
  PRIMARY KEY (`id`)
);

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
);

INSERT INTO `games` VALUES
  (1,'Flashcards','flashcards',1,0),
  (2,'Typing game','typing',1,1),
  (3,'Wordsearch','wordsearch',1,0),
  (4,'Multiple choice quiz','quiz',4,1),
  (5,'Snake','snake',4,1),
  (6,'Quadrant','quadrant',4,1),
  (7,'Shapes','shapes',1,1);

INSERT INTO `tags` VALUES
  (1,'Maths'),
  (2,'Computing'),
  (3,'Biology'),
  (4,'Chemistry'),
  (5,'Physics'),
  (6,'Music'),
  (7,'Economics'),
  (8,'Psychology'),
  (9,'Business'),
  (10,'Sociology'),
  (11,'English'),
  (12,'French'),
  (13,'German'),
  (14,'Spanish'),
  (15,'Gegraphy'),
  (16,'History'),
  (17,'Religious studies'),
  (18,'Philosophy'),
  (19,'Electronics'),
  (20,'Drama'),
  (21,'Law'),
  (22,'Music technology'),
  (23,'P.E'),
  (24,'Art'),
  (25,'IT');
