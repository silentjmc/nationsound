#------------------------------------------------------------
#       Table artist
#------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER `delete_artist` AFTER DELETE ON `artist` FOR EACH ROW BEGIN
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
            VALUES ('Artistes', OLD.id_artist, 'delete', JSON_OBJECT('nom', OLD.name_artist, 'description', OLD.content_artist, 'image', OLD.image_artist, 'miniature', OLD.thumbnail, 'type_music', OLD.type_music ), NOW(), @current_user_name);
        END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `insert_artist` AFTER INSERT ON `artist` FOR EACH ROW BEGIN
            INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
            VALUES ('Artistes', NEW.id_artist, 'insert', JSON_OBJECT('nom', NEW.name_artist, 'description', NEW.content_artist, 'image', NEW.image_artist, 'miniature', NEW.thumbnail, 'type_music', NEW.type_music ), NEW.date_modification_artist, NEW.user_modification_artist);
        END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_artist` AFTER UPDATE ON `artist` FOR EACH ROW BEGIN
                INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
                VALUES ('Artistes', NEW.id_artist, 'update', JSON_OBJECT('nom', OLD.name_artist, 'description', OLD.content_artist, 'image', OLD.image_artist, 'miniature', OLD.thumbnail, 'type_music', OLD.type_music ), JSON_OBJECT('nom', NEW.name_artist, 'description', NEW.content_artist, 'image', NEW.image_artist, 'miniature', NEW.thumbnail, 'type_music', NEW.type_music ), NEW.date_modification_artist, NEW.user_modification_artist);
            END
$$
DELIMITER ;

#------------------------------------------------------------
#       Table event
#------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER `delete_event` AFTER DELETE ON `event` FOR EACH ROW BEGIN
            DECLARE old_type VARCHAR(255);
            DECLARE old_artist VARCHAR(255);
            DECLARE old_date VARCHAR(255);
            DECLARE old_event_location VARCHAR(255);
            SELECT name_type INTO old_type FROM event_type WHERE id_event_type = OLD.type_id;
            SELECT name_artist INTO old_artist FROM artist WHERE id_artist = OLD.artist_id;
            SELECT date INTO old_date FROM event_date WHERE id_event_date = OLD.date_id;
            SELECT name_event_location INTO old_event_location FROM event_location WHERE id_event_location = OLD.event_location_id;
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
            VALUES ('Évènements', OLD.id_event, 'delete', JSON_OBJECT('heure de début', OLD.heure_debut, 'heure de fin', OLD.heure_fin, 'type', old_type, 'artiste', old_artist, 'date', old_date, 'lieu', old_event_location, 'publier', OLD.publish_event), NOW(), @current_user_name);
        END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `insert_event` AFTER INSERT ON `event` FOR EACH ROW BEGIN
            DECLARE new_type VARCHAR(255);
            DECLARE new_artist VARCHAR(255);
            DECLARE new_date VARCHAR(255);
            DECLARE new_event_location VARCHAR(255);
            SELECT name_type INTO new_type FROM event_type WHERE id_event_type = NEW.type_id;
            SELECT name_artist INTO new_artist FROM artist WHERE id_artist = NEW.artist_id;
            SELECT date INTO new_date FROM event_date WHERE id_event_date = NEW.date_id;
            SELECT name_event_location INTO new_event_location FROM event_location WHERE id_event_location = NEW.event_location_id;
            INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
            VALUES ('Évènements', NEW.id_event, 'insert', JSON_OBJECT('heure de début', NEW.heure_debut, 'heure de fin', NEW.heure_fin, 'type', new_type, 'artiste', new_artist, 'date', new_date, 'lieu', new_event_location, 'publier', NEW.publish_event), NEW.date_modification_event, NEW.user_modification_event);
        END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_event` AFTER UPDATE ON `event` FOR EACH ROW BEGIN
                DECLARE old_type VARCHAR(255);
                DECLARE old_artist VARCHAR(255);
                DECLARE old_date VARCHAR(255);
                DECLARE old_event_location VARCHAR(255);
                DECLARE new_type VARCHAR(255);
                DECLARE new_artist VARCHAR(255);
                DECLARE new_date VARCHAR(255);
                DECLARE new_event_location VARCHAR(255);
                SELECT name_type INTO old_type FROM event_type WHERE id_event_type = OLD.type_id;
                SELECT name_artist INTO old_artist FROM artist WHERE id_artist = OLD.artist_id;
                SELECT date INTO old_date FROM event_date WHERE id_event_date = OLD.date_id;
                SELECT name_event_location INTO old_event_location FROM event_location WHERE id_event_location = OLD.event_location_id;
                SELECT name_type INTO new_type FROM event_type WHERE id_event_type = NEW.type_id;
                SELECT name_artist INTO new_artist FROM artist WHERE id_artist = NEW.artist_id;
                SELECT date INTO new_date FROM event_date WHERE id_event_date = NEW.date_id;
                SELECT name_event_location INTO new_event_location FROM event_location WHERE id_event_location = NEW.event_location_id;
                INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
                VALUES ('Évènements', NEW.id_event, 'update', JSON_OBJECT('heure de début', OLD.heure_debut, 'heure de fin', OLD.heure_fin, 'type', old_type, 'artiste', old_artist, 'date', old_date, 'lieu', old_event_location, 'publier', OLD.publish_event), JSON_OBJECT('heure de début', NEW.heure_debut, 'heure de fin', NEW.heure_fin, 'type', new_type, 'artiste', new_artist, 'date', new_date, 'lieu', new_event_location, 'publier', NEW.publish_event), NEW.date_modification_event, NEW.user_modification_event);
            END
$$
DELIMITER ;

#------------------------------------------------------------
#       Table event_date
#------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER `delete_eventDate` AFTER DELETE ON `event_date` FOR EACH ROW BEGIN
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
            VALUES ('Dates du festival', OLD.id_event_date, 'delete', JSON_OBJECT('date', OLD.date ), NOW(), @current_user_name);
        END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `insert_eventDate` AFTER INSERT ON `event_date` FOR EACH ROW BEGIN
        INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
        VALUES ('Dates du festival', NEW.id_event_date, 'insert', JSON_OBJECT('date', NEW.date ), NEW.date_modification_event_date, NEW.user_modification_event_date);
        END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_eventDate` AFTER UPDATE ON `event_date` FOR EACH ROW BEGIN
                INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
                VALUES ('Dates du festival', NEW.id_event_date, 'update', JSON_OBJECT('date', OLD.date ), JSON_OBJECT('date', NEW.date ), NEW.date_modification_event_date, NEW.user_modification_event_date);
            END
$$
DELIMITER ;

#------------------------------------------------------------
#       Table event_location
#------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER `delete_eventLocation` AFTER DELETE ON `event_location` FOR EACH ROW BEGIN
            DECLARE old_type_location VARCHAR(255);
            SELECT name_location_type INTO old_type_location FROM location_type WHERE id_location_type = OLD.type_location_id;
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
            VALUES ('Lieux', OLD.id_event_location, 'delete', JSON_OBJECT('nom', OLD.name_event_location, 'latitude', OLD.latitude, 'longitude', OLD.longitude, 'description', OLD.content_event_location, 'publier', OLD.publish_event_location, 'type', old_type_location), NOW(), @current_user_name);
        END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `insert_eventLocation` AFTER INSERT ON `event_location` FOR EACH ROW BEGIN
        DECLARE new_type_location VARCHAR(255);
        SELECT name_location_type INTO new_type_location FROM location_type WHERE id_location_type = NEW.type_location_id;
        INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
        VALUES ('Lieux', NEW.id_event_location, 'insert', JSON_OBJECT('nom', NEW.name_event_location, 'latitude', NEW.latitude, 'longitude', NEW.longitude, 'description', NEW.content_event_location, 'publier', NEW.publish_event_location, 'type', new_type_location), NEW.date_modification_event_location, NEW.user_modification_event_location);
        END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_eventLocation` AFTER UPDATE ON `event_location` FOR EACH ROW BEGIN
                DECLARE old_type_location VARCHAR(255);
                DECLARE new_type_location VARCHAR(255);
                SELECT name_location_type INTO old_type_location FROM location_type WHERE id_location_type = OLD.type_location_id;
                SELECT name_location_type INTO new_type_location FROM location_type WHERE id_location_type = NEW.type_location_id;
                INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
                VALUES ('Lieux', NEW.id_event_location, 'update', JSON_OBJECT('nom', OLD.name_event_location, 'latitude', OLD.latitude, 'longitude', OLD.longitude, 'description', OLD.content_event_location, 'publier', OLD.publish_event_location, 'type', old_type_location), JSON_OBJECT('nom', NEW.name_event_location, 'latitude', NEW.latitude, 'longitude', NEW.longitude, 'description', NEW.content_event_location, 'publier', NEW.publish_event_location, 'type', new_type_location), NEW.date_modification_event_location, NEW.user_modification_event_location);
            END
$$
DELIMITER ;

#------------------------------------------------------------
#       Table event_type
#------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER `delete_eventType` AFTER DELETE ON `event_type` FOR EACH ROW BEGIN
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
            VALUES ('Type d''Évènements', OLD.id_event_type, 'delete', JSON_OBJECT('type', OLD.name_type ), NOW(), @current_user_name);
        END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `insert_eventType` AFTER INSERT ON `event_type` FOR EACH ROW BEGIN
        INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
        VALUES ('Type d''Évènements', NEW.id_event_type, 'insert', JSON_OBJECT('type', NEW.name_type ), NEW.date_modification_event_type, NEW.user_modification_event_type);
        END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_eventType` AFTER UPDATE ON `event_type` FOR EACH ROW BEGIN
                INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
                VALUES ('Type d''Évènements', NEW.id_event_type, 'update', JSON_OBJECT('type', OLD.name_type ), JSON_OBJECT('type', NEW.name_type ), NEW.date_modification_event_type, NEW.user_modification_event_type);
            END
$$
DELIMITER ;

#------------------------------------------------------------
#       Table faq
#------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER `delete_faq` AFTER DELETE ON `faq` FOR EACH ROW BEGIN
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
            VALUES ('FAQ', OLD.id_faq, 'delete', JSON_OBJECT('question', OLD.question, 'reponse', OLD.reponse, 'position', OLD.position_faq, 'publier', OLD.publish_faq ), NOW(), @current_user_name);
        END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `insert_faq` AFTER INSERT ON `faq` FOR EACH ROW BEGIN
        INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
        VALUES ('FAQ', NEW.id_faq, 'insert', JSON_OBJECT('question', NEW.question, 'reponse', NEW.reponse, 'position', NEW.position_faq, 'publier', NEW.publish_faq ), NEW.date_modification_faq, NEW.user_modification_faq);
        END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_faq` AFTER UPDATE ON `faq` FOR EACH ROW BEGIN
                INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
                VALUES ('FAQ', NEW.id_faq, 'update', JSON_OBJECT('question', OLD.question, 'reponse', OLD.reponse, 'position', OLD.position_faq, 'publier', OLD.publish_faq ), JSON_OBJECT('question', NEW.question, 'reponse', NEW.reponse, 'position', NEW.position_faq, 'publier', NEW.publish_faq ), NEW.date_modification_faq, NEW.user_modification_faq);
            END
$$
DELIMITER ;

#------------------------------------------------------------
#       Table information
#------------------------------------------------------------

DELIMITER $$
CREATE TRIGGER `delete_information` AFTER DELETE ON `information` FOR EACH ROW BEGIN
        DECLARE old_section_information VARCHAR(255);
        SELECT section_label INTO old_section_information FROM information_section WHERE id_information_section = OLD.section_information_id;
        INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
        VALUES ('Informations', OLD.id_information, 'delete', JSON_OBJECT('titre', OLD.title_information, 'description', OLD.content_information, 'position', OLD.position_information, 'section', old_section_information, 'publier', OLD.publish_information), NOW(), @current_user_name);
    END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `insert_information` AFTER INSERT ON `information` FOR EACH ROW BEGIN
    DECLARE new_section_information VARCHAR(255);
    SELECT section_label INTO new_section_information FROM information_section WHERE id_information_section = NEW.section_information_id;
    INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
    VALUES ('Informations', NEW.id_information, 'insert', JSON_OBJECT('titre', NEW.title_information, 'description', NEW.content_information, 'position', NEW.position_information, 'section', new_section_information, 'publier', NEW.publish_information), NEW.date_modification_information, NEW.user_modification_information);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_information` AFTER UPDATE ON `information` FOR EACH ROW BEGIN
                DECLARE old_section_information VARCHAR(255);
                DECLARE new_section_information VARCHAR(255);
                SELECT section_label INTO old_section_information FROM information_section WHERE id_information_section = OLD.section_information_id;
                SELECT section_label INTO new_section_information FROM information_section WHERE id_information_section = NEW.section_information_id;
                INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
                VALUES ('Informations', NEW.id_information, 'update', JSON_OBJECT('titre', OLD.title_information, 'description', OLD.content_information, 'position', OLD.position_information, 'section', old_section_information, 'publier', OLD.publish_information), JSON_OBJECT('titre', NEW.title_information, 'description', NEW.content_information, 'position', NEW.position_information, 'section', new_section_information, 'publier', NEW.publish_information), NEW.date_modification_information, NEW.user_modification_information);
            END
$$
DELIMITER ;

#------------------------------------------------------------
#       Table information_section
#------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER `delete_informationSection` AFTER DELETE ON `information_section` FOR EACH ROW BEGIN
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
            VALUES ('Section d''information', OLD.id_information_section, 'delete', JSON_OBJECT('section', OLD.section_label, 'titre', OLD.title_information_section, 'description', OLD.content_information_section, 'position', OLD.position_information_section ), NOW(), @current_user_name);
        END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `insert_sectionInformation` AFTER INSERT ON `information_section` FOR EACH ROW BEGIN
        INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
        VALUES ('Section d''information', NEW.id_information_section, 'insert', JSON_OBJECT('section', NEW.section_label, 'titre', NEW.title_information_section, 'description', NEW.content_information_section, 'position', NEW.position_information_section ), NEW.date_modification_information_section, NEW.user_modification_information_section);
        END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_informationSection` AFTER UPDATE ON `information_section` FOR EACH ROW BEGIN
                INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
                VALUES ('Section d''information', NEW.id_information_section, 'update', JSON_OBJECT('section', OLD.section_label, 'titre', OLD.title_information_section, 'description', OLD.content_information_section, 'position', OLD.position_information_section ), JSON_OBJECT('section', NEW.section_label, 'titre', NEW.title_information_section, 'description', NEW.content_information_section, 'position', NEW.position_information_section ), NEW.date_modification_information_section, NEW.user_modification_information_section);
            END
$$
DELIMITER ;
#------------------------------------------------------------
#       Table location_type
#------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER `delete_locationType` AFTER DELETE ON `location_type` FOR EACH ROW BEGIN
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
            VALUES ('Type de lieux', OLD.id_location_type, 'delete', JSON_OBJECT('type', OLD.name_location_type, 'symbol', OLD.symbol), NOW(), @current_user_name);
        END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `insert_locationType` AFTER INSERT ON `location_type` FOR EACH ROW BEGIN
        INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
        VALUES ('Type de lieux', NEW.id_location_type, 'insert', JSON_OBJECT('type', NEW.name_location_type, 'symbol', NEW.symbol), NEW.date_modification_location_type, NEW.user_modification_location_type);
        END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_locationType` AFTER UPDATE ON `location_type` FOR EACH ROW BEGIN
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
            VALUES ('Type de lieux', NEW.id_location_type, 'update', JSON_OBJECT('type', OLD.name_location_type, 'symbol', OLD.symbol), JSON_OBJECT('type', NEW.name_location_type, 'symbol', NEW.symbol), NEW.date_modification_location_type, NEW.user_modification_location_type);
        END
$$
DELIMITER ;
#------------------------------------------------------------
#       Table news
#------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER `delete_news` AFTER DELETE ON `news` FOR EACH ROW BEGIN
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
            VALUES ('Actualités', OLD.id_news, 'delete', JSON_OBJECT('titre', OLD.title_news, 'Contenu', OLD.content_news, 'Type', OLD.type_news, 'Date de notification', OLD.notification_date, 'Date de fin de notification', OLD.notification_end_date, 'publier', OLD.publish_news, 'Notifier', OLD.push), NOW(), @current_user_name);
        END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `insert_news` AFTER INSERT ON `news` FOR EACH ROW BEGIN
        INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
        VALUES ('Actualités', NEW.id_news, 'insert', JSON_OBJECT('titre', NEW.title_news, 'Contenu', NEW.content_news, 'Type', NEW.type_news, 'Date de notification', NEW.notification_date, 'Date de fin de notification', NEW.notification_end_date, 'publier', NEW.publish_news, 'Notifier', NEW.push), NEW.date_modification_news, NEW.user_modification_news);
        END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_news` AFTER UPDATE ON `news` FOR EACH ROW BEGIN
                INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
                VALUES ('Actualités', NEW.id_news, 'update', JSON_OBJECT('titre', OLD.title_news, 'Contenu', OLD.content_news, 'Type', OLD.type_news, 'Date de notification', OLD.notification_date, 'Date de fin de notification', OLD.notification_end_date, 'publier', OLD.publish_news, 'Notifier', OLD.push), JSON_OBJECT('titre', NEW.title_news, 'Contenu', NEW.content_news, 'Type', NEW.type_news, 'Date de notification', NEW.notification_date, 'Date de fin de notification', NEW.notification_end_date, 'publier', NEW.publish_news, 'Notifier', NEW.push), NEW.date_modification_news, NEW.user_modification_news);
            END
$$
DELIMITER ;

#------------------------------------------------------------
#       Table  partner
#------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER `delete_partner` AFTER DELETE ON `partner` FOR EACH ROW BEGIN
            DECLARE old_partner_type_name VARCHAR(255);
            SELECT title_partner_type INTO old_partner_type_name FROM partner_type WHERE id_partner_type = OLD.type_partner_id;
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
            VALUES ('Partenaires', OLD.id_partner, 'delete', JSON_OBJECT('nom', OLD.name_partner, 'logo', OLD.image_partner, 'url', OLD.url, 'type', old_partner_type_name, 'publier', OLD.publish_partner), NOW(), @current_user_name);
        END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `insert_partner` AFTER INSERT ON `partner` FOR EACH ROW BEGIN
        DECLARE new_partner_type_name VARCHAR(255);
        SELECT title_partner_type INTO new_partner_type_name FROM partner_type WHERE id_partner_type = NEW.type_partner_id;
        INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
        VALUES ('Partenaires', NEW.id_partner, 'insert', JSON_OBJECT('nom', NEW.name_partner, 'logo', NEW.image_partner, 'url', NEW.url, 'type', new_partner_type_name, 'publier', NEW.publish_partner), NEW.date_modification_partner, NEW.user_modification_partner);
        END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_partner` AFTER UPDATE ON `partner` FOR EACH ROW BEGIN
                DECLARE old_partner_type_name VARCHAR(255);
                DECLARE new_partner_type_name VARCHAR(255);
                SELECT title_partner_type INTO old_partner_type_name FROM partner_type WHERE id_partner_type = OLD.type_partner_id;
                SELECT title_partner_type INTO new_partner_type_name FROM partner_type WHERE id_partner_type = NEW.type_partner_id;
                INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
                VALUES ('Partenaires', NEW.id_partner, 'update', JSON_OBJECT('nom', OLD.name_partner, 'logo', OLD.image_partner, 'url', OLD.url, 'type', old_partner_type_name, 'publier', OLD.publish_partner), JSON_OBJECT('nom', NEW.name_partner, 'logo', NEW.image_partner, 'url', NEW.url, 'type', new_partner_type_name, 'publier', NEW.publish_partner), NEW.date_modification_partner, NEW.user_modification_partner);
            END
$$
DELIMITER ;

#------------------------------------------------------------
#       Table  partners_type
#------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER `delete_partnerType` AFTER DELETE ON `partner_type` FOR EACH ROW BEGIN
            INSERT INTO entity_history (entity_name, entity_id, action, old_values, date_action, user)
            VALUES ('Type de partenaire', OLD.id_partner_type, 'delete', JSON_OBJECT('type', OLD.title_partner_type), NOW(), @current_user_name);
        END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `insert_partnerType` AFTER INSERT ON `partner_type` FOR EACH ROW BEGIN
        INSERT INTO entity_history (entity_name, entity_id, action, new_values, date_action, user)
        VALUES ('Type de partenaire', NEW.id_partner_type, 'insert', JSON_OBJECT('type', NEW.title_partner_type), NEW.date_modification_partner_type, NEW.user_modification_partner_type);
        END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_partnerType` AFTER UPDATE ON `partner_type` FOR EACH ROW BEGIN
                INSERT INTO entity_history (entity_name, entity_id, action, old_values, new_values, date_action, user)
                VALUES ('Type de partenaire', NEW.id_partner_type, 'update', JSON_OBJECT('type', OLD.title_partner_type), JSON_OBJECT('type', NEW.title_partner_type), NEW.date_modification_partner_type, NEW.user_modification_partner_type);
            END
$$
DELIMITER ;
