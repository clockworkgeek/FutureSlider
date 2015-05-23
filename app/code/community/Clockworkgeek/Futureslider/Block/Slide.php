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

/**
 * @method Clockworkgeek_Futureslider_Model_Html_Attributes getHtmlAttributes()
 * @method Clockworkgeek_Futureslider_Block_Slide setHtmlAttributes(Clockworkgeek_Futureslider_Model_Html_Attributes)
 * @method Clockworkgeek_Futureslider_Model_Slide getSlide()
 * @method Clockworkgeek_Futureslider_Block_Slide setSlide(Clockworkgeek_Futureslider_Model_Slide)
 * @method string getSlideName()
 * @method int getSlideEntityId()
 * @method string getSlideImage()
 * @see app/design/frontend/base/default/template/futureslider/slide.phtml
 */
class Clockworkgeek_Futureslider_Block_Slide extends Clockworkgeek_Formelements_Block_Template
{

    protected function _construct()
    {
        parent::_construct();
        if (! $this->getTemplate()) {
            // if already set do not override
            $this->setTemplate('futureslider/slide.phtml');
        }
        // do not cache slide because it will be cached as part of parent banner
        $this->setHtmlAttributes(Mage::getModel('futureslider/html_attributes'));
    }

    protected function _beforeToHtml()
    {
        // prepare receptacle for attributes
        $attrs = $this->getHtmlAttributes();
        $attrs->setId($this->getHtmlId());
        $attrs->addClass('future-slide');
        $attrs->addClass($this->getHtmlId());
        $attrs->setStyle('background-color:'.$this->getSlide()->getBackgroundColor());
        $this->setHtmlAttributes($attrs);

        // opportunity to change attributes and child blocks and template(?)
        Mage::dispatchEvent('futureslider_block_slide_to_html_before', array(
            'block' => $this,
            'slide' => $this->getSlide()
        ));

        return parent::_beforeToHtml();
    }

    public function getHtmlId()
    {
        return 'future-slide-'.crc32($this->getNameInLayout());
    }

    public function getImageHtml()
    {
        $image = Mage::getModel('futureslider/html_responsiveimage');
        $image->setBackgroundSize($this->getSlide()->getBackgroundSize());
        $image->setFilename($this->getSlide()->getBackgroundImage());
        $image->prepareImages();
        return $image->toHtml(array(
            'class' => 'future-image'
        ));
    }
}
