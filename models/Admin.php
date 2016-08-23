<?php
/**
 * Created by PhpStorm.
 * User: a.zhakypov
 * Date: 15.04.2016
 * Time: 12:41
 */

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\Qlook;
use app\models\Coordinates;

class Admin extends Model
{
    public $startDate = '2014-07-15';
    public $endDate;
    public $top = 85, $bottom = -85, $left = -180, $right = 180;
    public $HR = true, $MR = false;
    /*public $images;
    public $footprints;*/

    public function rules(){
        return [
            /*['startDate','default','value'=> '2014-07-15'],*/
            ['endDate','default','value'=> function(){
                return date('Y-m-d');
            }],
            [['startDate','endDate'],'safe'],
            [['top','bottom'],'number','max'=>90,'min'=>-90],
            [['left','right'],'number','max'=>180,'min'=>-180]
        ];
    }

    public function dateToCode($date,$edge){
        $a = 1350000;
        if($edge == 'A')
            $b = 1012500;
        else
            $b = 2362499;
        $zero = date_create("1970-01-01");
        $y = date_diff($zero,$date);
        $y = $y->format("%a")-1;
        $x = $a * $y + $b;
        $mask = 64**5;
        $code ='';
        for($i=0;$i<6;$i++){
            $k = floor($x/$mask);
            if($k<26)
                $c = chr(ord('A') + $k);
            elseif ($k<52)
                $c = chr(ord('a') + $k - 26);
            elseif ($k<62)
                $c = $c = chr(ord('0') + $k - 52);
            elseif ($k == 62)
                $c = '$';
            else
                $c = '_';
            $code = $code.$c;
            $x = fmod($x,$mask);
            $mask /= 64;
        }
        $code = $code.$edge;
        return $code;
    }

    public function callCof(){
        $date = date_create_from_format("Y-m-d",$this->startDate);
        $startCode = $this->dateToCode($date,'A');
        $date = date_create_from_format("Y-m-d",$this->endDate);
        $endCode = $this->dateToCode($date,'_');
        //echo "start=".$startCode."\n end=".$endCode."\n";
        $response = $this->curlCof($startCode,$endCode);
        $this->parseCof($response);
    }

    private function curlCof($startCode,$endCode){
        $ch = curl_init();
        $abc = "7|0|14|http://cof2.gharysh.kz/customer-office/net.eads.astrium.faceo.HomePage/|F5D7A83DB22C52A50C21C05DB8965B9A|net.eads.astrium.faceo.middleware.gwt.client.ICatalogueGWTService|queryCatalogueSetRecords|net.eads.astrium.faceo.core.apis.catalogue.CatalogueSetRecordQuery/112575587|net.eads.astrium.faceo.core.apis.common.request.Criteria/4096422861|net.eads.astrium.faceo.core.apis.catalogue.CatalogueRecordQuery/3099495460|java.util.ArrayList/4159755760|net.eads.astrium.faceo.common.data.geographical.Box/1707532656|net.eads.astrium.faceo.common.data.geographical.GeoPosition/3149863295|EPSG:4326|net.eads.astrium.faceo.common.data.temporal.Period/2004917229|java.util.Date/3385151746|java.lang.Integer/3438268394|1|2|3|4|2|5|6|5|7|8|1|9|10|0|".$this->bottom."|".$this->left."|10|0|".$this->top."|".$this->right."|0|11|1|8|0|1000|8|1|12|13|".$endCode."|13|".$startCode."|8|1|14|0|0|0|6|0|0|0|";
        //$abc = "7|0|14|http://cof1.gharysh.kz/customer-office/net.eads.astrium.faceo.HomePage/|F5D7A83DB22C52A50C21C05DB8965B9A|net.eads.astrium.faceo.middleware.gwt.client.ICatalogueGWTService|queryCatalogueSetRecords|net.eads.astrium.faceo.core.apis.catalogue.CatalogueSetRecordQuery/112575587|net.eads.astrium.faceo.core.apis.common.request.Criteria/4096422861|net.eads.astrium.faceo.core.apis.catalogue.CatalogueRecordQuery/3099495460|java.util.ArrayList/4159755760|net.eads.astrium.faceo.common.data.geographical.Box/1707532656|net.eads.astrium.faceo.common.data.geographical.GeoPosition/3149863295|EPSG:4326|net.eads.astrium.faceo.common.data.temporal.Period/2004917229|java.util.Date/3385151746|java.lang.Integer/3438268394|1|2|3|4|2|5|6|5|7|8|1|9|10|0|".$this->top."|".$this->right."|10|0|".$this->bottom."|".$this->left."|0|11|1|8|0|1000|8|1|12|13|".$endCode."|13|".$startCode."|8|1|14|0|0|0|6|0|0|0|";
        curl_setopt($ch, CURLOPT_URL,            "http://89.218.69.35/customer-office/net.eads.astrium.faceo.HomePage/catalogueService.rpc" );
        // curl_setopt($ch, CURLOPT_URL,            "http://quickjson.com/generate/627604aecc6d" );
        // curl_setopt ($ch, CURLOPT_PORT , 81);
        //curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_POST,           1 );
        curl_setopt($ch, CURLOPT_POSTFIELDS,     $abc);
        curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/x-gwt-rpc; charset=UTF-8'));
	//curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
	//curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 2 );
        $result = curl_exec ($ch);
        //echo $result;
        if($result === false)
        {
            echo '?????? curl: ' . curl_error($ch);
        }
        //phpinfo();
        curl_close($ch);
        unset($abc);
        return $result;
    }

    private function parseCof($response){
        $indexStartUsefulInfo = strpos($response,'net.eads.astrium.faceo.core.apis.catalogue.CatalogueSetRecordResponse/2366861634')+83;
        //$indexStartUsefulInfo = strpos($response,'"<?xml version=',$indexStartUsefulInfo);
        //echo  $indexStartUsefulInfo;
        $indexEndUsefulInfo = strpos($response,'net.eads.astrium.faceo.core.apis.catalogue.CatalogueResponseInfo/2932340219');
        //echo $indexEndUsefulInfo;
        $cut = substr($response,$indexStartUsefulInfo,$indexEndUsefulInfo-$indexStartUsefulInfo);
        $arr = explode("\"<?xml version=",$cut);
        //$arr = strtok($cut,"");
        $m = count($arr);
        if($m<=1) return;
        $arrGmd = explode("<gmd:",$arr[1]);
        $n =count($arrGmd);
        $tmp = explode("CharacterString",$arrGmd[2],2)[1];
       /* $images = [];
        $footprints = [];*/

        if(substr($tmp,1,8) == "DS_DZHR1"){
            $image['type'] = "KazEOSat-1";
            $image['name'] = substr($tmp,1,43);
            $image['date'] = substr(explode("beginPosition",$arrGmd[112],2)[1],1,10);
            $tmp = explode(',',$arrGmd[$n-1]);
            $url = substr($tmp[count($tmp)-13],1,88);
            $image['url'] = $url."?key=".md5($url."p0sUe");
            $tmp = substr(explode("gco:CharacterString>",$arrGmd[77])[1],0,-3);
            $tmp = explode(' ',$tmp);
            sscanf($tmp[0],"%f",$lng);
            $lng = round($lng,4);
            sscanf($tmp[1],"%f",$lat);
            $lat = round($lat,4);
            $footprint = ['NE_lat' => $lat, 'NE_lng' => $lng,
                          'NW_lat' => $lat, 'NW_lng' => $lng,
                          'SE_lat' => $lat, 'SE_lng' => $lng,
                          'SW_lat' => $lat, 'SW_lng' => $lng];
            for($i=2;$i<count($tmp)-2;$i+=2){
                sscanf($tmp[$i],"%f",$lng);
                sscanf($tmp[$i+1],"%f",$lat);
                if($lng+$lat>$footprint['NE_lng']+$footprint['NE_lat']){
                    $footprint['NE_lng'] = round($lng,4);
                    $footprint['NE_lat'] = round($lat,4);
                }
                if ($lat-$lng>$footprint['NW_lat']-$footprint['NW_lng']){
                    $footprint['NW_lng'] = round($lng,4);
                    $footprint['NW_lat'] = round($lat,4);
                }
                if ($lng+$lat<$footprint['SW_lng']+$footprint['SW_lat']){
                    $footprint['SW_lng'] = round($lng,4);
                    $footprint['SW_lat'] = round($lat,4);
                }
                if ($lng-$lat>$footprint['SE_lng'] - $footprint['SE_lat']){
                    $footprint['SE_lng'] = round($lng,4);
                    $footprint['SE_lat'] = round($lat,4);
                }
            }
            $this->addRecord($image,$footprint);
           /* $images[] = $image;
            $footprints[] = $footprint;*/
        } elseif(substr($tmp,1,4) == "KM00"){
            $image['type'] = "KazEOSat-2";
            $image['name'] = substr($tmp,1,18);
            $image['date'] = substr(explode("DateTime",$arrGmd[39],2)[1],1,10);
            $tmp = explode(',',$arrGmd[$n-1]);
            $url = substr($tmp[count($tmp)-13],1,63);
            $image['url'] = $url."?key=".md5($url."p0sUe");
            $tmp = substr(explode("gco:CharacterString>",$arrGmd[72])[1],0,-3);
            $tmp = explode(' ',$tmp);
            sscanf($tmp[0],"%f",$lng);
            $lng = round($lng,4);
            sscanf($tmp[1],"%f",$lat);
            $lat = round($lat,4);
            $footprint = ['NE_lat' => $lat, 'NE_lng' => $lng,
                'NW_lat' => $lat, 'NW_lng' => $lng,
                'SE_lat' => $lat, 'SE_lng' => $lng,
                'SW_lat' => $lat, 'SW_lng' => $lng];
            for($i=2;$i<count($tmp)-2;$i+=2){
                sscanf($tmp[$i],"%f",$lng);
                sscanf($tmp[$i+1],"%f",$lat);
                if($lng+$lat>$footprint['NE_lng']+$footprint['NE_lat']){
                    $footprint['NE_lng'] = round($lng,4);
                    $footprint['NE_lat'] = round($lat,4);
                }
                if ($lat-$lng>$footprint['NW_lat']-$footprint['NW_lng']){
                    $footprint['NW_lng'] = round($lng,4);
                    $footprint['NW_lat'] = round($lat,4);
                }
                if ($lng+$lat<$footprint['SW_lng']+$footprint['SW_lat']){
                    $footprint['SW_lng'] = round($lng,4);
                    $footprint['SW_lat'] = round($lat,4);
                }
                if ($lng-$lat>$footprint['SE_lng'] - $footprint['SE_lat']){
                    $footprint['SE_lng'] = round($lng,4);
                    $footprint['SE_lat'] = round($lat,4);
                }
            }/*
            $images[] = $image;
            $footprints[] = $footprint;*/
            $this->addRecord($image,$footprint);
        }

        for($j = 1;$j<$m-1;$j++){
            $arrGmd = explode("<gmd:",$arr[$j]);
            $n =count($arrGmd);
            $tmp = explode("CharacterString",$arrGmd[2],2)[1];
            if(substr($tmp,1,8) == "DS_DZHR1"){
                $image['type'] = "KazEOSat-1";
                $image['name'] = substr($tmp,1,43);
                $image['date'] = substr(explode("beginPosition",$arrGmd[112],2)[1],1,10);
                $tmp = explode(',',$arrGmd[$n-1]);
                $url = substr($tmp[count($tmp)-3],1,88);
                $image['url'] = $url."?key=".md5($url."p0sUe");
                $tmp = substr(explode("gco:CharacterString>",$arrGmd[77])[1],0,-3);
                $tmp = explode(' ',$tmp);
                sscanf($tmp[0],"%f",$lng);
                $lng = round($lng,4);
                sscanf($tmp[1],"%f",$lat);
                $lat = round($lat,4);
                $footprint = ['NE_lat' => $lat, 'NE_lng' => $lng,
                    'NW_lat' => $lat, 'NW_lng' => $lng,
                    'SE_lat' => $lat, 'SE_lng' => $lng,
                    'SW_lat' => $lat, 'SW_lng' => $lng];
                for($i=2;$i<count($tmp)-2;$i+=2){
                    sscanf($tmp[$i],"%f",$lng);
                    sscanf($tmp[$i+1],"%f",$lat);
                    if($lng+$lat>$footprint['NE_lng']+$footprint['NE_lat']){
                        $footprint['NE_lng'] = round($lng,4);
                        $footprint['NE_lat'] = round($lat,4);
                    }
                    if ($lat-$lng>$footprint['NW_lat']-$footprint['NW_lng']){
                        $footprint['NW_lng'] = round($lng,4);
                        $footprint['NW_lat'] = round($lat,4);
                    }
                    if ($lng+$lat<$footprint['SW_lng']+$footprint['SW_lat']){
                        $footprint['SW_lng'] = round($lng,4);
                        $footprint['SW_lat'] = round($lat,4);
                    }
                    if ($lng-$lat>$footprint['SE_lng'] - $footprint['SE_lat']){
                        $footprint['SE_lng'] = round($lng,4);
                        $footprint['SE_lat'] = round($lat,4);
                    }
                }/*
                $images[] = $image;
                $footprints[] = $footprint;*/
                $this->addRecord($image,$footprint);
            } elseif(substr($tmp,1,4) == "KM00"){
                $image['type'] = "KazEOSat-2";
                $image['name'] = substr($tmp,1,18);
                $image['date'] = substr(explode("DateTime",$arrGmd[39],2)[1],1,10);
                $tmp = explode(',',$arrGmd[$n-1]);
                $url = substr($tmp[count($tmp)-3],1,63);
                $image['url'] = $url."?key=".md5($url."p0sUe");
                $tmp = substr(explode("gco:CharacterString>",$arrGmd[72])[1],0,-3);
                $tmp = explode(' ',$tmp);
                sscanf($tmp[0],"%f",$lng);
                $lng = round($lng,4);
                sscanf($tmp[1],"%f",$lat);
                $lat = round($lat,4);
                $footprint = ['NE_lat' => $lat, 'NE_lng' => $lng,
                    'NW_lat' => $lat, 'NW_lng' => $lng,
                    'SE_lat' => $lat, 'SE_lng' => $lng,
                    'SW_lat' => $lat, 'SW_lng' => $lng];
                for($i=2;$i<count($tmp)-2;$i+=2){
                    sscanf($tmp[$i],"%f",$lng);
                    sscanf($tmp[$i+1],"%f",$lat);
                    if($lng+$lat>$footprint['NE_lng']+$footprint['NE_lat']){
                        $footprint['NE_lng'] = round($lng,4);
                        $footprint['NE_lat'] = round($lat,4);
                    }
                    if ($lat-$lng>$footprint['NW_lat']-$footprint['NW_lng']){
                        $footprint['NW_lng'] = round($lng,4);
                        $footprint['NW_lat'] = round($lat,4);
                    }
                    if ($lng+$lat<$footprint['SW_lng']+$footprint['SW_lat']){
                        $footprint['SW_lng'] = round($lng,4);
                        $footprint['SW_lat'] = round($lat,4);
                    }
                    if ($lng-$lat>$footprint['SE_lng'] - $footprint['SE_lat']){
                        $footprint['SE_lng'] = round($lng,4);
                        $footprint['SE_lat'] = round($lat,4);
                    }
                }/*
                $images[] = $image;
                $footprints[] = $footprint;*/
                $this->addRecord($image,$footprint);
            }
        }

        $arrGmd = explode("<gmd:",$arr[$m-1]);
        $n =count($arrGmd);
        $tmp = explode("CharacterString",$arrGmd[2],2)[1];
        if(substr($tmp,1,8) == "DS_DZHR1"){
            $image['type'] = "KazEOSat-1";
            $image['name'] = substr($tmp,1,43);
            $image['date'] = substr(explode("beginPosition",$arrGmd[112],2)[1],1,10);
            $tmp = explode(',',$arrGmd[$n-1]);
            $url = substr($tmp[count($tmp)-2],1,88);
            $image['url'] = $url."?key=".md5($url."p0sUe");
            $tmp = substr(explode("gco:CharacterString>",$arrGmd[77])[1],0,-3);
            $tmp = explode(' ',$tmp);
            sscanf($tmp[0],"%f",$lng);
            $lng = round($lng,4);
            sscanf($tmp[1],"%f",$lat);
            $lat = round($lat,4);
            $footprint = ['NE_lat' => $lat, 'NE_lng' => $lng,
                'NW_lat' => $lat, 'NW_lng' => $lng,
                'SE_lat' => $lat, 'SE_lng' => $lng,
                'SW_lat' => $lat, 'SW_lng' => $lng];
            for($i=2;$i<count($tmp)-2;$i+=2){
                sscanf($tmp[$i],"%f",$lng);
                sscanf($tmp[$i+1],"%f",$lat);
                if($lng+$lat>$footprint['NE_lng']+$footprint['NE_lat']){
                    $footprint['NE_lng'] = round($lng,4);
                    $footprint['NE_lat'] = round($lat,4);
                }
                if ($lat-$lng>$footprint['NW_lat']-$footprint['NW_lng']){
                    $footprint['NW_lng'] = round($lng,4);
                    $footprint['NW_lat'] = round($lat,4);
                }
                if ($lng+$lat<$footprint['SW_lng']+$footprint['SW_lat']){
                    $footprint['SW_lng'] = round($lng,4);
                    $footprint['SW_lat'] = round($lat,4);
                }
                if ($lng-$lat>$footprint['SE_lng'] - $footprint['SE_lat']){
                    $footprint['SE_lng'] = round($lng,4);
                    $footprint['SE_lat'] = round($lat,4);
                }
            }
            $this->addRecord($image,$footprint);
        } elseif(substr($tmp,1,4) == "KM00") {
            $image['type'] = "KazEOSat-2";
            $image['name'] = substr($tmp, 1, 18);
            $image['date'] = substr(explode("DateTime", $arrGmd[39], 2)[1], 1, 10);
            $tmp = explode(',', $arrGmd[$n - 1]);
            $url = substr($tmp[count($tmp) - 2], 1, 63);
            $image['url'] = $url."?key=".md5($url."p0sUe");
            $tmp = substr(explode("gco:CharacterString>", $arrGmd[72])[1], 0, -3);
            $tmp = explode(' ', $tmp);
            sscanf($tmp[0],"%f",$lng);
            $lng = round($lng,4);
            sscanf($tmp[1],"%f",$lat);
            $lat = round($lat,4);
            $footprint = ['NE_lat' => $lat, 'NE_lng' => $lng,
                'NW_lat' => $lat, 'NW_lng' => $lng,
                'SE_lat' => $lat, 'SE_lng' => $lng,
                'SW_lat' => $lat, 'SW_lng' => $lng];
            for ($i = 2; $i < count($tmp) - 2; $i += 2) {
                sscanf($tmp[$i], "%f", $lng);
                sscanf($tmp[$i + 1], "%f", $lat);
                if ($lng + $lat > $footprint['NE_lng'] + $footprint['NE_lat']) {
                    $footprint['NE_lng'] = round($lng,4);
                    $footprint['NE_lat'] = round($lat,4);
                }
                if ($lat - $lng > $footprint['NW_lat'] - $footprint['NW_lng']) {
                    $footprint['NW_lng'] = round($lng,4);
                    $footprint['NW_lat'] = round($lat,4);
                }
                if ($lng + $lat < $footprint['SW_lng'] + $footprint['SW_lat']) {
                    $footprint['SW_lng'] = round($lng,4);
                    $footprint['SW_lat'] = round($lat,4);
                }
                if ($lng - $lat > $footprint['SE_lng'] - $footprint['SE_lat']) {
                    $footprint['SE_lng'] = round($lng,4);
                    $footprint['SE_lat'] = round($lat,4);
                }
            }/*
            $images[] = $image;
            $footprints[] = $footprint;*/
        }/*
        $this->images = $images;
        $this->footprints = $footprints;*/
    }

    public function addRecord($image,$footprint){
        $eps = 1e-5;
        $qlook = Qlook::find()->where(['like','name',$image['name']])->one();
        if($qlook != null) return;
        $coor = Coordinates::find();
        foreach ($footprint as $key => $value) {
            $coor->andWhere(['between',$key,$value-$eps,$value+$eps]);
        }
        $coor = $coor->one();
       // print_r($coor);
        if($coor == null) {
            $transaction = Coordinates::getDb()->beginTransaction();
            try {
                $coor = new Coordinates();
                foreach ($footprint as $key => $value) {
                    $coor[$key] = $value;
                }
                $coor->save();
                $transaction->commit();
            }  catch(\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        $transaction = Qlook::getDb()->beginTransaction();
        try {
            $qlook = new Qlook();
            $qlook->loadDefaultValues();
            $qlook->footprint_id = $coor->id;
            foreach ($image as $key => $value) {
                $qlook[$key] = $value;
            }
            //print_r($qlook);
            $qlook->insert();
            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        //$qlook = new Qlook();
        //$qlook -> attributes = $image;
    }
}