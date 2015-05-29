<?php
$installer = $this;
$installer->startSetup();

$installer->run(" ALTER TABLE  {$this->getTable('codebase/tickets')} ADD `estimate_need` int(11) default 1; ");

$installer->endSetup();
