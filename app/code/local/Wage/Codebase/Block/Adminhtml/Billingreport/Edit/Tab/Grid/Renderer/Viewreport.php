<?php
class Wage_Codebase_Block_Adminhtml_Billingreport_Edit_Tab_Grid_Renderer_Viewreport extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }
    protected function _getValue(Varien_Object $row)
    {      

	$index = $row->getData($this->getColumn()->getIndex());
   $URL = $this->getUrl("*/*/generatereport/",array("report_id"=>$index));
	$out = "<a href='".$URL."'>View Report</a>";
        return $out;
    }
}
