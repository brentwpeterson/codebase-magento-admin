<?php
$installer = $this;
$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('codebase/changepo')};
CREATE TABLE {$this->getTable('codebase/changepo')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `rule_title` varchar(255) NOT NULL default '',
  `current_user_id` int(11) NOT NULL,
  `new_user_id`  int(11) NOT NULL,
  `effective_from` datetime NULL,
  `status`  int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();
