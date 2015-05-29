<?php
class Wage_Codebase_Block_Adminhtml_Milestones extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct()
  {
    $this->_controller = 'adminhtml_milestones';
    $this->_blockGroup = 'codebase';

    $resource = Mage::getSingleton('core/resource');

      $readConnection = $resource->getConnection('core_read');
      $table = $resource->getTableName('codebase/refreshtime');
      $time = $readConnection->fetchCol('SELECT update_time FROM ' . $table . ' WHERE code = "milestone_refresh" ');
    if($time[0]){
        $this->_headerText = Mage::helper('codebase')->__('Milestones (Last refreshed on %s)',$time[0]);
    } else {
        $this->_headerText = Mage::helper('codebase')->__('Tickets');
    }

    parent::__construct();
    $this->_removeButton('add');
      $this->_addButton('adminhtml_milestones', array(
          'label' => $this->__('Refresh Milestones'),
          'onclick' => "setLocation('{$this->getUrl('*/*/refresh')}')",
      ));
     
  }

	
}
