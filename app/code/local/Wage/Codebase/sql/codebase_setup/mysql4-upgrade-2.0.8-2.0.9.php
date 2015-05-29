<?php
$installer = $this;
$installer->startSetup();

$installer->run(" ALTER TABLE  {$this->getTable('codebase/timetracking')} ADD `reduction_time` int(11); ");
$installer->run(" ALTER TABLE  {$this->getTable('codebase/timetracking')} ADD `reduction_reason` varchar(150) default ''; ");
$installer->run(" ALTER TABLE  {$this->getTable('codebase/timetracking')} ADD `reduction_approval` varchar(150) default ''; ");


$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('codebase/reports')};
CREATE TABLE {$this->getTable('codebase/reports')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `project_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `from_date` datetime NULL,
  `to_date` datetime NULL,
  `created_at` datetime NULL,
  `updated_at` datetime NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();
