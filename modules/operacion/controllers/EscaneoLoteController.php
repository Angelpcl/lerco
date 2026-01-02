<?php

namespace app\modules\operacion\controllers;

use yii\web\Controller;

/**
 * Default controller for the `admin` module
 */
class EscaneoLoteController extends  \app\controllers\AppController
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }


}
