<?php
class Wage_Codebase_Block_Adminhtml_Notification extends Mage_Adminhtml_Block_Widget_Form {
 protected function _toHtml() {
		if(Mage::getStoreConfig('codebase/general/apiuser') && Mage::getStoreConfig('codebase/general/apikey')) {
			return;	
		} else {
                $url = Mage::helper("adminhtml")->getUrl('system_config/edit/section');
        		$html = '<div class="notification-global"> ';
        		$message ='Caution !! It seems that API credentials are not added for Magento Codebase Integration. '.'<a href="'.$this->getUrl('adminhtml/system_config/edit').'section/codebase/">Click Here to add credentials</a>';
        		$messageHtml ='<font color=red><b>'.$message.'</b></font>';
			$html .= $messageHtml.'</div>';
        		return $html;
		}	
	}
	
}
