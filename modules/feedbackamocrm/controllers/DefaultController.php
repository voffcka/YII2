<?php

namespace app\modules\feedbackamocrm\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\widgets\ActiveForm;
use yii\helpers\Json;
use app\modules\feedbackamocrm\models\FormData;

class DefaultController extends Controller
{


	public function actionEntry()
	{
		$model = new FormData();


		\Yii::$app->getSession()->setFlash('error', 'Что-то пошло не так!');
		\Yii::$app->getSession()->setFlash('success', 'Спасибо за ваше сообщение. Мы скоро свяжемся с вами.');

		return $this->render('feed', ['model' => $model]);
		
	}

	public function actionValidate()
	{
		$model = new FormData();
		$request = \Yii::$app->getRequest();
		if ($request->isPost && $model->load($request->post())) {
			\Yii::$app->response->format = Response::FORMAT_JSON;
			return ActiveForm::validate($model);
		}
	}

	public function actionSend()
	{

		$request = Yii::$app->request;
		if (!$request->isAjax) {
			throw new \yii\web\HttpException(400);
		}		
		if (isset($_POST['FormData'])){

			$model = new FormData();


		//Авторизумся
			$getinfoauth=Json::decode($model->CurlGet('auth'));
		//Обрабатываем результат авторизации и от результата продолжаем работу
		//Если авторизация прошла успешно, 
			if($getinfoauth["response"]["auth"]==true){

		//получаем, 
		//id аккаунта, 
		//id добавленного нами кастомного поля 
		//и id поля для телефона

				$getinfo=$model->GetCurrentIDs(Json::decode($model->CurlGet('current')));
				$getinfo['post']=$_POST['FormData'];

				if($getinfo["uid"] && $getinfo["cf"] && $getinfo["tid"]){

		//Добавляем сделку, получаем id добавленной сделки

					$getleadid=$model->GetLeadID(Json::decode($model->CurlGet('leadsadd',$getinfo)));
					$getinfo['lid']=$getleadid;

					if($getinfo['lid']){

		//Добавляем контакт к сделке по id сделки и id поля номера телефона

						$model->CurlGet('contactadd',$getinfo);

		//Добавляем сообщение к сделке по id аккаунта,id сделки

						$model->CurlGet('notesadd',$getinfo);		

						return true;
					}
				}
			}else{throw new \yii\web\UnauthorizedHttpException();}
		}
	}
}
