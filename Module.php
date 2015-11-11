<?php
/**
 * @link https://github.com/borodulin/yii2-proxypool
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-proxypool/blob/master/LICENSE
 */

namespace conquer\proxypool;

use conquer\proxypool\console\ProxyController;
use conquer\proxypool\console\ScanController;

/**
 * 
 * @author Andrey Borodulin
 */
class Module extends \yii\base\Module implements \yii\base\BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        if ($app instanceof \yii\web\Application) {
            $app->getUrlManager()->addRules([
                    $this->id => $this->id . '/default/index',
                    $this->id . '/<id:\w+>' => $this->id . '/default/view',
                    $this->id . '/<controller:[\w\-]+>/<action:[\w\-]+>' => $this->id . '/<controller>/<action>',
            ], false);
        } elseif ($app instanceof \yii\console\Application) {
            $app->controllerMap[$this->id] = [
                    'class' => ProxyController::className(),
            ];
            $app->controllerMap[$this->id.'-scan'] = [
                    'class' => ScanController::className(),
            ];
        }
    }
}