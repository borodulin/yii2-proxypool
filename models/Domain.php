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

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%domain}}';
    }
    
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['proxy_address', 'proxy_port', 'created_at', 'updated_at'], 'required'],
            [['proxy_port', 'created_at', 'updated_at', 'fineproxy_id'], 'integer'],
            [['proxy_address', 'proxy_login', 'proxy_password'], 'string', 'max' => 100],
            [['proxy_address', 'proxy_port'], 'unique'],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
            ],
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

