<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SnFineproxy */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="sn-fineproxy-form">

    <?php $form = ActiveForm::begin(['enableClientValidation'=>false, 'options' => ['data-pjax' => true]]) ?>

    <?= $form->field($model, 'fineproxy_login')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fineproxy_password')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end() ?>

</div>
