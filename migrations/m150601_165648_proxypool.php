<?php
/**
 * @link https://github.com/borodulin/yii2-proxypool
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-proxypool/blob/master/LICENSE
 */

use yii\db\Migration;
use conquer\proxypool\ProxyPool;

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
        if (!$proxyPool = Yii::$app->get('proxyPool', false)) {
            Yii::$app->set('proxyPool', $proxyPool = new ProxyPool());
        }

        $domainTable = $proxyPool->domainTable;
        $proxyTable = $proxyPool->proxyTable;
        $connectionTable = $proxyPool->connectionTable;
    
        $this->createTable($proxyPool->domainTable, [
            'domain_id' => $this->primaryKey(),
            'domain_name' => $this->string()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'check_url' => $this->string(),
            'check_content' => $this->string(),
        ]);
        $this->createIndex('uk_domain_domain_name', $proxyPool->domainTable, 'domain_name', true);
        $this->createIndex('idx_domain_check_url', $proxyPool->domainTable, 'check_url');
        
        $this->createTable($proxyPool->proxyTable, [
                'proxy_id' => $this->primaryKey(),
                'proxy_address' => $this->string()->notNull(),
                'proxy_port' => $this->integer()->notNull(),
                'proxy_login' => $this->string(100),
                'proxy_password' => $this->string(100),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('uk_proxy', $proxyPool->proxyTable, ['proxy_address','proxy_port'], true);
        $this->createIndex('idx_proxy_proxy_login', $proxyPool->proxyTable, 'proxy_login');
        
        $this->createTable($proxyPool->connectionTable, [
                'connection_id' => $this->primaryKey(),
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
        $this->createIndex('uk_proxy_stat', $proxyPool->connectionTable, ['proxy_id','domain_id'], true);
        $this->createIndex('idx_proxy_stat_error_cnt', $proxyPool->connectionTable, 'error_cnt');
        $this->createIndex('idx_proxy_stat_updated_at', $proxyPool->connectionTable, 'updated_at');
        $this->addForeignKey('fk_proxy_stat_proxy_proxy_id', $proxyPool->connectionTable, 'proxy_id', $proxyPool->proxyTable, 'proxy_id', 'cascade', 'cascade');
        $this->addForeignKey('fk_proxy_stat_domain_domain_id', $proxyPool->connectionTable, 'domain_id', $proxyPool->domainTable, 'domain_id', 'cascade', 'cascade');
    }
    
    public function safeDown()
    {
        if (!$proxyPool = Yii::$app->get('proxyPool', false)) {
            Yii::$app->set('proxyPool', $proxyPool = new ProxyPool());
        }
        $this->dropTable($proxyPool->connectionTable);
        $this->dropTable($proxyPool->proxyTable);
        $this->dropTable($proxyPool->domainTable);
    }
}
