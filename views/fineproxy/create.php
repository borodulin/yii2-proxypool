<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\models\SnFineproxy */

conquer\gii\GiiAsset::register($this);

$this->title = Yii::t('yii', 'Create') . ' ' . 'Sn Fineproxy';
$this->params['breadcrumbs'][] = ['label' => 'Sn Fineproxies', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id'=>'pjax-sn-fineproxy-create']) ?>

<div class="sn-fineproxy-create">

    <?php if(!\Yii::$app->request->isAjax): ?>
    
    <h1><?= Html::encode($this->title) ?></h1>
    
    <?php endif ?>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php Pjax::end() ?>