<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use app\models\Customer;

/**
 * This command create *.kml and send it
 *
 */
class ShapeFormController extends Controller
{
    /**
     * This command create *.kml and send it
     * @param string $message the message to be echoed.
     */
    public function actionIndex(array $argv/*$uid,$HR,$MR,$startDate,$endDate,$top,$bottom,$left,$right*/)
    {
        $opt = (object) array(
            'uid' => $argv[0],//$uid,
            'HR' => $argv[1],//$HR,
            'MR' => $argv[2],//$MR,
            'startDate' => $argv[3],//$startDate,
            'endDate' => $argv[4],//$endDate,
            'top' => $argv[5],//$top,
            'bottom' => $argv[6],//$bottom,
            'left' => $argv[7],//$left,
            'right' => $argv[8]//$right
        );
        $model = new Customer();
        $fname = $model->formShape($model->queryBaseExt($opt));
        $chop_len  = 1024*256;
        $len = filesize($fname);
        $fd = fopen($fname,'r');
        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream" );
        header('Content-Disposition: attachment; filename="KGS_base.kml"');
        header('Content-length: ' . $len);
        header('Accept-Ranges: bytes');
        while(!feof($fd)){
            echo fread($fd,$chop_len);
            //ob_flush();
            flush();
        }
        fclose($fd);
        unlink($fname);
        return 0;
    }
}
