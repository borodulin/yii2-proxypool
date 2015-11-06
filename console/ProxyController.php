<?php
/**
 * @link https://github.com/borodulin/yii2-proxypool
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-proxypool/blob/master/LICENSE
 */

namespace conquer\proxypool\console;

use conquer\proxypool\models\Proxy;
use conquer\proxypool\models\Connection;

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
    public function actionCheck($limit = 500)
    {
        Connection::checkProxies($limit);
    }
}