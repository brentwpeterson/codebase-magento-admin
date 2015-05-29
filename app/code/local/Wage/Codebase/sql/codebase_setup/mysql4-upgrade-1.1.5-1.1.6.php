<?php
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE  {$this->getTable('codebase/projects')} ADD `user_id` int(11) default NULL;
");

$installer->run("
ALTER TABLE  {$this->getTable('codebase/projects')} ADD `role_id` int(11) default NULL;
");

$installer->run("
ALTER TABLE  {$this->getTable('codebase/projects')} ADD `last_updated_at` datetime default NULL;
");

$installer->run("
ALTER TABLE  {$this->getTable('codebase/projects')} DROP `owner_name` ;
");
$installer->run("
ALTER TABLE  {$this->getTable('codebase/projects')} DROP `owner_email` ;
");
$installer->run("
ALTER TABLE  {$this->getTable('codebase/projects')} DROP `owner_phone` ;
");
$installer->run("
ALTER TABLE  {$this->getTable('codebase/projects')} DROP `api_user` ;
");
$installer->run("
ALTER TABLE  {$this->getTable('codebase/projects')} DROP `api_key` ;
");

$installer->endSetup();

