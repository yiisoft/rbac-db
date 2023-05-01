CREATE TABLE [auth_item] (
  [name] nvarchar(128) NOT NULL PRIMARY KEY,
  [type] nvarchar(10) NOT NULL,
  [description] nvarchar(191),
  [ruleName] nvarchar(64),
  [createdAt] int NOT NULL,
  [updatedAt] int NOT NULL
);
CREATE INDEX [idx-auth_item-type] ON [auth_item] ([type]);
CREATE TABLE [auth_item_child] (
  [parent] nvarchar(128) NOT NULL,
  [child] nvarchar(128) NOT NULL,
  PRIMARY KEY ([parent], [child]),
  FOREIGN KEY ([parent]) REFERENCES [auth_item] ([name]),
  FOREIGN KEY ([child]) REFERENCES [auth_item] ([name])
);
CREATE TABLE [auth_assignment] (
  [itemName] nvarchar(128) NOT NULL,
  [userId] nvarchar(128) NOT NULL,
  [createdAt] int NOT NULL,
  PRIMARY KEY ([itemName], [userId]),
  FOREIGN KEY ([itemName]) REFERENCES [auth_item] ([name])
);
