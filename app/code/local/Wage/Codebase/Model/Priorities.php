<?php
class Wage_Codebase_Model_Priorities extends Mage_Core_Model_Abstract {
    public function _construct() {
        parent::_construct();
        $this->_init('codebase/priorities');
    }

    /**
     * find row by project id and priority id
     */
    public function findPriority($projectId, $priorityId) {
        $find = $this->getCollection()
            ->addFieldToFilter('priority_id',$priorityId)
            ->addFieldToFilter('project_id',$projectId)
            ->getFirstItem();

        if ($find->getId()) {
            $this->load($find->getId());
        }

        return $this;
    }

    /**
     * find priority id by project and label
     */
    public function findPrioritybyLabel($projectId, $label) {
        $find = $this->getCollection()
            ->addFieldToFilter('name', array('regexp' => strtolower($label)))
            ->addFieldToFilter('project_id',$projectId)
            ->getFirstItem();

        if ($find->getId()) {
            $this->load($find->getId());
        }

        return $this;
    }
}
