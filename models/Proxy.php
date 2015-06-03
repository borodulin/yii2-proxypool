<?php

namespace conquer\proxypool\models;

use conquer\helpers\Curl;
use conquer\helpers\XPath;

/**
 * 
 * @property string $proxy_address 
 * @property integer $proxy_port
 * @property integer $proxy_type 
 * @property string $proxy_login 
 * @property string $proxy_password 
 * @property integer $fineproxy_id
 * 
 * @author Andrey Borodulin
 *
 */
class Proxy extends \yii\db\ActiveRecord
{
    
    public static function tableName()
    {
        return '{{%proxy}}';
    }
    
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }
    
    public static function addProxy($address,$port,$type='HTTP',$login=null,$pwd=null,$fineproxy=null)
    {
        static $command;
        if(empty($command)){
            $sql=<<<SQL
INSERT INTO srv_proxy(proxy_address,proxy_port,proxy_type,proxy_login,proxy_password,fineproxy_id)
VALUES(:address,:port,:type,:login,:pwd,:fineproxy)
ON DUPLICATE KEY UPDATE proxy_type=VALUES(proxy_type),proxy_login=VALUES(proxy_login),proxy_password=VALUES(proxy_password),fineproxy_id=VALUES(fineproxy_id)
SQL;
            $command=Yii::$app->db->createCommand($sql);
        }
        return $command->execute(compact('address','port','type','login','pwd','fineproxy'));
    }
    
    public static function scanList()
    {
        $curl = new Curl('http://www.tubeincreaser.com/proxylist.txt');
        if($curl->execute()){
            $rows=array();
            $lines = explode("\n", $curl->content);
            $tran=\Yii::$app->db->beginTransaction();
            foreach ($lines as $line){
                if(preg_match('/(\d+\.\d+\.\d+\.\d+):(\d+)/',$line,$matches)){
                    Proxy::addProxy($matches[1], $matches[2]);
                }
            }
            $tran->commit();
        }
    }
    
    public static function scanList2()
    {
        for ($i = 1; $i < 41; $i++) {
            $curl = new Curl("http://foxtools.ru/Proxy?page={$i}");
            if($curl->execute()){
                $xpath = new XPath($curl->content, true);
                $elements = $xpath->query('//*[@id="theProxyList"]/tbody/tr');
                $tran=\Yii::$app->db->beginTransaction();
                foreach ($elements as $element) {
                    $proxyAddress=$element->childNodes->item(2)->textContent;
                    $proxyPort=$element->childNodes->item(4)->textContent;
                    $proxyType=$element->childNodes->item(10)->textContent;
                    if(!in_array($proxyType,array('http','https')))
                        $proxyType='http';
                    if(Proxy::addProxy($proxyAddress, $proxyPort,$proxyType))
                        echo "added new free proxy {$proxyAddress}:{$proxyPort}\n";
                }
                $tran->commit();
            }
        }
    }  
}
