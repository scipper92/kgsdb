<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Admin */
/* @var $form ActiveForm */

?>
<div class="admin-index">

    <?php $form = ActiveForm::begin(); ?>
        <?= $form->field($model,'HR')->checkbox()?>
        <?= $form->field($model,'MR')->checkbox()?>

        <br>
        <?= $form->field($model,'startDate')->widget(\yii\jui\DatePicker::className(),[
            'dateFormat' => 'yyyy-MM-dd'
        ]) ?>

        <?= $form->field($model,'endDate')->widget(\yii\jui\DatePicker::className(),[
            'dateFormat' => 'yyyy-MM-dd'
        ]) ?>

    <br>
    <table>
        <caption>Coordinates</caption>
        <tr>
            <td></td>
            <th>Latitude</th>
            <th>Longitude</th>
        </tr>
        <tr>
            <th> NE </th>
            <td> <?= $form->field($model,'top')->input('number',['min'=>-90, 'max'=>90])->label(false) ?> </td>
            <td> <?= $form->field($model,'right')->input('number',['min'=>-180, 'max'=>180])->label(false) ?>
        </tr>
        <tr>
            <th> SW </th>
            <td> <?= $form->field($model,'bottom')->input('number',['min'=>-90, 'max'=>90])->label(false) ?></td>
            <td> <?= $form->field($model,'left')->input('number',['min'=>-180, 'max'=>180])->label(false) ?></td>
        </tr>
    </table>
    <br>

        <div class="form-group">
            <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
        </div>
    <?php ActiveForm::end(); ?>

</div><!-- admin-index -->
