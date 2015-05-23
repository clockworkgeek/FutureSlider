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

class Clockworkgeek_Futureslider_Block_Adminhtml_Slide_Edit_Tab_Attributes extends Mage_Adminhtml_Block_Catalog_Form
{

    protected function _prepareForm()
    {
        $form = new Clockworkgeek_Data_Form();
        // a catalog trick, DataObject is used to display scope-specific details
        $form->setDataObject($this->getSlide());
        /* @var $group Clockworkgeek_Futureslider_Model_Entity_Attribute_Group */
        $group = $this->getGroup();

        $fieldset = $form->addFieldset('attribute_group_' . $group->getId(), array(
            'legend' => $this->__($group->getAttributeGroupName())
        ));
        $fieldset->addType('mediabrowser', Mage::getConfig()->getBlockClassName('futureslider/adminhtml_element_mediabrowser'));

        // _setFieldset creates fields from attributes
        $this->_setFieldset($group->getAttributesCollection(), $fieldset);

        $form->addValues($this->getSlide()->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return Clockworkgeek_Futureslider_Model_Slide
     */
    public function getSlide()
    {
        $slide = Mage::registry('slide');
        if (! $slide) {
            Mage::register('slide', $slide = Mage::getModel('futureslider/slide'));
        }
        return $slide;
    }
}
