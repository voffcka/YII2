<?php
namespace app\modules\feedbackamocrm\models;


use yii\base\Model;
use yii\helpers\Json;

class FormData extends Model
{
	public $name;
	public $phone;
	public $theme;
	public $message;

	public function rules()
	{
		return [
		[['name', 'phone', 'theme', 'message'], 'required'],
		//['phone', 'number'],
		];
	}

	public function GetLeadID($arr){

		return $arr["response"]["leads"]["add"][0]["id"];

	}

	public function GetCurrentIDs($arr){

		$allcf=[];
		$allcf['uid'] = $arr["response"]["account"]["users"][0]["id"];
		$allcf['cf']  = $arr["response"]["account"]["custom_fields"]["leads"][0]["id"];
		$allcf['tid'] = $arr["response"]["account"]["custom_fields"]["contacts"][1]["id"];

		return $allcf;
	}


	private function UrlBuild($split){

		return 'https://'.\Yii::$app->params['subAPI'] . \Yii::$app->params['urlAPI'].$split;

	}


	private function CmdBuild($cmd,$data=null){

		$ret=[];

		switch ($cmd) {
			case 'auth':

			$split='auth.php?type=json';
			$ret['url']=$this->UrlBuild($split);

			$ret['query']=json_encode([
				'USER_LOGIN'=>\Yii::$app->params['usrAPI'],
				'USER_HASH'=>\Yii::$app->params['keyAPI']
				]);

			break;
			case 'leadsadd':

			$split='v2/json/leads/set';
			$ret['url']=$this->UrlBuild($split);

			$leads['request']['leads']['add']=array(		 
				array(
					'name'=>$data["post"]["name"],
					'status_id'=>0,
					'price'=>0,
					"custom_fields"=>[
					[
					"id"=>$data["cf"], 
					"values"=>[ [
					"value"=>$data["post"]["theme"]
					]
					]
					]
					],
					)
				);
			$ret['query']=Json::encode($leads);

			break;
			case 'contactadd':

			$split='v2/json/contacts/set';
			$ret['url']=$this->UrlBuild($split);

			$contacts['request']['contacts']['add']=array(  array(
				'name'=>$data["post"]["name"],
				'linked_leads_id'=>array(
    				$data["lid"] //nomer sdelki
    				),
				'custom_fields'=>array(
					array(
						'id'=>$data["tid"],
						'values'=>array(
							array(
								'value'=>'7'. $data["post"]["phone"],
								'enum'=>'WORK'
								),
							)
						),
					)
				)
			);

			$ret['query']=Json::encode($contacts);

			break;
			case 'notesadd':

			$split='v2/json/notes/set';
			$ret['url']=$this->UrlBuild($split);


			$notes['request']['notes']['add']=array(

				array(
        'element_id'=>$data["lid"], //nomer sdelki
        'element_type'=>2,//sdelka ili contact
        'note_type'=>4,
        'text'=>$data["post"]["message"],
        'responsible_user_id'=>$data["uid"],
        ),

				);

			$ret['query']=Json::encode($notes);

			break;
			case 'current':

			$split='v2/json/accounts/current';
			$ret['url']=$this->UrlBuild($split);
			$ret['query']=false;

			break;
			default:
			
			$split='auth.php?type=json';
			$ret['url']=$this->UrlBuild($split);
			$ret['query']=Json::encode([
				'USER_LOGIN'=>\Yii::$app->params['usrAPI'],
				'USER_HASH'=>\Yii::$app->params['keyAPI']
				]);
		}

		return $ret;

	}




	public function CurlGet($cmd,$data=null){

		$ret=$this->CmdBuild($cmd,$data);

		if($ret['query']){
			$curl=curl_init(); 
		}else{
			$curl=curl_init($ret['url']);
		}
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');

		if($ret['query']){
			curl_setopt($curl,CURLOPT_URL,$ret['url']);	
			curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');	
			curl_setopt($curl,CURLOPT_POSTFIELDS,$ret['query']);
			curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
		}

		curl_setopt($curl,CURLOPT_HEADER,false);
   		curl_setopt($curl,CURLOPT_COOKIEFILE,$_SERVER['DOCUMENT_ROOT'].'/../cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
   		curl_setopt($curl,CURLOPT_COOKIEJAR,$_SERVER['DOCUMENT_ROOT'].'/../cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
   		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
   		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);

    $out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
    $code=curl_getinfo($curl,CURLINFO_HTTP_CODE); #Получим HTTP-код ответа сервера
    curl_close($curl); #Завершаем сеанс cURL
     if($code!=200){throw new \yii\web\HttpException($code);}
    return $out;

}


}
