<?php
$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('codebase/ticketsreport')};
CREATE TABLE {$this->getTable('codebase/ticketsreport')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `ticket_id` int(11) NOT NULL,
  `summary` text NOT NULL default '',
  `ticket_type` varchar(255) NOT NULL default '',
  `project_name` varchar(255) NOT NULL default '',
  `permalink` varchar(255) NOT NULL default '',
  `assignee` varchar(255) NOT NULL default '',
  `reporter` varchar(255) NOT NULL default '',
  `project_id` int(11)  NULL,
  `orig_estimated_time` int(11)  NULL,
  `updated_estimated_time` int(11)  NULL,
  `updated_at` datetime NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();
