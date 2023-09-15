CREATE TABLE [yii_rbac_item] (
  [name] nvarchar(128) NOT NULL PRIMARY KEY,
  [type] nvarchar(10) NOT NULL,
  [description] nvarchar(191),
  [ruleName] nvarchar(64),
  [createdAt] int NOT NULL,
  [updatedAt] int NOT NULL
);
CREATE INDEX [idx-yii_rbac_item-type] ON [yii_rbac_item] ([type]);
CREATE TABLE [yii_rbac_item_child] (
  [parent] nvarchar(128) NOT NULL,
  [child] nvarchar(128) NOT NULL,
  PRIMARY KEY ([parent], [child]),
  FOREIGN KEY ([parent]) REFERENCES [yii_rbac_item] ([name]),
  FOREIGN KEY ([child]) REFERENCES [yii_rbac_item] ([name])
);
CREATE TABLE [yii_rbac_assignment] (
  [itemName] nvarchar(128) NOT NULL,
  [userId] nvarchar(128) NOT NULL,
  [createdAt] int NOT NULL,
  PRIMARY KEY ([itemName], [userId])
);
