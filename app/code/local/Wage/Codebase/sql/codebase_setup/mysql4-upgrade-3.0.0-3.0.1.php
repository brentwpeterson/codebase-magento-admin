<?php
$installer = $this;
$installer->startSetup();

$installer->run(" ALTER TABLE  {$this->getTable('codebase/activities')} ADD `timestamp` datetime NULL; ");

$installer->endSetup();
