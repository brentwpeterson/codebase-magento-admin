<?php
class Wage_Codebase_Model_Milestones extends Mage_Core_Model_Abstract {
    public function _construct() {
        parent::_construct();
        $this->_init('codebase/milestones');
    }

    /**
     * find row by project id and milestone id
     */
    public function findMilestone($projectId, $milestoneId) {
        $find = $this->getCollection()
            ->addFieldToFilter('milestone_id',$milestoneId)
            ->addFieldToFilter('project_id',$projectId)
            ->getFirstItem();

        if ($find->getId()) {
            $this->load($find->getId());
        }

        return $this;
    }
}
