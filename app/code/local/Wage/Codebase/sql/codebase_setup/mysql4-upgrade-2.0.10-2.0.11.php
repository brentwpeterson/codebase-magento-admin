<?php
$installer = $this;
$installer->startSetup();

$installer->run(" ALTER TABLE  {$this->getTable('codebase/tickets')} ADD `comments` int(11) default 0; ");

$installer->endSetup();
