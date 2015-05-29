<?php
class Wage_Codebase_Model_Timetracking extends Mage_Core_Model_Abstract
{
    public function _construct() {
        parent::_construct();
        $this->_init('codebase/timetracking');
    }

    /**
     * load record by tracking id
     * @author atheotsky
     */
    public function loadByTrackingId($tracking_id) {
        $find = $this->getCollection()
            ->addFieldToFilter('tracking_id',$tracking_id)
            ->getFirstItem();

        if ($find->getId()) {
            $this->load($find->getId());
        }

        return $this;
    }

    /**
     * return period for timetracking
     * @author atheotsky
     */
    public function identifyPeriod($project) {
        //return null; // TODO: remove later after having all data fetched

        if ($this->getCollection()->addFieldToFilter('project_id', $project)->count() == 0) {
            return null;
        }

        return 'month';
    }

    /**
     * fetch time logged by a period of time
     * @author atheotsky
     */
    public function getTimetrackingByPeriod($period = null, $from = null, $to = null, $projects = null, $user_id = null) {
        /*set default range : last 3 months*/
        if (empty($from)) {
            $from = date('Y-m-d', strtotime('-1 month'));
        }
        else {
            $from = date('Y-m-d', strtotime($from));
        }

        if (empty($to)) {
            $to = date('Y-m-d');
        }
        else {
            $to = date('Y-m-d', strtotime($to));
        }

        $main_table = Mage::getSingleton('core/resource')->getTableName('codebase/timetracking');
        $ticket_table = Mage::getSingleton('core/resource')->getTableName('codebase/tickets');
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');

        switch ($period) {
        case 'year':
            $periodformat = $adapter->getDateExtractSql('main_table.updated_at', Varien_Db_Adapter_Interface::INTERVAL_YEAR);
            break;
        case 'month':
            $periodformat = $adapter->getDateFormatSql('main_table.updated_at', '%Y-%m');
            break;
        case 'week':
            $periodformat = $adapter->getDateFormatSql('main_table.updated_at', '%Y, Week #%v');
            break;
        default:
            // case day
            $periodformat = $adapter->getDateFormatSql('main_table.updated_at', '%Y-%m-%d');
            break;
        }

        $select = $adapter->select()->from(array('main_table' => $main_table), array());
        $select->columns(array('period' => $periodformat));
        $select->columns(array('project_id', 'minutes'));

        /*inject user_id condition*/
        if ($user_id) $this->injectUserCondition($select, $user_id);

        /*apply filters*/
        $select->where('main_table.updated_at >= ?', $from);
        $select->where('main_table.updated_at <= ?', $to);

        /*get projects and join to collect project names*/
        $p = $adapter->select()->from($ticket_table)->group('permalink');
        $select->joinLeft(array('p' => $p), 'p.project_id = main_table.project_id', array('project' => 'project_name'));

        $period_sum = array(); $project_sum = array();
        foreach ($adapter->fetchAll($select) as $row) {
            /*get total for sum type 2*/
            if (empty($project_sum['total']['name'])) $project_sum['total']['name'] = 'Total';
            if (empty($project_sum['total']['data'][$row['period']])) $project_sum['total']['data'][$row['period']] = 0;
            $project_sum['total']['data'][$row['period']] += $row['minutes'];

            /*process sum type 1*/
            if (empty($period_sum[$row['period']])) $period_sum[$row['period']] = array();

            /*apply project filter here to make sure we have total correct*/
            if (!in_array($row['project_id'], $projects)) continue;

            /*process sum type 1 - cont*/
            if (empty($period_sum[$row['period']][$row['project']])) $period_sum[$row['period']][$row['project']] = 0;
            if (empty($period_sum[$row['period']]['total'])) $period_sum[$row['period']]['total'] = 0;

            $period_sum[$row['period']][$row['project']] += $row['minutes'];
            $period_sum[$row['period']]['total'] += $row['minutes'];

            /*process sum type 2*/
            if (empty($project_sum[$row['project_id']])) $project_sum[$row['project_id']] = array();
            if (empty($project_sum[$row['project_id']]['name'])) $project_sum[$row['project_id']]['name'] = $row['project'];
            if (empty($project_sum[$row['project_id']]['data'][$row['period']])) $project_sum[$row['project_id']]['data'][$row['period']] = 0;

            $project_sum[$row['project_id']]['data'][$row['period']] += $row['minutes'];
        }

        /*round 2 to make sure we have all perido filled*/
        foreach (array_keys($period_sum) as $period) {
            foreach ($project_sum as $key=>$value) {
                if (empty($value['data'][$period])) $project_sum[$key]['data'][$period] = 0;
            }
        }

        ksort($period_sum);
        foreach ($project_sum as $project) ksort($project['data']);

        return array('period' => $period_sum, 'project' => $project_sum);
    }

    /**
     * fetch spent hours on project
     * @author atheotsky
     */
    public function getClientHoursByPeriod($period = null, $from = null, $to = null, $project = null) {
        $filter = $session = Mage::getSingleton("admin/session")->getFilter();
        if (!empty($filter['period'])) $period = $filter['period'];
        if (!empty($filter['from'])) $from = $filter['from'];
        if (!empty($filter['to'])) $to = $filter['to'];
        if (!empty($filter['project'])) $project = $filter['project'];

        if (empty($from)) { $from = date('Y-m-d', strtotime('-1 month')); }
        else { $from = date('Y-m-d', strtotime($from)); }

        if (empty($to)) { $to = date('Y-m-d'); }
        else { $to = date('Y-m-d', strtotime($to)); }

        $main_table = Mage::getSingleton('core/resource')->getTableName('codebase/timetracking');
        $ticket_table = Mage::getSingleton('core/resource')->getTableName('codebase/tickets');
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');

        switch ($period) {
        case 'year':
            $periodformat = $adapter->getDateExtractSql('main_table.updated_at', Varien_Db_Adapter_Interface::INTERVAL_YEAR);
            break;
        case 'month':
            $periodformat = $adapter->getDateFormatSql('main_table.updated_at', '%Y-%m');
            break;
        case 'week':
            $periodformat = $adapter->getDateFormatSql('main_table.updated_at', '%Y, Week #%v');
            break;
        default:
            $periodformat = $adapter->getDateFormatSql('main_table.updated_at', '%Y-%m-%d');
            break;
        }

        /*billable conditions*/
        $billable_statuses = explode(';', Mage::getStoreConfig('codebase/tickets/billable_statuses'));
        $billable_conditions = array();
        foreach ($billable_statuses as $status) {
            if (!empty($status)) $billable_conditions[] = "t.status_name LIKE '%{$status}%'";
        }
        $billable_conditions = implode(" OR ", $billable_conditions);

        /*bugfix conditions*/
        $bugfix_types = explode(';', Mage::getStoreConfig('codebase/tickets/bugfix_types'));
        $bugfix_conditions = array();
        foreach ($bugfix_types as $type) {
            if (!empty($type)) $bugfix_conditions[] = "t.ticket_type LIKE '%{$type}%'";
        }
        $bugfix_conditions = implode(" OR ", $bugfix_conditions);

        /*completed ticket sub query*/
        $completed_conditions = array();
        foreach ($billable_statuses as $status) {
            if (!empty($type)) $completed_conditions[] = "status_name LIKE '%{$status}%'";
        }
        $completed_conditions = implode(" OR ", $completed_conditions);

        $completed = $adapter->select()->from(array('main_table' => $ticket_table))
            ->columns(array("period" => $periodformat))
            ->columns(array("completed" => "SUM(IF({$completed_conditions}, 1, 0))"))
            ->columns(array("completed_estimate" => "SUM(IF({$completed_conditions}, estimated_time, 0))"))
            ->columns(array("new_estimate" => "SUM(IF(total_time_spent = 0, estimated_time, 0))"))
            ->columns(array("over_estimate" => "SUM(IF({$completed_conditions} AND estimated_time != 0 , total_time_spent - estimated_time, 0))"))
            ->columns(array("new" => "SUM(IF(main_table.created_at > '{$from}' AND main_table.created_at < '{$to}', 1, 0))"))
            ->where('main_table.updated_at >= ?', $from)->where('main_table.updated_at <= ?', $to)
            ->where('main_table.project_id = ? ', $project)
            ->group(array('main_table.project_id', 'period'));

        /*rebuild the collection*/
        $collection = $this->getCollection();
        $collection->getSelect()->join(array('t' => $ticket_table), "main_table.project_id = t.project_id and main_table.ticket_id = t.ticket_id");
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(array("period" => $periodformat));
        $collection->getSelect()->columns(array("gross" => "SUM(minutes)"));

        if (!empty($billable_conditions)) $collection->getSelect()->columns(array("billable" => "SUM(IF({$billable_conditions}, minutes, 0))"));
        if (!empty($bugfix_types)) $collection->getSelect()->columns(array("bug_fixes" => "SUM(IF({$bugfix_conditions}, minutes, 0))"));

        $collection->getSelect()->columns(array('uncategorized' => new Zend_Db_Expr("SUM(minutes) - SUM(IF({$billable_conditions}, minutes, 0)) - SUM(IF({$bugfix_conditions}, minutes, 0))")));

        if (!empty($completed_conditions)) $collection->getSelect()->joinLeft(array('c' => $completed), "c.period = {$periodformat}", array())
            ->columns(array("tickets_completed" => "IF(c.completed, c.completed, 0)", "tickets_created" => "IF(c.new, c.new, 0)"))
            ->columns(array("completed_estimate" => "IF(c.completed_estimate, c.completed_estimate, 0)"))
            ->columns(array("over_estimate" => "IF(c.over_estimate, c.over_estimate, 0)"))
            ->columns(array("new_estimate" => "IF(c.new_estimate, c.new_estimate, 0)"));

        $collection->getSelect()->where('main_table.project_id = ?', $project);
        $collection->getSelect()->where('main_table.updated_at >= ?', $from)->where('main_table.updated_at <= ?', $to);
        $collection->getSelect()->group(array('main_table.project_id', $periodformat));

        return $collection;
    }

    public function injectUserCondition($select, $user_id)
    {
        $select->where('main_table.user_id = ?', $user_id);
    }
}
