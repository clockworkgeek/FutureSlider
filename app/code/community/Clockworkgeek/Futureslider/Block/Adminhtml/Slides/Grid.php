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

class Clockworkgeek_Futureslider_Block_Adminhtml_Slides_Grid extends Clockworkgeek_Futureslider_Block_Adminhtml_Abstract_Grid
{

    protected $_gridName = 'futureslider_slides';

    public function __construct($attributes = array())
    {
        parent::__construct($attributes);

        $this->setId('futureslider_slides');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);

        $this->setDefaultSort('updated_at');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        /* @var $collection Clockworkgeek_Futureslider_Model_Resource_Slide_Collection */
        $collection = Mage::getResourceModel('futureslider/slide_collection');
        $collection->setStoreId($this->getRequest()->getParam('store'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('id');

        $this->getMassactionBlock()->addItem('enable', array(
            'label'=> Mage::helper('tag')->__('Enable'),
            'url'  => $this->getUrl('*/*/massUpdate', array(
                'enabled' => 1
            ))
        ));

        $this->getMassactionBlock()->addItem('disable', array(
            'label'=> Mage::helper('tag')->__('Disable'),
            'url'  => $this->getUrl('*/*/massUpdate', array(
                'enabled' => 0
            ))
        ));

        $this->getMassactionBlock()->addItem('delete', array(
            'label'=> Mage::helper('tag')->__('Delete'),
            'url'  => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('tag')->__('Are you sure?')
        ));

        return $this;
    }

    public function getGridUrl($params = array())
    {
        if (! isset($params['store'])) {
            $params['store'] = $this->getRequest()->getParam('store');
        }
        return $this->getUrl('*/*/grid', $params);
    }

    public function getRowUrl($slide)
    {
        if ($slide->getId()) {
            return $this->getUrl('*/*/edit', array(
                'id' => $slide->getId(),
                'store' => $this->getRequest()->getParam('store')
            ));
        }

        return false;
    }

    /**
     * Inject a store switcher which isn't standard
     *
     * @see Mage_Core_Block_Template::renderView()
     */
    public function renderView()
    {
        $html = '';
        if (! Mage::app()->isSingleStoreMode() && ! $this->getRequest()->getQuery('ajax')) {
            $switcher = $this->getLayout()->createBlock('adminhtml/store_switcher');
            // confirm dialog is an annoying JS modal
            $switcher->setUseConfirm(false);
            $html = $switcher->toHtml();
        }
        return $html . parent::renderView();
    }
}
