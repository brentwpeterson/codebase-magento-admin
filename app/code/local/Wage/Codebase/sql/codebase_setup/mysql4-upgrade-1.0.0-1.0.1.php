<?php
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE  {$this->getTable('admin_user')} ADD `api_user` varchar(255) NOT NULL ;
ALTER TABLE  {$this->getTable('admin_user')} ADD `api_key` varchar(255) NOT NULL ;
");

$installer->endSetup();