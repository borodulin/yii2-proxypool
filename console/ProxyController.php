<?php
/**
 * @link https://github.com/borodulin/yii2-proxypool
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-proxypool/blob/master/LICENSE
 */

namespace conquer\proxypool\console;

use conquer\proxypool\models\Fineproxy;
use conquer\proxypool\models\ProxyStat;
use conquer\proxypool\models\Proxy;

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
    public function actionCheck()
    {
        ProxyStat::checkProxies();
    }
    
    /**
     * 
     */
    public function actionFineproxy()
    {
        Fineproxy::scan();
    }
    
    /**
     * 
     */
    public function actionFoxtools()
    {
        Proxy::scanFoxtools();
    }    
}