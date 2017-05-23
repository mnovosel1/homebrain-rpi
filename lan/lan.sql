
PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;

CREATE TABLE lanDevices
(
  mac TEXT,
  name TEXT,
  allwaysOn BOOL,
  heating BOOL,
  turnOn INT,
  PRIMARY KEY (mac)
);

INSERT INTO "lanDevices" VALUES('5c:f8:a1:84:37:ae','Marijo mob',0,1);
INSERT INTO "lanDevices" VALUES('90:18:7c:63:06:5d','Mirela mob',0,1);
INSERT INTO "lanDevices" VALUES('00:13:49:ed:63:61','WiFi',0,0);
INSERT INTO "lanDevices" VALUES('00:0d:87:52:aa:2f','HouseServ',0,0);
INSERT INTO "lanDevices" VALUES('70:71:bc:a8:72:5c','KODI',0,0);
INSERT INTO "lanDevices" VALUES('4c:5e:0c:74:2e:d4','(Unknown)',NULL,NULL);
INSERT INTO "lanDevices" VALUES('00:06:4e:00:00:01','Broad Net Technology Inc.',NULL,NULL);
INSERT INTO "lanDevices" VALUES('00:80:5a:28:de:90','TULIP COMPUTERS INTERNATL B.V',NULL,NULL);
INSERT INTO "lanDevices" VALUES('00:12:12:3c:0e:79','PLUS  Corporation',NULL,NULL);
INSERT INTO "lanDevices" VALUES('00:22:fb:b0:42:22','Intel Corporate',NULL,NULL);
INSERT INTO "lanDevices" VALUES('80:57:19:13:0d:63','(Unknown)',NULL,NULL);
INSERT INTO "lanDevices" VALUES('00:30:1b:b7:e3:02','SHUTTLE, INC.',NULL,NULL);
INSERT INTO "lanDevices" VALUES('00:13:e8:6c:37:75','Intel Corporate',NULL,NULL);
INSERT INTO "lanDevices" VALUES('84:8e:df:a3:08:0d','(Unknown)',NULL,NULL);
INSERT INTO "lanDevices" VALUES('e0:f8:47:97:76:c9','Apple Inc',NULL,NULL);
INSERT INTO "lanDevices" VALUES('4c:5e:0c:5e:06:0b','(Unknown)',NULL,NULL);
INSERT INTO "lanDevices" VALUES('4c:ed:de:a2:c9:68','Askey Computer Corp',NULL,NULL);
INSERT INTO "lanDevices" VALUES('00:08:22:a4:f6:fb','InPro Comm',NULL,NULL);
INSERT INTO "lanDevices" VALUES('b0:89:00:fb:a2:5a','(Unknown) (DUP: 1)',NULL,NULL);
INSERT INTO "lanDevices" VALUES('00:08:22:22:00:fc','InPro Comm (DUP: 1)',NULL,NULL);
INSERT INTO "lanDevices" VALUES('30:75:12:38:be:ac','(Unknown) (DUP: 1)',NULL,NULL);
INSERT INTO "lanDevices" VALUES('f0:4f:7c:59:d7:74','(Unknown)',NULL,NULL);

CREATE TABLE turnOn
(
  ifon TEXT,
  thenon TEXT,
  PRIMARY KEY (ifon, thenon)
);

CREATE TABLE lanLog
(
  mac TEXT,
  ip TEXT,
  start DATETIME,
  stop DATETIME,
  PRIMARY KEY (mac, start)
);
COMMIT;
