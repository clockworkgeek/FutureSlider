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
 * Improved EAV setup.
 *
 * Made with the assumption that tables should be minimal to begin,
 * then user is free to add more columns later.
 */
class Clockworkgeek_Futureslider_Model_Resource_Setup extends Mage_Eav_Model_Entity_Setup
{

    public function addEntityType($code, array $params)
    {
        parent::addEntityType($code, $params);
        return $this->setDefaultSetToEntityType($code);
    }

    /**
     * Complement to addEntityType()
     *
     * Run with foreign key checks to delete attributes, sets, and groups too.
     * That means no startSetup() nor endSetup().
     *
     * @param string $code
     */
    public function deleteEntityType($code)
    {
        $table = $this->getTable('eav/entity_type');
        $this->deleteTableRow($table, 'entity_type_code', $code);
    }

    /**
     * @return Clockworkgeek_Futureslider_Model_Resource_Setup
     */
    public function createEntityTables($baseTableName, array $options = array())
    {
        $isNoCreateMainTable = $this->_getValue($options, 'no-main', false);
        $isNoDefaultTypes    = $this->_getValue($options, 'no-default-types', false);
        $customTypes         = $this->_getValue($options, 'types', array());
        $tables              = array();
        $connection          = $this->getConnection();

        if (!$isNoCreateMainTable) {
            /**
             * Create table main eav table
             */
            $mainTable = $connection
            ->newTable($this->getTable($baseTableName))
            ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                'identity'  => true,
                'nullable'  => false,
                'primary'   => true,
                'unsigned'  => true,
            ), 'Entity Id')
            ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ), 'Entity Type Id')
            ->addColumn('attribute_set_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ), 'Attribute Set Id')
            ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
                'nullable'  => false,
            ), 'Created At')
            ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
                'nullable'  => false,
            ), 'Updated At')
            ->addIndex($this->getIdxName($baseTableName, array('entity_type_id')),
                array('entity_type_id'))
            ->addForeignKey($this->getFkName($baseTableName, 'entity_type_id', 'eav/entity_type', 'entity_type_id'),
                'entity_type_id', $this->getTable('eav/entity_type'), 'entity_type_id',
                Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
            ->setComment('Eav Entity Main Table');

            $tables[$this->getTable($baseTableName)] = $mainTable;
        }

        $types = array();
        if (!$isNoDefaultTypes) {
            $types = array(
                'datetime'  => array(Varien_Db_Ddl_Table::TYPE_DATETIME, null),
                'decimal'   => array(Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4'),
                'int'       => array(Varien_Db_Ddl_Table::TYPE_INTEGER, null),
                'text'      => array(Varien_Db_Ddl_Table::TYPE_TEXT, '64k'),
                'varchar'   => array(Varien_Db_Ddl_Table::TYPE_TEXT, '255')
            );
        }

        if (!empty($customTypes)) {
            foreach ($customTypes as $type => $fieldType) {
                if (count($fieldType) != 2) {
                    throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Wrong type definition for %s', $type));
                }
                $types[$type] = $fieldType;
            }
        }

        /**
         * Create table array($baseTableName, $type)
         */
        foreach ($types as $type => $fieldType) {
            $eavTableName = array($baseTableName, $type);

            $eavTable = $connection->newTable($this->getTable($eavTableName));
            $eavTable
            ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                'identity'  => true,
                'nullable'  => false,
                'primary'   => true,
                'unsigned'  => true,
            ), 'Value Id')
            ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ), 'Entity Type Id')
            ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ), 'Store Id')
            ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ), 'Attribute Id')
            ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ), 'Entity Id')
            ->addColumn('value', $fieldType[0], $fieldType[1], array(
                'nullable'  => true,
            ), 'Attribute Value')
            ->addIndex($this->getIdxName($eavTableName, array('entity_id', 'store_id', 'attribute_id')),
                array('entity_id', 'store_id', 'attribute_id'),
                array('type' => 'unique'))
            ->addIndex($this->getIdxName($eavTableName, array('attribute_id')),
                array('attribute_id'))
            ->addIndex($this->getIdxName($eavTableName, array('store_id')),
                array('store_id'))
            ->addIndex($this->getIdxName($eavTableName, array('entity_id')),
                array('entity_id'));
            if ($type !== 'text') {
                $eavTable->addIndex($this->getIdxName($eavTableName, array('attribute_id', 'value')),
                    array('attribute_id', 'value'));
                $eavTable->addIndex($this->getIdxName($eavTableName, array('entity_type_id', 'value')),
                    array('entity_type_id', 'value'));
            }

            $eavTable
            ->addForeignKey($this->getFkName($eavTableName, 'entity_id', $baseTableName, 'entity_id'),
                'entity_id', $this->getTable($baseTableName), 'entity_id',
                Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
            ->addForeignKey($this->getFkName($eavTableName, 'attribute_id', 'eav/attribute', 'attribute_id'),
                'attribute_id', $this->getTable('eav/attribute'), 'attribute_id',
                Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
            ->addForeignKey($this->getFkName($eavTableName, 'store_id', 'core/store', 'store_id'),
                'store_id', $this->getTable('core/store'), 'store_id',
                Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
            ->setComment('Eav Entity Value Table');

            $tables[$this->getTable($eavTableName)] = $eavTable;
        }

        // DDL operations are forbidden within transactions
        // See Varien_Db_Adapter_Pdo_Mysql::_checkDdlTransaction()
        try {
            foreach ($tables as $tableName => $table) {
                $connection->createTable($table);
            }
        } catch (Exception $e) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Can\'t create table: %s', $tableName));
        }

        return $this;
    }

    /**
     * Complement to createEntityTables()
     *
     * @param string $baseTableName
     * @param array $options
     * @return Clockworkgeek_Futureslider_Model_Resource_Setup
     * @see Clockworkgeek_Futureslider_Model_Resource_Setup::createEntityTables
     */
    public function dropEntityTables($baseTableName, array $options = array())
    {
        $isNoCreateMainTable = $this->_getValue($options, 'no-main', false);
        $isNoDefaultTypes    = $this->_getValue($options, 'no-default-types', false);
        $types               = $this->_getValue($options, 'types', array());
        $connection          = $this->getConnection();

        if (!$isNoDefaultTypes) {
            $types = array_merge($types, array(
                'datetime',
                'decimal',
                'int',
                'text',
                'varchar',
                'char'
            ));
        }
        $types = array_unique($types);

        foreach ($types as $type) {
            $tableName = $this->getTable(array($baseTableName, $type));
            $connection->dropTable($tableName);
        }
        $connection->dropTable($this->getTable($baseTableName));

        return $this;
    }
}
