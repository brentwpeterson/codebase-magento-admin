<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE  {$this->getTable('codebase/tickets')} ADD `orig_estimated_time` int(11) default NULL;
");

$installer->run("
ALTER TABLE  {$this->getTable('codebase/tickets')} ADD `milestone_id` int(11) default NULL;
");


$installer->endSetup();

