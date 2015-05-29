<?php
$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE  {$this->getTable('codebase/changepo')} ADD `projects` varchar(255) default NULL");

$installer->endSetup();
