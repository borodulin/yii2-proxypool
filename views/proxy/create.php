<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\models\SnProxy */

conquer\gii\GiiAsset::register($this);

$this->title = Yii::t('yii', 'Create') . ' ' . 'Sn Proxy';
$this->params['breadcrumbs'][] = ['label' => 'Sn Proxies', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id'=>'pjax-sn-proxy-create']) ?>

<div class="sn-proxy-create">

    <?php if(!\Yii::$app->request->isAjax): ?>
    
    <h1><?= Html::encode($this->title) ?></h1>
    
    <?php endif ?>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php Pjax::end() ?>