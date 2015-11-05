<?php
/**
 * @link https://github.com/borodulin/yii2-proxypool
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-proxypool/blob/master/LICENSE
 */

namespace conquer\proxypool\models;

use Yii;
use yii\db\ActiveQuery;
use yii\behaviors\TimestampBehavior;
use conquer\helpers\CurlTrait;

/**
 * @property integer $connection_id
 * @property string $url
 * @property string $header   
 * @property array $options
 * @property string $content
 * @property integer $errorCode
 * @property string $errorMessage
 * @property string $cookies
 * @property array $info
 * 
 * @property Domain $domain
 * @property Proxy $proxy
 * 
 * @author Andrey Borodulin
 *
 */
class Connection extends \yii\db\ActiveRecord
{
    
    use CurlTrait;
    
    public function init()
    {
        parent::init();
        $this->_autoCookie = true;
    }
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        if ($proxyPool = Yii::$app->get('proxyPool', false)) {
            return $proxyPool->connectionTable;
        } else {
            return '{{%connection}}';
        }
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['proxy_id', 'domain_id'], 'required'],
            [['proxy_id', 'domain_id', 'created_at', 'updated_at', 'request_cnt', 'success_cnt', 'error_cnt'], 'integer'],
            [['speed_last', 'speed_avg', 'speed_savg'], 'double'],
            [['error_message', 'cookies'], 'string'],
            [['proxy_id', 'domain_id'], 'unique'],
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
     * 
     * @return ActiveQuery
     */
    public function getDomain()
    {
        return $this->hasOne(Domain::className(), ['domain_id'=>'domain_id']);
    }
    
    /**
     * 
     * @return ActiveQuery
     */
    public function getProxy()
    {
        return $this->hasOne(Proxy::className(), ['proxy_id'=>'proxy_id']);
    }

    /**
     *
     * @param double $value
     */
    public function setSpeedLast($value)
    {
        $this->speed_last = $value;
        $this->speed_avg = ($this->speed_avg * $this->request_cnt + $value)/(1 + $this->request_cnt);
        $this->speed_savg = sqrt((pow($this->speed_savg, 2) * $this->request_cnt + pow($value, 2)) / (1 + $this->request_cnt));
    }
    
    /**
     * 
     * @param string $errorMessage
     */
    public function handleError($errorMessage)
    {
        $this->error_cnt++;
        $this->error_message = $errorMessage;
    }
    
    /**
     * Check the proxies status
     * @param number $limit
     * @param number $interval (seconds)
     * @param number $errors
     */
    public static function checkProxies($limit = 500, $interval = 21600, $errors = 20)
    {
        Domain::initProxies();
    
        $time = time() - $interval;
        
        /* @var $connections Connection[] */
        $connections = Connection::find()
            ->from(['t' => static::tableName()])
            ->where(['<', 't.updated_at', $time])
            ->andWhere(['<', 'error_cnt', $errors])
            ->innerJoinWith(['proxy', 'domain'])
            ->andWhere(['is not', 'check_url', null])
            ->indexBy('stat_id')
            ->limit($limit)
            ->orderBy(['t.updated_at' => SORT_ASC])
            ->all();
        
        if (count($connections) > 0) {
            foreach ($connections as $connection) {
                $proxy = $connection->proxy;
                $options = [
                    CURLOPT_PROXY => $proxy->proxy_address,
                    CURLOPT_PROXYPORT => $proxy->proxy_port,
                    CURLOPT_URL => $connection->domain->check_url,
                ];
                if (!empty($proxy->proxy_login)) {
                    $userLogin = $proxy->proxy_login;
                    if (!empty($proxy->proxy_password)) {
                        $userLogin .= ':' . $proxy->proxy_password;
                    }
                    $options[CURLOPT_PROXYUSERPWD] = $userLogin;
                }
                $connection->options = $options;
            }
            
            static::curl_multi_exec($connections);
            
            $tran = Yii::$app->db->beginTransaction();
            
            foreach ($connections as $connection) {   
                if ($connection->isHttpOK()) {
                    if (isset($connection->domain->check_content) && (!preg_match($connection->domain->check_content, $connection->content))) {
                        $connection->handleError('Invalid content');
                    } else {
                        $connection->success_cnt++;
                        $connection->error_cnt = 0;
                        $connection->error_message = null;
                        $connection->setSpeedLast($connection->info['total_time']);
                    }
                } else {
                    $connection->handleError($connection->errorMessage);
                }
                $connection->save(false);
            }
            $tran->commit();
        }
    }
    
    /**
     * Executes the single curl
     * @return boolean
     */
    public function execute($url = null, $postData = null)
    {
        $proxy = $this->proxy;
        $options = [
            CURLOPT_PROXY => $proxy->proxy_address,
            CURLOPT_PROXYPORT => $proxy->proxy_port,
        ];

        if (!empty($proxy->proxy_login)) {
            $userLogin = $proxy->proxy_login;
            if (!empty($proxy->proxy_password)) {
                $userLogin .= ':' . $proxy->proxy_password;
            }
            $options[CURLOPT_PROXYUSERPWD] = $userLogin;
        }
        
        if (!empty($this->cookies)) {
            $options[CURLOPT_COOKIE] = $this->cookies;
        }
        $this->options = $options;
        
        if (!is_null($url)) {
            $this->url = $url;
        }
        if (!is_null($postData)) {
            $this->setPostData($postData);
        }
        
        $this->curl_execute();
        
        if ($this->errorCode) {
            $this->handleError($this->errorMessage);
        } elseif (!$this->isHttpOK()) {
            $this->error_message = $this->content;
        } else {
            $this->success_cnt++;
            $this->error_cnt = 0;
            $this->error_message = null;
            $this->setSpeedLast($this->info['total_time']);
            $this->cookies = $this->getCookies();
        }
        $this->save(false);
        return $this->error_cnt == 0;
    }
    
    /**
     * Executes parallels curls
     * @param ProxyStat[] $urls
     */
    public static function multiExec($connections)
    {
        if (count($connections) > 0) {
            foreach ($connections as $connection) {
                $proxy = $connection->proxy;
                $options = [
                    CURLOPT_PROXY => $proxy->proxy_address,
                    CURLOPT_PROXYPORT => $proxy->proxy_port,
                    CURLOPT_URL => $connection->domain->check_url,
                ];
                if (!empty($proxy->proxy_login)) {
                    $userLogin = $proxy->proxy_login;
                    if (!empty($proxy->proxy_password)) {
                        $userLogin .= ':' . $proxy->proxy_password;
                    }
                    $options[CURLOPT_PROXYUSERPWD] = $userLogin;
                }
                if (!empty($connection->cookies)) {
                    $options[CURLOPT_COOKIE] = $connection->cookies; 
                }
                $connection->setOptions($options);
            }
            static::curl_multi_exec($connections);
        
            $tran = Yii::$app->db->beginTransaction();
        
            foreach ($connections as $connection) {
                if ($connection->errorCode) {
                    $connection->handleError($this->errorMessage);
                } elseif (!$connection->isHttpOK()) {
                    $connection->error_message = $connection->content;
                } else {
                    $connection->success_cnt++;
                    $connection->error_cnt = 0;
                    $connection->error_message = null;
                    $connection->setSpeedLast($connection->info['total_time']);
                    $connection->cookies = $connection->getCookies();
                }                   
                $connection->save(false);
            }
            $tran->commit();
        }
    }

}
