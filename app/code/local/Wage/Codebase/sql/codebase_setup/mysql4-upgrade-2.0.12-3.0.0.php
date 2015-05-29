<?php
$installer = $this;
$installer->startSetup();

$installer->run(" ALTER TABLE  {$this->getTable('codebase/changepo')} ADD `effective_to` datetime NULL; ");

$installer->endSetup();
