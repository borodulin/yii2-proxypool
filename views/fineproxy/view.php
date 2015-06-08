<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\SnFineproxy */

conquer\gii\GiiAsset::register($this);

$this->title = $model->fineproxy_id;
$this->params['breadcrumbs'][] = ['label' => 'Sn Fineproxies', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sn-fineproxy-view">

    <?php if(!\Yii::$app->request->isAjax): ?>
  
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('yii', 'Update') , ['update', 'id' => $model->fineproxy_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('yii', 'Delete'), ['delete', 'id' => $model->fineproxy_id], [
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
            'fineproxy_id',
            'fineproxy_login',
            'fineproxy_password',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

</div>
