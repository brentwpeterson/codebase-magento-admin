<?php
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE  {$this->getTable('codebase/projects')} ADD `product_id` int(8) unsigned NOT NULL ;
");

$installer->endSetup();
