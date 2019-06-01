<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "client_order_master".
 *
 * @property string $id №
 * @property string $id_client № клиента
 * @property string $id_order № заявки
 * @property string $id_master № мастера
 * @property int $created_at Дата создания
 * @property int $id_region № региона
 */
class ClientOrderMaster extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'client_order_master';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_client', 'id_order', 'created_at'], 'required'],
            [['id_client', 'id_order', 'created_at'], 'integer'],
            [['id_master'], 'safe'],
            [['id_client', 'id_order', 'id_master'], 'unique', 'targetAttribute' => ['id_client', 'id_order', 'id_master']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '№',
            'id_client' => '№ клиента',
            'id_order' => '№ заявки',
            'id_master' => '№ мастера',
            'created_at' => 'Дата создания',
            'id_region' => '№ региона',
        ];
    }
    
    public function getClient()
    {
        return $this->hasOne(Klient::className(), ['id_klient' => 'id_client']);
    }
    
    public function getOrder()
    {
        return $this->hasOne(Zakaz::className(), ['id' => 'id_order']);
    }
    
    public function getMaster()
    {
        return $this->hasOne(Master::className(), ['id_master' => 'id_master']);
    }
    
    public static function getRelationTablesArray()
    {
        $vid = [];
        
        $mass = [Klient::getRelationTablesArray(), Zakaz::getRelationTablesArray(), Master::getRelationTablesArray()];
        
        foreach ($mass as $one) {
            foreach ($one as $key => $val) {
                $vid[$key] = $val;
            }
        }
                
        return $vid;
    }   
}
