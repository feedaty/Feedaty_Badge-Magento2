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

                ];

                $connection = $setup->getConnection();

                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableName, $name, $definition);
                }
            }

        }



        if(version_compare($context->getVersion(), '2.7.1') < 0) {

            $feedatyTable = $setup->getTable('feedaty_badge_settings');

            if ($setup->getConnection()->isTableExists($feedatyTable) != true) {
                $table = $setup->getConnection()
                    ->newTable($feedatyTable)
                    ->addColumn(
                        'id',
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
                        'create_pagination',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'nullable' => true
                        ],
                        'Create Review Cron Pagination '
                    )
                    ->addColumn(
                        'auth_code',
                        Table::TYPE_TEXT,
                        250,
                        'Auth Code '
                    )
                    ->setComment('Feedaty Badge');
                $setup->getConnection()->createTable($table);
            }
        }


        $installer->endSetup();
    }
}

