CREATE TABLE "auth_item" (
  "name" VARCHAR2(128) NOT NULL PRIMARY KEY,
  "type" VARCHAR2(10) NOT NULL,
  "description" VARCHAR2(191),
  "ruleName" VARCHAR2(64),
  "createdAt" NUMBER(10) NOT NULL,
  "updatedAt" NUMBER(10) NOT NULL
);
CREATE INDEX "idx-auth_item-type" ON "auth_item" ("type");
CREATE TABLE "auth_item_child" (
  "parent" VARCHAR2(128) NOT NULL,
  "child" VARCHAR2(128) NOT NULL,
  PRIMARY KEY ("parent", "child"),
  FOREIGN KEY ("parent") REFERENCES "auth_item" ("name"),
  FOREIGN KEY ("child") REFERENCES "auth_item" ("name")
);
CREATE TABLE "auth_assignment" (
  "itemName" VARCHAR2(128) NOT NULL,
  "userId" VARCHAR2(128) NOT NULL,
  "createdAt" NUMBER(10) NOT NULL,
  PRIMARY KEY ("itemName", "userId"),
  FOREIGN KEY ("itemName") REFERENCES "auth_item" ("name")
);
