<?php
class Wage_Codebase_Block_Adminhtml_Client extends Mage_Adminhtml_Block_Widget_Grid_Container {
    public function __construct()
    {
        $this->_controller = 'adminhtml_client';
        $this->_blockGroup = 'codebase';
        $this->_headerText = Mage::helper('codebase')->__('Weekly Client Hours');
        parent::__construct();
        $this->setTemplate('/report/grid/container.phtml');
        $this->_removeButton('add');
    }
}
