<?php
class Wage_Codebase_Block_Adminhtml_Ownerreport_Activities extends Mage_Adminhtml_Block_Template {

    public function _construct()
    {   	
        parent::_construct();
        $this->setTemplate('codebase/owneractivities.phtml');
    }

    public function getData()
    {
        $actoremail = $this->getRequest()->getParam('actoremail'); 
        $projectid = $this->getRequest()->getParam('projectid');
        $from = $this->getRequest()->getParam('fromdate');
        $from = date('Y-m-d', $from);
        $to = $this->getRequest()->getParam('todate');
        $to = date('Y-m-d', $to);

 
       $collection = Mage::getModel('codebase/activities')->getCollection(); 


       $collection->addFieldToFilter('project_id',$projectid)
                ->addFieldToFilter('actor_email', array('in' => $actoremail))
                ->addFieldToFilter('timestamp', array(
                    'from'     => $from,
                    'to'       => $to,
                    'datetime' => true
                ))
                ->addFieldToFilter('content', array('neq' => NULL))
                ->setOrder('number','ASC');

       return $collection;
    }

    
}
