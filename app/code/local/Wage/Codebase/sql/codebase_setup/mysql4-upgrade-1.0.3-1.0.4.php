<?php
$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('codebase/time')};
CREATE TABLE {$this->getTable('codebase/time')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `last_activity_time` datetime NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();
