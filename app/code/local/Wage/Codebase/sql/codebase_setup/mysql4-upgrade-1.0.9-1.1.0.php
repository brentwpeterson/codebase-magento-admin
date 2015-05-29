<?php
$installer = $this;

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('codebase/timetracking')};
CREATE TABLE {$this->getTable('codebase/timetracking')} (
    `id` int(11) unsigned NOT NULL auto_increment,
    `tracking_id` int(11) NOT NULL,
    `summary` VARCHAR(255),
    `project_id` int(11) NOT NULL,
    `ticket_id` int(11) NOT NULL,
    `minutes` int(11) NOT NULL,
    `session_date` DATE,
    `user_id` VARCHAR(25),
    `created_at` TIMESTAMP,
    `updated_at` TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
