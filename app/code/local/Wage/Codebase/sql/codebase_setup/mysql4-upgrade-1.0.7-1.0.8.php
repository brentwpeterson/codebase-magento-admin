<?php
$installer = $this;

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('codebase/statuses')};
CREATE TABLE {$this->getTable('codebase/statuses')} (
    `id` int(11) unsigned NOT NULL auto_increment,
    `status_id` int(11) NOT NULL,
    `project_id` int(11) NOT NULL,
    `name` varchar(255) NOT NULL default '',
    `background_color` varchar(15),
    `order` int(11) NOT NULL,
    `treat_as_closed` varchar(255) NOT NULL default '',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
