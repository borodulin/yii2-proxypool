<?php
/**
 * @link https://github.com/borodulin/yii2-proxypool
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-proxypool/blob/master/LICENSE
 */

namespace conquer\proxypool\models;

use yii\db\Expression;

/**
 * @author Andrey Borodulin
 */
class Domain extends \yii\db\ActiveRecord
{

    private $_proxyStats;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%pool_domain}}';
    }
    
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['domain_name'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['domain_name', 'check_url', 'check_content'], 'string', 'max' => 255],
            [['check_url'], 'url'],
            [['domain_name'], 'unique']
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

    public function getValidCount()
    {
        return static::find()
            ->where(['error_cnt'=>0])
            ->andWhere(['domain_id'=>$this->domain_id])
            ->andWhere(['>', 'updated_at', time()-24*3600])
            ->count();
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
INSERT INTO {{%pool_proxy_stat}}(proxy_id, domain_id, created_at, updated_at)
  SELECT sp.proxy_id, sd.domain_id, :created_at, :updated_at
  FROM {{%pool_domain}} sd
    JOIN {{%pool_proxy}} sp
    LEFT JOIN {{%pool_proxy_stat}} sps ON sd.domain_id = sps.domain_id AND sp.proxy_id = sps.proxy_id
  WHERE sps.stat_id IS NULL
SQL;
        \Yii::$app->db->createCommand($sql)
            ->bindValues(['created_at'=>time(),'updated_at'=>time()-1000])
            ->execute();
    }
    
    /**
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getValidProxies()
    {
        /* @var $query \yii\db\ActiveQuery */
        $query = ProxyStat::find()
            ->where(['error_cnt'=>0])
            ->andWhere(['>','success_cnt',0])
            ->indexBy('stat_id')
            ->orderBy(['RAND() / LN(request_cnt)*success_cnt/request_cnt DESC'=>SORT_ASC]);
        $query->link = ['domain_id'=>'domain_id'];
        $query->primaryModel = $this;
        $query->multiple = true;
        return $query;
    }

    
    
    
    public function nextProxyStat()
    {
        // $this->validProxies
    }
    
}

