<?php
class Wage_Codebase_Block_Adminhtml_Evsa extends Mage_Adminhtml_Block_Widget_Grid_Container {
    public function __construct() {
        $this->_controller = 'adminhtml_evsa';
        $this->_blockGroup = 'codebase';  
        $this->_headerText = Mage::helper('codebase')->__('Estimates Hours vs Actual hours');
        parent::__construct();
        $this->_removeButton('add');
    }
}
