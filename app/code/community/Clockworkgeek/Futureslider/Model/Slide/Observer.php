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

class Clockworkgeek_Futureslider_Model_Slide_Observer
{

    public function addContentPositionHint(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        $slide = $observer->getSlide();

        if ($block instanceof Clockworkgeek_Futureslider_Block_Slide &&
            $slide instanceof Clockworkgeek_Futureslider_Model_Slide &&
            $slide->getContentPosition()) {
            $block->getHtmlAttributes()->addClass($slide->getContentPosition());
        }
    }

    public function addDefaultFilters(Varien_Event_Observer $observer)
    {
        /* @var $collection Clockworkgeek_Futureslider_Model_Resource_Slide_Collection */
        $collection = $observer->getCollection();

        // never show disabled slides on frontend
        $collection->setEnabledFilter();

        // active dates are optional so expect nulls
        $now = Mage::getModel('core/date')->gmtDate();
        // left join because nulls need to be visible
        $collection->addAttributeToFilter('active_from', array(
            array('lteq' => $now),
            array('null' => '')
        ), 'left');
        $collection->addAttributeToFilter('active_to', array(
            array('gteq' => $now),
            array('null' => '')
        ), 'left');
    }
}
