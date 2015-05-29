<?php
class Wage_Codebase_Block_Adminhtml_Milestones_Renderer_Timespent extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }
    protected function _getValue(Varien_Object $row)
    {      
        $milestoneId = $row->getData($this->getColumn()->getMilestoneId());
	$projectId = $row->getData($this->getColumn()->getProjectId());


$newcollection = Mage::getModel('codebase/tickets')->getCollection()            
	    ->addFieldToFilter('milestone_id', $milestoneId)
	    ->addFieldToFilter('project_id', $projectId);

$newcollection->getSelect()
            ->columns('SUM((total_time_spent)) as total_spent_time')
            ->group(array('milestone_id','project_id'));

	foreach($newcollection as $item)
	{
		return $item['total_spent_time'];
	}       
    }
}
