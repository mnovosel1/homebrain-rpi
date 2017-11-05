
    BEGIN TRANSACTION;

        CREATE TABLE states (
            name varchar(50) PRIMARY KEY,
            auto int(1) NOT NULL DEFAULT 1,
            active int(1) NOT NULL DEFAULT 0
        );

        INSERT INTO "states" VALUES('KODI',1,0);
        INSERT INTO "states" VALUES('HomeServer',1,1);
        INSERT INTO "states" VALUES('HomeServer busy',0,0);
        INSERT INTO "states" VALUES('HomeBrain user',0,1);
        INSERT INTO "states" VALUES('MPD playing',1,0);
        INSERT INTO "states" VALUES('Heating',1,0);


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
        INSERT INTO "changelog" VALUES('2017-10-17 14:59:58','011110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-10-17 15:55:26','011100','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-10-19 20:46:03','011110','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-10-19 21:10:45','011010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-10-19 21:16:06','011000','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-10-20 08:15:18','011000','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-10-20 08:20:26','011100','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-10-20 14:35:27','110100','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-10-20 21:41:23','110010','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-10-20 21:45:30','111010','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-10-21 05:16:00','011010','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-10-22 17:45:49','000110','KODI',1);
        INSERT INTO "changelog" VALUES('2017-10-22 17:50:09','100110','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-10-24 22:45:39','110010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-10-25 18:40:11','000100','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-10-25 23:50:11','000100','KODI',1);
        INSERT INTO "changelog" VALUES('2017-10-27 12:30:34','000110','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-10-28 23:15:57','100010','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-10-29 21:50:18','110100','KODI',0);
        INSERT INTO "changelog" VALUES('2017-10-30 16:30:28','010100','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-11-01 08:55:48','000100','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-11-01 18:10:37','011110','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-11-01 18:10:44','001110','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-01 18:31:34','110110','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-01 18:55:18','111110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-02 08:05:52','010010','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-02 14:35:42','010110','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-11-02 14:35:47','010010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-02 16:15:54','000010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-02 17:35:30','000000','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-02 18:15:10','010100','KODI',1);
        INSERT INTO "changelog" VALUES('2017-11-03 15:50:39','000000','KODI',1);
        INSERT INTO "changelog" VALUES('2017-11-03 15:55:36','110000','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-03 16:05:11','111000','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-03 19:41:05','110000','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-11-03 19:51:23','110010','KODI',0);
        INSERT INTO "changelog" VALUES('2017-11-03 19:55:49','010010','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-11-03 20:55:50','000010','KODI',1);
        INSERT INTO "changelog" VALUES('2017-11-03 20:55:52','100010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-03 21:05:31','100000','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-04 08:46:27','000010','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-04 10:25:37','010110','HomeServer busy','Array');
        INSERT INTO "changelog" VALUES('2017-11-04 11:00:16','01Array110','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-04 11:01:53','010110','HomeServer busy','');
        INSERT INTO "changelog" VALUES('2017-11-04 11:04:25','01110','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-04 11:29:58','010110','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-04 13:15:56','110110','KODI',0);
        INSERT INTO "changelog" VALUES('2017-11-04 14:10:51','010110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-04 14:40:33','010100','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-11-04 18:05:23','010110','KODI',1);
        INSERT INTO "changelog" VALUES('2017-11-04 18:05:24','110110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-04 18:50:22','111100','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-04 19:20:24','110100','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-11-04 19:25:22','110100','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-04 19:45:33','111100','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-11-04 19:45:35','101100','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-04 19:55:27','100100','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-04 20:05:47','110100','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-11-04 23:40:59','110000','KODI',0);
        INSERT INTO "changelog" VALUES('2017-11-05 05:10:31','000000','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-05 05:10:37','010000','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-05 05:15:30','011000','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-05 05:20:39','010000','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-11-05 07:55:48','000000','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-11-05 08:10:36','000010','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-05 08:10:43','010010','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-05 08:10:44','011010','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-05 08:15:31','011110','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-05 09:26:07','000110','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-05 09:31:09','010110','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-11-05 09:31:17','000110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-05 09:40:45','000100','HomeServer',1);


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

