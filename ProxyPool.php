<?php

namespace conquer\proxypool;

use yii\base\Component;
use conquer\proxypool\console\ProxyController;
use conquer\proxypool\console\ScanController;

class ProxyPool extends Component implements \yii\base\BootstrapInterface
{
    public $connectionTable = '{{%connection}}';
    
    public $proxyTable = '{{%proxy}}';
    
    public $domainTable = '{{%domain}}';
    
    public $maxErrors = 20;
    
    public $checkInterval = 21600;
 
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        if ($app instanceof \yii\console\Application) {
            $app->controllerMap[$this->id] = [
                    'class' => ProxyController::className(),
            ];
            $app->controllerMap[$this->id . '-scan'] = [
                    'class' => ScanController::className(),
            ];
        }
    }
    
}