<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\MasterWorkNavik;


class MasterNavikForm extends Model
{
    
    public $id;
    public $massWork;
    public $massNavik;
    public $vidWork;
    public $vidNavik;
    
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['massWork', 'massNavik'], 'each', 'rule' => ['integer']]
        ];
    }
    
    public function generateNavik()
    {
        if (!$this->validate()) {
            return null;
        }
        $session = Yii::$app->session;
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        
        try {
            
            for ($i=0; $i<count($this->massWork); $i++) {
                $connection->createCommand('INSERT INTO `master_work_navik`(`id_master`, `id_vid_work`, `id_vid_navik`) VALUES ('
                . $this->id . ', '
                . $this->massWork[$i] . ', '
                . $this->massNavik[$i] . ')')->execute();           
            }
            $transaction->commit();
            $session->setFlash('message', 'Навыки добавлены для мастера ' . $this->id); 
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            $session->setFlash('message', 'Ошибка при записи навыков в базу данных');
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $session->setFlash('message', 'Ошибка при записи навыков в базу данных');
            throw $e;
        }       
        
        
        for ($i = 0; $i < count($this->massWork); $i++) {
            $master = new MasterWorkNavik();
            $master->id_master = $this->id;
            $master->id_vid_work = $this->massWork[$i];
            $master->id_vid_navik = $this->massNavik[$i];
            if ($master->validate()) {
                if ($master->save()) {
                    
                }
            } else {
                Yii::$app->session->setFlash('message', 'Параметры заданы неверно');
                return false;
            }
        }
        
    }
}

