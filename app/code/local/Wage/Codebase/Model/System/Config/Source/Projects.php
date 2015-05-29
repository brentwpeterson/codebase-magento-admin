<?php
class Wage_Codebase_Model_System_Config_Source_Projects
{
    public function toOptionArray($isMultiselect=false)
    {
        $options = array();
        /*$collection = Mage::getModel('codebase/tickets')->getCollection();

        foreach ($collection as $ticket) {
            if (empty($options[$ticket->getCompany()])) {
                $options[$ticket->getProjectId()] = array('value' => $ticket->getProjectId(), 'label' => $ticket->getProjectName());
            }
        }*/

        $projects = Mage::getModel('codebase/projects')->getCollection()
                        ->addFieldToFilter('status','active');

        foreach ($projects as $ticket) {
            $options[$ticket->getProjectId()] = array('value' => $ticket->getProjectId(), 'label' => $ticket->getProjectName());
        }

        return $options;
    }
}
