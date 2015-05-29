<?php
class Wage_Codebase_Block_Adminhtml_Projects_Edit_Tab_Grid_Renderer_Sendreport extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }
    protected function _getValue(Varien_Object $row)
    {      
        $ownerId = $row->getData($this->getColumn()->getUserId());
	$clientId = $row->getData($this->getColumn()->getIndex());
	$permalink = $row->getData($this->getColumn()->getPermalink());	        	        	        	        
	if($ownerId && $clientId)
	{
	//$URL = $this->getUrl("*/*/sendreport/",array("owner_id"=>$ownerId,"client_id"=>$clientId,"permalink"=>$permalink));
	$URL = $this->getUrl("*/*/viewreport/",array("owner_id"=>$ownerId,"client_id"=>$clientId,"permalink"=>$permalink));
	$out = "<a href='".$URL."'>View Report</a>";
	} else {
	$out = "Client or Product Owner not configured to this project yet.";
	}
        return $out;
    }
}
