<?php
/**
 * @link https://github.com/borodulin/yii2-proxypool
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-proxypool/blob/master/LICENSE
 */

namespace conquer\proxypool\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\base\Exception;
use yii\helpers\VarDumper;
/**
 * 
 * @property string $domain_name
 * @property string $check_url
 * @property string $check_content
 * @property integer $created_at
 * @property integer $updated_at
 * 
 * @property Connection[] $connections
 * @property Connection[] $goodConnections
 * 
 * @author Andrey Borodulin
 */
class Domain extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        if (!$proxyPool = Yii::$app->get('proxyPool', false)) {
            Yii::$app->set('proxyPool', $proxyPool = new ProxyPool());
        }
        return $proxyPool->domainTable;
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
            TimestampBehavior::className(),
        ];
    }

    /**
     * 
     * @param string $url
     * @return Domain
     */
    public static function getByUrl($url)
    {
        $host = parse_url($url,PHP_URL_HOST);
        if (!$domain = Domain::findOne(['domain_name' => $host])) {
            $domain = new Domain(['domain_name' => $host]);
            if (!$domain->save()) {
                throw new Exception(VarDumper::dumpAsString($domain->errors));
            }
        }
        return $domain;
    }
    
    public static function initProxies()
    {
        $connectionTable = Connection::tableName();
        $proxyTable = Proxy::tableName();
        $domainTable = Domain::tableName();
        $sql=<<<SQL
INSERT INTO $connectionTable(proxy_id, domain_id, created_at, updated_at)
  SELECT sp.proxy_id, sd.domain_id, :created_at, :updated_at
  FROM $domainTable sd
    JOIN $proxyTable sp ON 1=1
    LEFT JOIN $connectionTable sps ON sd.domain_id = sps.domain_id AND sp.proxy_id = sps.proxy_id
  WHERE sps.connection_id IS NULL
SQL;
        Yii::$app->db->createCommand($sql)
            ->bindValues(['created_at' => time(), 'updated_at' => 0])
            ->execute();
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConnections()
    {
        return $this->hasMany(Connection::className(), ['domain_id' => 'domain_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGoodConnections()
    {
        return $this->getConnections()
            ->where(['error_cnt' => 0])
            ->andWhere(['>','success_cnt',0])
            ->indexBy('connection_id')
            ->orderBy(['success_cnt' => SORT_DESC]);
    }
}

