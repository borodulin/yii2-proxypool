<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SnProxy */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="sn-proxy-form">

    <?php $form = ActiveForm::begin(['enableClientValidation'=>false, 'options' => ['data-pjax' => true]]) ?>

    <?= $form->field($model, 'proxy_address')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'proxy_port')->textInput() ?>

    <?= $form->field($model, 'proxy_login')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'proxy_password')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end() ?>

</div>
