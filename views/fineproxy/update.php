<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\models\SnFineproxy */

conquer\gii\GiiAsset::register($this);

$this->title = Yii::t('yii', 'Update') . ' ' . 'Sn Fineproxy' . ' ' . $model->fineproxy_id;
$this->params['breadcrumbs'][] = ['label' => 'Sn Fineproxies', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->fineproxy_id, 'url' => ['view', 'id' => $model->fineproxy_id]];
$this->params['breadcrumbs'][] = Yii::t('yii', 'Update');
?>

<?php Pjax::begin(['id'=>'pjax-sn-fineproxy-update']) ?>

<div class="sn-fineproxy-update">

    <?php if(!\Yii::$app->request->isAjax): ?>
    
    <h1><?= Html::encode($this->title) ?></h1>
    
    <?php endif ?>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php Pjax::end() ?>