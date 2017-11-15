
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
  
        INSERT INTO "fcm" VALUES('2017-11-12 16:02:40','marijo@bubulescu.org','false','fVy_ftTYqhU:APA91bGrLxljnGEY78gmlRsElGyu7ueCIqABOIuM4gDHGnIr54vV6FZgBhVXlhOP8v8tNHWVbPE4B7U2Qu4qNNG3WOzbteY7vOT6CMc7yEiR9wuU2VA65Tbwm6BDjwFEXDfKzThRUGE8');
        INSERT INTO "fcm" VALUES('2017-11-12 16:11:13','marijo@bubulescu.org','false','dMlrzJv2V1Q:APA91bHNccQmoh8-PFGThNc7_I6LNoUEiPaTHtKbHvEN6Fddz_2AQCKAzVuwrY-QIaVsPBljVA1QJ0GXgtUqEYEyMy3UkyAM0QQWKXQz8RCrkskEZTHsA3cs7XxmmBPhd5ebiP8Tsioz');
        INSERT INTO "fcm" VALUES('2017-11-12 16:40:52','marijo@bubulescu.org','false','fKaxRgd99tI:APA91bHr1_ItbDaWklbfs0b0WLxuOk41ogAbPI46s-omLcPkRONqJQMHDvCROVRMk2zd5dVu3HAWS3viXOcxosVWxHEaIA8qozHANf7g-_cwfBDKrR-I3VywOmcN0vm7E3Li2dRsAjAb');
        INSERT INTO "fcm" VALUES('2017-11-12 16:54:26','marijo@bubulescu.org','false','fozKC1cfots:APA91bEgsmd5iSShofddjU-zDnqXYqe7U62cxdCrqzp9AUwcErDyfK-SUt3jv9m5KZXGtEMiAmGEUB5k3w7p-HMmhhQ1X0oJyAbytBqeBJN4YwJJ2RUEm2cE56T5mDvXqMdzaSlyC2SU');
        INSERT INTO "fcm" VALUES('2017-11-12 17:25:29','marijo.novosel@gmail.com','false','dMXhoW--MGM:APA91bGYj3-ZzLJWnwFzWKF5QXFc_M6aDaryrukylbXKJc_JaJBNPp1SPwQA7YsT9lPKgGbiOF_A_NDsvjEQJyqlYdM2hMhqPh4ucYK7k5BNGY16tw6BAyNvda2u8xai6ZjAiLEh0Clh');
        INSERT INTO "fcm" VALUES('2017-11-12 19:46:51','marijo@bubulescu.org','false','eEr7QrADyW0:APA91bGfZFi6NZw68m9l0DvVqyDDew0BbSwlCNYGt6Gdxc0m0rWilAFW2tva-dgsYzcN6qmpywy75UXiI7h4D5VMni87PPvk7f31ymWBXCWR8wmP8RkbKjt2LIEDBXWUV3OVvRd3_JCL');


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
        INSERT INTO "changelog" VALUES('2017-11-04 19:45:33','111100','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-11-04 19:45:35','101100','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-05 11:10:44','011100','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-11-05 11:10:49','011000','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-11-05 14:41:08','000110','KODI',1);
        INSERT INTO "changelog" VALUES('2017-11-05 14:45:21','100110','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-05 15:10:30','110100','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-11-06 11:46:06','011110','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-11-06 11:46:10','001110','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-06 19:04:32','011100','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-11-06 20:45:32','011110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-06 22:22:57','110110','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-06 22:24:27','111110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-07 22:30:09','010110','KODI',1);
        INSERT INTO "changelog" VALUES('2017-11-07 22:30:18','110110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-07 22:50:20','110100','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-11-07 23:29:54','110010','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-07 23:35:38','110110','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-11-08 00:10:21','110010','KODI',0);
        INSERT INTO "changelog" VALUES('2017-11-08 12:16:59','010010','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-08 12:25:51','010110','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-11-08 12:42:52','011010','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-08 23:10:17','010100','KODI',1);
        INSERT INTO "changelog" VALUES('2017-11-10 22:48:45','100000','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-10 22:50:45','110100','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-10 22:55:45','111100','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-10 23:50:45','110100','KODI',0);
        INSERT INTO "changelog" VALUES('2017-11-11 19:40:52','000110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-12 10:05:35','000100','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-11-12 12:22:17','000110','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-11-12 12:35:27','011110','KODI',1);
        INSERT INTO "changelog" VALUES('2017-11-12 12:35:37','111110','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-12 13:40:17','110110','KODI',0);
        INSERT INTO "changelog" VALUES('2017-11-12 15:40:56','011110','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-12 15:50:22','010110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-12 17:06:07','010000','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-12 17:06:08','010100','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-11-12 17:26:57','010110','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-11-12 17:30:32','010010','KODI',1);
        INSERT INTO "changelog" VALUES('2017-11-12 17:30:44','110010','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-11-12 17:30:48','100010','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-12 17:30:50','100110','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-12 17:35:52','100100','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-12 21:05:26','110000','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-12 21:45:42','110100','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-11-13 17:45:44','000100','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-11-13 20:03:49','000000','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-13 20:15:28','000100','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-13 20:15:36','010100','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-13 20:20:40','011100','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-13 20:20:42','010100','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-11-13 22:25:33','000000','KODI',1);
        INSERT INTO "changelog" VALUES('2017-11-14 05:10:43','000000','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-14 05:10:50','010000','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-14 05:15:49','011000','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-14 11:11:11','000010','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-14 11:11:19','010010','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-14 11:20:27','010010','HomeServer',0);
        INSERT INTO "changelog" VALUES('2017-11-14 11:24:08','000010','HomeBrain user',1);
        INSERT INTO "changelog" VALUES('2017-11-14 11:35:47','000110','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-14 13:20:31','010110','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-14 13:20:33','011110','HomeBrain user',0);
        INSERT INTO "changelog" VALUES('2017-11-14 13:30:41','011010','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-14 13:30:42','010010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-14 21:05:48','000010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-14 21:45:43','000000','MPD playing',1);
        INSERT INTO "changelog" VALUES('2017-11-14 23:05:15','000010','KODI',1);
        INSERT INTO "changelog" VALUES('2017-11-14 23:05:23','100010','MPD playing',0);
        INSERT INTO "changelog" VALUES('2017-11-14 23:10:18','100000','HomeServer',1);
        INSERT INTO "changelog" VALUES('2017-11-14 23:10:26','110000','HomeServer busy',1);
        INSERT INTO "changelog" VALUES('2017-11-14 23:15:29','111000','HomeServer busy',0);
        INSERT INTO "changelog" VALUES('2017-11-15 01:05:46','110000','KODI',0);
        INSERT INTO "changelog" VALUES('2017-11-15 01:10:37','010000','HomeServer',0);


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

