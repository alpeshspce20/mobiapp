<?php

/**
 * This is the model class for table "mob_user_address".
 *
 * The followings are the available columns in table 'mob_user_address':
 * @property integer $id
 * @property string $user_id
 * @property string $address
 * @property integer $is_default
 * @property integer $created_by
 * @property integer $created_dt
 * @property integer $updated_by
 * @property integer $updated_dt
 * @property integer $is_deleted
 */
class UserAddress extends CActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return UserAddress the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return '{{user_address}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('address', 'required'),
            array('is_default,zipcode, country_id,state_id,created_by, created_dt, updated_by, updated_dt, is_deleted', 'numerical', 'integerOnly' => true),
            array('user_id,city,name', 'length', 'max' => 255),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id,name,state_id,country_id,city,zipcode, user_id, address, is_default, created_by, created_dt, updated_by, updated_dt, is_deleted', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'countryRel' => array(self::BELONGS_TO, "Countries", "country_id"),
            'stateRel' => array(self::BELONGS_TO, "States", "state_id"),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'user_id' => 'User',
            'address' => 'Address',
            'is_default' => 'Is Default Address ?',
            'created_by' => 'Created By',
            'created_dt' => 'Created Date',
            'updated_by' => 'Updated By',
            'updated_dt' => 'Updated Date',
            'is_deleted' => 'Is Deleted',
        );
    }

    public function defaultScope() {
        $alias = $this->getTableAlias(false, false);
        if ($alias == '' || $alias == 't') {
            return array('condition' => "t.is_deleted=  0 ",);
        } else {
            return array('condition' => $alias . ".is_deleted= 0 ",);
        }
    }

    protected function beforeSave() {
        if ($this->isNewRecord):
            $this->created_dt = common::getTimeStamp();
            $this->created_by = (isset(Yii::app()->user->id)) ? Yii::app()->user->id : 1;
        else:
            $this->updated_dt = common::getTimeStamp();
            $this->updated_by = (isset(Yii::app()->user->id)) ? Yii::app()->user->id : 1;
        endif;
        return parent::beforeSave();
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('user_id', $this->user_id, true);
        $criteria->compare('address', $this->address, true);
        $criteria->compare('is_default', $this->is_default);
        $criteria->compare('created_by', $this->created_by);
        $criteria->compare('created_dt', $this->created_dt);
        $criteria->compare('updated_by', $this->updated_by);
        $criteria->compare('updated_dt', $this->updated_dt);
        $criteria->compare('is_deleted', $this->is_deleted);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

}
