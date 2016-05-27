<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Qlook */

$this->title = 'Create Qlook';
$this->params['breadcrumbs'][] = ['label' => 'Qlooks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qlook-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
