<?php
/**
 * @link https://github.com/borodulin/yii2-proxypool
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-proxypool/blob/master/LICENSE
 */

namespace conquer\proxypool\models;

use Yii;
use yii\behaviors\TimestampBehavior;


/**
 * 
 * @property string $proxy_address 
 * @property integer $proxy_port
 * @property string $proxy_login 
 * @property string $proxy_password 
 * 
 * @property Connection[] $connections
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
        if (!$proxyPool = Yii::$app->get('proxyPool', false)) {
            Yii::$app->set('proxyPool', $proxyPool = new ProxyPool());
        }
        return $proxyPool->proxyTable;
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['proxy_address', 'proxy_port'], 'required'],
            [['proxy_port', 'created_at', 'updated_at'], 'integer'],
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
    public function getConnections()
    {
        return $this->hasMany(Connection::className(), ['proxy_id' => 'proxy_id']);
    }
    
    /**
     * 
     * @param string $address
     * @param integer $port
     * @param string $login
     * @param string $pwd
     * @return Proxy
     */
    public static function addProxy($address, $port, $login = null, $pwd = null)
    {
        $model = static::findOne([
                'proxy_address' => $address,
                'proxy_port' => $port,
        ]);
        if (empty($model)) {
            $model = new static([
                    'proxy_address' => $address,
                    'proxy_port' => $port,
            ]);
        }
        $model->proxy_login = $login;
        $model->proxy_password = $pwd;
        $model->save(false);
        
        return $model;
    }
}
