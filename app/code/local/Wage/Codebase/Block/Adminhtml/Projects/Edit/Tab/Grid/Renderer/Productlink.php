<?php
class Wage_Codebase_Block_Adminhtml_Projects_Edit_Tab_Grid_Renderer_Productlink extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }
    protected function _getValue(Varien_Object $row)
    {      
        $val = $row->getData($this->getColumn()->getIndex());	
	if($val > 0)    {
	$url = Mage::helper("adminhtml")->getUrl("adminhtml/catalog_product/edit",array("id"=>$val));
        $out = "<div><a href=$url target='_blank'>Edit Product</a></div>";
	} else {
	$out = "Product is not configured with project yet.";
	}
        return $out;
    }
}
