<?php
/**
 * @link https://github.com/borodulin/yii2-proxypool
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-proxypool/blob/master/LICENSE
 */

namespace conquer\proxypool\models;

use conquer\helpers\Curl;
use yii\behaviors\TimestampBehavior;

/**
 * 
 * @author Andrey Borodulin
 */
class Fineproxy extends \yii\db\ActiveRecord
{

    const FINEPROXY_API = 'http://account.fineproxy.org/api/getproxy/?';
    
    public static function tableName()
    {
        return '{{%fineproxy}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }
    
    public static function scan()
    {
        foreach (static::findAll() as $model){
            $model->scanProxies();
        }
    }
    
    public function scanProxies()
    {
        $url = self::FINEPROXY_API.http_build_query([
            'format' => 'csv',
            'type' => 'httpauth',
            'login' => $this->fineproxy_login,
            'password' => $this->fineproxy_password,                
        ]);
        $curl = new Curl($url);
        if($curl->execute()){
            if(preg_match_all('/(.*?):(\d+)/m', $curl->content, $matches, PREG_SET_ORDER)){
                $tran=Yii::$app->db->beginTransaction();
                foreach ($matches as $match){
                    if(Proxy::addProxy($match[1], $match[2], 'HTTP', $this->fineproxy_login, $this->fineproxy_password, $this->fineproxy_id))
                        echo "added new fineproxy: {$match[1]}:{$match[2]}\n";
                }
                $tran->commit();
            }
        }
    }
}