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
use conquer\proxypool\models\Fineproxy;
use yii\data\ActiveDataProvider;

/**
 * FineproxyController implements the CRUD actions for Fineproxy model.
 */
class FineproxyController extends Controller
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
     * Lists all Fineproxy models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Fineproxy::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Fineproxy model.
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
     * Creates a new Fineproxy model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Fineproxy();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if(Yii::$app->request->isAjax)
                return $this->redirect(['index']);
            else
                return $this->redirect(['view', 'id' => $model->fineproxy_id]);
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
     * Updates an existing Fineproxy model.
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
                return $this->redirect(['view', 'id' => $model->fineproxy_id]);
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
     * Deletes an existing Fineproxy model.
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
     * Finds the Fineproxy model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Fineproxy the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Fineproxy::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
