<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SnDomain */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="domain-form">

    <?php $form = ActiveForm::begin(['enableClientValidation'=>false, 'options' => ['data-pjax' => true]]) ?>

    <?= $form->field($model, 'domain_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'check_url')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'check_content')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end() ?>

</div>
