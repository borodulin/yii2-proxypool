<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel app\models\SnFineproxySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

conquer\gii\GiiAsset::register($this);

$this->title = 'Fineproxies';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="fineproxy-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
    <?= Html::button('Create Fineproxy', [
        'class' => 'btn btn-success show-modal',
        'value' => Url::to(['create']),
        'data-target' => '#modal_view',
        'data-header' => 'Create Fineproxy',
    ]); ?>
    </p>

    <?= Modal::widget([
        'id' => 'modal_view',
    ]); ?>

    <?php Pjax::begin(['id'=>'pjax-fineproxy-index']);?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'fineproxy_id',
            'fineproxy_login',
            'fineproxy_password',
            'created_at:datetime',
            'updated_at:datetime',

            [
                'class' => 'yii\grid\ActionColumn',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        $options = array_merge([
                            'title' => Yii::t('yii', 'View'),
                            'aria-label' => Yii::t('yii', 'View'),
                            'data-pjax' => '0',
                            'class'=>'show-modal',
                            'value' => $url,
                            'data-target' => '#modal_view', 
                            'data-header' => Yii::t('yii', 'View') . ' ' . 'Fineproxies',
                        ]);
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', 'javascript:;', $options);
                    },
                    'update' => function ($url, $model, $key) {
                        $options = array_merge([
                            'title' => Yii::t('yii', 'Update'),
                            'aria-label' => Yii::t('yii', 'Update'),
                            'data-pjax' => '0',
                            'class'=>'show-modal',
                            'value' => $url, 
                            'data-target' => '#modal_view', 
                            'data-header' => Yii::t('yii', 'Update') . ' ' . 'Fineproxies',
                        ]);
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', 'javascript:;', $options);
                    },
                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end();?>
    
</div>
