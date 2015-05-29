<?php
$installer = $this;
$installer->startSetup();

$installer->run(" ALTER TABLE  {$this->getTable('codebase/projects')} ADD `backlog_id` varchar(255); ");

$installer->endSetup();
