#Ce fichier contient la base de données à créer

CREATE DATABASE IF NOT EXISTS raspisms;
USE raspisms;

CREATE TABLE IF NOT EXISTS setting
(
	id INT NOT NULL AUTO_INCREMENT,
    id_user INT NOT NULL,
	name VARCHAR(50) NOT NULL,
	value VARCHAR(1000) NOT NULL,
	PRIMARY KEY (id),
	FOREIGN KEY (id_user) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	UNIQUE (name)
);

CREATE TABLE IF NOT EXISTS scheduled
(
	id INT NOT NULL AUTO_INCREMENT,
    id_user INT NOT NULL,
    send_by VARCHAR(25) DEFAULT NULL,
	at DATETIME NOT NULL,
    text VARCHAR(1000) NOT NULL,
    flash BOOLEAN NOT NULL DEFAULT 0,
	FOREIGN KEY (id_user) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS received
(
	id INT NOT NULL AUTO_INCREMENT,
	at DATETIME NOT NULL,
    text VARCHAR(1000) NOT NULL,
    origin VARCHAR(20) NOT NULL,
    destination VARCHAR(20),
	command BOOLEAN NOT NULL DEFAULT 0,
	PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS sended
(
	id INT NOT NULL AUTO_INCREMENT,
	at DATETIME NOT NULL,
    text VARCHAR(1000) NOT NULL,
    origin VARCHAR(20) NOT NULL,
    destination VARCHAR(20),
    flash BOOLEAN NOT NULL DEFAULT 0,
	PRIMARY KEY (id)
);


CREATE TABLE IF NOT EXISTS contact
(
	id INT NOT NULL AUTO_INCREMENT,
    id_user INT NOT NULL,
	name VARCHAR(100) NOT NULL,
	number VARCHAR(20) NOT NULL,
	PRIMARY KEY (id),
	FOREIGN KEY (id_user) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	UNIQUE (name)
);

CREATE TABLE IF NOT EXISTS group
(
	id INT NOT NULL AUTO_INCREMENT,
    id_user INT NOT NULL,
	name VARCHAR(100) NOT NULL,
	PRIMARY KEY (id),
	FOREIGN KEY (id_user) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	UNIQUE (name)
);

CREATE TABLE IF NOT EXISTS group_contact
(
	id INT NOT NULL AUTO_INCREMENT,
	id_group INT NOT NULL,
	id_contact INT NOT NULL,
	PRIMARY KEY (id),
	FOREIGN KEY (id_group) REFERENCES group (id) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (id_contact) REFERENCES contact (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS scheduled_contact
(
	id INT NOT NULL AUTO_INCREMENT,
	id_scheduled INT NOT NULL,
	id_contact INT NOT NULL,
	PRIMARY KEY (id),
	FOREIGN KEY (id_scheduled) REFERENCES scheduled (id) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (id_contact) REFERENCES contact (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS scheduled_group
(
	id INT NOT NULL AUTO_INCREMENT,
	id_scheduled INT NOT NULL,
	id_group INT NOT NULL,
	PRIMARY KEY (id),
	FOREIGN KEY (id_scheduled) REFERENCES scheduled (id) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (id_group) REFERENCES group (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS scheduled_number
(
	id INT NOT NULL AUTO_INCREMENT,
	id_scheduled INT NOT NULL,
	number VARCHAR(20) NOT NULL,
	PRIMARY KEY (id),
	FOREIGN KEY (id_scheduled) REFERENCES scheduled (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS command
(
	id INT NOT NULL AUTO_INCREMENT,
    id_user INT NOT NULL,
	name VARCHAR(25) NOT NULL,
	script VARCHAR(100) NOT NULL,
	admin BOOLEAN NOT NULL,
	PRIMARY KEY (id),
	FOREIGN KEY (id_user) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	UNIQUE (name)
);

CREATE TABLE IF NOT EXISTS event
(
	id INT NOT NULL AUTO_INCREMENT,
    id_user INT NOT NULL,
	type VARCHAR(25) NOT NULL,
	at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	text VARCHAR(255) NOT NULL,	
	FOREIGN KEY (id_user) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS user
(
	id INT NOT NULL AUTO_INCREMENT,
	email VARCHAR(150) NOT NULL,
	password VARCHAR(255) NOT NULL,
	admin BOOLEAN NOT NULL DEFAULT FALSE,
	transfer BOOLEAN NOT NULL DEFAULT FALSE,
	PRIMARY KEY (id),
	UNIQUE (email)
);

CREATE TABLE IF NOT EXISTS user_number
(
	id INT NOT NULL AUTO_INCREMENT,
	id_user INT NOT NULL,
	phone_number VARCHAR(25) NOT NULL,
	platform VARCHAR(100) NOT NULL,
    platform_datas JSON NOT NULL,
    CHECK (JSON_VALID(platform_datas)),
	PRIMARY KEY (id),
	FOREIGN KEY (id_user) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE
);

#Table to ensure external validation process by mailing or other
CREATE TABLE IF NOT EXISTS validation
(
	id INT NOT NULL AUTO_INCREMENT,
	token VARCHAR(200) NOT NULL,
    random VARCHAR(32) NOT NULL,
    action VARCHAR(200) NOT NULL,
    datas JSON NOT NULL,
    CHECK (JSON_VALID(datas)),
	PRIMARY KEY (id),
    UNIQUE(token)
);

CREATE TABLE IF NOT EXISTS transfer
(
	id INT NOT NULL AUTO_INCREMENT,
	id_received INT NOT NULL,
	PRIMARY KEY (id),
	FOREIGN KEY (id_received) REFERENCES received (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS smsstop
(
	id INT NOT NULL AUTO_INCREMENT,
    id_user INT NOT NULL,
	number VARCHAR(20) NOT NULL,
	PRIMARY KEY (id),
	FOREIGN KEY (id_user) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	UNIQUE (number)
);

CREATE TABLE IF NOT EXISTS webhook
(
	id INT NOT NULL AUTO_INCREMENT,
    id_user INT NOT NULL,
	url VARCHAR(250) NOT NULL,
	type INT NOT NULL,
	FOREIGN KEY (id_user) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS webhook_querie
(
	id INT NOT NULL AUTO_INCREMENT,
	url VARCHAR(250) NOT NULL,
	datas VARCHAR(10000) NOT NULL,
	PRIMARY KEY (id)
);
