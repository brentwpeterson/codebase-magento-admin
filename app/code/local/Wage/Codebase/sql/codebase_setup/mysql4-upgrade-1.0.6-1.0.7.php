<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('codebase/refreshtime')};
CREATE TABLE {$this->getTable('codebase/refreshtime')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `code` varchar(255) NOT NULL default '',
  `update_time` datetime NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();
