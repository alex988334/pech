<?php

namespace frontend\controllers;

use Yii;
use yii\filters\AccessControl;
use common\models\MasterVsZakaz;
use common\models\MasterVsZakazSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\Master;
use common\models\Zakaz;
use frontend\models\MasterZakazForm;
use common\models\MasterWorkNavik;

use common\models\VidChangeParametr;
use common\models\VidStatusZakaz;
use common\models\VidWork;
use common\models\VidShag;
use common\models\VidRegion;

/**
 * MasterVsZakazController implements the CRUD actions for MasterVsZakaz model.
 */
class MasterVsZakazController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                //'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['error'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [  
                        'allow' => true,
                        'roles' => ['manager', 'head_manager'],
                    ],
                    [  
                        'actions' => ['update'],
                        'allow' => false,                        
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all MasterVsZakaz models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MasterVsZakazSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'massFilters' => $this->getRelationTablesArray()
        ]);
    }
    
    public function actionRepeat()
    {
        $id = Yii::$app->request->post('id'); 
        $model = MasterVsZakaz::find()->where('id=:id', [':id' => $id])->limit(1)->one();        
        if ($model) {
            if(Yii::$app->db->createCommand('UPDATE zakaz SET id_status_zakaz='
                        . VidStatusZakaz::ORDER_EXECUTES .' WHERE id=' . $model->id_zakaz)->execute()) {
                Yii::$app->session->setFlash('message', 'Успех операции');
            } else { Yii::$app->session->setFlash('message', 'Ошибка операции');}
        }
        return $this->redirect('index');
    }

    public function actionGotov()
    {                
        $id = Yii::$app->request->post('id');                
        $model = MasterVsZakaz::find()->where('id=:id', [':id' => $id])->limit(1)->one();        
        if ($model) {
            
            $transact = Yii::$app->db->beginTransaction();            
            try {                
                $reyting = Yii::$app->db->createCommand('SELECT reyting, reyting_add FROM master, '
                        . 'vid_change_parametr WHERE id_master='. $model->id_master . ' LIMIT 1')->queryOne();
                
                Yii::$app->db->createCommand('UPDATE zakaz z,  master m SET z.id_status_zakaz='
                        . VidStatusZakaz::ORDER_EXECUTED .', m.reyting=' 
                        . ($reyting['reyting'] + $reyting['reyting_add']) 
                        . ' WHERE z.id=' . $model->id_zakaz . ' AND m.id_master='
                        . $model->id_master)->execute();
                
                Yii::$app->db->createCommand('DELETE FROM master_vs_zakaz WHERE id=' . $id)->execute();
                $transact->commit(); 
                Yii::$app->session->setFlash('message', 'Успех изменения №' . $id);
            } catch (\Exception $e) {
                $transact->rollBack();
                Yii::$app->session->setFlash('message', 'Ошибка при выполнении1');
                throw $e;
            } catch (\Throwable $e) {
                $transact->rollBack();
                Yii::$app->session->setFlash('message', 'Ошибка при выполнении2');
                throw $e;
            }                 
        }   
        return $this->redirect('index');
    }
    
    /**
     * Displays a single MasterVsZakaz model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
   /* public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new MasterVsZakaz model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new MasterVsZakaz(); 
        
        if ($model->load(Yii::$app->request->post())  /*&& $model->*/) {
            
            if ($model->validate() && (count($this->findMaster($model->id_zakaz, $model->id_master)))) {
                                
                $transaction = Yii::$app->db->beginTransaction();            
                $change = VidChangeParametr::find()->limit(1)->one();
                $cena = Zakaz::find()->select(['cena'])->where(['id' => $model->id_zakaz])->limit(1)->scalar();
                $balans = Master::find()->select('balans')->where(['id_master' => $model->id_master])->limit(1)->scalar();
                $balans = $balans - (int)($cena * $change->balans_delete / 100);
                
                $query[] = 'INSERT INTO `master_vs_zakaz`(`id_master`, `id_zakaz`) VALUES ('
                        . $model->id_master . ', '. $model->id_zakaz . ')';
                $query[] = 'UPDATE `zakaz` SET `id_status_zakaz`=' . VidStatusZakaz::ORDER_EXECUTES 
                        . ' WHERE `id`=' . $model->id_zakaz;
                $query[] = 'UPDATE `master` SET `balans`=' . $balans . ' WHERE `id_master`=' . $model->id_master;
                
                try {
                    foreach ($query as $one) {
                        Yii::$app->db->createCommand($one)->execute();
                    }
                    $transaction->commit();
                    Yii::$app->session->setFlash('message', 'Назначение успешно');
                    return $this->redirect('index'); 
                } catch(\Exception $e) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('message', 'Ошибка при записи в бд, повторите через минуту');
                    throw $e;
                } catch(\Throwable $e) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('message', 'Ошибка при записи в бд, повторите через минуту');
                }
            } else { 
                if (!count($massMaster = $this->findMaster($model->id_zakaz))) { 
                    Yii::$app->session->setFlash('message', 'Требования заявки слишком высоки, '
                            . 'не найден ни один подходящий мастер');
                }
                return $this->render('create', ['model' => $model, 'massMaster' => $massMaster]); 
            }
        }
        
        $model1 = new MasterZakazForm();
        
        if ($model1->load(Yii::$app->request->post())) {
            
            if ($model1->validate()) {
                
                $model->id_zakaz = $model1->id_zakaz;                
                if (!count($massMaster = $this->findMaster($model->id_zakaz))) { 
                    Yii::$app->session->setFlash('message', 'Требования заявки слишком высоки, '
                            . 'не найден ни один подходящий мастер');
                }
                
                return $this->render('create', ['model' => $model, 'massMaster' => $massMaster]); 
                
            } else { $massId[$model->id_zakaz] = $model->id_zakaz; }
        } else {       
            $massZakaz = Zakaz::findBySql('SELECT z.id, z.name, cena FROM zakaz z '
                . ' LEFT JOIN `master_vs_zakaz` m ON m.id_zakaz=z.id WHERE m.id_zakaz IS NULL '
                . ' AND id_region=' . Yii::$app->session->get('id_region') 
                . ' AND id_status_zakaz=' . VidStatusZakaz::ORDER_AVAILABLE)->asArray()->all();
        
            foreach ($massZakaz as $one) { 
                $massId[$one['id']] = $one['id'] .' - '. $one['name'] .', цена '. $one['cena'];                 
            } 
        }
        
        return $this->render('master-zakaz', ['model' => $model1, 'massId' => $massId]);
    }
    

    protected function findMaster($id, $idMaster = '')
    {        
        $zakaz = Yii::$app->db->createCommand('SELECT z.id_vid_work, sort, '
                . 'id_region, cena, balans_delete, reyting_start FROM zakaz z, vid_navik n, '
                . 'vid_change_parametr p WHERE id_navik=n.id AND z.id=' 
                . $id)->queryOne();
        
        if ($idMaster != '') { 
            $idMaster = ' AND m.id_master=' . $idMaster;
        }        
        $massMaster = MasterWorkNavik::findBySql('SELECT m.id_master AS id, m.imya, m.familiya FROM master m,'
            . ' master_vs_zakaz z, master_work_navik mwn, vid_navik n WHERE mwn.id_vid_navik=n.id AND m.id_region=' 
            . $zakaz['id_region'] . ' AND m.reyting >=' . $zakaz['reyting_start'] 
            . ' AND m.balans >=' . (int)($zakaz['cena'] * $zakaz['balans_delete'] / 100) 
            . ' AND mwn.id_master=m.id_master AND mwn.id_vid_work=' . $zakaz['id_vid_work']
            . ' AND n.sort >=' . $zakaz['sort'] . ' ' . $idMaster . ' AND (m.id_master in (SELECT '
            . ' mas.id_master FROM master mas LEFT JOIN master_vs_zakaz zas ON mas.id_master=zas.id_master'
            . ' WHERE zas.id_master IS null AND mas.id_region='. $zakaz['id_region'] .') OR m.limit_zakaz > '
            . ' (SELECT COUNT(mz.id_master) FROM master_vs_zakaz mz WHERE mz.id_master=m.id_master)) '
            . ' GROUP BY m.id_master')->asArray()->all();         
        
        $mass = [];
        
        foreach ($massMaster as $one) { 
            $mass[$one['id']] = $one['id'] .' - '. $one['imya'] .' '. $one['familiya'];            
        }        
        return $mass;

        /*      РАБОЧИЙ SQL 
SELECT 
    m.id_master AS id, m.imya, m.familiya
    ,  (SELECT COUNT(mz.id_master) FROM master_vs_zakaz mz WHERE mz.id_master=m.id_master) as total
FROM 
	master m,
    master_vs_zakaz z,
    master_work_navik mwn,
    vid_navik n
WHERE 
	mwn.id_master=m.id_master
	AND mwn.id_vid_navik=n.id
	AND m.reyting >=0
    AND m.balans >=7000
	AND m.id_region=5
    AND mwn.id_vid_work=1
    AND n.sort >=1    
	AND (m.id_master in (
                SELECT 
                    mas.id_master 
                FROM 
                    `master` mas 
                LEFT JOIN master_vs_zakaz zas ON mas.id_master=zas.id_master
                WHERE 
                    zas.id_master IS null AND mas.id_region=5
            )
	OR m.limit_zakaz > (SELECT COUNT(mz.id_master) FROM master_vs_zakaz mz WHERE mz.id_master=m.id_master)
)
GROUP BY m.id_master*/
    }

    /**
     * Updates an existing MasterVsZakaz model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    /*public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }*/

    /**
     * Deletes an existing MasterVsZakaz model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {   
        $model = $this->findModel($id);
        
        $change = VidChangeParametr::find()->limit(1)->one();
        $zakaz = Zakaz::find()->select(['cena', 'id_status_zakaz'])->where(['id' => $model->id_zakaz])->limit(1)->one();
        $master = Master::find()->select(['balans', 'reyting'])->where(['id_master' => $model->id_master])->limit(1)->one();
        $balans = $master->balans + (int)($zakaz->cena * $change->balans_add / 100);
        
        if ($zakaz->id_status_zakaz == VidStatusZakaz::ORDER_REQUEST_REJECTION) {
            $reyting = ', `reyting`=' . ($master->reyting - (int)($master->reyting * $change->reyting_delete / 100));
        } else {
            $reyting = '';
        }        

        $query[] = 'UPDATE `master` SET `balans`=' . $balans . $reyting . 
                ' WHERE `id_master`=' . $model->id_master;
        $query[] = 'UPDATE `zakaz` SET `id_status_zakaz`=' . VidStatusZakaz::ORDER_AVAILABLE 
                . ' WHERE `id`=' . $model->id_zakaz;
        $query[] = 'DELETE FROM `master_vs_zakaz` WHERE id=' . $id;
                   
        $transaction = Yii::$app->db->beginTransaction();              
        try {
            foreach ($query as $one) {
                Yii::$app->db->createCommand($one)->execute();
            }
            $transaction->commit();
            Yii::$app->session->setFlash('message', 'Успех удаления');
        } catch(\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('message', 'Ошибка при удалении, повторите через минуту');
            throw $e;
        } catch(\Throwable $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('message', 'Ошибка при удалении, повторите через минуту');
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Finds the MasterVsZakaz model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return MasterVsZakaz the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MasterVsZakaz::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    
    protected function getRelationTablesArray()
    {
        $vid = [];
        
        $vid['vidWork'] = VidWork::find()->asArray()->all();
        $vid['vidStatusZakaz'] = VidStatusZakaz::find()->select(['id', 'name'])->asArray()->all();
        $vid['vidShag'] = VidShag::find()->asArray()->all();
        $vid['vidRegion'] = VidRegion::find()->select(['id', 'name'])
                ->where('parent_id <> 0')->asArray()->all();
        
        return $vid;
    }   
}
