<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class SignupForm extends Model
{
    public $name;
    public $surname;
    public $email;
    public $company;
    public $address;
    public $phone;
    public $verifyCode;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // name, email, subject and body are required
            [['name', 'surname', 'email', 'company', 'address', 'phone'], 'required'],
            // email has to be a valid email address
            ['email', 'email'],
            // verifyCode needs to be entered correctly
            ['verifyCode', 'captcha'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'verifyCode' => 'Verification Code',
        ];
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     * @param  string  $email the target email address
     * @return boolean whether the model passes validation
     */
    public function contact($email,$subject,$body)
    {
        Yii::$app->mailer->compose()
                ->setFrom($email)
                ->setTo([$this->email])
                ->setSubject($subject)
                ->setTextBody($body)
                ->send();
        return true;
    }

    public function insertContacts(){
        if(!$this->validate())
            return false;
        $pass_len = 8;
        $comp = explode(" ",$this->company);
        $login = $this->name.$this->surname."_";
        foreach ($comp as $a){
            $login = $login.$a[0];
        }
        $user = new User();
        $user->username = $login;
        $user->password = Yii::$app->getSecurity()->generateRandomString($pass_len);
        $user->token = Yii::$app->getSecurity()->generateRandomString($pass_len);
        $transaction = Qlook::getDb()->beginTransaction();
        try {
            $user->insert();
            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $contact = new Contacts();
        $contact->name = $this->name;
        $contact->surname = $this->surname;
        $contact->company = $this->company;
        $contact->email = $this->email;
        $contact->phone = $this->phone;
        $contact->address = $this->address;
        $contact->uid = $user->id;
       // print_r($contact);
        $transaction = Contacts::getDb()->beginTransaction();
        try {
            $contact->insert();
            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $subject = "You have successfully signed up in cover.gharysh.kz";
        $body = "There are your login and password below:\n\rlogin: "
            .$user->username."\npassword: "
            .$user->password
            ."\n\rPlease, don't share them with other persons.\n\rBest regards,\n\rKGS.";
        $email = Yii::$app->params['adminEmail'];
        //$email = 'jakypovabylai@gmail.com';
        $this->contact($email,$subject,$body);
        return true;
    }
}
