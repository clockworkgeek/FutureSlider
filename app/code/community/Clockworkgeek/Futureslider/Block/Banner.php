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
 * Banner is a widget.  It animates between slides.
 *
 * @method string getSlideIds()
 * @method Clockworkgeek_Futureslider_Model_Html_Attributes getHtmlAttributes()
 */
class Clockworkgeek_Futureslider_Block_Banner extends Mage_Core_Block_Template
implements Mage_Widget_Block_Interface
{

    protected function _construct()
    {
        parent::_construct();
        $this->setCacheLifetime(3600);
        $this->setCacheTags(array(
            Clockworkgeek_Futureslider_Model_Slide::CACHE_TAG,
            Mage_Cms_Model_Block::CACHE_TAG,
            Mage_Cms_Model_Page::CACHE_TAG,
            'widget_instance'
        ));
    }

    protected $_extraCacheKeys = array();

    /**
     * Unlike cache tags, keys are required before loading from cache.
     *
     * Add any specific info that describes the contents.
     * A good event to use is core_block_abstract_to_html_before
     *
     * @param string $key
     * @return Clockworkgeek_Futureslider_Block_Banner
     */
    public function addCacheKey($key)
    {
        if (! in_array($key, $this->_extraCacheKeys)) {
            $this->_extraCacheKeys[] = $key;
        }
        return $this;
    }

    public function getCacheKeyInfo()
    {
        return array_merge(
            parent::getCacheKeyInfo(),
            $this->_extraCacheKeys
        );
    }

    /**
     * Prevent recursion by tracking slides being rendered.
     *
     * Keys are slide IDs.  Values are unused.
     *
     * @var array
     */
    protected static $_currentSlides = array();

    protected function _beforeToHtml()
    {
        /* @var $attrs Clockworkgeek_Futureslider_Model_Html_Attributes */
        $attrs = Mage::getModel('futureslider/html_attributes');
        $attrs->addClass('widget');
        $attrs->addClass('widget-future-banner');
        if ($this->getAspectRatio()) {
            $attrs->addClass('aspect-'.$this->getAspectRatio());
        }
        $this->setHtmlAttributes($attrs);

        /* @var $slide Clockworkgeek_Futureslider_Model_Slide */
        foreach ($this->getSlideCollection() as $slide) {
            if (isset(self::$_currentSlides[$slide->getId()])) {
                continue;
            }
            self::$_currentSlides[$slide->getId()] = true;

            $block = $this->getLayout()->createBlock('futureslider/slide')
                ->setSlide($slide);
            $this->append($block);
            $this->addToChildGroup('slides', $block);
        }

        // opportunity to change attributes and child blocks and template(?)
        Mage::dispatchEvent('futureslider_block_banner_to_html_before', array(
            'block' => $this
        ));

        return parent::_beforeToHtml();
    }

    protected function _afterToHtml($html)
    {
        // undo _beforeToHtml action in case same slides are reused elsewhere on the same page
        foreach ($this->getSlideCollection() as $slide) {
            $id = $slide->getId();
            unset(self::$_currentSlides[$id]);
        }

        return parent::_afterToHtml($html);
    }

    public function getHtmlId()
    {
        return 'future-banner-' . crc32($this->getNameInLayout());
    }

    /**
     * @var Clockworkgeek_Futureslider_Model_Resource_Slide_Collection
     */
    protected $_slideCollection;

    /**
     * Instantiate new collection of Clockworkgeek_Futureslider_Model_Slide
     *
     * @return Clockworkgeek_Futureslider_Model_Resource_Slide_Collection
     */
    public function getSlideCollection()
    {
        if (is_null($this->_slideCollection)) {
            $this->_slideCollection = Mage::getResourceModel('futureslider/slide_collection');
            $this->_slideCollection->addWidgetFilters($this->getData());
        }
        return $this->_slideCollection;
    }

    /**
     * Get CSS properties as array keyed by state.
     *
     * Each slide is introduced with "start" to "show" and exits by "show" to "end".
     * It exists in "show" state for "duration" seconds and in "hide" state otherwise.
     *
     * @param Clockworkgeek_Futureslider_Block_Slide $slide
     * @param int $index
     * @return array
     */
    public function getAnimatedProperties($slide, $index)
    {
        // temporary static rules until transition type is available
        return array(
            'show-start' => 'opacity:0;z-index:1;',
            'show-end' => 'opacity:1;z-index:1;',
            'hide-start' => 'opacity:1;z-index:0;animation-timing-function:step-end;',
            'hide-end' => 'opacity:0;z-index:-1;',
            'hide' => 'opacity:0;z-index:-1;'
        );
    }

    /**
     * Form CSS animation rules to spec.
     *
     * @return string
     */
    public function getKeyframes()
    {
        $animation = $this->getAnimation();
        if (! $animation) {
            // specified animation type is missing
            return '';
        }

        return $animation->toCss();
    }

    /**
     * JS fallback for legacy browsers.
     *
     * Uses same animation details as CSS.
     *
     * @return string
     */
    public function getJavascript()
    {
        $animation = $this->getAnimation();
        if (! $animation) {
            // specified animation type is missing
            return '';
        }

        return $animation->toJson();
    }

    public function getDuration()
    {
        return $this->hasData('duration') ? $this->getData('duration') : 10;
    }

    public function getTransitionTime()
    {
        return $this->hasData('transition_time') ? $this->getData('transition_time') : 1;
    }

    /**
     * @var Clockworkgeek_Futureslider_Model_Html_Animation_Abstract
     */
    protected $_animation;

    /**
     * @return Clockworkgeek_Futureslider_Model_Html_Animation_Abstract
     */
    public function getAnimation()
    {
        if (! $this->_animation) {
            $type = $this->hasData('transition_type') ? $this->getData('transition_type') : 'futureslider/html_animation_fade';
            $animation = Mage::getModel($type);
            if (! $animation) {
                Mage::log("Animation type '$type' cannot be resolved.", Zend_Log::ERR);
            }

            $blocks = $this->getChildGroup('slides');
            if (count($blocks) >= 2) {
                $animation->setDuration($this->getDuration());
                $animation->setTransitionTime($this->getTransitionTime());
                foreach ($blocks as $block) {
                    $animation->addSlide($block->getSlide(), $block->getHtmlId());
                }
            }

            $this->_animation = $animation;
        }

        return $this->_animation;
    }

    public function getSortedChildBlocks()
    {
        $children = parent::getSortedChildBlocks();

        // make a reference to each child's siblings
        $prev = end($children);
        foreach ($children as $child) {
            $child->setPrev($prev);
            $prev->setNext($child);
            $prev = $child;
        }

        // decoration classes
        if ($children) {
            reset($children)->getHtmlAttributes()->addClass('first');
            end($children)->getHtmlAttributes()->addClass('last');
        }

        return $children;
    }
}
