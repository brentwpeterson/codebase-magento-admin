<?php
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE  {$this->getTable('codebase/projects')} ADD `client_id` int(11) default NULL;
");



$installer->endSetup();

