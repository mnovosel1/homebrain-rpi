
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
        INSERT INTO "changelog" VALUES('2017-10-19 20:46:03','011110','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-10-19 21:10:45','011010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-10-20 08:15:18','011000','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-10-20 14:35:27','110100','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-10-20 21:41:23','110010','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-10-20 21:45:30','111010','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-10-21 05:16:00','011010','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-10-24 22:45:39','110010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-10-25 18:40:11','000100','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-10-25 23:50:11','000100','KODI',1);
        INSERT INTO "changelog" VALUES('2017-10-27 12:30:34','000110','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-10-28 23:15:57','100010','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-10-29 21:50:18','110100','KODI',0);
        INSERT INTO "changelog" VALUES('2017-11-01 08:55:48','000100','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-11-02 08:05:52','010010','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-02 14:35:42','010110','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-11-02 14:35:47','010010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-02 16:15:54','000010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-02 18:15:10','010100','KODI',1);
        INSERT INTO "changelog" VALUES('2017-11-03 15:50:39','000000','KODI',1);
        INSERT INTO "changelog" VALUES('2017-11-03 19:41:05','110000','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-11-03 19:51:23','110010','KODI',0);
        INSERT INTO "changelog" VALUES('2017-11-03 19:55:49','010010','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-11-03 20:55:50','000010','KODI',1);
        INSERT INTO "changelog" VALUES('2017-11-03 20:55:52','100010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-03 21:05:31','100000','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-04 10:25:37','010110','HomeServer busy','Array');
        INSERT INTO "changelog" VALUES('2017-11-04 11:00:16','01Array110','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-04 11:01:53','010110','HomeServer busy','');
        INSERT INTO "changelog" VALUES('2017-11-04 11:04:25','01110','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-04 13:15:56','110110','KODI',0);
        INSERT INTO "changelog" VALUES('2017-11-04 19:45:33','111100','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-11-04 19:45:35','101100','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-05 08:10:36','000010','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-05 08:10:43','010010','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-05 09:31:17','000110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-05 11:07:50','010100','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-05 11:10:44','011100','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-11-05 11:10:49','011000','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-11-05 11:30:23','011010','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-05 14:41:08','000110','KODI',1);
        INSERT INTO "changelog" VALUES('2017-11-05 14:45:21','100110','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-05 14:50:23','111110','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-05 14:50:24','110110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-05 15:10:30','110100','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-11-05 15:25:40','100100','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-05 16:10:14','110000','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-05 17:05:10','110000','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-05 20:05:29','111000','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-05 21:30:19','000000','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-05 21:50:46','000100','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-06 00:45:17','010100','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-11-06 05:10:38','000000','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-06 05:10:44','010000','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-06 05:15:36','011000','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-06 07:21:04','000000','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-11-06 07:40:23','000010','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-06 11:46:06','011110','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-11-06 11:46:10','001110','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-06 11:55:42','010110','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-11-06 12:05:24','000110','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-06 19:04:32','011100','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-11-06 19:56:42','011110','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-06 20:45:32','010110','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-06 20:45:32','011110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-06 20:50:30','011100','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-06 21:45:55','010110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-06 22:15:47','010100','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-11-06 22:22:48','010110','KODI',1);
        INSERT INTO "changelog" VALUES('2017-11-06 22:22:57','110110','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-06 22:24:27','111110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-06 22:25:56','110100','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-06 22:26:40','111100','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-06 22:45:15','110100','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-11-07 00:05:12','110000','KODI',0);
        INSERT INTO "changelog" VALUES('2017-11-07 00:10:24','010000','HomeServer',0);


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

