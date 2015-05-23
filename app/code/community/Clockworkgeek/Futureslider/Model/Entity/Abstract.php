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

class Clockworkgeek_Futureslider_Model_Entity_Abstract extends Mage_Catalog_Model_Abstract
{

    protected $_entityType;

    /**
     * @return Mage_Eav_Model_Resource_Entity_Attribute_Group_Collection
     */
    public function getAttributeGroupCollection()
    {
        $setId = $this->getAttributeSetId();
        return Mage::getResourceModel('eav/entity_attribute_group_collection')
            ->setAttributeSetFilter($setId)
            ->setSortOrder()
            ->setModel('futureslider/entity_attribute_group');
    }

    public function getAttributeSetId()
    {
        if (!$this->getData('attribute_set_id')) {
            $this->setAttributeSetId($this->getEntityType()->getDefaultAttributeSetId());
        }
        return $this->getData('attribute_set_id');
    }

    /**
     * @return Mage_Eav_Model_Entity_Attribute_Set
     */
    public function getAttributeSet()
    {
        $setId = $this->getAttributeSetId();
        return Mage::getModel('eav/entity_attribute_set')->load($setId);
    }

    public function getDefaultData()
    {
        $attributes = $this->getEntityType()->getAttributeCollection();
        $data = array();
        /* @var $attr Mage_Eav_Model_Entity_Attribute */
        foreach ($attributes as $attr) {
            if ($attr->hasDefaultValue()) {
                $data[$attr->getAttributeCode()] = $attr->getDefaultValue();
            }
        }
        return $data;
    }

    /**
     * @return Mage_Eav_Model_Entity_Type
     */
    public function getEntityType()
    {
        return Mage::getModel('eav/entity_type')->loadByCode($this->_entityType);
    }
}
