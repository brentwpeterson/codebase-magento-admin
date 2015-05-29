<?php
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE  {$this->getTable('codebase/projects')} ADD `last_report_sent_at` datetime default NULL;
");


$installer->endSetup();
