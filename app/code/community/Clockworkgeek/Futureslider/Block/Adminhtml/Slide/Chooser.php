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

class Clockworkgeek_Futureslider_Block_Adminhtml_Slide_Chooser extends Clockworkgeek_Futureslider_Block_Adminhtml_Abstract_Grid
{

    protected $_gridName = 'futureslider_slides_chooser';

    public function __construct($attributes = array())
    {
        parent::__construct($attributes);

        $this->setId('futureslider_slides');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setMassactionBlockName('futureslider/adminhtml_slide_chooser_massaction');

        $this->setDefaultSort('updated_at');
        $this->setDefaultDir('DESC');
        $this->setDefaultFilter(array('enabled' => true));
    }

    /**
     * Prepare chooser element HTML
     *
     * @param Varien_Data_Form_Element_Abstract $element Form Element
     * @return Varien_Data_Form_Element_Abstract
     */
    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $uniqId = Mage::helper('core')->uniqHash($element->getId());
        $sourceUrl = $this->getUrl('adminhtml/futureslider_widget/chooser', array('uniq_id' => $uniqId));

        // $chooser is button which triggers a modal
        $chooser = $this->getLayout()->createBlock('widget/adminhtml_widget_chooser')
            ->setElement($element)
            ->setTranslationHelper($this->getTranslationHelper())
            ->setConfig($this->getConfig())
            ->setFieldsetId($this->getFieldsetId())
            ->setSourceUrl($sourceUrl)
            ->setUniqId($uniqId);


        if ($element->getValue()) {
            $slideIds = explode(',', $element->getValue());
            /* @var $slides Clockworkgeek_Futureslider_Model_Resource_Slide_Collection */
            $slides = Mage::getResourceModel('futureslider/slide_collection');
            $slides->addIdFilter($slideIds);
            $slides->addAttributeToSelect('name');
            $names = $slides->getColumnValues('name');
            $chooser->setLabel(implode(', ', $names));
        }

        $element->setData('after_element_html', $chooser->toHtml());
        return $element;
    }

    protected function _prepareCollection()
    {
        /* @var $collection Clockworkgeek_Futureslider_Model_Resource_Slide_Collection */
        $collection = Mage::getResourceModel('futureslider/slide_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id'); // attr whose value to use
        $this->getMassactionBlock()->setFormFieldName('slide'); // name of checkbox inputs
        $this->getMassactionBlock()->addItem('select', array()); // will still show if item is empty
    }

    public function getRowUrl($item)
    {
        // if row URL is present then javascript attempts to navigate on click
        // an empty value prevents that and allows checkbox to be toggled instead
        return '';
    }
}
