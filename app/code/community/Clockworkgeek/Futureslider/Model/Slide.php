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
 * A slide is normally static content.
 *
 * Many slides make a banner and a slide may be part of several banners.
 *
 * @method string getName()
 * @method bool getEnabled()
 * @method int getDuration()
 * @method string getActiveFrom()
 * @method string getActiveTo()
 * @method string getLinkUrl()
 * @method string getLinkText()
 * @method string getBackgroundColor()
 * @method string getBackgroundImage()
 * @method string getBackgroundSize()
 * @method string getContentWidget()
 * @method Clockworkgeek_Futureslider_Model_Slide setName(string)
 * @method Clockworkgeek_Futureslider_Model_Slide setEnabled(bool)
 * @method Clockworkgeek_Futureslider_Model_Slide setDuration(int)
 * @method Clockworkgeek_Futureslider_Model_Slide setActiveFrom(string)
 * @method Clockworkgeek_Futureslider_Model_Slide setActiveTo(string)
 * @method Clockworkgeek_Futureslider_Model_Slide setLinkUrl(string)
 * @method Clockworkgeek_Futureslider_Model_Slide setLinkText(string)
 * @method Clockworkgeek_Futureslider_Model_Slide setBackgroundColor(string)
 * @method Clockworkgeek_Futureslider_Model_Slide setBackgroundImage(string)
 * @method Clockworkgeek_Futureslider_Model_Slide setBackgroundSize(string)
 * @method Clockworkgeek_Futureslider_Model_Slide setContentWidget(string)
 */
class Clockworkgeek_Futureslider_Model_Slide extends Clockworkgeek_Futureslider_Model_Entity_Abstract
{

    const CACHE_TAG = 'slide';
    const ENTITY = 'futureslider_slide';

    protected $_entityType = self::ENTITY;

    protected $_eventPrefix = self::ENTITY;

    protected $_eventObject = 'slide';

    protected $_cacheTag = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('futureslider/slide');
    }
}
