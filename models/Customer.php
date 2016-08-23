<?php
/**
 * Created by PhpStorm.
 * User: a.zhakypov
 * Date: 23.05.2016
 * Time: 12:58
 */

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\Qlook;
use app\models\Coordinates;

class Customer extends Model
{   public $startDate = '2014-07-15';
    public $endDate;
    public $top = 85, $bottom = -85, $left = -180, $right = 180;
    public $HR, $MR;

    public function rules(){
        return [
            /*['startDate','default','value'=> '2014-07-15'],*/
            ['endDate','default','value'=> function(){
                return date('Y-m-d');
            }],
            [['startDate','endDate'],'safe'],
            [['top','bottom'],'number','max'=>90,'min'=>-90],
            [['left','right'],'number','max'=>180,'min'=>-180],
            [['HR','MR'],'boolean','trueValue'=>true,'falseValue'=>false]
        ];
    }

    public function queryBase(){
        $qlook = [];
        if($this->HR && !$this->MR) {
            $coor = Coordinates::find()->innerJoinWith('qlook')
                ->where(['and',['between', 'qlook.date', $this->startDate, $this->endDate],['like','type','KazEOSat-1']])
                ->andWhere(['<=','SW_lat',$this->top])
                ->andWhere(['>=','NE_lat',$this->bottom])
                ->andWhere(['>=','NE_lng',$this->left])
                ->andWhere(['<=','SW_lng',$this->right])
                ->all();
            /**/
        } elseif($this->MR && !$this->HR) {
            $coor = Coordinates::find()->innerJoinWith('qlook')
                ->where(['and',['between', 'qlook.date', $this->startDate, $this->endDate],['like','type','KazEOSat-2']])
                ->andWhere(['<=','SW_lat',$this->top])
                ->andWhere(['>=','NE_lat',$this->bottom])
                ->andWhere(['>=','NE_lng',$this->left])
                ->andWhere(['<=','SW_lng',$this->right])
                ->all();
        } else {
            $coor = Coordinates::find()->innerJoinWith('qlook')
                ->where(['between', 'qlook.date', $this->startDate, $this->endDate])
                ->andWhere(['<=','SW_lat',$this->top])
                ->andWhere(['>=','NE_lat',$this->bottom])
                ->andWhere(['>=','NE_lng',$this->left])
                ->andWhere(['<=','SW_lng',$this->right])
                ->all();
        }
        //print_r($coor);
        foreach ($coor as $footprint) {
            $qlooks = $footprint->qlook;
            foreach($qlooks as $image){
                $qlook[] = ['cid'=>$image['name'],'date'=>$image['date'],'url'=>$image['url'],'Satellite'=>$image['type'],
                    'X'=>[$footprint['NW_lng'],$footprint['NE_lng'],$footprint['SE_lng'],$footprint['SW_lng']],
                    'Y'=>[$footprint['NW_lat'],$footprint['NE_lat'],$footprint['SE_lat'],$footprint['SW_lat']],
                    'cloud'=>$image['cloud'],'angle'=>$image['angle']];
            }
        }
        return $qlook;
    }

    public function formShape($qlooks){
        $fname = date_timestamp_get(date_create());
        $fname = '../tmp/'.$fname.'.kml';
        $fd = fopen($fname,'w');
        fwrite($fd,"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
        fwrite($fd,"<kml xmlns=\"http://www.opengis.net/kml/2.2\" xmlns:gx=\"http://www.google.com/kml/ext/2.2\" xmlns:kml=\"http://www.opengis.net/kml/2.2\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n");
        fwrite($fd,"<Document>\n\t");
        fwrite($fd,"<name>KGS_base</name>\n\t");
        foreach($qlooks as $qlook) {
            fwrite($fd, "<Placemark>\n\t\t<name>".$qlook['cid']."</name>\n\t\t");
            fwrite($fd,"<description>\n\t\t\t<![CDATA[\n\t\t\t\t");
            fwrite($fd,"<p><a href=\"".$qlook['url']."\">".$qlook['cid']."</a></p>\n\t\t\t");
			fwrite($fd,"<p>date: ".$qlook['date']. " </p>\n\t\t\t");
            fwrite($fd,"<p>cloudiness: ".$qlook['cloud']. " </p>\n\t\t\t");
            fwrite($fd, "<p>angle: ".$qlook['angle']. " </p>\n\t\t\t");
            fwrite($fd,"]]>\n\t\t</description>");
            fwrite($fd,"<Polygon>\n\t\t\t<outerBoundaryIs>\n\t\t\t\t<LinearRing>\n\t\t\t\t\t<coordinates>\n\t\t\t\t\t\t");
            foreach($qlook['X'] as $key=>$value){
                fwrite($fd,$value . "," . $qlook['Y'][$key] . ",0 ");
            }
            fwrite($fd, $qlook['X'][0] . "," . $qlook['Y'][0] . ",0 \n\t\t\t\t\t");
            fwrite($fd, "</coordinates>\n\t\t\t\t</LinearRing>\n\t\t\t</outerBoundaryIs>\n\t\t</Polygon>\n\t</Placemark>\n\t");
        }
        fwrite($fd, "</Document>\n</kml>\n");
        fclose($fd);
        return $fname;
    }

}