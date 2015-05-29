<?php
$installer = $this;
$installer->startSetup();

$installer->run(" ALTER TABLE  {$this->getTable('codebase/activities')} CHANGE `timestamp` `timestamp` DATE NULL DEFAULT NULL; ");

$installer->endSetup();
