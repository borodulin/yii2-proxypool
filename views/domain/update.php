<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\models\SnDomain */

conquer\gii\GiiAsset::register($this);

$this->title = Yii::t('yii', 'Update') . ' ' . 'Domain' . ' ' . $model->domain_id;
$this->params['breadcrumbs'][] = ['label' => 'Domains', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->domain_id, 'url' => ['view', 'id' => $model->domain_id]];
$this->params['breadcrumbs'][] = Yii::t('yii', 'Update');
?>

<?php Pjax::begin(['id'=>'pjax-domain-update']) ?>

<div class="sn-domain-update">

    <?php if(!\Yii::$app->request->isAjax): ?>
    
    <h1><?= Html::encode($this->title) ?></h1>
    
    <?php endif ?>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php Pjax::end() ?>