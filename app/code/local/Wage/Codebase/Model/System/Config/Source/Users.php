<?php
class Wage_Codebase_Model_System_Config_Source_Users {
    public function toOptionArray($isMultiselect=false)
    {
        $options = array();
        $collection = Mage::getModel('codebase/users')->getCollection();

        foreach ($collection as $user) {
            $options[] = array('value' => $user->getUserId(), 'label' => $user->getUserName());
        }
        
        return $options;
    }
}
