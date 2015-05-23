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

$this->startSetup();

$this->createEntityTables('futureslider/slide');
$this->addEntityType('futureslider_slide', array(
    'entity_model' => 'futureslider/slide',
    'attribute_model' => 'catalog/resource_eav_attribute',
    'table' => 'futureslider/slide',
    'additional_attribute_table' => 'futureslider/eav',
    'entity_attribute_collection' => 'futureslider/entity_attribute_collection'
));

$eavTable = new Varien_Db_Ddl_Table();
$eavTable->setName($this->getTable('futureslider/eav'));
$eavTable->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5, array(
    'unsigned' => true,
    'primary' => true,
));
$eavTable->addColumn('frontend_input_renderer', Varien_Db_Ddl_Table::TYPE_TEXT, 255);
$eavTable->addColumn('is_global', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5, array(
    'unsigned' => true,
    'nullable' => false,
    'default' => 0
));
$eavTable->addColumn('is_visible', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5, array(
    'unsigned' => true,
    'nullable' => false,
    'default' => 1
));
$eavTable->addForeignKey(
    $this->getFkName($eavTable->getName(), 'attribute_id', 'eav/attribute', 'attribute_id'),
    'attribute_id',
    $this->getTable('eav/attribute'),
    'attribute_id'
);

$this->getConnection()->createTable($eavTable);

$this->endSetup();
