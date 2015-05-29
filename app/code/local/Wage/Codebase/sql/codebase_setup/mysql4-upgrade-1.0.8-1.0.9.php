<?php
$installer = $this;

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('codebase/users')};
CREATE TABLE {$this->getTable('codebase/users')} (
    `id` int(11) unsigned NOT NULL auto_increment,
    `company` VARCHAR(100),
    `api_key` VARCHAR(50) NOT NULL,
    `email_address` VARCHAR(255) NOT NULL,
    `last_activity` TIMESTAMP,
    `user_id` VARCHAR(25),
    `first_name` VARCHAR(50),
    `last_name` VARCHAR(50),
    `time_zone` VARCHAR(100),
    `user_name` VARCHAR(100),
    `enabled` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
