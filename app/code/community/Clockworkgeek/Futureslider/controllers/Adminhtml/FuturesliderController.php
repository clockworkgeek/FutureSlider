<?php
/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 Daniel Deady
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

class Clockworkgeek_Futureslider_Adminhtml_FuturesliderController extends Mage_Adminhtml_Controller_Action
{

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/cms/futureslider_slides');
    }

    /**
     * List slides by default
     */
    public function indexAction()
    {
        $this->_forward('slides');
    }

    public function slidesAction()
    {
        $this->loadLayout();
        $this->_title($this->__('CMS'))
            ->_title($this->__('Future Slides'))
            ->_setActiveMenu('cms/futureslider');
        // TODO Mage_Adminhtml_Helper_Data::setPageHelpUrl
        $this->renderLayout();
    }

	public function gridAction()
	{
	    $this->loadLayout()->renderLayout();
	}

	public function newAction()
	{
	    /* @var $slide Clockworkgeek_Futureslider_Model_Slide */
	    $slide = Mage::getModel('futureslider/slide');
	    $slide->addData($slide->getDefaultData());
	    if (is_array($this->_getSession()->getFormData())) {
    	    $slide->addData($this->_getSession()->getFormData());
	    }
	    Mage::register('slide', $slide);

	    $this->loadLayout();
        $this->_title($this->__('CMS'))
            ->_title($this->__('Future Slides'))
            ->_title($this->__('New Slide'))
            ->_setActiveMenu('cms/futureslider');
        // TODO Mage_Adminhtml_Helper_Data::setPageHelpUrl
        $this->renderLayout();

        // clear temporary data in case user clicks 'Reset'
        $this->_getSession()->unsFormData();
	}

	public function editAction()
	{
	    $id = $this->getRequest()->getParam('id');
	    $store = $this->getRequest()->getParam('store');
	    /* @var $slide Clockworkgeek_Futureslider_Model_Slide */
	    $slide = Mage::getModel('futureslider/slide');
	    $slide->setStoreId($store)->load($id);
	    if (is_array($this->_getSession()->getFormData())) {
    	    $slide->addData($this->_getSession()->getFormData());
	    }
	    Mage::register('slide', $slide);

	    $this->loadLayout();
        $this->_title($this->__('CMS'))
            ->_title($this->__('Future Slides'))
            ->_title($slide->getId() ? $slide->getName() : $this->__('New Slide'))
            ->_setActiveMenu('cms/futureslider');
        // TODO Mage_Adminhtml_Helper_Data::setPageHelpUrl
        $this->renderLayout();

        // clear temporary data in case user clicks 'Reset'
        $this->_getSession()->unsFormData();
	}

	public function saveAction()
	{
	    $id = $this->getRequest()->getParam('id');
	    $store = $this->getRequest()->getParam('store');
	    $return = $this->getRequest()->getParam('back') == 'edit';
	    try {
    	    /* @var $slide Clockworkgeek_Futureslider_Model_Slide */
    	    $slide = Mage::getModel('futureslider/slide');
    	    // set store first in case of multi-store
    	    $slide->setStoreId($store)->load($id);
    	    // addData merges instead of replaces
    	    $slide->addData($this->getRequest()->getPost());
    	    // name will be used for success message even if not saved for this store
    	    $slideName = $slide->getName();

    	    // for multi-store forms apply 'Use Default Value' checkboxes
    	    $use_default = (array) $this->getRequest()->getPost('use_default');
    	    foreach ($use_default as $attributeCode) {
    	        // false prevents saving of attribute for this store
    	        $slide->setData($attributeCode, false);
    	    }

    	    $slide->save();
    	    // reassign ID in case this was a new object
    	    $id = $slide->getId();

    	    // execution will not get this far if there was an error
    	    $this->_getSession()->addSuccess($this->__('The slide "%s" has been saved.', $slideName));
    	    // clear temporary data
    	    $this->_getSession()->unsFormData();
	    } catch (Exception $e) {
	        $this->_getSession()->addError($e->getMessage());
	        // set temporary date to be resurrected on next page
	        $this->_getSession()->setFormData($this->getRequest()->getPost());
	        $return = true;
	    }

	    if ($return) {
	        $this->_redirect('*/*/edit', array(
	            'id' => $id,
	            'store' => $store
	        ));
	    }
	    else {
    	    $this->_redirect('*/*/slides', array(
    	        'store' => $store
    	    ));
	    }
	}

	public function deleteAction()
	{
	    /* @var $slide Clockworkgeek_Futureslider_Model_Slide */
	    $slide = Mage::getModel('futureslider/slide');
	    $slide->load($this->getRequest()->getParam('id'));
	    try {
    	    $slide->delete();

    	    // execution will not get this far if there was an error
    	    $this->_getSession()->addSuccess($this->__('The slide "%s" has been deleted.', $slide->getName()));
    	    $this->_redirect('*/*/slides', array(
    	        'store' => $this->getRequest()->getParam('store')
    	    ));
	    } catch (Exception $e) {
	        $this->_getSession()->addException($e, $this->__('Could not delete slide "%s".', $slide->getName()));
	        $this->_redirectReferer($this->getUrl('*/*/edit', $this->getRequest()->getParams()));
	    }
	}

	public function massDeleteAction()
	{
	    $ids = $this->getRequest()->getParam('id');

	    if (! is_array($ids) || ! $ids) {
	        $this->_getSession()->addError($this->__('No slides were selected.'));
	    }
	    else {
	        $deleted = 0;
	        try {
    	        /* @var $slides Clockworkgeek_Futureslider_Model_Resource_Slide_Collection */
    	        $slides = Mage::getResourceModel('futureslider/slide_collection');
    	        $slides->addIdFilter($ids);

    	        foreach ($slides as $slide) {
    	            $slide->delete();
    	            $deleted++;
    	        }
    	    } catch (Exception $e) {
    	        $this->_getSession()->addError($e->getMessage());
    	    }

    	    if ($deleted) {
    	        $this->_getSession()->addSuccess($this->__('Successfully deleted %d slide(s).', $deleted));
    	    }
	    }

	    $this->_redirect('*/*/slides', array(
	        'store' => $this->getRequest()->getParam('store')
	    ));
	}

	public function massUpdateAction()
	{
	    $params = $this->getRequest()->getParams();
	    $ids = @$params['id'];
	    unset($params['id']);

	    if (! is_array($ids) || ! $ids) {
	        $this->_getSession()->addError($this->__('No slides were selected.'));
	    }
	    else {
	        $updated = 0;
	        try {
    	        /* @var $slides Clockworkgeek_Futureslider_Model_Resource_Slide_Collection */
    	        $slides = Mage::getResourceModel('futureslider/slide_collection');
    	        // load all attributes because slide will be saved again soon
    	        $slides->addAttributeToSelect('*');
    	        $slides->addIdFilter($ids);

    	        foreach ($slides as $slide) {
    	            $slide->addData($params)->save();
    	            $updated++;
    	        }
    	    } catch (Exception $e) {
    	        $this->_getSession()->addError($e->getMessage());
    	    }

    	    if ($updated) {
    	        $action = 'updated';
    	        if (@$params['enabled'] == 1) {
    	            $action = 'enabled';
    	        }
    	        elseif (@$params['enabled'] == 0) {
    	            $action = 'disabled';
    	        }
    	        $this->_getSession()->addSuccess($this->__("Successfully {$action} %d slide(s).", $updated));
    	    }
	    }

	    $this->_redirect('*/*/slides', array(
	        'store' => $this->getRequest()->getParam('store')
	    ));
	}
}
