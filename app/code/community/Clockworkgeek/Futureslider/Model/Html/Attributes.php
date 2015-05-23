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
 * Forms attributes for a single element.
 *
 * Treated like an object it has magic getters and setters.
 * Treated like a string it outputs valid HTML.
 *
 * @example
 * <code>
 * $attributes->setId('element-id');<br>
 * $attributes->addClass('some-class');<br>
 * echo "&lt;div{$attributes}&gt;";<br>
 * // &lt;div id="element-id" class="some-class"&gt;
 * </code>
 */
class Clockworkgeek_Futureslider_Model_Html_Attributes extends Varien_Object
{

    /**
     * Adds a single name to the class attribute, which will be space-separated
     *
     * @param string $classname
     * @return Clockworkgeek_Futureslider_Model_Html_Attributes
     */
    public function addClass($classname)
    {
        $classes = (array) $this->getClass();
        if (! in_array($classname, $classes)) {
            $classes[] = $classname;
            // only set if there is a change
            // if none existed before there certainly will be a change
            $this->setClass($classes);
        }

        return $this;
    }

    /**
     * Removes a class name if it exists
     *
     * @param string $classname
     * @return Clockworkgeek_Futureslider_Model_Html_Attributes
     */
    public function removeClass($classname)
    {
        $classes = (array) $this->getClass();
        foreach (array_keys($classes, $classname) as $key) {
            unset($classes[$key]);
        }
        $this->setClass($classes);

        return $this;
    }

    public function __toString()
    {
        $html = '';
        $helper = Mage::helper('core');
        foreach ($this->_data as $key => $value) {
            if (is_array($value)) {
                $value = implode(' ', $value);
            }
            elseif (! is_scalar($value)) {
                try {
                    $value = (string) $value;
                } catch (Exception $e) {
                    // silently skip objects without __toString()
                    continue;
                }
            }
            $html .= ' ' . $helper->quoteEscape($key) . '="';
            $html .= $helper->jsQuoteEscape($value) . '"';
        }

        return $html;
    }
}
