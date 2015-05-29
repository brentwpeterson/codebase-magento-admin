<?php
$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('codebase/projects')};
CREATE TABLE {$this->getTable('codebase/projects')} (
  `entity_id` int(11) unsigned NOT NULL auto_increment,
  `project_name` varchar(255) NOT NULL default '',
  `project_id` int(11),
  `permalink` varchar(255) NOT NULL default '',
  `status` varchar(255) NOT NULL default '',
  `owner_name` varchar(255) NOT NULL default '',
  `owner_email` varchar(255) NOT NULL default '',
  `owner_phone` varchar(255) NOT NULL default '',
  `api_user` varchar(255) NOT NULL default '',
  `api_key` varchar(255) NOT NULL default '',
  PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- DROP TABLE IF EXISTS {$this->getTable('codebase/projectindex')};
CREATE TABLE {$this->getTable('codebase/projectindex')} (
  `entity_id` int(11) unsigned NOT NULL auto_increment,
  `project_id` int(11),
  `user_id` int(11),
  `user_email` varchar(255) NOT NULL default '',
  PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();
