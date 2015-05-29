<?php
$installer = $this;

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('codebase/milestones')};
CREATE TABLE {$this->getTable('codebase/milestones')} (
    `id` int(11) unsigned NOT NULL auto_increment,
    `milestone_id` int(11) NOT NULL,
    `project_id` int(11) NOT NULL,
    `project_name` varchar(255) NOT NULL default '',
    `name` varchar(255) NOT NULL default '',
    `responsible_user_id` int(11) default NULL,
    `parent_id` int(11) default NULL,
    `estimated_time` int(11) NOT NULL,
    `status` varchar(15) NOT NULL,
    `description` text NOT NULL,
    `start_at` date default NULL,
    `deadline` date default NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
