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
            $text = $model->formShape($model->queryBase());
            header("Content-Description: File Transfer");
            header("Content-Type: application/octet-stream" );
            header('Content-Disposition: attachment; filename="KGS_base.kml"');
            header('Content-length: '.strlen($text));
            echo $text;
            ob_flush();
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
