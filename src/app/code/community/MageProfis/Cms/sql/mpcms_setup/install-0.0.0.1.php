<?php

$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('cms/page'),
    'groupname',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 50,
        'unsigned' => true,
        'nullable' => true,
        'default'  => '',
        'comment'  => 'Group Name of CMS Item'
    )
);

// set default groupname
$installer->getConnection()->query("UPDATE `{$installer->getTable('cms/page')}` SET groupname=identifier");

$installer->endSetup();