<?php
/**
 * @link https://github.com/borodulin/yii2-proxypool
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-proxypool/blob/master/LICENSE
 */

namespace conquer\proxypool\models;

use yii\db\Expression;
use yii\behaviors\TimestampBehavior;

/**
 * @author Andrey Borodulin
 */
class Domain extends \yii\db\ActiveRecord
{

    public static function tableName()
    {
        return '{{%domain}}';
    }
    /**
     * @deprecated
     */
    public function relations()
    {
        return array(
            'validStatCount'=>array(self::STAT, 'SrvProxyStat', 'domain_id', 'condition'=>'error_cnt=0 and m_time>now()-interval 1 DAY'),
            'validFreeCount'=>array(self::STAT, 'SrvProxyStat', 'domain_id',
                    'join'=>"LEFT JOIN gg_request gr ON t.stat_id = gr.stat_id AND gr.status IN ('NEW', 'PROCESS')", 
                    'condition'=>'gr.request_id IS NULL AND t.error_cnt=0 AND t.m_time>NOW() - INTERVAL 4 HOUR'),                
        );
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
     * @param string $url
     * @return SrvDomain
     */
    public static function getByUrl($url)
    {
        $host = parse_url($url,PHP_URL_HOST);
        $domain = Domain::findOne(['domain_name'=>$host]);
        if(empty($domain)) {
            $domain = new Domain();
            $domain->domain_name = $host;
            $domain->save();
        }
        return $domain;
    }
    
    public static function initProxies()
    {
        $sql=<<<SQL
INSERT INTO {{%proxy_stat}}(proxy_id, domain_id, created_at, updated_at)
  SELECT sp.proxy_id, sd.domain_id, :created_at, :updated_at
  FROM {{%domain}} sd
    JOIN {{%proxy}} sp
    LEFT JOIN {{%proxy_stat}} sps ON sd.domain_id = sps.domain_id AND sp.proxy_id = sps.proxy_id
  WHERE sps.stat_id IS NULL
SQL;
        Yii::$app->db->createCommand($sql)
            ->bindValues(['created_at'=>time(),'updated_at'=>time()-1000])
            ->execute();
    }
    
    /**
     * 
     * @return \conquer\proxypool\models\ProxyStat
     */
    public function getProxyStat()
    {
        return ProxyStat::find()
            ->where(['error_cnt'=>0])
            ->andWhere(['>','success_cnt',0])
            ->andWhere(['domain_id'=>$this->domain_id])
            ->orderBy('RAND()/ LN(request_cnt)*success_cnt/request_cnt DESC');
    }
    
}

