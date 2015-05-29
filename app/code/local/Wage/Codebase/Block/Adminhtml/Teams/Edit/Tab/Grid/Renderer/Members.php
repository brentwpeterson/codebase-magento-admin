<?php
class Wage_Codebase_Block_Adminhtml_Teams_Edit_Tab_Grid_Renderer_Members extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }
    protected function _getValue(Varien_Object $row)
    {      
        $teamId = $row->getData($this->getColumn()->getIndex());
	$team = Mage::getModel('codebase/teams')->load($teamId);
	$members = explode(',',$team->getMembers());

	$users = Mage::getModel('codebase/users')->getCollection()
				->addFieldToFilter('user_id',array('in' => $members));
	$out = '';
	foreach($users as $user){
	$out .= $user->getFirstName().' '.$user->getLastName().'<br/>';
	}
        return $out;
    }
}
