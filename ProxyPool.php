<?php

namespace conquer\proxypool;

use yii\base\Component;

class ProxyPool extends Component
{
    public $connectionTable = '{{%connection}}';
    
    public $proxyTable = '{{%proxy}}';
    
    public $domainTable = '{{%domain}}';
    
    public $maxErrors = 20;
    
    public $checkInterval = 21600;
    
}