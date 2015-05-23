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

    protected function _prepareLayout()
    {
        /* @var $head Mage_Page_Block_Html_Head */
        $head = $this->getLayout()->getBlock('head');
        if ($head) {
            // $head only allows stylesheet to be added once
            $head->addItem('skin_css', 'css/futureslider.css');
            // condition chosen to match RWD conditions
            $head->addItem('skin_css', 'css/futureslider-ie8.css', null, ' (lte IE 8) & (!IEMobile)');
            // if banner is active but no slides are, then head is still updated
        }
        return parent::_prepareLayout();
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
     * @param array $blocks
     * @return string
     */
    public function getKeyframes()
    {
        $blocks = $this->getChildGroup('slides');
        if (count($blocks) < 2) {
            // nothing to animate
            return '';
        }

        $keyframes = array();
        $time = 0;
        // what if $transition==0 ?
        $transition = $this->getTransitionTime();
        foreach ($blocks as $index => $block) {
            $duration = $this->getSlideDuration($block->getSlide());
            $properties = $this->getAnimatedProperties($block, $index);
            $keyframes[$index] = array_filter(array(
                $time - $transition             => $properties['show-start'],
                $time                           => $properties['show-end'],
                $time + $duration               => $properties['hide-start'],
                $time + $duration + $transition => $properties['hide-end'],
                'from'                          => $properties['hide'],
                'to'                            => $properties['hide']
            ));
            $time += $duration + $transition;
        }
        $totalTime = $time;

        $css = '';
        foreach ($keyframes as $index => $frames) {
            $id = $blocks[$index]->getHtmlId();
            if (isset($frames[0])) {
                unset($frames['from']);
                $frames[$totalTime] = $frames[0];
            }
            if (isset($frames[$totalTime])) {
                unset($frames['to']);
            }
            ksort($frames);

            $css .= "#{$id} { animation: {$id} {$totalTime}s infinite; }\n";
            $css .= "@keyframes {$id} {\n";
            foreach ($frames as $time => $frame) {
                if (is_numeric($time)) {
                    if ($time < 0) {
                        // wrap around to end of sequence
                        // TODO need to move 'from' and 'to' frames
                        $time += $totalTime;
                    }
                    $time = sprintf('%.2f%%', 100 * $time / $totalTime);
                }
                $css .= "{$time} { {$frame} }\n";
            }
            $css .= "}\n";
        }

        // add prefixes
        $css = preg_replace(array(
            '/\b(animation[-\w]*:[^;]+;)/i',
            '/@(keyframes \S+ {(?:[^{}]*{[^{}]*})*[^{}]*})/i'
        ), array(
            '-webkit-\1 -moz-\1 -ms-\1 -o-\1 \1',
            '@-webkit-\1 @-moz-\1 @-ms-\1 @-o-\1 @\1'
        ), $css);

        return $css;
    }

    public function getTransitionTime()
    {
        return $this->hasData('transition_time') ? $this->getData('transition_time') : 1;
    }

    public function getSlideDuration(Clockworkgeek_Futureslider_Model_Slide $slide)
    {
        $time = $slide->getDuration();
        return is_null($time) ? $this->getDuration() : $time;
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
