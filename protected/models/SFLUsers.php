<?php

/**
 * This is the model class for table "{{users}}".
 *
 * The followings are the available columns in table '{{users}}':
 * @property string $id
 * @property string $email
 * @property string $name
 * @property string $group_id
 * @property string $pwd
 * @property string $created
 * @property string $lastvisit
 * @property string $active
 * @property string $salt
 * @property integer $server_id
 * @property string $sess_id
 * @property double $free_limit
 * @property integer $confirmed
 */
class SFLUsers extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return SFLUsers the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{users}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('email, pwd, created, lastvisit, server_id, sess_id, free_limit, confirmed', 'required'),
            array('server_id, confirmed', 'numerical', 'integerOnly'=>true),
            array('free_limit', 'numerical'),
            array('email', 'length', 'max'=>45),
            array('name, salt', 'length', 'max'=>20),
            array('group_id, active', 'length', 'max'=>10),
            array('pwd, sess_id', 'length', 'max'=>255),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, email, name, group_id, pwd, created, lastvisit, active, salt, server_id, sess_id, free_limit, confirmed', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'email' => 'Email',
            'name' => 'Name',
            'group_id' => 'Group',
            'pwd' => 'Pwd',
            'created' => 'Created',
            'lastvisit' => 'Lastvisit',
            'active' => 'Active',
            'salt' => 'Salt',
            'server_id' => 'Server',
            'sess_id' => 'Sess',
            'free_limit' => 'Free Limit',
            'confirmed' => 'Confirmed',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id,true);
        $criteria->compare('email',$this->email,true);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('group_id',$this->group_id,true);
        $criteria->compare('pwd',$this->pwd,true);
        $criteria->compare('created',$this->created,true);
        $criteria->compare('lastvisit',$this->lastvisit,true);
        $criteria->compare('active',$this->active,true);
        $criteria->compare('salt',$this->salt,true);
        $criteria->compare('server_id',$this->server_id);
        $criteria->compare('sess_id',$this->sess_id,true);
        $criteria->compare('free_limit',$this->free_limit);
        $criteria->compare('confirmed',$this->confirmed);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }
}