
    BEGIN TRANSACTION;

        CREATE TABLE states (
            name varchar(50) PRIMARY KEY,
            auto int(1) NOT NULL DEFAULT 1,
            active int(1) NOT NULL DEFAULT 0
        );

        INSERT INTO "states" VALUES('KODI',0,0);
        INSERT INTO "states" VALUES('HomeServer',1,0);
        INSERT INTO "states" VALUES('HomeServer busy',0,0);
        INSERT INTO "states" VALUES('HomeBrain user',0,0);
        INSERT INTO "states" VALUES('MPD playing',0,0);
        INSERT INTO "states" VALUES('Heating',0,0);


		CREATE TABLE fcm (
			timestamp DATETIME,
            email varchar(99) NOT NULL,
            approved BOOLEAN DEFAULT false,
			token varchar(99) NOT NULL,
			PRIMARY KEY(token)
        );
  
        INSERT INTO "fcm" VALUES('2017-10-26 16:26:29','marijo.novosel@gmail.com','false','cmiZkev-cV8:APA91bHG4uBrd5sUEFDltXirL5TsCRWdaHNugRJQGX1GU2pOvdXFkb7RY4y7kZRKBhRSi6qCfd_qoGGzFDp9c9Osd1Ys2LxEW6FHbq211t_nmqoAh8_FvM_QDUXOE1JVU7IBYRj0-VfN');
        INSERT INTO "fcm" VALUES('2017-10-27 13:27:32','marijo@bubulescu.org','false','drlPNautB1s:APA91bEDHHkKOSPI1QRNt-w9vsotdTSxC6S3w2VVwsPFZPR46YJMO9BEwXgno2vT1mUJzWgl_lUseSrbKEClz-ra3-bRIq7LQbJqgiZRdTGNHf3RnWbIH0YGoNjIaC4dl4uHORiJQ1MF');


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
        INSERT INTO "changelog" VALUES('2017-10-09 20:45:50','111010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-10-11 04:20:35','111000','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-10-11 04:20:39','101000','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-10-11 04:20:42','100000','KODI',0);
        INSERT INTO "changelog" VALUES('2017-10-19 21:10:45','011010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-10-20 08:15:18','011000','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-10-20 21:41:23','110010','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-10-20 21:45:30','111010','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-10-24 22:45:39','110010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-10-25 23:50:11','000100','KODI',1);
        INSERT INTO "changelog" VALUES('2017-10-28 23:15:57','100010','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-03 19:41:05','110000','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-11-04 10:25:37','010110','HomeServer busy','Array');
        INSERT INTO "changelog" VALUES('2017-11-04 11:00:16','01Array110','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-04 11:01:53','010110','HomeServer busy','');
        INSERT INTO "changelog" VALUES('2017-11-04 11:04:25','01110','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-04 13:15:56','110110','KODI',0);
        INSERT INTO "changelog" VALUES('2017-11-04 19:45:33','111100','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-11-04 19:45:35','101100','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-05 11:10:44','011100','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-11-05 11:10:49','011000','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-11-05 14:41:08','000110','KODI',1);
        INSERT INTO "changelog" VALUES('2017-11-05 14:45:21','100110','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-05 14:50:23','111110','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-05 15:10:30','110100','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-11-06 11:46:06','011110','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-11-06 11:46:10','001110','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-06 19:04:32','011100','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-11-06 20:45:32','011110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-06 22:22:57','110110','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-06 22:24:27','111110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-07 09:05:28','000100','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-07 09:05:34','010100','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-07 09:10:37','011100','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-07 19:30:58','000100','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-11-07 22:11:33','010000','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-07 22:30:09','010110','KODI',1);
        INSERT INTO "changelog" VALUES('2017-11-07 22:30:18','110110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-07 22:41:19','110000','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-07 22:50:20','110100','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-11-07 23:29:54','110010','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-07 23:35:38','110110','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-11-08 00:10:21','110010','KODI',0);
        INSERT INTO "changelog" VALUES('2017-11-08 10:55:44','000010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-08 12:16:59','010010','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-08 12:25:51','010110','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-11-08 12:42:52','011010','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-08 12:51:39','011110','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-11-08 13:45:59','010010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-08 23:10:17','010100','KODI',1);
        INSERT INTO "changelog" VALUES('2017-11-08 23:20:30','110100','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-11-09 14:45:36','000010','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-09 14:45:49','010010','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-09 14:56:17','011010','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-09 22:40:16','000010','KODI',1);
        INSERT INTO "changelog" VALUES('2017-11-09 22:40:23','100010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-09 22:45:15','100000','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-09 22:45:22','110000','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-09 22:50:22','111000','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-10 02:05:19','110000','KODI',0);
        INSERT INTO "changelog" VALUES('2017-11-10 14:11:11','010110','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-10 15:11:04','011110','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-10 18:05:48','010100','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-11-10 18:46:04','010110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-10 22:45:41','000000','KODI',1);
        INSERT INTO "changelog" VALUES('2017-11-10 22:48:45','100000','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-10 22:50:38','100100','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-10 22:50:45','110100','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-10 22:55:45','111100','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-10 23:50:45','110100','KODI',0);
        INSERT INTO "changelog" VALUES('2017-11-11 02:50:48','010100','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-11-11 05:10:54','000000','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-11 05:11:00','010000','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-11 05:15:58','011000','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-11 05:20:22','010000','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-11-11 11:00:42','000000','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-11 11:06:08','000000','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-11-11 11:55:51','000110','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-11 16:06:20','010110','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-11-11 16:15:55','010010','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-11-11 19:11:31','000110','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-11-11 19:11:40','000010','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-11 19:40:52','000110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-11 22:25:20','000100','HomeBrain user',0);


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

