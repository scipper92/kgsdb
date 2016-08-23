<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "coordinates".
 *
 * @property integer $id
 * @property double $NW_lat
 * @property double $NW_lng
 * @property double $NE_lat
 * @property double $NE_lng
 * @property double $SW_lat
 * @property double $SW_lng
 * @property double $SE_lat
 * @property double $SE_lng
 */
class Coordinates extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'coordinates';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['NW_lat', 'NW_lng', 'NE_lat', 'NE_lng', 'SW_lat', 'SW_lng', 'SE_lat', 'SE_lng'], 'required'],
            [['NW_lat', 'NW_lng', 'NE_lat', 'NE_lng', 'SW_lat', 'SW_lng', 'SE_lat', 'SE_lng'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'NW_lat' => 'Nw Lat',
            'NW_lng' => 'Nw Lng',
            'NE_lat' => 'Ne Lat',
            'NE_lng' => 'Ne Lng',
            'SW_lat' => 'Sw Lat',
            'SW_lng' => 'Sw Lng',
            'SE_lat' => 'Se Lat',
            'SE_lng' => 'Se Lng',
        ];
    }

    public function getQlook(){
        return $this->hasMany(Qlook::className(),['footprint_id'=>'id']);
    }

    private function Haversine($x)
    {
        return (1-cos($x))/2.;
    }

    // Average Earth radius in $fi latitude
    private function rEarth($fi)
    {
        $f = 298.257223563;
        $e1 = (2-1/$f)/$f;
        $a = 6378.137;
        return $a*sqrt(1-$e1)/(1-$e1*$this->Haversine(2*$fi));

    }

    // Angle distance between A and B
    private function dist($A,$B)
    {
        return 2*asin(sqrt($this->Haversine($B['lat']-$A['lat'])+cos($A['lat'])*cos($B['lat'])*$this->Haversine($B['lng'] - $A['lng'])));
    }

    // width of footprint
    public function getWidth()
    {
        $A = array('lat' => deg2rad($this->NW_lat),'lng' => deg2rad($this->NW_lng));
        $B = array('lat' => deg2rad($this->NE_lat),'lng' => deg2rad($this->NE_lng));
        return $this->rEarth(($A['lat']+$B['lat'])/2)*$this->dist($A,$B);
    }
}
