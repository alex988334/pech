<?php

namespace frontend\controllers;

use Yii;
use yii\filters\AccessControl;
use common\models\KlientVsZakaz;
use common\models\KlientVsZakazSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\Klient;
use common\models\Zakaz;
use common\models\VidWork;
use common\models\VidRegion;

/**
 * KlientVsZakazController implements the CRUD actions for KlientVsZakaz model.
 */
class KlientVsZakazController extends Controller
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
                    ]
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
     * Lists all KlientVsZakaz models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new KlientVsZakazSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'massFilters' => $this->getRelationTablesArray()
        ]);
    }

    /**
     * Displays a single KlientVsZakaz model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new KlientVsZakaz model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new KlientVsZakaz();
        
        if ($id = Yii::$app->request->get('id_zakaz')) {
            if (is_int((int)$id)) { $model->id_zakaz = $id; }
        }
        
        if ($model->load(Yii::$app->request->post())) { 
            
            $idRegionZ = Zakaz::find()->select(['id_region'])
                    ->where('id=:id', [':id' => $model->id_zakaz])->limit(1)->scalar();
            $idRegionK = Klient::find()->select(['id_region'])
                    ->where('id_klient=:id', [':id' => $model->id_klient])->limit(1)->scalar();

            if (Yii::$app->session->get('id_region') == ($idRegionK == $idRegionZ)) { 
                if ($model->save()) {
                    Yii::$app->session->setFlash('message', 'Связь успешно создана');
                } else {
                    Yii::$app->session->setFlash('message', 'Ошибка при сохранении, повторите операцию');
                }                     
            } else {
                Yii::$app->session->setFlash('message', 'Клиент или заявка не из вашего региона');
            }            
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing KlientVsZakaz model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
/*    public function actionUpdate($id)
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
     * Deletes an existing KlientVsZakaz model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the KlientVsZakaz model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return KlientVsZakaz the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = KlientVsZakaz::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    
    protected function getRelationTablesArray()
    {
        $vid = [];
        
        $vid['vidWork'] = VidWork::find()->select(['id', 'name'])->asArray()->all();
        
        $vid['vidRegion'] = VidRegion::find()->select(['id', 'name'])
                ->where('parent_id <> 0')->asArray()->all();
        
        return $vid;
    }
}
