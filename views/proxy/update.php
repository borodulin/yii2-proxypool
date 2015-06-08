<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\models\SnProxy */

conquer\gii\GiiAsset::register($this);

$this->title = Yii::t('yii', 'Update') . ' ' . 'Proxy' . ' ' . $model->proxy_id;
$this->params['breadcrumbs'][] = ['label' => 'Proxies', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->proxy_id, 'url' => ['view', 'id' => $model->proxy_id]];
$this->params['breadcrumbs'][] = Yii::t('yii', 'Update');
?>

<?php Pjax::begin(['id'=>'pjax-sn-proxy-update']) ?>

<div class="sn-proxy-update">

    <?php if(!\Yii::$app->request->isAjax): ?>
    
    <h1><?= Html::encode($this->title) ?></h1>
    
    <?php endif ?>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php Pjax::end() ?>