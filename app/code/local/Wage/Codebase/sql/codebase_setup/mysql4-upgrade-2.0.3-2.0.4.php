<?php
$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('codebase/sentreport')};
CREATE TABLE {$this->getTable('codebase/sentreport')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `project_id` int(11) NOT NULL,
  `html_text` text NOT NULL default '',
  `to_email` varchar(255) NOT NULL default '',
  `cc_email` varchar(255) NOT NULL default '',
  `report_sent_at` datetime NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();
