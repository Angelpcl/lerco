<?php

namespace app\modules\v1\controllers;

use Yii;
use app\models\cliente\Cliente;
use yii\data\ActiveDataProvider;
use app\models\Esys;

class UserController extends DefaultController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                // restrict access to
                'Origin' => ['*'],
                // Allow only POST and PUT methods
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                // Allow only headers 'X-Wsse'
                'Access-Control-Request-Headers' => ['*'],
                // Allow credentials (cookies, authorization headers, etc.) to be exposed to the browser
                'Access-Control-Allow-Credentials' => false,
                'Access-Control-Allow-Origin' => ['*'],
                // Allow OPTIONS caching
                'Access-Control-Max-Age' => 3600,
                // Allow the X-Pagination-Current-Page header to be exposed to the browser.
                'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page'],
            ],
        ];

        return $behaviors;
    }

    public function actionMe()
    {
    	$post = Yii::$app->request->post();
		// Validamos Token
        $paquete  = $this->authToken($post["token"]);

        return [
        	"code" => 202,
        	"name" => "User",
        	"data" => Cliente::find()->where(["id" => $paquete->cliente_id ])->asArray()->one(),
        	"type" => "Success",
        ];
    }
}
?>
