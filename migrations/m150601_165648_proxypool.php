<?php

use yii\db\Schema;
use yii\db\Migration;

class m150601_165648_proxypool extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->createTable('{{%domain}}', [
            'domain_id' => Schema::TYPE_PK,
            'domain_name' => Schema::TYPE_STRING . ' NOT NULL',
            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'check_url' => Schema::TYPE_STRING,
            'check_content' => Schema::TYPE_STRING,
        ]);
        $this->createIndex('uk_domain_domain_name', '{{%domain}}', 'domain_name', true);
        $this->createIndex('idx_domain_check_url', '{{%domain}}', 'check_url');
        
        $this->createTable('{{%fineproxy}}', [
            'fineproxy_id' => Schema::TYPE_PK,
            'fineproxy_login' => Schema::TYPE_STRING . '(100) NOT NULL',
            'fineproxy_password' => Schema::TYPE_INTEGER . '(100) NOT NULL',
            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
        ]);
        $this->createIndex('uk_fineproxy_fineproxy_login', '{{%fineproxy}}', 'fineproxy_login', true);
        
        $this->createTable('{{%proxy}}', [
                'proxy_id' => Schema::TYPE_PK,
                'proxy_address' => Schema::TYPE_STRING . ' NOT NULL',
                'proxy_port' => Schema::TYPE_INTEGER . ' NOT NULL',
                'proxy_type' => Schema::TYPE_SMALLINT . ' NOT NULL',
                'proxy_login' => Schema::TYPE_STRING .'(100)',
                'proxy_password' => Schema::TYPE_STRING .'(100)',
                'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'fineproxy_id' => Schema::TYPE_INTEGER,
        ]);
        $this->createIndex('uk_proxy', '{{%proxy}}', ['proxy_address','proxy_port'], true);
        $this->createIndex('idx_proxy_proxy_login', '{{%proxy}}', 'proxy_login');
        $this->addForeignKey('fk_proxy_fineproxy_fineproxy_id', '{{%proxy}}', 'fineproxy_id', '{{%fineproxy}}', 'fineproxy_id');
        
        $this->createTable('{{%proxy_stat}}', [
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
        $this->createIndex('uk_proxy_stat', '{{%proxy_stat}}', ['proxy_id','domain_id'], true);
        $this->createIndex('idx_proxy_stat_error_cnt', '{{%proxy_stat}}', 'error_cnt');
        $this->createIndex('idx_proxy_stat_updated_at', '{{%proxy_stat}}', 'updated_at');
        $this->addForeignKey('fk_proxy_stat_proxy_proxy_id', '{{%proxy_stat}}', 'proxy_id', '{{%proxy}}', 'proxy_id');
        $this->addForeignKey('fk_proxy_stat_domain_domain_id', '{{%proxy_stat}}', 'domain_id', '{{%domain}}', 'domain_id');
    }
    
    public function safeDown()
    {
        $this->dropTable('{{%proxy_stat}}');
        $this->dropTable('{{%proxy}}');
        $this->dropTable('{{%fineproxy}}');
        $this->dropTable('{{%domain}}');
    }
}
