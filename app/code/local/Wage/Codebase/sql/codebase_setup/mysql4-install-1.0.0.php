<?php
$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('codebase/tickets')};
CREATE TABLE {$this->getTable('codebase/tickets')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `ticket_id` int(11) NOT NULL,
  `summary` text NOT NULL default '',
  `ticket_type` varchar(255) NOT NULL default '',
  `project_name` varchar(255) NOT NULL default '',
  `permalink` varchar(255) NOT NULL default '',
  `assignee` varchar(255) NOT NULL default '',
  `reporter` varchar(255) NOT NULL default '',
  `category_name` varchar(255) NOT NULL default '',
  `priority_name` varchar(255) NOT NULL default '',
  `status_name` varchar(255) NOT NULL default '',
  `type_name` varchar(255) NOT NULL default '',
  `tags` varchar(255) NOT NULL default '',
  `created_at` datetime NULL,
  `updated_at` datetime NULL,
  `project_id` int(11)  NULL,
  `milestone_name` varchar(255) NOT NULL default '',
  `estimated_time` int(11)  NULL,
  `total_time_spent` int(11)  NULL,
  `time_left` int(11)  NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();