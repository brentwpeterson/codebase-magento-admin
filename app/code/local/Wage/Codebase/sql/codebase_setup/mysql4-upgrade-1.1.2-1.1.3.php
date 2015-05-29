<?php
$installer = $this;

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('codebase/priorities')};
CREATE TABLE {$this->getTable('codebase/priorities')} (
    `id` int(11) unsigned NOT NULL auto_increment,
    `priority_id` int(11) NOT NULL,
    `project_id` int(11) NOT NULL,
    `name` varchar(255) NOT NULL default '',
    `color` varchar(15),
    `default` varchar(15) NOT NULL default 'false',
    `position` int(11) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
