<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "manager_table_grant".
 *
 * @property string $id №
 * @property string $id_manager № менеджера
 * @property int $id_table_field № таблицы или поля
 * @property int $visibility_field Разрешение на просмотр поля
 * @property int $change_field Разрешение на редактирование поля
 */
class ManagerTableGrant extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'manager_table_grant';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_manager', 'id_table_field'], 'required'],
            [['id_manager', 'id_table_field', 'visibility_field', 'change_field'], 'integer'],
            [['id_manager'], 'exist', 'skipOnError' => true, 'targetClass' => Manager::className(), 'targetAttribute' => ['id_manager' => 'id_manager']],
            [['id_table_field'], 'exist', 'skipOnError' => true, 'targetClass' => ManagerTable::className(), 'targetAttribute' => ['id_table_field' => 'id']],
         //   [['visibility'], 'exist', 'skipOnError' => true, 'targetClass' => VidDefault::className(), 'targetAttribute' => ['visibility' => 'id']],
        ];
    }
    
  /*  public function scenarios() {
        parent::scenarios();
        
        return [
            
            self::SCENARIO_SAVE_VISIBLE => [
                'id_vid_work', 'id_navik', 'name', 'opisanie', 'reyting_start', 
                'id_status_zakaz', 'zametka', 'gorod', 'poselok', 'ulica', 'dom', 
                'kvartira', 'data_start', 'data_end', 'cena'
                ],              
        ];
    }
*/
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '№',
            'id_manager' => '№ менеджера',
            'id_table_field' => '№ таблицы или поля',
            'visibility_field' => 'Разрешение на просмотр поля',
            'change_field' => 'Разрешение на редактирование поля',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManager()
    {
        return $this->hasOne(Manager::className(), ['id_manager' => 'id_manager']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getField()
    {
        return $this->hasOne(ManagerTable::className(), ['id' => 'id_table_field']);
    }
}
