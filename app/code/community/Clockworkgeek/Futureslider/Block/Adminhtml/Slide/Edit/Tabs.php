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

class Clockworkgeek_Futureslider_Block_Adminhtml_Slide_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();

        // ID used in HTML+JS
        $this->setId('slideTabs');

        // element ID must match form ID in futureslider/adminhtml_slide_edit_form
        $this->setDestElementId('edit_form');
    }

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        $slide = $this->getSlide();
        $groupCollection = $slide->getAttributeGroupCollection();

        /* @var $group Clockworkgeek_Futureslider_Model_Entity_Attribute_Group */
        foreach ($groupCollection as $group) {
//             $group->setEntityTypeId($slide->getEntityTypeId());
            $tab = $this->getLayout()->createBlock('futureslider/adminhtml_slide_edit_tab_attributes');
            $tab->setGroup($group);
            $this->addTab('group_'.$group->getId(), array(
                'label' => $group->getAttributeGroupName(),
                'content' => $tab->toHtml()
            ));
        }
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
