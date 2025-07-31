    CREATE TABLE users (
        user_data_id INT PRIMARY KEY AUTO_INCREMENT,
        first_name VARCHAR(50),
        last_name VARCHAR(50),
        gender ENUM('Male', 'Female', 'Other'),
        birth_date DATE,
        email VARCHAR(100) UNIQUE,
        mobile VARCHAR(15) UNIQUE,
        country VARCHAR(50),
        state VARCHAR(50),
        city VARCHAR(50),
        pincode VARCHAR(10),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

ALTER TABLE `users` 
DROP FOREIGN KEY `fk_users_tag_link`;

ALTER TABLE `users` 
ADD CONSTRAINT `fk_users_tag_link`
FOREIGN KEY (`tag_link_id`) 
REFERENCES `tag_link`(`id`) 
ON DELETE CASCADE;


CREATE TABLE user_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_data_id INT,
    image_path VARCHAR(255),
    FOREIGN KEY (user_data_id) REFERENCES users(user_data_id) ON DELETE CASCADE
);

CREATE TABLE doctors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_data_id INT,
    doc_name VARCHAR(100),
    doc_phone VARCHAR(15),
    FOREIGN KEY (user_data_id) REFERENCES users(user_data_id) ON DELETE CASCADE
);

CREATE TABLE emergency_contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_data_id INT,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    mobile VARCHAR(15),
    email VARCHAR(100),
    relation VARCHAR(50),
    FOREIGN KEY (user_data_id) REFERENCES users(user_data_id) ON DELETE CASCADE
);

CREATE TABLE medical_info (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_data_id INT,
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'),
    height DECIMAL(5,2),
    weight DECIMAL(5,2),
    any_disease ENUM('yes', 'no'),
    disease VARCHAR(255) NULL,
    any_allergies ENUM('yes', 'no'),
    allergies VARCHAR(255) NULL,
    prescription TEXT,
    important_notes TEXT,
    FOREIGN KEY (user_data_id) REFERENCES users(user_data_id) ON DELETE CASCADE
);


DROP PROCEDURE IF EXISTS InsertOrReplaceUserData;

DELIMITER //

CREATE PROCEDURE InsertOrReplaceUserData(
    IN p_tag_id VARCHAR(255),
    IN p_user_id INT,
    IN p_first_name VARCHAR(255),
    IN p_last_name VARCHAR(255),
    IN p_gender VARCHAR(50),
    IN p_birth_date DATE,
    IN p_email VARCHAR(255),
    IN p_mobile VARCHAR(20),
    IN p_country VARCHAR(255),
    IN p_state VARCHAR(255),
    IN p_city VARCHAR(255),
    IN p_pincode VARCHAR(20),
    IN p_address TEXT,
    IN p_blood_group VARCHAR(10),
    IN p_height INT,
    IN p_weight INT,
    IN p_any_disease ENUM('yes', 'no'),
    IN p_disease TEXT,
    IN p_any_allergies ENUM('yes', 'no'),
    IN p_allergies TEXT,
    IN p_prescription TEXT,
    IN p_important_notes TEXT,
    IN p_emergency_first_name VARCHAR(255),
    IN p_emergency_last_name VARCHAR(255),
    IN p_emergency_mobile VARCHAR(20),
    IN p_emergency_email VARCHAR(255),
    IN p_relation VARCHAR(50),
    IN p_doc_name VARCHAR(255),
    IN p_doc_phone VARCHAR(20),
    IN p_user_image VARCHAR(255) -- Path of uploaded image
)
BEGIN
    DECLARE tag_index_id INT;
    DECLARE tag_link_index_id INT;
    DECLARE existing_user_data_id INT;
    DECLARE new_user_data_id INT;

    -- Error handling: If any error occurs, rollback the transaction
    DECLARE EXIT HANDLER FOR SQLEXCEPTION 
    BEGIN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'An error occurred while inserting or replacing user data';
    END;

    -- Get the tag_link_id from the tag_link table
    SELECT savemeout_id INTO tag_index_id FROM savmeyes WHERE id = p_tag_id LIMIT 1;
    INSERT INTO test(user_data_id,reason) VALUES(tag_index_id,"tag_index_id");

    SELECT id INTO tag_link_index_id FROM tag_link WHERE tag_id = tag_index_id AND user_id= p_user_id LIMIT 1;
    INSERT INTO test(user_data_id,reason) VALUES(tag_link_index_id,"tag_link_index_id");

    -- If no tag_link_id is found, terminate the procedure
    IF tag_link_index_id IS NULL THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Invalid tag_id';
    END IF;

    -- Check if a user exists with this tag_link_id
    SELECT user_data_id INTO existing_user_data_id FROM users WHERE tag_link_id = tag_link_index_id LIMIT 1;
    INSERT INTO test(user_data_id,reason) VALUES(user_data_id,"user Data id exists");

    IF existing_user_data_id IS NOT NULL THEN
        -- Delete related records first to maintain integrity
        INSERT INTO test(user_data_id,reason) VALUES(existing_user_data_id,"exisitng exists");
        DELETE FROM users WHERE user_data_id = existing_user_data_id;
    END IF;

    -- Insert new user record
    INSERT INTO users (first_name, last_name, gender, birth_date, email, mobile, country, state, city, pincode, address, tag_link_id)
    VALUES (p_first_name, p_last_name, p_gender, p_birth_date, p_email, p_mobile, p_country, p_state, p_city, p_pincode, p_address, tag_link_index_id);

    -- Get the newly inserted user ID
    SET new_user_data_id = LAST_INSERT_ID();

    -- Insert into medical_info
    INSERT INTO medical_info (user_data_id, blood_group, height, weight, any_disease, disease, any_allergies, allergies, prescription, important_notes)
    VALUES (new_user_data_id, p_blood_group, p_height, p_weight, p_any_disease, p_disease, p_any_allergies, p_allergies, p_prescription, p_important_notes);

    -- Insert into emergency_contacts
    INSERT INTO emergency_contacts (user_data_id, first_name, last_name, mobile, email, relation)
    VALUES (new_user_data_id, p_emergency_first_name, p_emergency_last_name, p_emergency_mobile, p_emergency_email, p_relation);

    -- Insert into doctors
    INSERT INTO doctors (user_data_id, doc_name, doc_phone)
    VALUES (new_user_data_id, p_doc_name, p_doc_phone);

    -- Insert user image if provided
    INSERT INTO user_images (user_data_id, image_path)
    VALUES (new_user_data_id, p_user_image);

    -- Return user_data_id and tag_id
    SELECT existing_user_data_id AS old_data_id, new_user_data_id AS new_data_id, p_tag_id AS tag_id;
END //

DELIMITER ;






ALTER TABLE savemeyes ADD COLUMN user_id INT UNIQUE NULL;
ALTER TABLE savemeyes ADD CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL;

DROP PROCEDURE IF EXISTS GetUserData;

DELIMITER //
CREATE PROCEDURE GetUserData(IN tagId VARCHAR(255),IN tagPin INT)
BEGIN
    DECLARE tag_index_id INT;
    DECLARE tag_link_index_id INT;
    DECLARE existing_user_data_id INT;

    SELECT savemeout_id INTO tag_index_id FROM savmeyes WHERE id = tagId AND pin = tagPin LIMIT 1;
    SELECT id INTO tag_link_index_id FROM tag_link WHERE tag_id = tag_index_id LIMIT 1;

    -- If no tag_link_id is found, terminate the procedure
    IF tag_link_index_id IS NULL THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Invalid tag_id';
    END IF;

    -- Check if a user exists with this tag_link_id
    SELECT user_data_id INTO existing_user_data_id FROM users WHERE tag_link_id = tag_link_index_id LIMIT 1;

    IF existing_user_data_id IS  NULL THEN
        SIGNAL SQLSTATE 'EMPTY' 
        SET MESSAGE_TEXT = 'NO Data for user';
    END IF;

    SELECT
        -- Users Table
        u.user_data_id AS user_data_id,
        u.first_name AS user_first_name,
        u.last_name AS user_last_name,
        u.gender AS user_gender,
        u.birth_date AS user_birth_date,
        u.email AS user_email,
        u.mobile AS user_mobile,
        u.country AS user_country,
        u.state AS user_state,
        u.city AS user_city,
        u.pincode AS user_pincode,
        u.address AS user_address,

        -- Medical Info Table
        m.blood_group AS medical_blood_group,
        m.height AS medical_height,
        m.weight AS medical_weight,
        m.any_disease AS medical_any_disease,
        m.disease AS medical_disease,
        m.any_allergies AS medical_any_allergies,
        m.allergies AS medical_allergies,
        m.prescription AS medical_prescription,
        m.important_notes AS medical_important_notes,

        -- Emergency Contacts Table
        e.first_name AS emergency_first_name,
        e.last_name AS emergency_last_name,
        e.mobile AS emergency_mobile,
        e.email AS emergency_email,
        e.relation AS emergency_relation,

        -- Doctors Table
        d.doc_name AS doctor_name,
        d.doc_phone AS doctor_phone,

        i.image_path AS user_image,
        -- Savmeyes Table
        s.qr AS qr

    FROM users u
    LEFT JOIN medical_info m ON u.user_data_id = m.user_data_id
    LEFT JOIN emergency_contacts e ON u.user_data_id = e.user_data_id
    LEFT JOIN doctors d ON u.user_data_id = d.user_data_id
    LEFT JOIN savmeyes s ON tagId = s.id
    LEFT JOIN user_images i ON u.user_data_id = i.user_data_id
    WHERE u.user_data_id = existing_user_data_id LIMIT 1;
END; //

DELIMITER ;

CALL GetUserData("SMO2C960B3990")


CREATE TABLE tag_link (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tag_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES auth(user_id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES savmeyes(savemeout_id) ON DELETE CASCADE
);


DROP PROCEDURE IF EXISTS LinkTagToUser;
DELIMITER $$
CREATE PROCEDURE LinkTagToUser(
    IN p_user_id INT, 
    IN p_tag_id INT, 
    IN p_tag_pin VARCHAR(255)
)
BEGIN
    DECLARE v_savemeout_id INT;
    DECLARE v_link_exists INT;
    
    -- Start transaction for atomic operations
    START TRANSACTION;

    -- Check if the tag_id and tag_pin combination is valid
    SELECT savemeout_id INTO v_savemeout_id
    FROM savmeyes
    WHERE id = p_tag_id AND pin = p_tag_pin
    LIMIT 1;

    -- If no match found, throw error and rollback
    IF v_savemeout_id IS NULL THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'COMIN001'; -- Tag ID or PIN incorrect
    END IF;

    -- Check if savemeout_id already exists in tag_link
    SELECT COUNT(*) INTO v_link_exists 
    FROM tag_link 
    WHERE tag_id = v_savemeout_id;

    -- If already linked, throw error and rollback
    IF v_link_exists > 0 THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'ALTAP001'; -- Tag already linked
    END IF;

    -- Insert into tag_link table
    INSERT INTO tag_link (user_id, tag_id) 
    VALUES (p_user_id, v_savemeout_id);

    -- Commit transaction if everything is successful
    COMMIT;
    
END $$

DELIMITER ;
