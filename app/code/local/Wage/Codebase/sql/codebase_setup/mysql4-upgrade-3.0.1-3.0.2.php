<?php
$installer = $this;
$installer->startSetup();

$installer->run(" ALTER TABLE  {$this->getTable('codebase/projectindex')} ADD `company` varchar(255); ");

$installer->endSetup();
