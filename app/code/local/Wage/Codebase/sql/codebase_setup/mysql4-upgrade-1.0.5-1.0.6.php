<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE  {$this->getTable('codebase/tickets')} ADD `resolution` varchar(255) NOT NULL default '' ;
");

$installer->endSetup();
$installer->endSetup();
