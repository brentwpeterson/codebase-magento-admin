<?php
class Wage_Codebase_Block_Adminhtml_Evsa_Renderer_Link extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }
    protected function _getValue(Varien_Object $row)
    {      
        $val = $row->getData($this->getColumn()->getIndex());
        $permalink = $row->getData('permalink');

        /*prepare class to mark row*/
        $orig_estimated_time = $row->getData('orig_estimated_time');
        if (!empty($orig_estimated_time)) {
            $over = $row->getData('total_time_spent')/$orig_estimated_time;
        }
        else {
            $over = 1;
        }

        if ($over > 1.25) {
            $class = "over_25";
        }
        elseif ($over > 1.1) {
            $class = "over_10";
        }
        elseif ($over > 0.9) {
            $class = "over_0";
        }
        else {
            $class = "under_09";
        }

        $url = Mage::getStoreConfig('codebase/general/host').'/projects/'.$permalink.'/tickets/'.$val;
        $out = "<div><a class='{$class}' href={$url} target='_blank'>Ticket-{$val}</a></div>"; 
        return $out;
    }
}
