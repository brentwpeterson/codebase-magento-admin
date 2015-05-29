<?php
$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('codebase/activities')};
CREATE TABLE {$this->getTable('codebase/activities')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `number` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL default '',
  `content` text NOT NULL default '',
  `actor_email` varchar(255) NOT NULL default '',
  `actor_name` varchar(255) NOT NULL default '',
  `project_id` int(11)  NULL,
  `status` varchar(255) NOT NULL default '',
  `priority` varchar(255) NOT NULL default '',
  `assignee` varchar(255) NOT NULL default '',
  `project_permalink` varchar(255) NOT NULL default '',
  `project_name` varchar(255) NOT NULL default '',
  `estimated_time` time NULL,
  `time_added` time NULL,
  `time_left` time NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();
