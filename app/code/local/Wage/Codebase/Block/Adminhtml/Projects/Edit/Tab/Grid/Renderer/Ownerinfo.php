<?php
class Wage_Codebase_Block_Adminhtml_Projects_Edit_Tab_Grid_Renderer_Ownerinfo extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }
    protected function _getValue(Varien_Object $row)
    {      
        $projectId = $row->getData($this->getColumn()->getIndex());	
        $userId = $row->getData($this->getColumn()->getUserId());	
	$user = Mage::getModel('codebase/users')->getCollection()
				->addFieldToFilter('user_id',$userId)
				->getFirstItem();
        return $user->getFirstName().' '.$user->getLastName();
    }
}
