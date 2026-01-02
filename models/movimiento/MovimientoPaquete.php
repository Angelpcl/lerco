<?php

namespace app\models\movimiento;

use Yii;
use app\models\user\User;
use app\models\caja\CajaDetalleMex;
use app\models\caja\CajaMex;
use app\models\envio\Envio;
use app\models\envio\EnvioDetalle;
use app\models\viaje\ViajeDetalle;
use app\models\viaje\Viaje;

/**
 * This is the model class for table "movimiento_paquete".
 *
 * @property int $id ID
 * @property int $paquete_id Paquete ID
 * @property string $tracked Tracked
 * @property int $tipo_envio Tipo de servicio
 * @property int $tipo_movimiento Tipo de movimiento
 * @property int $created_at Creado
 * @property int $created_by Creado por
 */
class MovimientoPaquete extends \yii\db\ActiveRecord
{


    const MEX_BODEGA        = 20;
    const MEX_CAJA          = 30;
    const MEX_REPARTO       = 60;
    const MEX_REENVIO       = 70;
    const MEX_APERTURA      = 50;


    const MEX_SUCURSAL      = 10;
    const MEX_TRANSCURSO    = 20;
    const MEX_SUCURSAL_USA  = 30;
    const MEX_ENTREGADO     = 40;




    const TIPO_PAQUETE      = 10;
    const TIPO_CAJA         = 20;
    const TIPO_VIAJE        = 30;


    //MEX Y LOS ANGELES 


    public static $tipoMexList = [
        self::MEX_SUCURSAL          => 'Sucursal [MX]',
        self::MEX_TRANSCURSO        => 'Transcurso [USA]',
        self::MEX_SUCURSAL_USA      => 'Sucursal [USA]',
        self::MEX_ENTREGADO         => 'Entregado',

        //self::MEX_BODEGA          => 'Bodega (MX)',
        //self::MEX_CAJA            => 'Caja',
        //self::MEX_REPARTO         => 'Reparto',
        //self::MEX_REENVIO         => 'Reenvio',
        //self::MEX_APERTURA        => 'Apertura Caja',
    ];




    const LAX_TIER_CANCEL               = 1;
    const LAX_TIER_DOCUMENTADO          = 2;
    const LAX_TIER_SUCURSAL             = 10;
    const LAX_TIER_BODEGA_LAX           = 15;
    //
    const LAX_TIER_TRANSCURSO           = 20;
    const LAX_TIER_BODEGA               = 30;

    const LAX_TIER_BODEGA_PUEBLA        = 35;
    const LAX_TIER_REPARTO              = 40;
    const LAX_TIER_PROCESO_ENTREGA      = 45;
    const LAX_TIER_REENVIO              = 50;
    const LAX_TIER_PAQUETERIA           = 55;
    const LAX_TIER_ENTREGADO            = 60;



    public static $tipoLaxTierList = [
        self::LAX_TIER_DOCUMENTADO      => 'Documentado',
        self::LAX_TIER_SUCURSAL         => 'Sucursal (USA)',
        self::LAX_TIER_BODEGA_LAX       => 'Bodega [LOS ANGELES]',
        self::LAX_TIER_TRANSCURSO       => 'Transcurso (MX)',
        self::LAX_TIER_BODEGA           => 'Bodega (MX)',
        self::LAX_TIER_BODEGA_PUEBLA    => 'Bodega [PUEBLA]',
        self::LAX_TIER_REPARTO          => 'Reparto',
        self::LAX_TIER_PROCESO_ENTREGA  => 'En proceso de entrega',
        self::LAX_TIER_REENVIO          => 'Reenvio (Entrega a domicilio) ',
        self::LAX_TIER_PAQUETERIA       => 'Entregado por',
        self::LAX_TIER_ENTREGADO        => 'Entregado',
        self::LAX_TIER_CANCEL           => 'Cancelado',
    ];



    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'movimiento_paquete';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['paquete_id', 'tracked', 'tipo_envio'], 'required'],
            [['paquete_id', 'tipo_envio', 'caja_id', 'tipo_movimiento', 'created_at', 'created_by', 'viaje_id', 'tipo', 'sucursal_id'], 'integer'],
            [["fecha_entrega"], 'safe'],
            [['tracked'], 'string', 'max' => 20],
            [['paqueteria'], 'string', 'max' => 150],
            [['paqueteria_no_guia'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'paquete_id' => 'Paquete ID',
            'peso_mx' => 'Peso mx',
            'tracked' => 'Tracked',
            'tipo_envio' => 'Tipo Envio',
            'tipo' => 'Tipo',
            'paqueteria' => 'Paqueteria',
            'paqueteria_no_guia' => 'N° de guia',
            'tipo_movimiento' => 'Tipo Movimiento',
            'viaje_id' => 'Viaje ID',
            'sucursal_id' => 'Sucursal ID',
            'peso_mx' => 'Peso mx',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCaja()
    {
        return $this->hasOne(CajaMex::className(), ['id' => 'caja_id']);
    }

    public static function getMovimientoItem($tracked)
    {
        return MovimientoPaquete::find()->where(["tracked" => $tracked])->all();
    }

    public function validaMovimientoTracked($tracked, $movimiento)
    {
        $MovimientoPaquete = MovimientoPaquete::find()->where(["tracked" => $tracked])->orderBy("id desc")->one();
        return  isset($MovimientoPaquete->tipo_movimiento) && $MovimientoPaquete->tipo_movimiento == $movimiento ? true : false;
    }

    public function canMoveToTranscurso($tracked, $movimiento, $sucursal_id = 0)
    {
        $SUC_LOS_ANGELES = 44;
        if ($sucursal_id == $SUC_LOS_ANGELES) {
            return true;
        }
        if ($movimiento == self::LAX_TIER_BODEGA_LAX || $movimiento == self::LAX_TIER_CANCEL) {
            return true;
        }
        $MovimientoPaquete = MovimientoPaquete::find()
            ->where(["tracked" => $tracked])
            ->andWhere(['tipo_movimiento' => self::LAX_TIER_BODEGA_LAX])
            ->orderBy("id desc")->all();

        if ($MovimientoPaquete) {
            return true;
            //if ($movimiento == self::LAX_TIER_TRANSCURSO) {
            //   // return   $MovimientoPaquete->tipo_movimiento == self::LAX_TIER_BODEGA_LAX ? true : false;
            //}
            //return true;
        }
        return false;
    }

    public function validaMovimientoAdmin($tracked, $movimiento)
    {
        $MovimientoPaquete = MovimientoPaquete::find()->where(["tracked" => $tracked])->orderBy("id desc")->one();

        if ($MovimientoPaquete) {
            if ($MovimientoPaquete->tipo_envio == Envio::TIPO_ENVIO_TIERRA /*|| $MovimientoPaquete->tipo_envio == Envio::TIPO_ENVIO_LAX*/) {
                $status       = [];
                $position_new = 0;
                $position_old = 0;
                foreach (self::$tipoLaxTierList as $key => $item) {
                    array_push($status, $key);
                    if ($key == $movimiento)
                        $position_new = count($status) - 1;

                    if ($key == $MovimientoPaquete->tipo_movimiento)
                        $position_old = count($status) - 1;
                }

                if ($movimiento == self::LAX_TIER_ENTREGADO) {
                    return $MovimientoPaquete->tipo_movimiento == self::LAX_TIER_PAQUETERIA || $MovimientoPaquete->tipo_movimiento == self::LAX_TIER_REPARTO || $MovimientoPaquete->tipo_movimiento == self::LAX_TIER_REENVIO  ? true : false;
                } elseif ($movimiento == self::LAX_TIER_PROCESO_ENTREGA) {
                    return true;
                } elseif ($movimiento == self::LAX_TIER_PAQUETERIA) {
                    return true;
                } elseif ($movimiento == self::LAX_TIER_REPARTO) {
                    return true;
                } else {
                    return isset($status[$position_old + 1]) &&  $status[$position_new] > $status[$position_old] &&  $status[$position_old + 1] == $status[$position_new] ? true : false;
                }
            } elseif ($tipo_envio == Envio::TIPO_ENVIO_MEX) {
                /*$status       = [];
                $position_new = 0;
                $position_old = 0;
                foreach (self::$tipoMexList as $key => $item) {
                    array_push($status, $key);
                    if ($key == $movimiento)
                        $position_new = count($status) - 1;

                    if ($key == $MovimientoPaquete->tipo_movimiento )
                        $position_old = count($status) - 1;
                }
                return isset($status[$position_old + 1]) && $status[$position_new] > $status[$position_old] &&  $status[$position_old + 1] == $status[$position_new] ? true : false;*/
                return false;
            }
        }
        return false;
    }

    public function validaMovimientoTrackedMex($tracked, $movimiento)
    {
        $MovimientoPaquete = MovimientoPaquete::find()->where(["tracked" => $tracked])->orderBy("id desc")->one();
        if (isset($MovimientoPaquete->tipo_movimiento)) {
            if ($MovimientoPaquete->tipo_movimiento == $movimiento) {
                $CajaMex = CajaMex::findOne($MovimientoPaquete->caja_id);
                if ($CajaMex) {
                    $MovimientoPaqueteCaja = MovimientoPaquete::find()->where(["tracked" => $CajaMex->folio])->orderBy("id desc")->one();
                    //return $MovimientoPaqueteCaja->tipo_movimiento == self::MEX_APERTURA ? true : false;  CAMBIO PARA QUITAR LA APERTURAA DE LAS  CAJAS
                    return $MovimientoPaqueteCaja->tipo_movimiento == self::MEX_APERTURA ? true : true;
                }
            }
            return  true;
        }
        return  true;
    }

    public function validaMovimientoTranscusoApertura($tracked, $tipo_envio)
    {
        $MovimientoPaquete = MovimientoPaquete::find()->where(["tracked" => $tracked])->orderBy("id desc")->one();
        if (isset($MovimientoPaquete->tipo_movimiento)) {
            if ($tipo_envio == Envio::TIPO_ENVIO_TIERRA /*||$tipo_envio == Envio::TIPO_ENVIO_LAX */) {
                if ($MovimientoPaquete->tipo_movimiento == self::LAX_TIER_TRANSCURSO) {
                    $Viaje = Viaje::findOne($MovimientoPaquete->viaje_id);
                    return $Viaje->status == Viaje::STATUS_TERMINADO ? false : true;
                }
            } elseif ($tipo_envio == Envio::TIPO_ENVIO_MEX) {
                if ($MovimientoPaquete->tipo_movimiento == self::MEX_TRANSCURSO) {

                    $Viaje = Viaje::findOne($MovimientoPaquete->viaje_id);
                    if ($Viaje) {
                        if ($Viaje->status == Viaje::STATUS_TERMINADO) {
                            return true;
                        } else {
                            return false;
                        }
                    }
                    return  false;
                }
            }
            return false;
        }

        return false;
    }

    public function validaEntregaPaquete($tracked, $tipo_envio)
    {

        $MovimientoPaquete  = MovimientoPaquete::find()->where(["tracked" => $tracked])->orderBy("id desc")->one();

        $EnvioDetalle       = EnvioDetalle::findOne($MovimientoPaquete->paquete_id);

        $Paquetes           = EnvioDetalle::find()->where(["envio_id" => $EnvioDetalle->envio->id])->all();
        $is_terminado = false;
        if ($tipo_envio == Envio::TIPO_ENVIO_TIERRA /*||$tipo_envio == Envio::TIPO_ENVIO_LAX */) {
            foreach ($Paquetes as $key => $paquete) {

                $Movimientos = MovimientoPaquete::find()
                    ->andWhere(["paquete_id" => $paquete->id])
                    ->andWhere(["tipo_envio" => $tipo_envio])
                    ->andWhere(["tipo" => self::TIPO_PAQUETE])->all();

                if ($Movimientos) {
                    foreach ($Movimientos as $key => $movimiento) {
                        if ($paquete->id ==  $movimiento->paquete_id &&  $movimiento->tipo_envio == $tipo_envio)
                            $movimiento->tipo_movimiento == self::LAX_TIER_ENTREGADO ? $is_terminado = true : $is_terminado = false;
                    }
                } else
                    $is_terminado = false;
            }
        } elseif ($tipo_envio == Envio::TIPO_ENVIO_MEX) {
            foreach ($Paquetes as $key => $paquete) {
                $Movimientos = MovimientoPaquete::find()
                    ->andWhere(["paquete_id" => $paquete->id])
                    ->andWhere(["tipo_envio" => $tipo_envio])
                    ->andWhere(["tipo" => self::TIPO_PAQUETE])->all();
                if ($Movimientos) {
                    foreach ($Movimientos as $key => $movimiento) {
                        if ($paquete->id ==  $movimiento->paquete_id &&  $movimiento->tipo_envio == $tipo_envio)
                            $movimiento->tipo_movimiento == self::MEX_ENTREGADO ? $is_terminado = true : $is_terminado = false;
                    }
                } else
                    $is_terminado = false;
            }
        }



        return $is_terminado;
    }

    public function saveMovimiento($is_app = false)
    {
        switch ($this->tipo_envio) {
                //case Envio::TIPO_ENVIO_LAX    :
            case Envio::TIPO_ENVIO_TIERRA:
                //if ($this->tipo_movimiento == self::LAX_TIER_TRANSCURSO) {
                $ViajeDetalle = new ViajeDetalle();
                $ViajeDetalle->viaje_id         =   $this->viaje_id;
                $ViajeDetalle->paquete_id       =   $this->paquete_id;
                $ViajeDetalle->tracked          =   $this->tracked;
                $ViajeDetalle->tipo             =   $this->tipo;
                $ViajeDetalle->save();
                //}
                break;

                /*case Envio::TIPO_ENVIO_MEX:
                if ($this->tipo_movimiento == self::MEX_CAJA) {
                    $CajaDetalleMex = new CajaDetalleMex();
                    $CajaDetalleMex->caja_mex_id        =   $this->caja_id;
                    $CajaDetalleMex->envio_detalle_id         =   $this->paquete_id;
                    $CajaDetalleMex->tracked            =   $this->tracked;
                    $CajaDetalleMex->save();
                }/*
                if ($this->tipo_movimiento == self::MEX_TRANSCURSO ) {
                    $ViajeDetalle = new ViajeDetalle();
                    $ViajeDetalle->viaje_id         =   $this->viaje_id;
                    $ViajeDetalle->caja_id       =   $this->paquete_id;
                    $ViajeDetalle->tracked          =   $this->tracked;
                    $ViajeDetalle->tipo             =   $this->tipo;
                    $ViajeDetalle->save();
                }*/
            default:
                $ViajeDetalle = new ViajeDetalle();
                $ViajeDetalle->viaje_id         =   $this->viaje_id;
                $ViajeDetalle->paquete_id       =   $this->paquete_id;
                $ViajeDetalle->tracked          =   $this->tracked;
                $ViajeDetalle->tipo             =   $this->tipo;
                $ViajeDetalle->save();
                break;
        }


        if ($this->save()) {
            return true;
        }
        throw new \yii\web\HttpException(202, json_encode($this->errors), 11);



        return false;
    }


    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            if ($insert) {
                $this->created_at = time();

                $this->created_by = $this->created_by ? $this->created_by : null;
                $this->created_by = Yii::$app->user->identity && !$this->created_by ? Yii::$app->user->identity->id : $this->created_by;
            }
            return true;
        } else
            return false;
    }
}
