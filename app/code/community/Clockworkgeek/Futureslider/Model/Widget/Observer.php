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

class Clockworkgeek_Futureslider_Model_Widget_Observer
{

    public function renderWidgetContent(Varien_Event_Observer $observer)
    {
        /* @var $slide Clockworkgeek_Futureslider_Model_Slide */
        $slide = $observer->getSlide();

        if ($slide->getContentWidget()) {
            /* @var $block Clockworkgeek_Futureslider_Block_Slide */
            $block = $observer->getBlock();
            /* @var $child Clockworkgeek_Formelements_Block_Template */
            $child = $block->getLayout()->createBlock('formelements/template');
            $child->setTemplate('futureslider/slide/content/widget.phtml');
            // Formelements block will take care of rendering the widget
            $child->setSlide($slide);
            $block->append($child, 'content_widget');
        }
    }

    public function addSlideFilters(Varien_Event_Observer $observer)
    {
        /* @var $collection Clockworkgeek_Futureslider_Model_Resource_Slide_Collection */
        $collection = $observer->getCollection();

        // widgets have right to all slide data
        $collection->addAttributeToSelect('*');

        // if slide_ids exists but is empty, nothing will show
        $filters = $collection->getWidgetFilters();
        if (isset($filters['slide_ids'])) {
            $ids = explode(',', $filters['slide_ids']);
            $collection->addIdFilter($ids);
        }
    }
}
