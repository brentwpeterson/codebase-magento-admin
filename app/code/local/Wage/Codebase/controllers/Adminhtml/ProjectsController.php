<?php

class Wage_Codebase_Adminhtml_ProjectsController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('codebase/projects')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Project Manager'), Mage::helper('adminhtml')->__('Project Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('codebase/projects')->load($id);
        Mage::getSingleton('adminhtml/session')->setProjectId($id);
		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('projects_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('codebase/projects');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Projects Manager'), Mage::helper('adminhtml')->__('Projects Manager'));


			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('codebase/adminhtml_projects_edit'))
				->_addLeft($this->getLayout()->createBlock('codebase/adminhtml_projects_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('codebase')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {

			$model = Mage::getModel('codebase/projects');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			try {
                $project = Mage::getModel('codebase/projects')->load($this->getRequest()->getParam('id'));
                if($project->getStatus() != $data['status']){
                    Mage::getModel('codebase/codebase')->changeProductStatus($project->getProjectId(), $data['status']);
                }
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('codebase')->__('Project Information was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('codebase')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('codebase/projects');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    
   
  
    public function exportCsvAction()
    {
        $fileName   = 'projects.csv';
        $content    = $this->getLayout()->createBlock('codebase/adminhtml_projects_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'projects.xml';
        $content    = $this->getLayout()->createBlock('codebase/adminhtml_projects_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function sendReportAction()
    {
        $clientId = $this->getRequest()->getParam('client_id');
        $userId = $this->getRequest()->getParam('owner_id');
        $permalink = $this->getRequest()->getParam('permalink');
        $project = Mage::getModel('codebase/projects')->loadProjectByPermalink($permalink);
        try {
            $report = Mage::getModel('codebase/codebase')->sendProjectReportWithDate($userId,$clientId,$permalink);
            $project->setLastReportSentAt(now());
            $project->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Report is successfully sent to client.'));
            $this->_redirect('*/*/');
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/');
        }

    }

    public function sendReportWithNoteAction()
    {
        if ($data = $this->getRequest()->getPost()) {

            $clientId = $data['client_id'];
            $userId = $data['owner_id'];
            $permalink = $data['permalink'];
            $noteData = array();
            $project = Mage::getModel('codebase/projects')->loadProjectByPermalink($permalink);
            $noteData['title'] = $data['title'];
            $noteData['subject'] = $data['subject'];
            $noteData['project_id'] = $project->getProjectId();
            $user = Mage::getSingleton('admin/session');
            $userEmail = $user->getUser()->getEmail();
            $user = Mage::getModel('codebase/users')->getCollection()
                ->addFieldToFilter('email_address',$userEmail)
                ->getFirstItem();
            $noteData['user_id'] = $user->getUserId();
            try {
                $model = Mage::getModel('codebase/notes');
                $model->setData($noteData);
                $model->setCreatedAt(now())
                    ->setUpdatedAt(now());
                $model->save();

                $report = Mage::getModel('codebase/codebase')->sendProjectReportWithDate($userId,$clientId,$permalink,$noteData['title'],$noteData['subject']);
                $project->setLastReportSentAt(now());
                $project->setLastClientContact(now());
                $project->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Report is successfully sent to client and New note also created.'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/');
            }
        }
    }

    public function generateReportAction()
    {
        try {
            if ($data = $this->getRequest()->getParams()) {
                $reportFromDate = date("Y-m-d", strtotime($data['report_from']));
                $reportToDate = date("Y-m-d", strtotime($data['report_to']));
                Mage::getSingleton('admin/session')->setCodebaseReportFromDate($reportFromDate);
                Mage::getSingleton('admin/session')->setCodebaseReportToDate($reportToDate);
                $url = Mage::getSingleton('core/session')->getLastUrl();
                $this->_redirectUrl($url);
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/');
        }
    }

    public function notesAction() {

        $this->loadLayout();
        $this->renderLayout();
    }

    public function reportsAction() {

        $this->loadLayout();
        $this->renderLayout();
    }

    public function viewreportAction(){
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('codebase/adminhtml_viewreport'));
        $this->_setActiveMenu('codebase');
        $this->renderLayout();
    }

    public function oldreportAction(){
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('codebase/adminhtml_sentreport'));
        $this->_setActiveMenu('codebase');
        $this->renderLayout();
    }

    public function deletereportAction(){
        try {
            $projectId = $this->getRequest()->getParam('project_id');

            $oldReport = Mage::getModel('codebase/sentreport')->load($this->getRequest()->getParam('report_id'));
            $oldReport->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Report removed successfully.'));

            $this->_redirect('*/*/edit', array('id' => $projectId));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/edit', array('id' => $projectId));
        }
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
