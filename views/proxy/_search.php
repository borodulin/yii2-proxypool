<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SnProxySearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="sn-proxy-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'proxy_id') ?>

    <?= $form->field($model, 'proxy_address') ?>

    <?= $form->field($model, 'proxy_port') ?>

    <?= $form->field($model, 'proxy_login') ?>

    <?= $form->field($model, 'proxy_password') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'fineproxy_id') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
