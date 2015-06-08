<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\SnProxy */

conquer\gii\GiiAsset::register($this);

$this->title = $model->proxy_id;
$this->params['breadcrumbs'][] = ['label' => 'Sn Proxies', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sn-proxy-view">

    <?php if(!\Yii::$app->request->isAjax): ?>
  
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('yii', 'Update') , ['update', 'id' => $model->proxy_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('yii', 'Delete'), ['delete', 'id' => $model->proxy_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?php endif ?>
    
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'proxy_id',
            'proxy_address',
            'proxy_port',
            'proxy_login',
            'proxy_password',
            'created_at:datetime',
            'updated_at:datetime',
            'fineproxy.fineproxy_login:html:Fineproxy',
        ],
    ]) ?>

</div>
