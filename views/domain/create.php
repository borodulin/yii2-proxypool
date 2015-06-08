<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\models\SnDomain */

conquer\gii\GiiAsset::register($this);

$this->title = Yii::t('yii', 'Create') . ' ' . 'Domain';
$this->params['breadcrumbs'][] = ['label' => 'Domains', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id'=>'pjax-domain-create']) ?>

<div class="domain-create">

    <?php if(!\Yii::$app->request->isAjax): ?>
    
    <h1><?= Html::encode($this->title) ?></h1>
    
    <?php endif ?>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php Pjax::end() ?>