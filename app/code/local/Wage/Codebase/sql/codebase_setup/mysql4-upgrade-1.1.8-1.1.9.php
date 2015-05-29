<?php
$installer = $this;

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('codebase/teams')};
CREATE TABLE {$this->getTable('codebase/teams')} (
    `team_id` int(11) unsigned NOT NULL auto_increment,    
    `team_name` varchar(255) NOT NULL default '',
    `members` varchar(255) NOT NULL default '',
    PRIMARY KEY (`team_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->run("
ALTER TABLE  {$this->getTable('codebase/projects')} ADD `team_id` int(11) default NULL;
");


$installer->endSetup();
