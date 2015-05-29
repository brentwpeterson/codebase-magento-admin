<?php
class Wage_Codebase_Block_Adminhtml_Evsa_Renderer_Hour extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }
    protected function _getValue(Varien_Object $row)
    {      
        $minutes = htmlspecialchars($row->getData($this->getColumn()->getIndex()));
        if (!empty($minutes)) {
            $hours = number_format($minutes/60, 2, '.', ' ');
            $out = "{$hours}"; 
        }
        else {
            $out = "0";
        }

        return $out;
    }
}
