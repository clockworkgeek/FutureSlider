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

class Clockworkgeek_Futureslider_Model_Resource_Slide_Collection extends Mage_Catalog_Model_Resource_Collection_Abstract
{

    protected $_widgetFilters = array();

    protected function _construct()
    {
        $this->_init('futureslider/slide');
    }

    // TODO refactor to abstract collection class
    protected function _afterLoad()
    {
        // useful for group-wide operations only applicable to collections, like explicit ordering
        Mage::dispatchEvent('futureslider_slide_collection_load_after', array('collection' => $this));

        // useful for those attribute after load events which are normally missed by collections
        /* @var $model Mage_Core_Model_Abstract */
        foreach ($this as $model) {
            $model->afterLoad();
        }
        return parent::_afterLoad();
    }

    protected function _renderFiltersBefore()
    {
        // please add filters now
        Mage::dispatchEvent('futureslider_slide_collection_filter_before', array('collection' => $this));
        return parent::_renderFiltersBefore();
    }

    public function setEnabledFilter()
    {
        return $this->addAttributeToFilter('enabled', true);
    }

    public function addWidgetFilters(array $widgetData)
    {
        $this->_widgetFilters = array_merge_recursive($this->_widgetFilters, $widgetData);
        return $this;
    }

    public function getWidgetFilters()
    {
        return $this->_widgetFilters;
    }

    public function getIdFieldName()
    {
        if (! ($fieldName = parent::getIdFieldName())) {
            $this->_idFieldName = $fieldName = $this->getEntity()->getIdFieldName();
        }

        return $fieldName;
    }

    public function addIdFilter(array $ids)
    {
        $ids = array_map('intval', $ids);
        $this->addAttributeToFilter($this->getIdFieldName(), array('in' => $ids));
        $select = $this->getSelect();
        $select->order(sprintf(
            'FIELD(%s, %s)',
            $this->_getAttributeFieldName($this->getIdFieldName()),
            $this->getConnection()->quote($ids)
        ));
        return $this;
    }

    /**
     * Supports asterisk as wildcard.
     *
     * An asterisk is not SQL standard but it is convenient for users.
     * Fulltext search is not possible with InnoDB tables.
     *
     * @param string $name
     */
    public function addNameFilter($name)
    {
        $name = '%' . str_replace('*', '%', $name) . '%';
        // DB adapter should handle quoting without breaking wildcards
        $this->addAttributeToFilter('name', array('like' => $name));
    }
}
