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
 * @method number getDuration()
 * @method number getTransitionTime()
 * @method Varien_Object setDuration(number)
 * @method Varien_Object setTransitionTime(number)
 */
abstract class Clockworkgeek_Futureslider_Model_Html_Animation_Abstract extends Varien_Object
{

    /**
     * @var Clockworkgeek_Futureslider_Model_Slide[]
     */
    protected $_slides = array();

    /**
     * Implementors should return an array with keys 'show-start', 'show-end',
     * 'hide-start', 'hide-end' and 'hidden'.
     * Values are CSS rules or a nested array for child elements,
     * that key is the CSS selector.
     */
    abstract public function getAnimatedProperties();

    /**
     * Add a slide to the sequence of animation
     *
     * @param Clockworkgeek_Futureslider_Model_Slide $slide
     * @param string $id HTML element ID
     */
    public function addSlide(Clockworkgeek_Futureslider_Model_Slide $slide, $id)
    {
        $this->_slides[$id] = $slide;
        $this->unsetData('total_time');
    }

    public function getSlides()
    {
        return $this->_slides;
    }

    /**
     * Length of animation in seconds
     *
     * @return number
     */
    public function getTotalTime()
    {
        if ($this->hasTotalTime()) {
            $time = parent::getTotalTime();
        }
        else {
            $time = 0;
            $transitionTime = $this->getTransitionTime();
            $defaultDuration = $this->getDuration();
            /* @var $slide Clockworkgeek_Futureslider_Model_Slide */
            foreach ($this->getSlides() as $slide) {
                $time += $slide->getDuration() ? $slide->getDuration() : $defaultDuration;
                $time += $transitionTime;
            }
            $this->setTotalTime($time);
        }
        return $time;
    }

    public function toCss()
    {
        $css = '';
        $totalTime = $this->getTotalTime();
        foreach ($this->toKeyframes() as $id => $frames) {
            $keyid = $this->_dasherize($id);
            $css .= "#$id { animation: {$keyid} {$totalTime}s infinite; }\n";
            $css .= "@keyframes $keyid {\n";
            foreach ($frames as $index => $frame) {
                $css .= "$index { $frame }\n";
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

    public function toKeyframes()
    {
        $keyframes = array();
        $totalTime = $this->getTotalTime();
        $time = 0;
        $transition = $this->getTransitionTime();
        $defaultDuration = $this->getDuration();
        $properties = $this->getAnimatedProperties();
        /* @var $slide Clockworkgeek_Futureslider_Model_Slide */
        foreach ($this->getSlides() as $id => $slide) {
            $duration = $slide->getDuration() ? $slide->getDuration() : $defaultDuration;
            $keyframes = array_merge(
                $keyframes,
                $this->_getKeyframes($id, $properties, $time, $duration, $transition, $totalTime)
            );
            $time += $duration + $transition;
        }

        return $keyframes;
    }

    protected function _getKeyframes($id, $properties, $offsetTime, $duration, $transition, $totalTime)
    {
        // convenience function to force all times to range of 0%~99.999%
        $pct = function ($time) use ($offsetTime, $totalTime) {
            $time += $offsetTime;
            // modulo operation for floats
            while ($time < 0) $time += $totalTime;
            while ($time >= $totalTime) $time -= $totalTime;
            $time /= $totalTime / 100;
            // round to short form 5 significant figures
            return sprintf('%.5g%%', $time);
        };

        $rules = array(
            $pct(-$transition)            => @$properties['show-start'],
            $pct(0)                       => @$properties['show-end'],
            $pct($duration)               => @$properties['hide-start'],
            $pct($duration + $transition) => @$properties['hide-end']
        );
        // animation is looped so first/last state is also last/first state
        if (isset($rules['0%'])) {
            $rules['100%'] = $rules['0%'];
        }
        // allow rules to overwrite defaults of 'hidden' state
        $keyframes[$id] = array_merge(
            array_filter(
                array(
                    '0%' => @$properties['hidden'],
                    '100%' => @$properties['hidden']
                )
            ),
            array_filter($rules)
        );
        // natural key sort
        uksort($keyframes[$id], "strnatcmp");

        // search for and merge nested properties
        foreach (array_filter($properties, 'is_array') as $selector => $childprops) {
            $keyframes = array_merge_recursive(
                $keyframes,
                $this->_getKeyframes(
                    $id.' '.$selector,
                    $childprops,
                    $offsetTime,
                    $duration,
                    $transition,
                    $totalTime
                )
            );
        }

        return $keyframes;
    }

    /**
     * Replace non-word characters (even dashes) to dashes.
     * 
     * @param string $string
     * @return string
     */
    protected function _dasherize($string)
    {
        return preg_replace('/\W+/', '-', $string);
    }
}
