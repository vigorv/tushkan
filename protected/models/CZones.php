<?php

/**
 * This is the model class for table "{{zones}}".
 *
 * The followings are the available columns in table '{{zones}}':
 * @property string $zone_id
 * @property string $zone_title
 * @property integer $zone_active
 * @property integer $zone_prio
 */
class CZones extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @return Zones the static model class
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
        return '{{zones}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('zone_active, zone_prio', 'numerical', 'integerOnly'=>true),
            array('zone_title', 'length', 'max'=>11),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('zone_id, zone_title, zone_active, zone_prio', 'safe', 'on'=>'search'),
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
            'zone_id' => 'Zone',
            'zone_title' => 'Zone Title',
            'zone_active' => 'Zone Active',
            'zone_prio' => 'Zone Prio',
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

        $criteria->compare('zone_id',$this->zone_id,true);
        $criteria->compare('zone_title',$this->zone_title,true);
        $criteria->compare('zone_active',$this->zone_active);
        $criteria->compare('zone_prio',$this->zone_prio);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    public function getFullColumnsList() {
        return Yii::app()->db->cache(20)->createCommand('SHOW FULL COLUMNS FROM ' . $this->tableName())->queryAll();
    }

    /**
     *
     * @param mixed ip
     * @param bool is Should conv to mysql int?
     * @return array
     */
    public function getZones($ip, $conv = true) {
        if ($conv)
            $ip = sprintf('%u', ip2long($ip));
        return Yii::app()->db->cache(10)->createCommand()
            ->select('z.*,INET_NTOA(zr.range_ip),zr.range_mask')
            ->from('{{zones}} z')
            ->join('{{zones_ranges}} zr', ' ((z.zone_id = zr.zone_id) AND (zr.range_ip = (' . $ip . ' & ~(  (1<< (32 - zr.range_mask)) -1) )))')
            ->order('z.zone_prio DESC')
            ->queryAll();
    }

    /**
     *
     * @param mixed $ip
     * @param bool $conv
     * @return array
     */
    public function getActiveZones($ip, $conv = true) {
        if ($conv)
            $ip = sprintf('%u', ip2long($ip));
        return Yii::app()->db->cache(10)->createCommand()
            ->select('z.*')
            ->from('{{zones}} z')
            ->join('{{zones_ranges}} zr', ' ((z.zone_id = zr.zone_id) AND (zr.range_ip = (' . $ip . ' & ~(  (1<< (32 - zr.range_mask)) -1) )))')
            ->group('zone_id')
            ->order('z.zone_prio DESC')
            ->where('z.zone_active=1')
            ->queryAll();
    }

    /**
     *
     * @param mixed $ip
     * @param bool $conv
     * @return string
     */
    public function getActiveZoneslst($ip, $conv = true) {
        $zones_active_list = $this->getActiveZones($ip, $conv);
        $ar_active_zones = array();
        foreach ($zones_active_list as $zone) {
            $ar_active_zones [] = $zone['zone_id'];
        }

        $lst_active_zones = implode(',', $ar_active_zones);
        return $lst_active_zones;
    }

}