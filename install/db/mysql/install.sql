CREATE TABLE IF NOT EXISTS `excel2sql_class_list` (
    `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `SORT` int(11) not null DEFAULT 500,
    `TABLE_NAME` varchar(64) NOT NULL,
    `ORM_PATH` TEXT NOT NULL,
    PRIMARY KEY (`ID`)
);