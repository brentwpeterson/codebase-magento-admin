<?php
class Wage_Codebase_Adminhtml_BillingreportController extends Mage_Adminhtml_Controller_Action {
    protected function _initAction() {
        $this->loadLayout()
            ->_setActiveMenu('codebase/projects')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Billing Report Manager'), Mage::helper('adminhtml')->__('Billing Report Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
            ->renderLayout();
    }
    public function generatereportAction(){

        if ($filter = Mage::app()->getRequest()->getPost()) {
            $session = Mage::getSingleton("admin/session");
            $session->setFilter(
                array(
                    'from' => Mage::app()->getRequest()->getPost('from'),
                    'to' => Mage::app()->getRequest()->getPost('to'),
                    'project' => Mage::app()->getRequest()->getPost('project'),
                )
            );
        }
        else {
            $session = Mage::getSingleton("admin/session");

            $session->unsetData('filter');
        }

        if($reportId = $this->getRequest()->getParam('report_id'))
        {
            $report = Mage::getModel('codebase/reports')->load($reportId);
            $session = Mage::getSingleton("admin/session");
            $session->setFilter(
                array(
                    'from' => $report->getFromDate(),
                    'to' => $report->getToDate(),
                    'project' => $report->getProjectId(),
                )
            );
        }
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('codebase/adminhtml_generatereport'));
        $this->_setActiveMenu('codebase');
        $this->renderLayout();
    }

    public function savereportAction(){

        if ( $post = Mage::app()->getRequest()->getPost()) {
//            echo "<pre>";
//            print_r($post);
//            exit;
            if($post['report_id']) {
                $reportId = $post['report_id'];
                $report = Mage::getModel('codebase/reports')->load($reportId);
                $reportdata = $report->getData();
            } else {
               $report = Mage::getModel('codebase/reports');
            }
            $reportdata['project_id'] = $post['project_id'];
            $reportdata['owner_id'] = $post['owner_id'];
            $reportdata['from_date'] = $post['from_date'];
            $reportdata['to_date'] = $post['to_date'];

            try{
                $report->setData($reportdata);
                if($reportId) {
                    $report->setUpdatedAt(now());
                    $report->setId($reportId)->save();
                } else {
                $report->setCreatedAt(now())
                    ->setUpdatedAt(now());
                    $report->save();
                }


                $trackingIds = $post['entry_id'];
                foreach($trackingIds as $trackingId){
                    $timetracking = Mage::getModel('codebase/timetracking');
                    if($post['reduction'][$trackingId] && $post['reduction_reason'][$trackingId]){
                        $timetracking = $timetracking->load($trackingId);
                        $timetracking->setReductionTime($post['reduction'][$trackingId]);
                        $timetracking->setReductionReason($post['reduction_reason'][$trackingId]);
                        $timetracking->setReductionApproval($post['reduction_approval'][$trackingId]);
                        try {
                            $timetracking->setId($timetracking->getId())->save();
                        } catch (Exception $e) {
                            Mage::log($e->getMessage());
                        }
                    }
                }

            } catch (Exception $e) {
                Mage::log($e->getMessage());
            }

        }
        if($post['report_id']) {
            $this->_redirect("*/*/generatereport/",array("report_id"=>$post['report_id']));
        }
        else{
            $this->_redirect('*/*/');
        }
    }
    public function exportCsvAction()
    {
        $filter = Mage::getSingleton("admin/session")->getFilter();
        $fileName   = 'Billable_Report.csv';
        $content = '';
        $summary = array();
        $timeEntries = array();
        $heading = array('Date','Ticket Number','Status','Assignee','Summary','Time Logged', 'Reduction', 'Billable Time', 'Reduction Reason', 'Approval');

        $project = Mage::getModel('codebase/projects')->loadProjectByProjectId($filter['project']);
        $productOwnerId = $project->getUserId();
        $productOwner = Mage::getModel('codebase/users')->findUser($productOwnerId);
        $summary[] = array('Project Name:',$project->getProjectName());
        $summary[] = array('Project Owner:',$productOwner->getFirstName().' '.$productOwner->getLastName());
        $summary[] = array('Report From:',$filter['from'],'','Report To:',$filter['to']);

            $collection = Mage::getModel('codebase/timetracking')->getCollection()
                ->addFieldToFilter('updated_at', array(
                    'from'     => $filter['from'],
                    'to'       => $filter['to'],
                    'datetime' => true
                ))
                ->addFieldToFilter('project_id', $filter['project'])
                ->setOrder('updated_at','ASC');
        $totalLoggedTime = 0;
        $totalReductionTime = 0;
        $totalBillableTime = 0;
        $reductionCount = 0;

        foreach($collection as $item)
        {
            if($item->getReductionTime())
                $reductionCount += 1;

            $totalLoggedTime += $item->getMinutes();
            $totalReductionTime += $item->getReductionTime();
            $totalBillableTime += ($item->getMinutes() - $item->getReductionTime());

            $ticket = Mage::getModel('codebase/tickets')->getCollection()
                                ->addFieldToFilter('project_id',$item->getProjectId())
                                ->addFieldToFilter('ticket_id',$item->getTicketId())
                                ->getFirstItem();
            $entryId = $item->getId();
            $user = Mage::getModel('codebase/users')->findUser($item->getUserId());
            $data = array();
            $data[] = $item->getUpdatedAt();
            $data[] = $item->getTicketId();
            $data[] = $ticket->getStatusName();
            $data[] = $user->getFirstName().' '.$user->getLastName();
            $data[] = $item->getSummary();
            $data[] = $item->getMinutes();
            $data[] = $item->getReductionTime();
            $data[] = $item->getMinutes() - $item->getReductionTime();
            $data[] = $item->getReductionReason();
            $data[] = $item->getReductionApproval();
            //$content .= implode(',',$data)."\n";
            $timeEntries[] = $data;
        }

        $tickets = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addFieldToFilter('permalink', $project->getPermalink());
        $activeTickets = count($tickets->getData());
        $summary[] = array('Total Number of Active Tickets:',$activeTickets);

        $newTicketsCollection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','open')
            ->addFieldToFilter('created_at', array(
                'from'     => $filter['from'],
                'to'       => $filter['to'],
                'datetime' => true
            ))
            ->addFieldToFilter('permalink', $project->getPermalink());

        $newTickets =  count($newTicketsCollection->getData());
        $summary[] = array('Total New Tickets Created:',$newTickets);

        $closedTicketsCollection = Mage::getModel('codebase/tickets')->getCollection()
            ->addFieldToFilter('resolution','close')
            ->addFieldToFilter('updated_at', array(
                'from'     => $filter['from'],
                'to'       => $filter['to'],
                'datetime' => true
            ))
            ->addFieldToFilter('permalink', $project->getPermalink());

        $closedTickets =  count($closedTicketsCollection->getData());
        $summary[] = array('Total Tickets Closed:',$closedTickets);
        $summary[] = array('Total Time Logged:',$totalLoggedTime);
        $summary[] = array('Total Time in Reductions:',$totalReductionTime);
        $summary[] = array('Total Number of Reductions:',$reductionCount);

        foreach($summary as $item){
            $content .= implode(',',$item)."\n";
        }

        $content .= "\n\n\n";
        $content .= implode(',',$heading)."\n";

        foreach($timeEntries as $entry){
            $content .= implode(',',$entry)."\n";
        }

        $totalRow = array('','','','','',$totalLoggedTime,$totalReductionTime,$totalBillableTime,'','');
        $content .= implode(',',$totalRow)."\n";;

        $this->_sendUploadResponse($fileName, $content);
    }
    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

}
