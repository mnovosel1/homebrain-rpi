
    BEGIN TRANSACTION;

        CREATE TABLE states (
            name varchar(50) PRIMARY KEY,
            auto int(1) NOT NULL DEFAULT 1,
            active int(1) NOT NULL DEFAULT 0
        );

        INSERT INTO "states" VALUES('KODI',1,0);
        INSERT INTO "states" VALUES('HomeServer',1,0);
        INSERT INTO "states" VALUES('HomeServer busy',0,0);
        INSERT INTO "states" VALUES('HomeBrain user',0,0);
        INSERT INTO "states" VALUES('MPD playing',1,0);
        INSERT INTO "states" VALUES('Heating',1,0);


		CREATE TABLE fcm (
			timestamp DATETIME,
            email varchar(99) NOT NULL,
            approved BOOLEAN DEFAULT false,
			token varchar(99) NOT NULL,
			PRIMARY KEY(token)
        );
  
        INSERT INTO "fcm" VALUES('2017-10-25 22:25:17','marijo@bubulescu.org','false','f3nPqegZnTs:APA91bGqniWwPl4KULOZsqGeryaNRrDxbtDneC9l1OO-lmUYS4B5nNMce__5k8sCRKbxA-kIrqaN0blCYq7RwTgIQxs9jqn9U0Sc0Goiiz0SaRJWXkA6AUT27g3MNkZgUz1WJLGA_zsB');
        INSERT INTO "fcm" VALUES('2017-10-26 16:26:29','marijo.novosel@gmail.com','false','cmiZkev-cV8:APA91bHG4uBrd5sUEFDltXirL5TsCRWdaHNugRJQGX1GU2pOvdXFkb7RY4y7kZRKBhRSi6qCfd_qoGGzFDp9c9Osd1Ys2LxEW6FHbq211t_nmqoAh8_FvM_QDUXOE1JVU7IBYRj0-VfN');


        CREATE TABLE changelog (
            timestamp DATETIME,
            statebefore varchar(30) NOT NULL,
            state varchar(50) NOT NULL,
            changedto int(1) NOT NULL,
            PRIMARY KEY(statebefore, state, changedto),
            FOREIGN KEY (state) REFERENCES states(name)
        );

        CREATE TRIGGER changelog_trigg
            BEFORE UPDATE ON states
            FOR EACH ROW
            WHEN OLD.active <> NEW.active
            BEGIN
                INSERT OR REPLACE INTO changelog (timestamp, statebefore, state, changedto)
                VALUES (
                            datetime('now','localtime'), 
                            (SELECT group_concat(active, '') FROM states), 
                            NEW.name, 
                            NEW.active
                        );
                DELETE FROM changelog WHERE timestamp <= date('now', '-60 day');
            END;

        INSERT INTO "changelog" VALUES('2017-10-08 23:10:52','100100','KODI',0);
        INSERT INTO "changelog" VALUES('2017-10-09 10:43:57','010110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-10-09 10:44:16','010100','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-10-09 20:45:50','111010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-10-11 04:20:35','111000','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-10-11 04:20:39','101000','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-10-11 04:20:42','100000','KODI',0);
        INSERT INTO "changelog" VALUES('2017-10-15 19:31:08','000110','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-10-15 21:55:36','010110','KODI',1);
        INSERT INTO "changelog" VALUES('2017-10-17 14:59:58','011110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-10-17 15:55:26','011100','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-10-18 15:05:19','110110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-10-18 16:41:28','010010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-10-19 20:05:15','011010','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-10-19 20:11:18','011110','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-10-19 20:15:50','010110','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-10-19 20:46:03','011110','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-10-19 21:10:45','011010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-10-19 21:16:06','011000','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-10-20 08:15:18','011000','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-10-20 08:20:26','011100','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-10-20 11:05:09','010100','KODI',1);
        INSERT INTO "changelog" VALUES('2017-10-20 14:35:27','110100','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-10-20 15:06:06','110110','KODI',0);
        INSERT INTO "changelog" VALUES('2017-10-20 19:30:28','010110','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-10-20 21:41:23','110010','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-10-20 21:45:30','111010','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-10-21 05:10:52','000010','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-10-21 05:10:59','010010','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-10-21 05:16:00','011010','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-10-21 22:20:11','100010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-10-22 10:06:03','000100','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-10-22 17:45:49','000110','KODI',1);
        INSERT INTO "changelog" VALUES('2017-10-22 17:50:09','100110','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-10-22 17:50:13','110110','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-10-22 17:50:13','111110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-10-22 17:55:13','111100','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-10-22 18:05:34','110100','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-10-23 15:50:50','110000','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-10-23 16:50:24','110010','KODI',0);
        INSERT INTO "changelog" VALUES('2017-10-23 16:55:37','010010','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-10-24 00:40:22','110000','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-10-24 01:05:29','111000','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-10-24 05:10:40','010000','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-10-24 05:15:39','011000','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-10-24 22:40:58','000010','KODI',1);
        INSERT INTO "changelog" VALUES('2017-10-24 22:45:36','100010','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-10-24 22:45:39','110010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-10-25 07:40:27','000000','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-10-25 18:40:11','000100','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-10-25 22:30:40','000110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-10-25 23:50:11','000100','KODI',1);
        INSERT INTO "changelog" VALUES('2017-10-25 23:55:46','100100','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-10-26 00:50:50','110100','KODI',0);
        INSERT INTO "changelog" VALUES('2017-10-26 02:05:41','010100','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-10-26 05:10:19','000000','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-10-26 09:11:01','000000','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-10-26 10:25:53','000010','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-10-26 16:51:15','000110','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-10-26 17:10:37','000010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-10-27 00:05:47','000000','KODI',1);
        INSERT INTO "changelog" VALUES('2017-10-27 00:10:38','100000','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-10-27 00:25:40','110000','KODI',0);
        INSERT INTO "changelog" VALUES('2017-10-27 00:30:51','010000','HomeServer',0);


        CREATE VIEW logic AS 
            SELECT 
                    COUNT(*) AS weight, 
                    STRFTIME('%H', timestamp)*1 AS hour,
                    STRFTIME('%w', timestamp)*1 AS dow,
                    c.statebefore, 
                    c.changedto, 
                    s.name, 
                    s.auto
                FROM changelog c join states s ON c.state=s.name
                GROUP BY c.statebefore, c.state, c.changedto
                ORDER BY weight desc, c.timestamp desc;

    COMMIT;

