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
 * Generic grid for EAV collections that gets column definitions from adminhtml.xml
 */
abstract class Clockworkgeek_Futureslider_Block_Adminhtml_Abstract_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    protected $_gridName;

    /**
     * Add column indexes to select fields.
     */
    protected function _prepareCollection()
    {
        /* @var $collection Mage_Eav_Model_Entity_Collection_Abstract */
        $collection = $this->getCollection();

        /* @var $config Mage_Admin_Model_Config */
        $config = Mage::getSingleton('admin/config');
        $xpath = sprintf('grid/%s/columns/*/index', $this->_gridName);
        $attributes = $config->getAdminhtmlConfig()->getXpath($xpath);
        foreach ($attributes as $attr) {
            $collection->addAttributeToSelect((string) $attr);
        }

        return parent::_prepareCollection();
    }

    /**
     * Configures columns by definitions from adminhtml.xml
     *
     * Each column should have a <code>header</code> value which may
     * be translated by specifying <code>`module="modulealias" translate="header"`</code>
     * XML attributes.  Other values may also be translated in the same way.
     * Any value matching <code>modulealias/classname::methodname</code> will
     * be treated as a callback and replaced with the callbacks result.
     * This makes <code>options</code> and <code>source_model</code> possible.
     *
     * Otherwise columns behave typically.
     * The <code>type</code> instructs to use a non-text input.
     * The source value is taken from <code>index</code> attribute in each row.
     */
    protected function _prepareColumns()
    {
        if (! $this->_gridName) {
            throw new Mage_Exception('Descendent classes of abstract grid must specify "_gridName"');
        }

        $path = sprintf('grid/%s/columns', $this->_gridName);
        /* @var $config Varien_Simplexml_Element */
        $config = Mage::getSingleton('admin/config')->getAdminhtmlConfig()->getNode($path);
        if (!$config) {
            throw new Mage_Exception("Grid definition \"{$this->_gridName}\" does not exist");
        }
        $columns = $config->children();

        /* @var $column Varien_Simplexml_Element */
        foreach ($columns as $columnId => $column) {
            $columnData = $column->asCanonicalArray();

            // callbacks before translation so that callbacks cannot be injected via CSV
            foreach ($columnData as &$val) {
                if (preg_match('#^(\w+/\w+)::(\w+)$#', $val, $match)) {
                    list(, $alias, $method) = $match;
                    $model = Mage::getSingleton($alias);
                    if (method_exists($model, $method)) {
                        $val = $model->{$method}();
                    }
                }
            }

            $attrs = $column->attributes();
            $module = (string) $attrs['module'];
            $translate = explode(' ', (string) $attrs['translate']);
            foreach ($translate as $name) {
                if (isset($columnData[$name])) {
                    $columnData[$name] = Mage::helper($module)->__($columnData[$name]);
                }
            }

            $this->addColumn($columnId, $columnData);
        }

        return parent::_prepareColumns();
    }
}
