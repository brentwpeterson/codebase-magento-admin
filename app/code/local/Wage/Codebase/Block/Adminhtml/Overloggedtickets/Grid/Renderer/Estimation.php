<?php
class Wage_Codebase_Block_Adminhtml_Overloggedtickets_Grid_Renderer_Estimation extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }
    protected function _getValue(Varien_Object $row)
    {      
        $estHours = $row->getData($this->getColumn()->getIndex());
	    $spentHours = $row->getData($this->getColumn()->getSpentindex());


        if($spentHours > $estHours) {
        $out = 'Over estimation';
        } elseif($spentHours < $estHours) {
            $out = 'Under estimation';
        } else {
            $out = 'Both are equal';
        }

        return $out;
    }
}
