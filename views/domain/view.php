<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\SnDomain */

conquer\gii\GiiAsset::register($this);

$this->title = $model->domain_id;
$this->params['breadcrumbs'][] = ['label' => 'Domains', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sn-domain-view">

    <?php if(!\Yii::$app->request->isAjax): ?>
  
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('yii', 'Update') , ['update', 'id' => $model->domain_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('yii', 'Delete'), ['delete', 'id' => $model->domain_id], [
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
            'domain_id',
            'domain_name',
            'created_at:datetime',
            'updated_at:datetime',
            'check_url:url',
            'check_content',
        ],
    ]) ?>

</div>
