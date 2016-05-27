<?php

namespace app\controllers;

use app\models\Admin;
//use app\models\Qlook;

class AdminController extends \yii\web\Controller
{
    public function actionIndex()
    {   $model = new Admin;
        if( $model->load(\Yii::$app->request->post()) && $model->validate()){
            $model->callCof();
            //$model->addRecords();
        }
        return $this->render('index',['model' => $model]);
    }

}
