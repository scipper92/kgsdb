<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\QlookSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Qlooks';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="qlook-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Qlook', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'url:url',
            'date',
            'type',
            // 'footprint_id',
            // 'angle',
            // 'cloud',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
