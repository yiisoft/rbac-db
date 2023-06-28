CREATE TABLE `auth_item` (
  `name` varchar(128) NOT NULL PRIMARY KEY,
  `type` varchar(10) NOT NULL,
  `description` varchar(191),
  `ruleName` varchar(64),
  `createdAt` int(11) NOT NULL,
  `updatedAt` int(11) NOT NULL
);
CREATE INDEX `idx-auth_item-type` ON `auth_item` (`type`);
CREATE TABLE `auth_item_child` (
  `parent` varchar(128) NOT NULL,
  `child` varchar(128) NOT NULL,
  PRIMARY KEY (`parent`, `child`),
  FOREIGN KEY (`parent`) REFERENCES `auth_item` (`name`),
  FOREIGN KEY (`child`) REFERENCES `auth_item` (`name`)
);
CREATE TABLE `auth_assignment` (
  `itemName` varchar(128) NOT NULL,
  `userId` varchar(128) NOT NULL,
  `createdAt` int(11) NOT NULL,
  PRIMARY KEY (`itemName`, `userId`),
  FOREIGN KEY (`itemName`) REFERENCES `auth_item` (`name`)
);
