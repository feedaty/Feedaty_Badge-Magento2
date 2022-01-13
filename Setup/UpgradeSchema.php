<?php

namespace Feedaty\Badge\Setup;


use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

/**
 * Class UpgradeSchema
 * @package Feedaty\Badge\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {

        $installer = $setup;

        $installer->startSetup();

        if(version_compare($context->getVersion(), '2.7.0') < 0) {

            $tableName = $setup->getTable('review_detail');
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $columns = [
                    'feedaty_source' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                        'nullable' => false,
                        'default' => false,
                        'comment' => 'Feedaty Review',
                    ],
                    'feedaty_pagination' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => false,
                        'default' => 0,
                        'comment' => 'Feedaty Review Pagination',
                    ],
                    'feedaty_source_id' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => false,
                        'default' => 0,
                        'comment' => 'Feedaty Review ID',
                    ],
                    'feedaty_update' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                        'nullable' => false,
                        'size' => null,
                        'comment' => 'Feedaty Review Update At',
                    ],
                    'feedaty_create' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                        'nullable' => false,
                        'size' => null,
                        'comment' => 'Feedaty Review Create At',
                    ],
                    'feedaty_product_review_id' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => false,
                        'default' => 0,
                        'comment' => 'Feedaty Product Review ID',
                    ],
                    'feedaty_product_mediated' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                        'nullable' => false,
                        'default' => 0,
                        'comment' => 'Feedaty Product Mediated',
                    ]

                ];

                $connection = $setup->getConnection();

                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableName, $name, $definition);
                }
            }
        }



        $tableName = $setup->getTable('feedaty_orders');
        if(version_compare($context->getVersion(), '2.7.0') < 0) {
            if ($setup->getConnection()->isTableExists($tableName) != true) {
                $table = $setup->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                        'feedaty_orders_id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ],
                        'ID'
                    )
                    ->addColumn(
                        'order_id',
                        Table::TYPE_TEXT,
                        null,
                        ['nullable' => false],
                        'Title'
                    )
                    ->addColumn(
                        'feedaty_customer_notified',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => false,
                            'unsigned' => true,
                            'nullable' => true,
                            'primary' => false
                        ],
                        'Feedaty Order Notification Sent'
                    )
                    ->addColumn(
                        'created_at',
                        Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                        'Created At'
                    )
                    ->addColumn(
                        'updated_at',
                        Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                        'Created At'
                    )
                    ->setComment('Feedaty Orders Data');

                $setup->getConnection()->createTable($table);
            }
        }

        $installer->endSetup();
    }
}

