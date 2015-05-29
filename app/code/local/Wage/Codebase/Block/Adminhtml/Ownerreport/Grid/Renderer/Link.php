<?php
class Wage_Codebase_Block_Adminhtml_Ownerreport_Grid_Renderer_Link extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }
    protected function _getValue(Varien_Object $row)
    {      
        $val = $row->getData($this->getColumn()->getIndex());
	    $actoremail = $row->getData($this->getColumn()->getActoremail());
        $projectid = $row->getData($this->getColumn()->getProjectid());

        $fromdate = $this->getColumn()->getFromdate();
        $todate = $this->getColumn()->getTodate();

        $url = Mage::helper("adminhtml")->getUrl("codebase/adminhtml_ownerreport/activities/",array("actoremail"=>$actoremail,"projectid"=>$projectid,"fromdate"=>$fromdate,"todate"=>$todate));
       
        $out = "<div><a href=$url target='_blank'>".$val."</a></div>";
        return $out;
    }
}
