# log_table(`nick`, `said`, `when`, `channel`)
CREATE TABLE log_table (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `nick` VARCHAR( 255 ) NOT NULL ,
    `when` DATETIME NOT NULL ,
    `said` TEXT NOT NULL ,
    `channel` VARCHAR( 255 ) NOT NULL
) ENGINE = MYISAM ;

CREATE TABLE message (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `nick` VARCHAR( 255 ) NOT NULL ,
    `when` DATETIME NOT NULL ,
    `message` TEXT NOT NULL ,
    `from` VARCHAR( 255 ) NOT NULL
) ENGINE = MYISAM ;
