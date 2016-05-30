<?php

namespace app\controllers;

use app\models\Customer;
use yii\filters\VerbFilter;
use yii\helpers\Url;

class CustomerController extends \yii\web\Controller
{   //public $model;
    public $enableCsrfValidation = false;

    public function behaviors(){
        return [
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
                'cors' => [],
                'actions' => [
                    'reply' => [
                        'Origin' => ['*'],
                        'Access-Control-Request-Method' => ['GET', 'POST'],
                        'Access-Control-Request-Headers' => ['*'],
                        'Access-Control-Allow-Credentials' => null,
                        'Access-Control-Max-Age' => 86400,
                        'Access-Control-Expose-Headers' => [],
                    ],
                ],
            ],

        ];
    }

    public function beforeAction($action)
    {
        if ($action->id == 'reply') {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $model = new Customer();
        if( $model->load(\Yii::$app->request->post()) && $model->validate()){
        //    $model->queryBase();
            $fname = $model->formShape($model->queryBase());
            $chop_len  = 1024*256;
            $len = filesize($fname);
            $fd = fopen($fname,'r');/*
            header("Pragma: public");
            header("Expires: -1");
            header("Cache-Control: public, must-revalidate, post-check=0, pre-check=0");*/
            header("Content-Description: File Transfer");
            header("Content-Type: application/octet-stream" );
            header('Content-Disposition: attachment; filename="KGS_base.kml"');
        /*    if($len>$chop_len) {
                header('HTTP/1.1 206 Partial Content');
                header('Content-Range: bytes 0-' . ($chop_len - 1) . '/' . $len);
                header('Content-length: '.$chop_len);
            } else {*/
            header('Content-length: ' . $len);
          //  }
            header('Accept-Ranges: bytes');
            while(!feof($fd)){
                echo fread($fd,$chop_len);
                ob_flush();
                //flush();
            }
            fclose($fd);
            unlink($fname);/**/
            return $this->refresh();
        }
        return $this->render('index',['model'=>$model]);
    }

    public function actionReply(){
        $model = new Customer();
        $model->setAttributes(\Yii::$app->request->post());
        if($model->validate()) {
            print_r($model->queryBase());
        }
    }


}
