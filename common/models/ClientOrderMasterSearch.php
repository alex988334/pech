<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ClientOrderMaster;

/**
 * ClientOrderMasterSearch represents the model behind the search form of `common\models\ClientOrderMaster`.
 */
class ClientOrderMasterSearch extends ClientOrderMaster
{   
    public $created_at;
        //  клиентские параметры
    public $client_id_klient;
    public $client_imya;
    public $client_familiya;
    public $client_otchestvo;
    public $client_vozrast; 
    public $client_phone;
    public $client_reyting;
    public $client_balans;
    public $client_id_region;    
        //  параметры заявок
    public $order_id;
    public $order_id_vid_work;
    public $order_id_navik;
    public $order_name;
    public $order_cena;
    public $order_opisanie;
    public $order_reyting_start;
    public $order_zametka;
    public $order_gorod;
    public $order_poselok;
    public $order_ulica;
    public $order_dom;
    public $order_kvartira;
    public $order_id_status_zakaz;
    public $order_id_shag;
    public $order_data_registry;
    public $order_data_start;
    public $order_data_end;
    public $order_dolgota;
    public $order_shirota;
    public $order_dolgota_change;
    public $order_shirota_change;
    public $order_image;
    public $order_id_region;
    public $order_id_ocenka;
    public $order_otziv;    
        //  параметры мастеров
    public $master_id_master;
    public $master_familiya;
    public $master_imya;
    public $master_otchestvo;    
    public $master_vozrast;
    public $master_staj;
    public $master_reyting;
    public $master_id_status_work;
    public $master_data_registry;
    public $master_data_unregistry;
    public $master_phone;
    public $master_mesto_jitelstva;
    public $master_mesto_raboti;
    public $master_balans;
    public $master_id_region;
    public $master_limit_zakaz;
    
        //  типы используемых моделей
    const CLIENT = 'client';
    const ORDER = 'order';
    const MASTER = 'master';
    
        //  названия таблиц в бд
    const KLIENT = 'klient';
    const ZAKAZ = 'zakaz';
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'id_client', 'id_order', 'id_master', 'created_at', 'id_region'], 'integer'],
            
           // [['client_id_klient', 'client_imya', 'client_phone', 'client_id_region'], 'required'],
            [['client_id_klient', 'client_vozrast', 'client_reyting', 'client_balans', 'client_id_region'], 'integer'],
            [['client_imya', 'client_familiya', 'client_otchestvo'], 'string', 'max' => 50],
            [['client_phone'], 'string', 'max' => 11],
            [['client_id_klient'], 'unique'],
            [['client_phone'], 'unique'],
    //        [['old_id'], 'unique'],
          //  [['id_status_on_off'], 'exist', 'skipOnError' => true, 'targetClass' => VidDefault::className(), 'targetAttribute' => ['id_status_on_off' => 'id']],
            [['client_id_klient'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['client_id_klient' => 'id']],
            [['client_id_region'], 'exist', 'skipOnError' => true, 'targetClass' => VidRegion::className(), 'targetAttribute' => ['client_id_region' => 'id']],
                    
           // [['order_id_vid_work', 'order_id_navik', 'order_name', 'order_cena', 'order_opisanie', 'order_reyting_start', 'order_data_registry', 'order_data_start', 'order_data_end', 'order_id_region'], 'required'],
            [['order_id', 'order_id_vid_work', 'order_id_navik', 'order_cena', 'order_reyting_start', 'order_dom', 'order_kvartira', 'order_id_status_zakaz', 'order_id_shag', 'order_id_region', 'order_id_ocenka'], 'integer'],
            [['order_data_registry', 'order_data_start', 'order_data_end'], 'safe'],
            [['order_dolgota', 'order_shirota', 'order_dolgota_change', 'order_shirota_change'], 'number'],
            [['order_name'], 'string', 'max' => 100],
            [['order_opisanie'], 'string', 'max' => 500],
            [['order_zametka'], 'string', 'max' => 255],
            [['order_gorod', 'order_poselok', 'order_ulica', 'order_image'], 'string', 'max' => 50],
            [['order_otziv'], 'string', 'max' => 1000],
            
            [['order_id_navik'], 'exist', 'skipOnError' => true, 'targetClass' => VidNavik::className(), 'targetAttribute' => ['order_id_navik' => 'id']],
            [['order_id_status_zakaz'], 'exist', 'skipOnError' => true, 'targetClass' => VidStatusZakaz::className(), 'targetAttribute' => ['order_id_status_zakaz' => 'id']],
            [['order_id_shag'], 'exist', 'skipOnError' => true, 'targetClass' => VidShag::className(), 'targetAttribute' => ['order_id_shag' => 'id']],
            [['order_id_region'], 'exist', 'skipOnError' => true, 'targetClass' => VidRegion::className(), 'targetAttribute' => ['order_id_region' => 'id']],
            [['order_id_vid_work'], 'exist', 'skipOnError' => true, 'targetClass' => VidWork::className(), 'targetAttribute' => ['order_id_vid_work' => 'id']],
            [['order_id_ocenka'], 'exist', 'skipOnError' => true, 'targetClass' => VidOcenka::className(), 'targetAttribute' => ['order_id_ocenka' => 'id']],
                    
      //      [['master_id_master', 'master_familiya', 'master_imya', 'master_reyting', 'master_data_registry', 'master_phone', 'master_id_region'], 'required'],
            [['master_id_master', 'master_vozrast', 'master_staj', 'master_reyting', 'master_id_status_work', 'master_balans', 'master_id_region', 'master_limit_zakaz'], 'integer'],
            [['master_data_registry', 'master_data_unregistry'], 'safe'],
            [['master_familiya', 'master_imya', 'master_otchestvo'], 'string', 'max' => 50],
            [['master_phone'], 'string', 'max' => 11],
            [['master_mesto_jitelstva', 'master_mesto_raboti'], 'string', 'max' => 100],
            [['master_id_master'], 'unique'],
            [['master_phone'], 'unique'],
     //       [['old_id'], 'unique'],
            [['master_id_region'], 'exist', 'skipOnError' => true, 'targetClass' => VidRegion::className(), 'targetAttribute' => ['master_id_region' => 'id']],
            //[['id_status_on_off'], 'exist', 'skipOnError' => true, 'targetClass' => VidDefault::className(), 'targetAttribute' => ['id_status_on_off' => 'id']],
            [['master_id_status_work'], 'exist', 'skipOnError' => true, 'targetClass' => VidStatusWork::className(), 'targetAttribute' => ['master_id_status_work' => 'id']],
            [['master_id_master'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['master_id_master' => 'id']],    
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }
    
        //  выполняет очистку параметров от префиксов моделей
        //  params - параметры моделей
        //  префиксы моделей потомков client_ order_ master_, требуют удаления
        //  параметров родительской модели идут без префиксов
    protected function decodeParams(array $params)
    {
            //  создаем массив условий, который содержит массивы для каждой отдельной модели
        $massWhere = [self::CLIENT => [], self::ORDER => [], self::MASTER => []];
            //  переберем все параметры
        foreach ($params as $key => $val) {
                //  если параметр содержит префикс client
            if (strpos($key, self::CLIENT)) {                                   //  для параметров модели клиентов
                    //  обрежем ключ параметра на длинну префикса и положим в массив условий
                $massWhere[self::CLIENT][substr($key, 7)] = $val;
            } 
            if (strpos($key, self::ORDER)){                                     //  для параметров модели заявок
                $massWhere[self::ORDER][substr($key, 6)] = $val;
            }
            if (strpos($key, self::MASTER)){                                    //  для параметров модели мастеров
                $massWhere[self::MASTER][substr($key, strlen(self::MASTER)+1)] = $val;
            }            
        }
        
        return $massWhere;                                                      //  вернем сгенерированный массив условий
    }
    
        //  создает параметры сортировки столбцов таблицы
        //  т.к. параметры модели идут с префиксом, то столбцы сортировки придется указать детально
        //  при этом префиксы параметров отличаются от названия таблиц
    protected function getSortAllFields()
    {
        $attributes = [];
            //  создаем массив с названиями параметров всех моделей
        $mass = [ 
            Klient::getTableSchema()->getColumnNames(), 
            Zakaz::getTableSchema()->getColumnNames(), 
            Master::getTableSchema()->getColumnNames() 
        ];   
            //  создаем массивы сопоставления префиксов параметров и таблиц бд моделей
        $names = [ self::CLIENT, self::ORDER, self::MASTER ];                   //  префиксы
        $names1 = [ self::KLIENT, self::ZAKAZ, self::MASTER ];                  //  названия таблиц
        foreach ($mass as $one) {                                               //  перебираем массивы параметров моделей
            foreach ($one as $val) {
                $attributes[current($names) .'.'. $val] = [                     //  указываем сортировку для параметра в формате
                    'asc' => [current($names1) .'.'. $val => SORT_ASC],         //  [client.name => [asc => [klient.name => sort_asc]]]
                    'desc' => [current($names1) .'.'. $val => SORT_DESC]
                ];    
            }
            next($names1);                                                      //  переходим к следующему элементу массива
            next($names);
        }
        
        return $attributes;                                                     //  возращаем сгенерированный массив сортировки
    }

        //  создает параметры сортировки
    protected function getSort()
    {
        $attributes = $this->getSortAllFields();
        
        $attributes['created_at'] = ['asc' => ['client_order_master.created_at' => SORT_ASC], 'desc' => ['client_order_master.created_at' => SORT_DESC]];  
        $attributes['id_region'] = ['asc' => ['client_order_master.id_region' => SORT_ASC], 'desc' => ['client_order_master.id_region' => SORT_DESC]];  
        $attributes['id_client'] = ['asc' => ['client_order_master.id_client' => SORT_ASC], 'desc' => ['client_order_master.id_client' => SORT_DESC]];  
        $attributes['id_order'] = ['asc' => ['client_order_master.id_order' => SORT_ASC], 'desc' => ['client_order_master.id_order' => SORT_DESC]];  
        $attributes['id_master'] = ['asc' => ['client_order_master.id_master' => SORT_ASC], 'desc' => ['client_order_master.id_master' => SORT_DESC]];  
        
        
        $attributes['client_id_region'] = ['asc' => ['klient.id_region' => SORT_ASC], 'desc' => ['klient.id_region' => SORT_DESC]]; 
        
        $attributes['order_id_vid_work'] = ['asc' => ['zakaz.id_vid_work' => SORT_ASC], 'desc' => ['zakaz.id_vid_work' => SORT_DESC]];
        $attributes['order_id_navik'] = ['asc' => ['zakaz.id_navik' => SORT_ASC], 'desc' => ['zakaz.id_navik' => SORT_DESC]];
        $attributes['order_id_status_zakaz'] = ['asc' => ['zakaz.id_status_zakaz' => SORT_ASC], 'desc' => ['zakaz.id_status_zakaz' => SORT_DESC]];
        $attributes['order_id_shag'] = ['asc' => ['zakaz.id_shag' => SORT_ASC], 'desc' => ['zakaz.id_shag' => SORT_DESC]];
        $attributes['order_id_region'] = ['asc' => ['zakaz.id_region' => SORT_ASC], 'desc' => ['zakaz.id_region' => SORT_DESC]];
        $attributes['order_id_ocenka'] = ['asc' => ['zakaz.id_ocenka' => SORT_ASC], 'desc' => ['zakaz.id_ocenka' => SORT_DESC]];
        
        $attributes['client_order_master.id_master'] =  ['asc' => ['client_order_master.id_master' => SORT_ASC], 'desc' => ['client_order_master.id_master' => SORT_DESC]];  
        $attributes['master_id_status_work'] = ['asc' => ['master.id_status_work' => SORT_ASC], 'desc' => ['master.id_status_work' => SORT_DESC]];
        $attributes['master_id_region'] = ['asc' => ['master.id_region' => SORT_ASC], 'desc' => ['master.id_region' => SORT_DESC]];
        
        return $attributes;
    }


    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {    
        if ($id = Yii::$app->request->post('id')) $this->id = $id;
        if ($id = Yii::$app->request->post('id_master')) $this->id_master = $id;
        if ($id = Yii::$app->request->post('id_client')) $this->id_client = $id;
        if ($id = Yii::$app->request->post('id_order')) {
            $this->order_id = $id;
            $this->id_order = $id;
        }
            
        $query = ClientOrderMaster::find()
                ->where(['client_order_master.id_region' => Yii::$app->session->get('id_region')])               
                ->joinWith('client')->joinWith('order')->joinWith('master');
            //  если кнопка видимости выполненных заявок отключена, то устанавливаем условие иключающее выполненные заявки  
        if (Yii::$app->session->get('invisibleExecutedOrders') == FALSE) {
            $query->andWhere(['<>', 'id_status_zakaz', VidStatusZakaz::ORDER_EXECUTED]);
            $query->andWhere(['<>', 'id_status_zakaz', VidStatusZakaz::ORDER_CANCELLED]);
        } 
            //  если кнопка заблокированных заявок отключена, то исключаем со статусом - заблокирована
        if (Yii::$app->session->get('invisibleBlockedOrders') == FALSE) {
            $query->andWhere(['<>', 'id_status_zakaz', VidStatusZakaz::ORDER_UNAVAILABLE]);            
        } 
        $query->asArray();
        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);    
            //  устанавливаем сортировку
        $dataProvider->setSort([
            'attributes' => $this->getSort()           
        ]);    
        
        $this->load($params);
            //  если указаны даты, то приводим их нужному формату
        if (isset($this->created_at) && (!empty($this->created_at))) {
            $this->created_at = strtotime($this->created_at);
        }
        if (isset($this->order_data_registry) && (!empty($this->order_data_registry))) {
            $this->order_data_registry = date('Y-m-d', strtotime($this->order_data_registry));
        }
        if (isset($this->order_data_start) && (!empty($this->order_data_start))) {
            $this->order_data_start = date('Y-m-d', strtotime($this->order_data_start));
        }
        if (isset($this->order_data_end) && (!empty($this->order_data_end))) {
            $this->order_data_end = date('Y-m-d', strtotime($this->order_data_end));
        }
        if (isset($this->master_data_registry) && (!empty($this->master_data_registry))) {
            $this->master_data_registry = date('Y-m-d', strtotime($this->master_data_registry));
        }
        if (isset($this->master_data_unregistry) && (!empty($this->master_data_unregistry))) {
            $this->master_data_unregistry = date('Y-m-d', strtotime($this->master_data_unregistry));
        }        
        
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');            
            return $dataProvider;
        }
            //  добавляем условия выбрки
        $query->andFilterWhere([  
            'client_order_master.id' => $this->id,
            'client_order_master.id_client' => $this->id_client,       
            'client_order_master.id_master' => $this->id_master,
            'client_order_master.id_order' => $this->id_order,
            'client_order_master.created_at' => $this->created_at,
            'client_order_master.id_region' => $this->id_region,
            'klient.vozrast' => $this->client_vozrast,
            'klient.reyting' => $this->client_reyting,
            'klient.balans' => $this->client_balans,
            'klient.id_region' => $this->client_id_region,
        ]);  
        $query->andFilterWhere([
            'zakaz.id' => $this->order_id,
            'zakaz.id_vid_work' => $this->order_id_vid_work,     
            'zakaz.id_navik' => $this->order_id_navik,
            'zakaz.cena' => $this->order_cena,
            'zakaz.reyting_start' => $this->order_reyting_start,
            'zakaz.dom' => $this->order_dom,
            'zakaz.kvartira' => $this->order_kvartira,
            'zakaz.id_status_zakaz' => $this->order_id_status_zakaz,
            'zakaz.id_shag' => $this->order_id_shag,
            'zakaz.data_registry' => $this->order_data_registry,
            'zakaz.data_start' => $this->order_data_start,
            'zakaz.data_end' => $this->order_data_end,
            'zakaz.dolgota' => $this->order_dolgota,   
            'zakaz.shirota' => $this->order_shirota,   
            'zakaz.dolgota_change' => $this->order_dolgota_change,   
            'zakaz.shirota_change' => $this->order_shirota_change,   
            'zakaz.id_region' => $this->order_id_region,  
            'zakaz.id_ocenka' => $this->order_id_ocenka,   
        ]);
        $query->andFilterWhere([       
            'master.vozrast' => $this->master_vozrast,
            'master.staj' => $this->master_staj,
            'master.reyting' => $this->master_reyting,
            'master.id_status_work' => $this->master_id_status_work,
            'master.balans' => $this->master_balans,
            'master.id_region' => $this->master_id_region,
            'master.limit_zakaz' => $this->master_limit_zakaz,    
            'master.data_registry' => $this->master_data_registry,
            'master.data_unregistry' => $this->master_data_unregistry,
        ]);         
        
        $query->andFilterWhere(['like', 'klient.imya', $this->client_imya])
                ->andFilterWhere(['like', 'klient.familiya', $this->client_familiya])
                ->andFilterWhere(['like', 'klient.otchestvo', $this->client_otchestvo])
                ->andFilterWhere(['like', 'klient.phone', $this->client_phone]);                //
        
        $query->andFilterWhere(['like', 'zakaz.name', $this->order_name])
                ->andFilterWhere(['like', 'zakaz.opisanie', $this->order_opisanie])
                ->andFilterWhere(['like', 'zakaz.zametka', $this->order_zametka])
                ->andFilterWhere(['like', 'zakaz.gorod', $this->order_gorod])
                ->andFilterWhere(['like', 'zakaz.poselok', $this->order_poselok])
                ->andFilterWhere(['like', 'zakaz.ulica', $this->order_ulica])
         //       ->andFilterWhere(['like', 'zakaz.data_registry', $this->order_data_registry])
         //       ->andFilterWhere(['like', 'zakaz.data_start', $this->order_data_start])
         //       ->andFilterWhere(['like', 'zakaz.data_end', $this->order_data_end])
                ->andFilterWhere(['like', 'zakaz.image', $this->order_image])
                ->andFilterWhere(['like', 'zakaz.otziv', $this->order_otziv]);
      
        $query->andFilterWhere(['like', 'master.familiya', $this->master_familiya])
                ->andFilterWhere(['like', 'master.imya', $this->master_imya])
                ->andFilterWhere(['like', 'master.otchestvo', $this->master_otchestvo])
               // ->andFilterWhere(['like', 'master.data_registry', $this->master_data_registry])
              //  ->andFilterWhere(['like', 'master.data_unregistry', $this->master_data_unregistry])
                ->andFilterWhere(['like', 'master.phone', $this->master_phone])                         //
                ->andFilterWhere(['like', 'master.mesto_jitelstva', $this->master_mesto_jitelstva])
                ->andFilterWhere(['like', 'master.mesto_raboti', $this->master_mesto_raboti]);
    /**/
        return $dataProvider;
    }
    
    
}
