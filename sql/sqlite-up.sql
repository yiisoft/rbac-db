CREATE TABLE `yii_rbac_item` (
  `name` varchar(128) NOT NULL PRIMARY KEY,
  `type` varchar(10) NOT NULL,
  `description` varchar(191),
  `ruleName` varchar(64),
  `createdAt` integer NOT NULL,
  `updatedAt` integer NOT NULL
);
CREATE INDEX `idx-yii_rbac_item-type` ON `yii_rbac_item` (`type`);
CREATE TABLE `yii_rbac_item_child` (
  `parent` varchar(128) NOT NULL,
  `child` varchar(128) NOT NULL,
  PRIMARY KEY (`parent`, `child`),
  FOREIGN KEY (`parent`) REFERENCES `yii_rbac_item` (`name`),
  FOREIGN KEY (`child`) REFERENCES `yii_rbac_item` (`name`)
);
CREATE TABLE `yii_rbac_assignment` (
  `itemName` varchar(128) NOT NULL,
  `userId` varchar(128) NOT NULL,
  `createdAt` integer NOT NULL,
  PRIMARY KEY (`itemName`, `userId`)
);
