<?php

/**
 * This is the model class for table "{{zones_ranges}}".
 *
 * The followings are the available columns in table '{{zones_ranges}}':
 * @property string $range_id
 * @property string $range_ip
 * @property integer $range_mask
 * @property string $range_desc
 * @property integer $zone_id
 */
class CZonesRanges extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @return ZonesRanges the static model class
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
        return '{{zones_ranges}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('range_mask, zone_id', 'numerical', 'integerOnly'=>true),
            array('range_ip', 'length', 'max'=>10),
            array('range_desc', 'length', 'max'=>11),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('range_id, range_ip, range_mask, range_desc, zone_id', 'safe', 'on'=>'search'),
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
            'range_id' => 'Range',
            'range_ip' => 'Range Ip',
            'range_mask' => 'Range Mask',
            'range_desc' => 'Range Desc',
            'zone_id' => 'Zone',
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

        $criteria->compare('range_id',$this->range_id,true);
        $criteria->compare('range_ip',$this->range_ip,true);
        $criteria->compare('range_mask',$this->range_mask);
        $criteria->compare('range_desc',$this->range_desc,true);
        $criteria->compare('zone_id',$this->zone_id);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }


    public function getFullColumnsList(){
        return Yii::app()->db->cache(20)->createCommand('SHOW FULL COLUMNS FROM '.$this->tableName())->queryAll();
    }

}


