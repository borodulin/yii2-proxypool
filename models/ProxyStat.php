<?php
/**
 * @link https://github.com/borodulin/yii2-proxypool
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-proxypool/blob/master/LICENSE
 */

namespace conquer\proxypool\models;

use yii\db\ActiveQuery;
use conquer\helpers\Curl;
use yii\behaviors\TimestampBehavior;

/**
 * @property Domain $domain
 * @property Proxy $proxy
 * 
 * @author Andrey Borodulin
 *
 */
class ProxyStat extends \yii\db\ActiveRecord
{
	
	public static function tableName()
	{
		return '{{%proxy_stat}}';
	}
	
	public function getDomain()
	{
		return $this->hasOne(Domain::className(), ['domain_id'=>'domain_id']);
	}
	
	public function getProxy()
	{
		return $this->hasOne(Proxy::className(), ['proxy_id'=>'proxy_id']);
	}

	
	public function behaviors()
	{
		return [
			[
				'class' => TimestampBehavior::className(),
			],
		];
	}
		
	/**
	 *
	 * @param double $value
	 */
	public function setSpeedLast($value)
	{
		$this->speed_last=$value;
		$this->speed_avg=($this->speed_avg*$this->request_cnt+$value)/(1+$this->request_cnt);
		$this->speed_savg=sqrt((pow($this->speed_savg,2)*$this->request_cnt+pow($value,2))/(1+$this->request_cnt));
	}
	/**
	 * 
	 * @param string $errorMessage
	 */
	public function handleError($errorMessage)
	{
	    $this->error_cnt++;
	    $this->error_message=$errorMessage;
	}
	
	public static function checkProxies()
	{
		Domain::initProxies();
	
		$time = time()-6*60*60;
		
		/* @var $proxyStats ProxyStat[] */
		$proxyStats=ProxyStat::find()
			->where(['>', 'updated_at', $time])
			->andWhere([ '<', 'error_cnt', 20])
			->innerJoinWith(['proxy','domain'])
            ->andWhere(['domain.check_url is not null'])
			->indexBy('stat_id')
            ->limit(500)
            ->orderBy('RAND()')
            ->all();
		
		if(count($proxyStats)>0)
		{
		    $urls = [];
		    
		    foreach ($proxyStats as $key => $proxyStat){
		        $proxy = $proxyStat->proxy;
		        $options = [
	                CURLOPT_PROXY => $proxyStat->proxy->proxy_address,
	                CURLOPT_PROXYPORT => $proxyStat->proxy->proxy_port,
		        ];
		        if(!empty($proxy->proxy_login)){
		            $userLogin=$proxy->proxy_login;
		            if(!empty($proxy->proxy_password))
		                $userLogin.=':'.$proxy->proxy_password;
		            $options[CURLOPT_PROXYUSERPWD]=$userLogin;
		        }
		        $urls[$key] = new Curl($proxyStat->domain->check_url, $options);
		    }
		    
		    Curl::multiExec($urls);
		    
			$tran=\Yii::$app->db->beginTransaction();
			
			foreach ($urls as $key => $url){
			    $proxyStat = $proxyStats[$key];
			    if($url->isHttpOK()){
			        if(isset($proxyStat->domain->check_content)&& (!preg_match($proxyStat->domain->check_content, $url->content))) {
		                $proxyStat->handleError('Invalid content');
			        } else {
    			        $proxyStat->success_cnt++;
    			        $proxyStat->error_cnt=0;
    			        $proxyStat->error_message=null;
    			        $proxyStat->setSpeedLast($info['total_time']);
			        }
			    } else {
			        $proxyStat->handleError($url->errorMessage);
			    }
			    $proxyStat->save();
			}
			
			$tran->commit();
		}
	}

	
	
}
