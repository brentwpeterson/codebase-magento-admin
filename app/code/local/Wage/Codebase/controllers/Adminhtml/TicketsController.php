<?php
class Wage_Codebase_Adminhtml_TicketsController extends Mage_Adminhtml_Controller_Action {
    protected function _initAction()

    {
        $this->loadLayout()
            ->_setActiveMenu('codebase/open_tickets');
        return $this;
    }
    public function indexAction() {
        $this->loadLayout();
        $this->_initAction();
        $this->renderLayout();
    }
    public function exportCsvAction()
    {
        $fileName   = 'tickets.csv';
        $content    = $this->getLayout()->createBlock('codebase/adminhtml_tickets_grid')
            ->getCsv();
 
        $this->_sendUploadResponse($fileName, $content);
    }
 
    public function exportXmlAction()
    {
        $fileName   = 'tickets.xml';
        $content    = $this->getLayout()->createBlock('codebase/adminhtml_tickets_grid')
            ->getXml();
 
        $this->_sendUploadResponse($fileName, $content);
    }

    public function refreshAction()
    {
        //$tickets = Mage::getModel('codebase/codebase')->getStatuses();
        $tickets = Mage::getModel('codebase/codebase')->getTickets();
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('codebase')->__('Ticket has been refreshed successfully'));
        $this->_redirect('*/*/');
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('codebase/adminhtml_tickets_grid')->toHtml()
        );
    }

    /**
     * update tickets' status
     * @author atheotsky
     */
    public function updateStatusAction()
    {
        $success = array(); $failed = array();
        $mticketIds = $this->getRequest()->getParam('ids');
        $model = Mage::getModel('codebase/codebase');
        foreach ($mticketIds as $id) {
            $params = array();
            $status = Mage::getModel('codebase/statuses');
            $ticket = Mage::getModel('codebase/tickets')->load($id);
            /*exclude tickets with 'hold'*/
            if (strstr(strtolower($ticket->getStatusName()), 'hold') !== FALSE) {
                $failed[] = 'Ticket #' . $ticket->getTicketId() . ' : ' . $ticket->getSummary() . ' - status is  Hold ';
                continue;
            }

            if ($ticket->getId()) {
                $status = $status->findStatusbyLabel($ticket->getProjectId(), 'close');
                if ($status->getStatusId()) {
                    $params['changes'] = array('status-id' => $status->getStatusId());
                    if ($model->updateTicket($ticket, $params)){
                        $success[] = $ticket->getPermalink() . ' #' . $ticket->getTicketId();
                        if (Mage::getStoreConfig('codebase/status/delete')) {
                            $ticket->delete();
                        }
                        else {
                            $ticket->setStatusName($status->getName())->save();
                        }
                    }
                    else {
                        $failed[] = $ticket->getPermalink() . ' #' . $ticket->getTicketId();
                    }
                }
                else {
                    $failed[] = 'There is no status name contains "close" in project ' . $ticket->getPermalink();
                }
            }
        }

        Mage::getSingleton('adminhtml/session')->addSuccess('The Following tickets have been closed : '. implode(', ', $success) );
        if (!empty($failed)) Mage::getSingleton('adminhtml/session')->addError('The Following tickets failed to update : '. implode(', ', $failed) );

        $this->_redirect('*/*/');
    }

    public function updatePriorityAction()
    {
        $success = array(); $failed = array();
        $mticketIds = $this->getRequest()->getParam('ids');
        $priority_label   = (string)$this->getRequest()->getParam('priority');
        $model = Mage::getModel('codebase/codebase');
        $comment = Mage::getStoreConfig('codebase/priorities/mass_update_comment');
        foreach ($mticketIds as $id) {
            $params = array();
            $ticket = Mage::getModel('codebase/tickets')->load($id);
            $priority = Mage::getModel('codebase/priorities')->findPrioritybyLabel($ticket->getProjectId(), $priority_label);
            $params['changes'] = array('priority-id' => $priority->getPriorityId());
            if($priority->getPriorityId())
            {
                if ($result = $model->updateTicket($ticket, $params, $comment)){
                    if($result['id'])
                    {
                        $success[] = $ticket->getPermalink() . ' #' . $ticket->getTicketId();
                        $ticket->setPriorityName($priority_label)->save();
                    }
                    else
                    {
                        $failed[] = $ticket->getPermalink() . ' #' . $ticket->getTicketId();
                    }
                }
            } else {
                $failed[] = 'There is no priority name contains "'.$priority_label.'" in project ' . $ticket->getPermalink();
            }
        }
        Mage::getSingleton('adminhtml/session')->addSuccess('The Following tickets priority have been changed : '. implode(', ', $success) );
        if (!empty($failed)) Mage::getSingleton('adminhtml/session')->addError('The Following tickets failed to update priority : '. implode(', ', $failed) );

        $this->_redirect('*/*/');
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
