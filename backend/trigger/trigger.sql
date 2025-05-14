#------------------------------------------------------------
#       Table artist
#------------------------------------------------------------
CREATE TRIGGER `delete_artist` AFTER DELETE ON `artist`
 FOR EACH ROW BEGIN
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
            VALUES ('Artistes', OLD.id, 'delete', JSON_OBJECT('nom', OLD.name, 'description', OLD.description, 'image', OLD.image, 'miniature', OLD.thumbnail, 'type_music', OLD.type_music ), NOW(), @current_user_name);
        END

CREATE TRIGGER `insert_artist` AFTER INSERT ON `artist`
 FOR EACH ROW BEGIN
            INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
            VALUES ('Artistes', NEW.id, 'insert', JSON_OBJECT('nom', NEW.name, 'description', NEW.description, 'image', NEW.image, 'miniature', NEW.thumbnail, 'type_music', NEW.type_music ), NEW.date_modification, NEW.user_modification);
        END

CREATE TRIGGER `update_artist` AFTER UPDATE ON `artist`
 FOR EACH ROW BEGIN
                INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
                VALUES ('Artistes', NEW.id, 'update', JSON_OBJECT('nom', OLD.name, 'description', OLD.description, 'image', OLD.image, 'miniature', OLD.thumbnail, 'type_music', OLD.type_music ), JSON_OBJECT('nom', NEW.name, 'description', NEW.description, 'image', NEW.image, 'miniature', NEW.thumbnail, 'type_music', NEW.type_music ), NEW.date_modification, NEW.user_modification);
            END

#------------------------------------------------------------
#       Table event
#------------------------------------------------------------
CREATE TRIGGER `delete_event` AFTER DELETE ON `event`
 FOR EACH ROW BEGIN
            DECLARE old_type VARCHAR(255);
            DECLARE old_artist VARCHAR(255);
            DECLARE old_date VARCHAR(255);
            DECLARE old_event_location VARCHAR(255);
            SELECT type INTO old_type FROM event_type WHERE id = OLD.type_id;
            SELECT name INTO old_artist FROM artist WHERE id = OLD.artist_id;
            SELECT date INTO old_date FROM event_date WHERE id = OLD.date_id;
            SELECT location_name INTO old_event_location FROM event_location WHERE id = OLD.event_location_id;
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
            VALUES ('Ã‰vÃ¨nements', OLD.id, 'update', JSON_OBJECT('heure de dÃ©but', OLD.heure_debut, 'heure de fin', OLD.heure_fin, 'type', old_type, 'artiste', old_artist, 'date', old_date, 'lieu', old_event_location, 'publiÃ©', OLD.publish), NOW(), @current_user_name);
        END

CREATE TRIGGER `insert_event` AFTER INSERT ON `event`
 FOR EACH ROW BEGIN
            DECLARE new_type VARCHAR(255);
            DECLARE new_artist VARCHAR(255);
            DECLARE new_date VARCHAR(255);
            DECLARE new_event_location VARCHAR(255);
            SELECT type INTO new_type FROM event_type WHERE id = NEW.type_id;
            SELECT name INTO new_artist FROM artist WHERE id = NEW.artist_id;
            SELECT date INTO new_date FROM event_date WHERE id = NEW.date_id;
            SELECT location_name INTO new_event_location FROM event_location WHERE id = NEW.event_location_id;
            INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
            VALUES ('Ã‰vÃ¨nements', NEW.id, 'insert', JSON_OBJECT('heure de dÃ©but', NEW.heure_debut, 'heure de fin', NEW.heure_fin, 'type', new_type, 'artiste', new_artist, 'date', new_date, 'lieu', new_event_location, 'publiÃ©', NEW.publish), NEW.date_modification, NEW.user_modification);
        END

CREATE TRIGGER `update_event` AFTER UPDATE ON `event`
 FOR EACH ROW BEGIN
                DECLARE old_type VARCHAR(255);
                DECLARE old_artist VARCHAR(255);
                DECLARE old_date VARCHAR(255);
                DECLARE old_event_location VARCHAR(255);
                DECLARE new_type VARCHAR(255);
                DECLARE new_artist VARCHAR(255);
                DECLARE new_date VARCHAR(255);
                DECLARE new_event_location VARCHAR(255);
                SELECT type INTO old_type FROM event_type WHERE id = OLD.type_id;
                SELECT name INTO old_artist FROM artist WHERE id = OLD.artist_id;
                SELECT date INTO old_date FROM event_date WHERE id = OLD.date_id;
                SELECT location_name INTO old_event_location FROM event_location WHERE id = OLD.event_location_id;
                SELECT type INTO new_type FROM event_type WHERE id = NEW.type_id;
                SELECT name INTO new_artist FROM artist WHERE id = NEW.artist_id;
                SELECT date INTO new_date FROM event_date WHERE id = NEW.date_id;
                SELECT location_name INTO new_event_location FROM event_location WHERE id = NEW.event_location_id;
                INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
                VALUES ('Ã‰vÃ¨nements', NEW.id, 'update', JSON_OBJECT('heure de dÃ©but', OLD.heure_debut, 'heure de fin', OLD.heure_fin, 'type', old_type, 'artiste', old_artist, 'date', old_date, 'lieu', old_event_location, 'publiÃ©', OLD.publish), JSON_OBJECT('heure de dÃ©but', NEW.heure_debut, 'heure de fin', NEW.heure_fin, 'type', new_type, 'artiste', new_artist, 'date', new_date, 'lieu', new_event_location, 'publiÃ©', NEW.publish), NEW.date_modification, NEW.user_modification);
            END

#------------------------------------------------------------
#       Table event_date
#------------------------------------------------------------
CREATE TRIGGER `delete_eventDate` AFTER DELETE ON `event_date`
 FOR EACH ROW BEGIN
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
            VALUES ('Dates du festival', OLD.id, 'delete', JSON_OBJECT('date', OLD.date ), NOW(), @current_user_name);
        END

CREATE TRIGGER `insert_eventDate` AFTER INSERT ON `event_date`
 FOR EACH ROW BEGIN
        INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
        VALUES ('Dates du festival', NEW.id, 'insert', JSON_OBJECT('date', NEW.date ), NEW.date_modification, NEW.user_modification);
        END

CREATE TRIGGER `update_eventDate` AFTER UPDATE ON `event_date`
 FOR EACH ROW BEGIN
                INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
                VALUES ('Dates du festival', NEW.id, 'update', JSON_OBJECT('date', OLD.date ), JSON_OBJECT('date', NEW.date ), NEW.date_modification, NEW.user_modification);
            END

#------------------------------------------------------------
#       Table event_location
#------------------------------------------------------------
CREATE TRIGGER `delete_eventLocation` AFTER DELETE ON `event_location`
 FOR EACH ROW BEGIN
            DECLARE old_type_location VARCHAR(255);
            SELECT type INTO old_type_location FROM location_type WHERE id = OLD.type_location_id;
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
            VALUES ('Lieux', OLD.id, 'delete', JSON_OBJECT('nom', OLD.location_name, 'latitude', OLD.latitude, 'longitude', OLD.longitude, 'description', OLD.description, 'publiÃ©', OLD.publish, 'type', old_type_location), NOW(), @current_user_name);
        END

CREATE TRIGGER `insert_eventLocation` AFTER INSERT ON `event_location`
 FOR EACH ROW BEGIN
        DECLARE new_type_location VARCHAR(255);
        SELECT type INTO new_type_location FROM location_type WHERE id = NEW.type_location_id;
        INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
        VALUES ('Lieux', NEW.id, 'insert', JSON_OBJECT('nom', NEW.location_name, 'latitude', NEW.latitude, 'longitude', NEW.longitude, 'description', NEW.description, 'publiÃ©', NEW.publish, 'type', new_type_location), NEW.date_modification, NEW.user_modification);
        END

CREATE TRIGGER `update_eventLocation` AFTER UPDATE ON `event_location`
 FOR EACH ROW BEGIN
                DECLARE old_type_location VARCHAR(255);
                DECLARE new_type_location VARCHAR(255);
                SELECT type INTO old_type_location FROM location_type WHERE id = OLD.type_location_id;
                SELECT type INTO new_type_location FROM location_type WHERE id = NEW.type_location_id;
                INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
                VALUES ('Lieux', NEW.id, 'update', JSON_OBJECT('nom', OLD.location_name, 'latitude', OLD.latitude, 'longitude', OLD.longitude, 'description', OLD.description, 'publiÃ©', OLD.publish, 'type', old_type_location), JSON_OBJECT('nom', NEW.location_name, 'latitude', NEW.latitude, 'longitude', NEW.longitude, 'description', NEW.description, 'publiÃ©', NEW.publish, 'type', new_type_location), NEW.date_modification, NEW.user_modification);
            END

#------------------------------------------------------------
#       Table event_type
#------------------------------------------------------------
CREATE TRIGGER `delete_eventType` AFTER DELETE ON `event_type`
 FOR EACH ROW BEGIN
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
            VALUES ('Type d''Ã©vÃ¨nements', OLD.id, 'delete', JSON_OBJECT('type', OLD.type ), NOW(), @current_user_name);
        END

CREATE TRIGGER `insert_eventType` AFTER INSERT ON `event_type`
 FOR EACH ROW BEGIN
        INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
        VALUES ('Type d''Ã©vÃ¨nements', NEW.id, 'insert', JSON_OBJECT('type', NEW.type ), NEW.date_modification, NEW.user_modification);
        END

CREATE TRIGGER `update_eventType` AFTER UPDATE ON `event_type`
 FOR EACH ROW BEGIN
                INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
                VALUES ('Type d''Ã©vÃ¨nements', NEW.id, 'update', JSON_OBJECT('type', OLD.type ), JSON_OBJECT('type', NEW.type ), NEW.date_modification, NEW.user_modification);
            END

#------------------------------------------------------------
#       Table faq
#------------------------------------------------------------
CREATE TRIGGER `delete_faq` AFTER DELETE ON `faq`
 FOR EACH ROW BEGIN
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
            VALUES ('FAQ', OLD.id, 'delete', JSON_OBJECT('question', OLD.question, 'reponse', OLD.reponse, 'position', OLD.position, 'publiÃ©', OLD.publish ), NOW(), @current_user_name);
        END

CREATE TRIGGER `insert_faq` AFTER INSERT ON `faq`
 FOR EACH ROW BEGIN
        INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
        VALUES ('FAQ', NEW.id, 'insert', JSON_OBJECT('question', NEW.question, 'reponse', NEW.reponse, 'position', NEW.position, 'publiÃ©', NEW.publish ), NEW.date_modification, NEW.user_modification);
        END

CREATE TRIGGER `update_faq` AFTER UPDATE ON `faq`
 FOR EACH ROW BEGIN
                INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
                VALUES ('FAQ', NEW.id, 'update', JSON_OBJECT('question', OLD.question, 'reponse', OLD.reponse, 'position', OLD.position, 'publiÃ©', OLD.publish ), JSON_OBJECT('question', NEW.question, 'reponse', NEW.reponse, 'position', NEW.position, 'publiÃ©', NEW.publish ), NEW.date_modification, NEW.user_modification);
            END

#------------------------------------------------------------
#       Table information
#------------------------------------------------------------
CREATE TRIGGER `delete_information` AFTER DELETE ON `information`
 FOR EACH ROW BEGIN
        DECLARE old_section_information VARCHAR(255);
        SELECT section INTO old_section_information FROM information_section WHERE id = OLD.type_section_id;
        INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
        VALUES ('Informations', OLD.id, 'delete', JSON_OBJECT('titre', OLD.titre, 'description', OLD.description, 'position', OLD.position, 'section', old_section_information, 'publiÃ©', OLD.publish), NOW(), @current_user_name);
    END

CREATE TRIGGER `insert_information` AFTER INSERT ON `information`
 FOR EACH ROW BEGIN
    DECLARE new_section_information VARCHAR(255);
    SELECT section INTO new_section_information FROM information_section WHERE id = NEW.type_section_id;
    INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
    VALUES ('Informations', NEW.id, 'insert', JSON_OBJECT('titre', NEW.titre, 'description', NEW.description, 'position', NEW.position, 'section', new_section_information, 'publiÃ©', NEW.publish), NEW.date_modification, NEW.user_modification);
END

CREATE TRIGGER `update_information` AFTER UPDATE ON `information`
 FOR EACH ROW BEGIN
                DECLARE old_section_information VARCHAR(255);
                DECLARE new_section_information VARCHAR(255);
                SELECT section INTO old_section_information FROM information_section WHERE id = OLD.type_section_id;
                SELECT section INTO new_section_information FROM information_section WHERE id = NEW.type_section_id;
                INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
                VALUES ('Informations', NEW.id, 'update', JSON_OBJECT('titre', OLD.titre, 'description', OLD.description, 'position', OLD.position, 'section', old_section_information, 'publiÃ©', OLD.publish), JSON_OBJECT('titre', NEW.titre, 'description', NEW.description, 'position', NEW.position, 'section', new_section_information, 'publiÃ©', NEW.publish), NEW.date_modification, NEW.user_modification);
            END

#------------------------------------------------------------
#       Table information_section
#------------------------------------------------------------
CREATE TRIGGER `delete_informationSection` AFTER DELETE ON `information_section`
 FOR EACH ROW BEGIN
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
            VALUES ('Section d''information', OLD.id, 'delete', JSON_OBJECT('section', OLD.section, 'titre', OLD.title, 'description', OLD.description, 'position', OLD.position ), NOW(), @current_user_name);
        END

CREATE TRIGGER `insert_sectionInformation` AFTER INSERT ON `information_section`
 FOR EACH ROW BEGIN
        INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
        VALUES ('Section d''information', NEW.id, 'insert', JSON_OBJECT('section', NEW.section, 'titre', NEW.title, 'description', NEW.description, 'position', NEW.position ), NEW.date_modification, NEW.user_modification);
        END

CREATE TRIGGER `update_informationSection` AFTER UPDATE ON `information_section`
 FOR EACH ROW BEGIN
                INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
                VALUES ('Section d''information', NEW.id, 'update', JSON_OBJECT('section', OLD.section, 'titre', OLD.title, 'description', OLD.description, 'position', OLD.position ), JSON_OBJECT('section', NEW.section, 'titre', NEW.title, 'description', NEW.description, 'position', NEW.position ), NEW.date_modification, NEW.user_modification);
            END

#------------------------------------------------------------
#       Table location_type
#------------------------------------------------------------
CREATE TRIGGER `delete_locationType` AFTER DELETE ON `location_type`
 FOR EACH ROW BEGIN
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
            VALUES ('Type de lieux', OLD.id, 'delete', JSON_OBJECT('type', OLD.type, 'symbol', OLD.symbol, 'Ã©vÃ¨nement possible',OLD.event_hostable ), NOW(), @current_user_name);
        END

CREATE TRIGGER `insert_locationType` AFTER INSERT ON `location_type`
 FOR EACH ROW BEGIN
        INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
        VALUES ('Type de lieux', NEW.id, 'insert', JSON_OBJECT('type', NEW.type, 'symbol', NEW.symbol, 'Ã©vÃ¨nement possible',NEW.event_hostable ), NEW.date_modification, NEW.user_modification);
        END

CREATE TRIGGER `update_locationType` AFTER UPDATE ON `location_type`
 FOR EACH ROW BEGIN
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
            VALUES ('Type de lieux', NEW.id, 'update', JSON_OBJECT('type', OLD.type, 'symbol', OLD.symbol, 'Ã©vÃ¨nement possible',OLD.event_hostable ), JSON_OBJECT('type', NEW.type, 'symbol', NEW.symbol, 'Ã©vÃ¨nement possible',NEW.event_hostable ), NEW.date_modification, NEW.user_modification);
        END

#------------------------------------------------------------
#       Table news
#------------------------------------------------------------
CREATE TRIGGER `delete_news` AFTER DELETE ON `news`
 FOR EACH ROW BEGIN
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
            VALUES ('ActualitÃ©s', OLD.id, 'delete', JSON_OBJECT('titre', OLD.title, 'Contenu', OLD.content, 'Type', OLD.type, 'Date de notification', OLD.notification_date, 'Date de fin de notification', OLD.notification_end_date, 'publier', OLD.publish, 'Notifier', OLD.push), NOW(), @current_user_name);
        END

CREATE TRIGGER `insert_news` AFTER INSERT ON `news`
 FOR EACH ROW BEGIN
        INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
        VALUES ('ActualitÃ©s', NEW.id, 'insert', JSON_OBJECT('titre', NEW.title, 'Contenu', NEW.content, 'Type', NEW.type, 'Date de notification', NEW.notification_date, 'Date de fin de notification', NEW.notification_end_date, 'publier', NEW.publish, 'Notifier', NEW.push), NEW.date_modification, NEW.user_modification);
        END

CREATE TRIGGER `update_news` AFTER UPDATE ON `news`
 FOR EACH ROW BEGIN
                INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
                VALUES ('ActualitÃ©s', NEW.id, 'update', JSON_OBJECT('titre', OLD.title, 'Contenu', OLD.content, 'Type', OLD.type, 'Date de notification', OLD.notification_date, 'Date de fin de notification', OLD.notification_end_date, 'publier', OLD.publish, 'Notifier', OLD.push), JSON_OBJECT('titre', NEW.title, 'Contenu', NEW.content, 'Type', NEW.type, 'Date de notification', NEW.notification_date, 'Date de fin de notification', NEW.notification_end_date, 'publier', NEW.publish, 'Notifier', NEW.push), NEW.date_modification, NEW.user_modification);
            END

#------------------------------------------------------------
#       Table  partners
#------------------------------------------------------------
CREATE TRIGGER `delete_partners` AFTER DELETE ON `partners`
 FOR EACH ROW BEGIN
            DECLARE old_partner_type_name VARCHAR(255);
            SELECT type INTO old_partner_type_name FROM partner_type WHERE id = OLD.type_id;
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
            VALUES ('Partenaires', OLD.id, 'update', JSON_OBJECT('nom', OLD.name, 'logo', OLD.image, 'url', OLD.url, 'type', old_partner_type_name, 'publiÃ©', OLD.publish), NOW(), @current_user_name);
        END

CREATE TRIGGER `insert_partners` AFTER INSERT ON `partners`
 FOR EACH ROW BEGIN
        DECLARE new_partner_type_name VARCHAR(255);
        SELECT type INTO new_partner_type_name FROM partner_type WHERE id = NEW.type_id;
        INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
        VALUES ('Partenaires', NEW.id, 'insert', JSON_OBJECT('nom', NEW.name, 'logo', NEW.image, 'url', NEW.url, 'type', new_partner_type_name, 'publiÃ©', NEW.publish), NEW.date_modification, NEW.user_modification);
        END

CREATE TRIGGER `update_partners` AFTER UPDATE ON `partners`
 FOR EACH ROW BEGIN
                DECLARE old_partner_type_name VARCHAR(255);
                DECLARE new_partner_type_name VARCHAR(255);
                SELECT type INTO old_partner_type_name FROM partner_type WHERE id = OLD.type_id;
                SELECT type INTO new_partner_type_name FROM partner_type WHERE id = NEW.type_id;
                INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
                VALUES ('Partenaires', NEW.id, 'update', JSON_OBJECT('nom', OLD.name, 'logo', OLD.image, 'url', OLD.url, 'type', old_partner_type_name, 'publiÃ©', OLD.publish), JSON_OBJECT('nom', NEW.name, 'logo', NEW.image, 'url', NEW.url, 'type', new_partner_type_name, 'publiÃ©', NEW.publish), NEW.date_modification, NEW.user_modification);
            END

#------------------------------------------------------------
#       Table  partners_type
#------------------------------------------------------------
CREATE TRIGGER `delete_partnerType` AFTER DELETE ON `partner_type`
 FOR EACH ROW BEGIN
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
            VALUES ('Type de partenaire', OLD.id, 'delete', JSON_OBJECT('type', OLD.type), NOW(), @current_user_name);
        END

CREATE TRIGGER `insert_partnerType` AFTER INSERT ON `partner_type`
 FOR EACH ROW BEGIN
        INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
        VALUES ('Type de partenaire', NEW.id, 'insert', JSON_OBJECT('type', NEW.type), NEW.date_modification, NEW.user_modification);
        END

CREATE TRIGGER `update_partnerType` AFTER UPDATE ON `partner_type`
 FOR EACH ROW BEGIN
                INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
                VALUES ('Type de partenaire', NEW.id, 'update', JSON_OBJECT('type', OLD.type), JSON_OBJECT('type', NEW.type), NEW.date_modification, NEW.user_modification);
            END
