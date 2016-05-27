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
    public $top = 70, $bottom = -70, $left = -180, $right = 180;
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
        $coor = Coordinates::find()->where(['or',['<=','SW_lat',$this->top],['<=','SE_lat',$this->top]]);
        $coor->andWhere(['or',['>=','NE_lat',$this->bottom],['>=','NW_lat',$this->bottom]]);
        $coor->andWhere(['or',['>=','NE_lng',$this->left],['>=','SE_lng',$this->left]]);
        $coor->andWhere(['or',['<=','NW_lng',$this->right],['>=','SW_lng',$this->right]]);
        $coor = $coor->all();
        $qlook = [];
        if($this->HR && !$this->MR) {
            foreach ($coor as $footprint) {
                $qlookQuery = $footprint->getQlook()->where(['between', 'date', $this->startDate, $this->endDate]);
                $qlooks = $qlookQuery->andWhere(['like','type','KazEOSat-1'])->all();
                foreach($qlooks as $image){
                    $qlook[] = ['cid'=>$image['name'],'date'=>$image['date'],'url'=>$image['url'],'Satellite'=>'KazEOSat-1',
                                'X'=>[$footprint['NW_lng'],$footprint['NE_lng'],$footprint['SE_lng'],$footprint['SW_lng']],
                                'Y'=>[$footprint['NW_lat'],$footprint['NE_lat'],$footprint['SE_lat'],$footprint['SW_lat']],
                                'cloud'=>$image['cloud'],'angle'=>$image['angle']];
                }
            }
        } elseif($this->MR && !$this->HR) {
            foreach ($coor as $footprint) {
                $qlookQuery = $footprint->getQlook()->where(['between', 'date', $this->startDate, $this->endDate]);
                $qlooks = $qlookQuery->andWhere(['like','type','KazEOSat-2'])->all();
                foreach($qlooks as $image){
                    $qlook[] = ['cid'=>$image['name'],'date'=>$image['date'],'url'=>$image['url'],'Satellite'=>'KazEOSat-2',
                        'X'=>[$footprint['NW_lng'],$footprint['NE_lng'],$footprint['SE_lng'],$footprint['SW_lng']],
                        'Y'=>[$footprint['NW_lat'],$footprint['NE_lat'],$footprint['SE_lat'],$footprint['SW_lat']],
                        'cloud'=>$image['cloud'],'angle'=>$image['angle']];
                }
            }
        } elseif($this->MR && $this->HR) {
            foreach ($coor as $footprint) {
                $qlooks = $footprint->getQlook()->where(['between', 'date', $this->startDate, $this->endDate])->all();
                foreach($qlooks as $image){
                    $qlook[] = ['cid'=>$image['name'],'date'=>$image['date'],'url'=>$image['url'],'Satellite'=>$image['type'],
                        'X'=>[$footprint['NW_lng'],$footprint['NE_lng'],$footprint['SE_lng'],$footprint['SW_lng']],
                        'Y'=>[$footprint['NW_lat'],$footprint['NE_lat'],$footprint['SE_lat'],$footprint['SW_lat']],
                        'cloud'=>$image['cloud'],'angle'=>$image['angle']];
                }
            }
        }
        //
        return $qlook;
    }

    public function formShape($qlooks){
        $text = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $text = $text."<kml xmlns=\"http://www.opengis.net/kml/2.2\" xmlns:gx=\"http://www.google.com/kml/ext/2.2\" xmlns:kml=\"http://www.opengis.net/kml/2.2\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
        $text = $text."<Document>\n\t";
        $text = $text."<name>KGS_base</name>\n\t";
       /* $text = $text."<Style>\n\t\t";
        $text = $text."<LineStyle>\n\t\t\t<color>ffff6600</color>\n\t\t\t<width>2</width>\n\t\t</LineStyle>\n\t\t";
        $text = $text."<PolyStyle>\n\t\t\t<color>00000000</color>\n\t\t</PolyStyle>\n\t</Style>\n\t";*/
        foreach($qlooks as $qlook) {
            $text = $text . "<Placemark>\n\t\t<name>".$qlook['cid']."</name>\n\t\t";
            $text = $text . "<description>\n\t\t\t<![CDATA[\n\t\t\t\t";
            $text = $text . "<p><a href=\"".$qlook['url']."\">".$qlook['cid']."</a></p>\n\t\t\t";
            $text = $text . "<p>cloudiness: ".$qlook['cloud']. " </p>\n\t\t\t";
            $text = $text . "<p>angle: ".$qlook['angle']. " </p>\n\t\t\t";
            $text = $text . "]]>\n\t\t</description>";
            $text = $text . "<Polygon>\n\t\t\t<outerBoundaryIs>\n\t\t\t\t<LinearRing>\n\t\t\t\t\t<coordinates>\n\t\t\t\t\t\t";
            foreach($qlook['X'] as $key=>$value){
                $text = $text . $value . "," . $qlook['Y'][$key] . ",0 ";
            }
            $text = $text . $qlook['X'][0] . "," . $qlook['Y'][0] . ",0 \n\t\t\t\t\t";
            $text = $text . "</coordinates>\n\t\t\t\t</LinearRing>\n\t\t\t</outerBoundaryIs>\n\t\t</Polygon>\n\t</Placemark>\n";
        }
        $text = $text . "</Document>\n</kml>\n";
        return $text;
    }

}