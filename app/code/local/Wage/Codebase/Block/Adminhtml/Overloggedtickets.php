<?php
class Wage_Codebase_Block_Adminhtml_Overloggedtickets extends Mage_Adminhtml_Block_Widget_Grid_Container {


    public function __construct()
  {
    $this->_controller = 'adminhtml_overloggedtickets';
    $this->_blockGroup = 'codebase';
      $resource = Mage::getSingleton('core/resource');

      $readConnection = $resource->getConnection('core_read');
      $table = $resource->getTableName('codebase/refreshtime');
      $time = $readConnection->fetchCol('SELECT update_time FROM ' . $table . ' WHERE code = "ticket_refresh" ');
    if($time[0]){
        $this->_headerText = Mage::helper('codebase')->__('Tickets logged time more than 8 Hours (Last refreshed on %s)',$time[0]);
    } else {
        $this->_headerText = Mage::helper('codebase')->__('Tickets logged time more than 8 Hours');
    }
    parent::__construct();
    $this->_removeButton('add');     
  }

	
}
