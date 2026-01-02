<?php

namespace app\modules\operacion\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use app\models\envio\Envio;
/**
 * Default controller for the `operacion` module
 */
class SeguimientoController extends \app\controllers\AppController
{


 	private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create_basic' => false,
            'update_basic' => false,
            'delete_basic' => false,
            'cancel_basic' => false,
            'create_mex' => false,
            'update_mex' => false,
            'delete_mex' => false,
            'cancel_mex' => false,
            'seguimiento' => Yii::$app->user->can('seguimiento'),
        ];
    }

    public function actionIndex()
    {
        return $this->render('index',[
        	'can' => $this->can
        ]);
    }

    public function actionEscaneo()
    {
        if ($response = Yii::$app->request->post()) {
            $Envio = Envio::find()->where(["folio" => trim($response['folio']) ])->one();
            if (isset($Envio->id)) {
                if ($Envio->tipo_envio == Envio::TIPO_ENVIO_MEX )
                    return $this->redirect(['/operacion/envio-mex/view', 'id' => $Envio->id]);
                elseif ($Envio->tipo_envio == Envio::TIPO_ENVIO_LAX  || $Envio->tipo_envio == Envio::TIPO_ENVIO_TIERRA )
                    return $this->redirect(['/operacion/envio/view', 'id' => $Envio->id]);
            }else{
                Yii::$app->session->setFlash('danger', "No se encontro ningun envío con el folio #" . $response['folio'] );
                return $this->redirect(['escaneo']);
            }
        }

        return $this->render('escaneo',[
            'can' => $this->can
        ]);
    }

}
