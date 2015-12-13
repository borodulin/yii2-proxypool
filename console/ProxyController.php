<?php
/**
 * @link https://github.com/borodulin/yii2-proxypool
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-proxypool/blob/master/LICENSE
 */

namespace conquer\proxypool\console;

use conquer\proxypool\models\Proxy;
use conquer\proxypool\models\Connection;
use conquer\proxypool\models\Domain;

/**
 * 
 * @author Andrey Borodulin
 *
 */
class ProxyController extends \yii\console\Controller
{
    public function actionIndex()
    {
        echo "Proxy controller usage";
    }
    
    /**
     * 
     */
    public function actionCheck($limit = 500, $force = null)
    {
        $proxyPool = \Yii::$app->get('proxyPool');
        
        Domain::initProxies();
        
        if ($force) {
            $time = null;
        } else {
            $time = time() - $proxyPool->checkInterval;
        }
        /* @var $connections Connection[] */
        $connections = Connection::find()
            ->from(['t' => Connection::tableName()])
            ->where(['<', 'error_cnt', $proxyPool->maxErrors])
            ->andFilterWhere(['<', 't.updated_at', $time])
            ->innerJoinWith(['proxy', 'domain'])
            ->andWhere(['is not', 'check_url', null])
            ->indexBy('connection_id')
            ->limit($limit)
            ->orderBy(['t.updated_at' => SORT_ASC])
            ->all();
        
        Connection::checkProxies($connections);
    }
}