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
}
