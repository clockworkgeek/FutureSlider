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

// "General" group
$this->addAttribute('futureslider_slide', 'name', array(
    'type' => 'varchar',
    'label' => 'Name',
    'required' => true
));
$this->addAttribute('futureslider_slide', 'enabled', array(
    'type' => 'int',
    'label' => 'Enabled',
    'input' => 'select',
    'source' => 'eav/entity_attribute_source_boolean',
    'default' => 1,
    'global' => 0,
    'required' => true
));
$this->addAttribute('futureslider_slide', 'duration', array(
    'type' => 'int',
    'label' => 'Display Time (seconds)',
    'input' => 'number',
    'frontend_class' => 'validate-not-negative-number',
    'global' => 0,
    'required' => false
));
$this->addAttribute('futureslider_slide', 'active_from', array(
    'type' => 'datetime',
    'label' => 'Active From Date',
    'input' => 'date',
    'frontend_class' => 'validate-date validate-date-range date-range-active-from',
    'backend' => 'eav/entity_attribute_backend_datetime',
    'global' => 0,
    'required' => false
));
$this->addAttribute('futureslider_slide', 'active_to', array(
    'type' => 'datetime',
    'label' => 'Active To Date',
    'input' => 'date',
    'frontend_class' => 'validate-date validate-date-range date-range-active-to',
    'backend' => 'eav/entity_attribute_backend_datetime',
    'global' => 0,
    'required' => false
));
$this->addAttribute('futureslider_slide', 'link_url', array(
    'type' => 'text', /* text because URLs with tracking code can get very long */
    'label' => 'Link URL',
    'input' => 'url',
    'frontend_class' => 'validate-url',
    'global' => 0,
    'required' => false
));
$this->addAttribute('futureslider_slide', 'link_text', array(
    'type' => 'varchar',
    'label' => 'Link Text',
    'note' => 'Used for SEO if Link URL is specified',
    'global' => 0,
    'input' => 'text',
    'required' => false
));


// "Display" group
$this->addAttribute('futureslider_slide', 'background_color', array(
    'type' => 'varchar',
    'label' => 'Background Color',
    'note' => 'Color might be seen if "Scale Image" is not "Cover whole slide" or is a transparent PNG.',
    'input' => 'color',
    'frontend_class' => 'validate-color',
    'group' => 'Display',
    'default' => '#ffffff',
    'global' => 0,
    'required' => true
));
$this->addAttribute('futureslider_slide', 'background_image', array(
    'type' => 'varchar',
    'label' => 'Image File',
    'input' => 'mediaurl',
    'group' => 'Display',
    'global' => 0,
    'required' => false
));
$this->addAttribute('futureslider_slide', 'background_size', array(
    'type' => 'varchar',
    'label' => 'Scale Image',
    'input' => 'select',
    'group' => 'Display',
    'source' => 'futureslider/entity_attribute_source_backgroundsize',
    'default' => 'cover',
    'global' => 0,
    'required' => false
));

// no attribute can be exactly "content" because that's an ID on many pages
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
