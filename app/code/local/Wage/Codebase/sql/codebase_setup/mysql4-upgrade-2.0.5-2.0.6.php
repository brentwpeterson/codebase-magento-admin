<?php
$installer = $this;
$installer->startSetup();

$installer->run(" ALTER TABLE  {$this->getTable('codebase/tickets')} ADD `orig_estimator` varchar(255) default NULL; ");
$installer->run(" ALTER TABLE  {$this->getTable('codebase/tickets')} ADD `final_estimate` int(11) default NULL; ");

$installer->endSetup();
