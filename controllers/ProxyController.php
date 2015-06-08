<?php
/**
 * @link https://github.com/borodulin/yii2-proxypool
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-proxypool/blob/master/LICENSE
 */

namespace conquer\proxypool\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use conquer\proxypool\models\Proxy;
use yii\data\ActiveDataProvider;


/**
 * ProxyController implements the CRUD actions for Proxy model.
 */
class ProxyController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Proxy models.
     * @return mixed
     */
    public function actionIndex()
    {
//        $searchModel = new SnProxySearch();
//        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider = new ActiveDataProvider([
            'query' => Proxy::find()->with('fineproxy'),
        ]);

        return $this->render('index', [
        //    'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Proxy model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        if(Yii::$app->request->isAjax)
            return $this->renderAjax('view', [
                'model' => $this->findModel($id),
            ]);
        else
            return $this->render('view', [
                'model' => $this->findModel($id),
            ]);
    }

    /**
     * Creates a new Proxy model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Proxy();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if(Yii::$app->request->isAjax)
                return $this->redirect(['index']);
            else
                return $this->redirect(['view', 'id' => $model->proxy_id]);
        } else {
            if(Yii::$app->request->isAjax)
                return $this->renderAjax('create', [
                    'model' => $model,
                ]);
            else
                return $this->render('create', [
                    'model' => $model,
                ]);
        }
    }

    /**
     * Updates an existing Proxy model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if(Yii::$app->request->isAjax)
                return $this->redirect(['index']);
            else
                return $this->redirect(['view', 'id' => $model->proxy_id]);
        } else {
            if(Yii::$app->request->isAjax)
                return $this->renderAjax('update', [
                    'model' => $model,
                ]);
            else
                return $this->render('update', [
                    'model' => $model,
                ]);
        }
    }

    /**
     * Deletes an existing Proxy model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the SnProxy model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SnProxy the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Proxy::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
