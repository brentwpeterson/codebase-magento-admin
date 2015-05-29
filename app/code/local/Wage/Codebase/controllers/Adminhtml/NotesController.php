<?php

class Wage_Codebase_Adminhtml_NotesController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('codebase/notes')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Notes Manager'), Mage::helper('adminhtml')->__('Notes Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('codebase/notes')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('notes_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('codebase/notes');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Notes Manager'), Mage::helper('adminhtml')->__('Notes Manager'));


			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('codebase/adminhtml_notes_edit'))
				->_addLeft($this->getLayout()->createBlock('codebase/adminhtml_notes_edit_tabs'));

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
            $pid = Mage::getSingleton('adminhtml/session')->getProjectId();
            $project = Mage::getModel('codebase/projects')->load($pid);
            $data['project_id'] = $project->getProjectId();

            $user = Mage::getSingleton('admin/session');
            $userEmail = $user->getUser()->getEmail();
            $user = Mage::getModel('codebase/users')->getCollection()
                                    ->addFieldToFilter('email_address',$userEmail)
                                    ->getFirstItem();
            $data['user_id'] = $user->getUserId();
            $model = Mage::getModel('codebase/notes');
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			try {
                if ($model->getCreatedAt() == NULL || $model->getUpdatedAt() == NULL) {
                    $model->setCreatedAt(now())
                        ->setUpdatedAt(now());
                    $project->setLastClientContact(now());
                    $project->save();
                } else {
                    $model->setUpdatedAt(now());
                }
                $model->save();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('codebase')->__('Note Information was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

                $this->_redirect('codebase/adminhtml_projects/edit', array('id' => $pid));

                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('codebase/adminhtml_projects/edit', array('id' => $pid));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('codebase')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
                $pid = Mage::getSingleton('adminhtml/session')->getProjectId();

                $model = Mage::getModel('codebase/notes');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('codebase/adminhtml_projects/edit', array('id' => $pid));
            } catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('codebase/adminhtml_projects/edit', array('id' => $pid));
            }
		}
		$this->_redirect('*/*/');
	}

    
   
  
    public function exportCsvAction()
    {
        $fileName   = 'notes.csv';
        $content    = $this->getLayout()->createBlock('codebase/adminhtml_notes_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'notes.xml';
        $content    = $this->getLayout()->createBlock('codebase/adminhtml_notes_grid')
            ->getXml();

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
