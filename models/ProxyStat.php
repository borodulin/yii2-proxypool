<?php
/**
 * @link https://github.com/borodulin/yii2-proxypool
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-proxypool/blob/master/LICENSE
 */

namespace conquer\proxypool\models;

use yii\db\ActiveQuery;
use conquer\helpers\CurlTrait;



/**
 * 
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
class ProxyStat extends \yii\db\ActiveRecord
{
    
    use CurlTrait;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%pool_proxy_stat}}';
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
            \yii\behaviors\TimestampBehavior::className(),
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
        $this->speed_avg = ($this->speed_avg*$this->request_cnt+$value)/(1+$this->request_cnt);
        $this->speed_savg = sqrt((pow($this->speed_savg,2)*$this->request_cnt+pow($value,2))/(1+$this->request_cnt));
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
     */
    public static function checkProxies()
    {
        Domain::initProxies();
    
        $time = time()-6*60*60;
        
        /* @var $proxyStats ProxyStat[] */
        $proxyStats = ProxyStat::find()
            ->from(['t'=>static::tableName()])
            ->where(['<', 't.updated_at', $time])
            ->andWhere([ '<', 'error_cnt', 20])
            ->innerJoinWith(['proxy','domain'])
            ->andWhere(['is not', 'check_url', null])
            ->indexBy('stat_id')
            ->limit(500)
            ->orderBy(['t.updated_at' => SORT_ASC, 'RAND()' => SORT_ASC])
            ->all();
        
        if (count($proxyStats)>0) {
            foreach ($proxyStats as $proxyStat) {
                $proxy = $proxyStat->proxy;
                $options = [
                    CURLOPT_PROXY => $proxy->proxy_address,
                    CURLOPT_PROXYPORT => $proxy->proxy_port,
                    CURLOPT_URL => $proxyStat->domain->check_url,
                ];
                if (!empty($proxy->proxy_login)) {
                    $userLogin = $proxy->proxy_login;
                    if (!empty($proxy->proxy_password)) {
                        $userLogin .= ':'.$proxy->proxy_password;
                    }
                    $options[CURLOPT_PROXYUSERPWD] = $userLogin;
                }
                $proxyStat->options = $options;
            }
            
            static::curl_multi_exec($proxyStats);
            
            $tran = \Yii::$app->db->beginTransaction();
            
            foreach ($proxyStats as $proxyStat) {   
                if ($proxyStat->isHttpOK()) {
                    if (isset($proxyStat->domain->check_content) && (!preg_match($proxyStat->domain->check_content, $url->content))) {
                        $proxyStat->handleError('Invalid content');
                    } else {
                        $proxyStat->success_cnt++;
                        $proxyStat->error_cnt=0;
                        $proxyStat->error_message=null;
                        $proxyStat->setSpeedLast($url->info['total_time']);
                    }
                } else {
                    $proxyStat->handleError($url->errorMessage);
                }
                $proxyStat->save(false);
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
            $userLogin=$proxy->proxy_login;
            if (!empty($proxy->proxy_password)) {
                $userLogin.=':'.$proxy->proxy_password;
            }
            $options[CURLOPT_PROXYUSERPWD]=$userLogin;
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
            $this->error_cnt=0;
            $this->error_message=null;
            $this->setSpeedLast($this->info['total_time']);
            $this->cookies = $this->cookies;
        }
        $this->save(false);
        return $this->error_cnt === 0;
    }
    
    /**
     * Executes parallels curls
     * @param ProxyStat[] $urls
     */
    public static function multiExec($proxyStats)
    {
        if (count($proxyStats)>0) {
            foreach ($proxyStats as $proxyStat) {
                $proxy = $proxyStat->proxy;
                $options = [
                    CURLOPT_PROXY => $proxy->proxy_address,
                    CURLOPT_PROXYPORT => $proxy->proxy_port,
                    CURLOPT_URL => $proxyStat->domain->check_url,
                ];
                if (!empty($proxy->proxy_login)) {
                    $userLogin=$proxy->proxy_login;
                    if (!empty($proxy->proxy_password)) {
                        $userLogin.=':'.$proxy->proxy_password;
                    }
                    $options[CURLOPT_PROXYUSERPWD]=$userLogin;
                }
                if (!empty($proxyStat->cookies)) {
                    $options[CURLOPT_COOKIE] = $proxyStat->cookies; 
                }
                $proxyStat->setOptions($options);
            }
            static::curl_multi_exec($proxyStats);
        
            $tran=\Yii::$app->db->beginTransaction();
        
            foreach ($proxyStats as $proxyStat) {
                if ($proxyStat->errorCode) {
                    $proxyStat->handleError($this->errorMessage);
                } elseif (!$proxyStat->isHttpOK()) {
                    $proxyStat->error_message = $proxyStat->content;
                } else {
                    $proxyStat->success_cnt++;
                    $proxyStat->error_cnt=0;
                    $proxyStat->error_message=null;
                    $proxyStat->setSpeedLast($proxyStat->info['total_time']);
                    $proxyStat->cookies = $proxyStat->getCookies();
                }                   
                $proxyStat->save(false);
            }
            $tran->commit();
        }
    }

}
