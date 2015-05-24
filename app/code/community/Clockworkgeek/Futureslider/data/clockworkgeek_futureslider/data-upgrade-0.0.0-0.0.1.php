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

/* @var $this Clockworkgeek_Futureslider_Model_Resource_Setup */

// "Display" group
// no attribute can be "content" because that's an ID on many pages
$this->addAttribute('futureslider_slide', 'content_widget', array(
    'type' => 'text',
    'label' => 'Content',
    'input' => 'widget',
    'group' => 'Display',
    'global' => 0,
    'required' => false
));

$this->addAttribute('futureslider_slide', 'content_position', array(
    'type' => 'varchar',
    'label' => 'Content Position',
    'input' => 'select',
    'group' => 'Display',
    'source' => 'futureslider/entity_attribute_source_contentposition',
    'default' => 'center',
    'global' => 0,
    'required' => false
));
