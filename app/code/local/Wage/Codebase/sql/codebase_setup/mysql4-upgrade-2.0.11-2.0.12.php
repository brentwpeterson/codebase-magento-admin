<?php
$installer = $this;
$installer->startSetup();

$installer->run(" ALTER TABLE  {$this->getTable('codebase/tickets')} ADD `assignee_id` int(11); ");
$installer->run(" ALTER TABLE  {$this->getTable('codebase/tickets')} ADD `last_status_updater_id` int(11); ");

$installer->endSetup();
