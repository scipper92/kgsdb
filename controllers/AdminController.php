<?php

namespace app\controllers;

use app\models\Admin;
use app\models\Qlook;

class AdminController extends \yii\web\Controller
{
    public function actionIndex()
    {   $model = new Admin;
        if( $model->load(\Yii::$app->request->post()) && $model->validate()){
            $model->callCof();
            $model->updateAngle();
            //$model->addRecords();
        }
        return $this->render('index',['model' => $model]);
    }

    public function actionUpdateUrl()
    {   $transaction = Qlook::getDb()->beginTransaction();
        try{
            foreach (Qlook::find()->batch() as $qlooks) {
                foreach ($qlooks as $qlook) {
                    $url = $qlook['url'];
                    if(!strstr($url,"?key=")) {
                        $url = chop($url, "\"");
                        $qlook['url'] = $url . "?key=" . md5($url . "p0sUe");
                        $qlook->update(false);
                    }
                }
            }
            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        $model = new Admin;
        return $this->render('index',['model' => $model]);
    }

    public function actionUpdateAngle(){
        $model = new Admin;
        if( $model->load(\Yii::$app->request->post()) && $model->validate()){
            $model->updateAngle();
            //$model->addRecords();
        }
        return $this->render('index',['model' => $model]);
    }

}
