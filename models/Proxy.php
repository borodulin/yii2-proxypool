<?php
/**
 * @link https://github.com/borodulin/yii2-proxypool
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-proxypool/blob/master/LICENSE
 */

namespace conquer\proxypool\models;

use conquer\helpers\Curl;
use conquer\helpers\XPath;
use yii\behaviors\TimestampBehavior;

/**
 * 
 * @property string $proxy_address 
 * @property integer $proxy_port
 * @property string $proxy_login 
 * @property string $proxy_password 
 * @property integer $fineproxy_id
 * 
 * @author Andrey Borodulin
 *
 */
class Proxy extends \yii\db\ActiveRecord
{
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%pool_proxy}}';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['proxy_address', 'proxy_port'], 'required'],
            [['proxy_port', 'created_at', 'updated_at', 'fineproxy_id'], 'integer'],
            [['proxy_address'], 'string', 'max' => 255],
            [['proxy_login', 'proxy_password'], 'string', 'max' => 100],
            [['proxy_address', 'proxy_port'], 'unique', 'targetAttribute' => ['proxy_address', 'proxy_port'], 'message' => 'The combination of Proxy Address and Proxy Port has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'proxy_id' => 'Proxy ID',
            'proxy_address' => 'Proxy Address',
            'proxy_port' => 'Proxy Port',
            'proxy_login' => 'Proxy Login',
            'proxy_password' => 'Proxy Password',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'fineproxy_id' => 'Fineproxy ID',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFineproxy()
    {
        return $this->hasOne(Fineproxy::className(), ['fineproxy_id' => 'fineproxy_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProxyStats()
    {
        return $this->hasMany(ProxyStat::className(), ['proxy_id' => 'proxy_id']);
    }
    
    /**
     * 
     * @param string $address
     * @param integer $port
     * @param string $login
     * @param string $pwd
     * @param integer $fineproxy
     */
    public static function addProxy($address, $port, $login=null, $pwd=null, $fineproxy=null)
    {
        $model = static::findOne([
                'proxy_address' => $address,
                'proxy_port' => $port,
        ]);
        if (empty($model)) {
            $model = new static();
            $model->proxy_address = $address;
            $model->proxy_port = $port;
            $new = true;
        } else {
            $new = false;
        }
        $model->proxy_login = $login;
        $model->proxy_password = $pwd;
        $model->fineproxy_id = $fineproxy;
        $model->save(false);
        
        return $new;
    }
    
    /**
     * @deprecated
     */
    public static function scanTubeincreaser()
    {
        $curl = new Curl('http://www.tubeincreaser.com/proxylist.txt');
        if ($curl->execute()) {
            $rows = [];
            $lines = explode("\n", $curl->content);
            $tran = \Yii::$app->db->beginTransaction();
            foreach ($lines as $line) {
                if (preg_match('/(\d+\.\d+\.\d+\.\d+):(\d+)/',$line,$matches)) {
                    Proxy::addProxy($matches[1], $matches[2]);
                }
            }
            $tran->commit();
        }
    }
    
    /**
     * 
     */
    public static function scanFoxtools()
    {
        for ($i = 1; $i < 41; $i++) {
            $curl = new Curl("http://foxtools.ru/Proxy?page={$i}");
            if ($curl->execute()) {
                $xpath = new XPath($curl->content, true);
                $elements = $xpath->query('//*[@id="theProxyList"]/tbody/tr',null,false);
                if (!empty($elements)) {
                    $tran = \Yii::$app->db->beginTransaction();
                    foreach ($elements as $element) {
                        $query = $xpath->queryAll([
                            'address' => './td[2]',
                            'port' => './td[3]',
                        ], $element);
                        if (Proxy::addProxy($query['address'],$query['port'])) {
                            echo "added new free proxy {$query['address']}:{$query['port']}\n";
                        }
                    }
                    $tran->commit();
                }
            }
        }
    }  
}
