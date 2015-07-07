<?php
/**
 * @link https://github.com/borodulin/yii2-proxypool
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-proxypool/blob/master/LICENSE
 */

use yii\db\Schema;
use yii\db\Migration;

/**
 * 
 * @author Andrey Borodulin
 *
 */
class m150601_165648_proxypool extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->createTable('{{%pool_domain}}', [
            'domain_id' => Schema::TYPE_PK,
            'domain_name' => Schema::TYPE_STRING . ' NOT NULL',
            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'check_url' => Schema::TYPE_STRING,
            'check_content' => Schema::TYPE_STRING,
        ]);
        $this->createIndex('uk_domain_domain_name', '{{%pool_domain}}', 'domain_name', true);
        $this->createIndex('idx_domain_check_url', '{{%pool_domain}}', 'check_url');
        
        $this->createTable('{{%pool_fineproxy}}', [
            'fineproxy_id' => Schema::TYPE_PK,
            'fineproxy_login' => Schema::TYPE_STRING . '(100) NOT NULL',
            'fineproxy_password' => Schema::TYPE_STRING . '(100) NOT NULL',
            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
        ]);
        $this->createIndex('uk_fineproxy_fineproxy_login', '{{%pool_fineproxy}}', 'fineproxy_login', true);
        
        $this->createTable('{{%pool_proxy}}', [
                'proxy_id' => Schema::TYPE_PK,
                'proxy_address' => Schema::TYPE_STRING . ' NOT NULL',
                'proxy_port' => Schema::TYPE_INTEGER . ' NOT NULL',
                'proxy_login' => Schema::TYPE_STRING .'(100)',
                'proxy_password' => Schema::TYPE_STRING .'(100)',
                'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'fineproxy_id' => Schema::TYPE_INTEGER,
        ]);
        $this->createIndex('uk_proxy', '{{%pool_proxy}}', ['proxy_address','proxy_port'], true);
        $this->createIndex('idx_proxy_proxy_login', '{{%pool_proxy}}', 'proxy_login');
        $this->addForeignKey('fk_proxy_fineproxy_fineproxy_id', '{{%pool_proxy}}', 'fineproxy_id', '{{%pool_fineproxy}}', 'fineproxy_id', 'cascade', 'cascade');
        
        $this->createTable('{{%pool_proxy_stat}}', [
                'stat_id' => Schema::TYPE_PK,
                'proxy_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'domain_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'request_cnt' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'success_cnt' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'error_cnt' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'speed_last' => Schema::TYPE_DECIMAL . '(10,6)',
                'speed_avg' => Schema::TYPE_DECIMAL . '(10,6)',
                'speed_savg' => Schema::TYPE_DECIMAL . '(10,6)',
                'cookies' => Schema::TYPE_TEXT,
                'error_message' => Schema::TYPE_TEXT,
        ]);
        $this->createIndex('uk_proxy_stat', '{{%pool_proxy_stat}}', ['proxy_id','domain_id'], true);
        $this->createIndex('idx_proxy_stat_error_cnt', '{{%pool_proxy_stat}}', 'error_cnt');
        $this->createIndex('idx_proxy_stat_updated_at', '{{%pool_proxy_stat}}', 'updated_at');
        $this->addForeignKey('fk_proxy_stat_proxy_proxy_id', '{{%pool_proxy_stat}}', 'proxy_id', '{{%pool_proxy}}', 'proxy_id', 'cascade', 'cascade');
        $this->addForeignKey('fk_proxy_stat_domain_domain_id', '{{%pool_proxy_stat}}', 'domain_id', '{{%pool_domain}}', 'domain_id', 'cascade', 'cascade');
    }
    
    public function safeDown()
    {
        $this->dropTable('{{%pool_proxy_stat}}');
        $this->dropTable('{{%pool_proxy}}');
        $this->dropTable('{{%pool_fineproxy}}');
        $this->dropTable('{{%pool_domain}}');
    }
}
