<?php
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE  {$this->getTable('admin_user')} ADD `wagento_staff` tinyint(3) unsigned NOT NULL ;
");

$installer->endSetup();