<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "contacts".
 *
 * @property string $id
 * @property string $uid
 * @property string $surname
 * @property string $name
 * @property string $company
 * @property string $email
 * @property string $phone
 * @property string $address
 */
class Contacts extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contacts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'surname', 'name', 'company', 'email', 'phone', 'address'], 'required'],
            [['uid'], 'integer'],
            [['address'], 'string'],
            [['surname', 'name', 'phone'], 'string', 'max' => 20],
            [['company', 'email'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'Uid',
            'surname' => 'Surname',
            'name' => 'Name',
            'company' => 'Company',
            'email' => 'Email',
            'phone' => 'Phone',
            'address' => 'Address',
        ];
    }

    public function getUser(){
        return $this->hasOne(User::className(),['id' => 'uid']);
    }
}
