<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "qlook".
 *
 * @property integer $id
 * @property string $name
 * @property string $url
 * @property string $date
 * @property string $type
 * @property integer $footprint_id
 * @property double $angle
 * @property double $cloud
 */
class Qlook extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'qlook';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'url', 'date', 'type', 'footprint_id'], 'required'],
            [['date'], 'safe'],
            [['footprint_id'], 'integer'],
            [['angle', 'cloud'], 'number'],
            [['name'], 'string', 'max' => 50],
            [['url'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'url' => 'Url',
            'date' => 'Date',
            'type' => 'Type',
            'footprint_id' => 'Footprint ID',
            'angle' => 'Angle',
            'cloud' => 'Cloud',
        ];
    }

    public function getCoordinates(){
        return $this->hasOne(Coordinates::className(),['id' => 'footprint_id']);
    }
}
