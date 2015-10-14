<?php
/**
 * @link https://github.com/borodulin/yii2-proxypool
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-proxypool/blob/master/LICENSE
 */

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
            'domain_id' => $this->primaryKey(),
            'domain_name' => $this->string()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'check_url' => $this->string(),
            'check_content' => $this->string(),
        ]);
        $this->createIndex('uk_domain_domain_name', '{{%pool_domain}}', 'domain_name', true);
        $this->createIndex('idx_domain_check_url', '{{%pool_domain}}', 'check_url');
        
        $this->createTable('{{%pool_fineproxy}}', [
            'fineproxy_id' => $this->primaryKey(),
            'fineproxy_login' => $this->string(100)->notNull(),
            'fineproxy_password' => $this->string(100)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('uk_fineproxy_fineproxy_login', '{{%pool_fineproxy}}', 'fineproxy_login', true);
        
        $this->createTable('{{%pool_proxy}}', [
                'proxy_id' => $this->primaryKey(),
                'proxy_address' => $this->string()->notNull(),
                'proxy_port' => $this->integer()->notNull(),
                'proxy_login' => $this->string(100),
                'proxy_password' => $this->string(100),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),
                'fineproxy_id' => $this->integer()->notNull(),
        ]);
        $this->createIndex('uk_proxy', '{{%pool_proxy}}', ['proxy_address','proxy_port'], true);
        $this->createIndex('idx_proxy_proxy_login', '{{%pool_proxy}}', 'proxy_login');
        $this->addForeignKey('fk_proxy_fineproxy_fineproxy_id', '{{%pool_proxy}}', 'fineproxy_id', '{{%pool_fineproxy}}', 'fineproxy_id', 'cascade', 'cascade');
        
        $this->createTable('{{%pool_proxy_stat}}', [
                'stat_id' => $this->primaryKey(),
                'proxy_id' => $this->integer()->notNull(),
                'domain_id' => $this->integer()->notNull(),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),
                'request_cnt' => $this->integer()->notNull()->defaultValue(0),
                'success_cnt' => $this->integer()->notNull()->defaultValue(0),
                'error_cnt' => $this->integer()->notNull()->defaultValue(0),
                'speed_last' => $this->decimal(10,6),
                'speed_avg' => $this->decimal(10,6),
                'speed_savg' => $this->decimal(10,6),
                'cookies' => $this->text(),
                'error_message' => $this->text(),
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
