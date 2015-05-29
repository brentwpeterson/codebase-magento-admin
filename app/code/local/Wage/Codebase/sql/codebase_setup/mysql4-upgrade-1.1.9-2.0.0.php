<?php
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE  {$this->getTable('codebase/projects')} ADD `last_client_contact` datetime default NULL;
");


$installer->endSetup();
