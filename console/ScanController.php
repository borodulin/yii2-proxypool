<?php
/**
 * @link https://github.com/borodulin/yii2-proxypool
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-proxypool/blob/master/LICENSE
 */

namespace conquer\proxypool\console;

use conquer\proxypool\models\Proxy;
use conquer\helpers\Curl;
use conquer\helpers\XPath;

/**
 * 
 * @author Andrey Borodulin
 *
 */
class ScanController extends \yii\console\Controller
{
    public function actionIndex()
    {
        echo "Proxy controller usage";
    }
    
    /**
     * 
     */
    public function actionFineproxy($login, $password)
    {
        $url = 'http://account.fineproxy.org/api/getproxy/?'.http_build_query([
                'format' => 'csv',
                'type' => 'httpauth',
                'login' => $login,
                'password' => $password,
        ]);
        $curl = new Curl($url);
        if ($curl->execute()) {
            if (preg_match_all('/(.*?):(\d+)/m', $curl->content, $matches, PREG_SET_ORDER)) {
                $tran = Yii::$app->db->beginTransaction();
                foreach ($matches as $match) {
                    if (Proxy::addProxy($match[1], $match[2], 'HTTP', $login, $password)->isNewRecord) {
                        echo "added new fineproxy: {$match[1]}:{$match[2]}\n";
                    }
                }
                $tran->commit();
            }
        }
    }
    
    /**
     * 
     */
    public function actionFoxtools()
    {
        for ($i = 1; $i < 41; $i++) {
            $curl = new Curl("http://foxtools.ru/Proxy?page={$i}");
            if ($curl->execute()) {
                $xpath = new XPath($curl->content, true);
                $elements = $xpath->query('//*[@id="theProxyList"]/tbody/tr', null, false);
                if (!empty($elements)) {
                    $tran = Yii::$app->db->beginTransaction();
                    foreach ($elements as $element) {
                        $query = $xpath->queryAll([
                            'address' => './td[2]',
                            'port' => './td[3]',
                        ], $element);
                        if (Proxy::addProxy($query['address'], $query['port'])->isNewRecord) {
                            echo "added new free proxy {$query['address']}:{$query['port']}\n";
                        }
                    }
                    $tran->commit();
                }
            }
        }
    }
    
    public function actionTubeincreaser()
    {
        $curl = new Curl('http://www.tubeincreaser.com/proxylist.txt');
        if ($curl->execute()) {
            $rows = [];
            $lines = explode("\n", $curl->content);
            $tran = Yii::$app->db->beginTransaction();
            foreach ($lines as $line) {
                if (preg_match('/(\d+\.\d+\.\d+\.\d+):(\d+)/', $line, $matches)) {
                    if (Proxy::addProxy($matches[1], $matches[2])->isNewRecord) {
                        echo "added new free proxy {$matches[1]}:{$matches[2]}\n";
                    }
                }
            }
            $tran->commit();
        }
    }
}