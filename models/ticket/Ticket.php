<?php

namespace app\models\ticket;

use Yii;
use app\models\user\User;
use app\models\envio\Envio;
use app\models\esys\EsysListaDesplegable;
use app\models\cliente\Cliente;
use app\models\cliente\ClienteCodigoPromocion;
use app\models\ticket\Seguimiento;

/**
 * This is the model class for table "ticket".
 *
 * @property int $id ID
 * @property int $tipo_id Tipo
 * @property int $envio_id Envio ID
 * @property string $descripcion Descripcion
 * @property int $is_libra_free Is Libras Gratis
 * @property int $is_descuento_envio Is Descuento Envio
 * @property int $libra_free Libras Gratis
 * @property double $descuento_envio Descuento Envio
 * @property int $is_used Utilizado / Usado
 * @property int $status Estatus
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 *
 * @property User $createdBy
 * @property Envio $envio
 * @property EsysListaDesplegable $tipo
 * @property User $updatedBy
 */
class Ticket extends \yii\db\ActiveRecord
{



    const STATUS_ACTIVE     = 10;
    const STATUS_DESARROLLO     = 20;
    const STATUS_PENDIENTE   = 30;
    const STATUS_CERRADO  = 40;

    public static $statusList = [
        self::STATUS_ACTIVE     => 'NUEVO / SIN REVISAR',
        self::STATUS_DESARROLLO  => 'SOLUCION EN DESARROLO / ANALISIS',
        self::STATUS_PENDIENTE    => 'EN REVISION / EL CLIENTE ESTA VALIDANDO',
        self::STATUS_CERRADO   => 'SE HA RESUELTO CORRECTAMENTE',
    ];

    public static $proyectosList = [
        'UCG' => 'UCG',
        'SEA' => 'SEA',
        'TRACTIESA' => 'TRACTIESA',
        'LAAD' => 'LAAD',
        'TLAHUICA' => 'TLAHUICA',
        'CEAMI' => 'CEAMI',
        'BD' => 'BD',
    ];

    /* const STATUS_NUEVO     = 10;
    const STATUS_MODIFICACION     = 20;
    const STATUS_ACTUALIZACION   = 30;
    const STATUS_CORRECCION  = 40;

    public static $clasificacionsList = [
        self::STATUS_NUEVO     => 'NUEVO REQUERIMIENTO',
        self::STATUS_MODIFICACION  => 'MODIFICACION',
        self::STATUS_ACTUALIZACION    => 'ACTUALIZACION',
        self::STATUS_CORRECCION   => 'CORRECCION',
    ]; */

    public static $clasificacionList = [
        'NUEVO REQUERIMIENTO' => 'NUEVO REQUERIMIENTO',
        'MODIFICACION' => 'MODIFICACION',
        'ACTUALIZACION' => 'ACTUALIZACION',
        'CORRECCION' => 'CORRECCION',
    ];

    public static $productoList = [
        'SISTEMA' => 'SISTEMA',
        'WEBAPP' => 'WEBAPP',
        'APP' => 'APP',
        'OTRO' => 'OTRO',
    ];


    const REEMBOLSO_STATUS_PROGRESO = 10;
    const REEMBOLSO_STATUS_TERMINADO = 20;

    const REMBOLSO_ON           = 10;
    const CONDONACION_ON        = 10;

    public static $reembolsoStatusList = [
        self::REEMBOLSO_STATUS_PROGRESO     => 'Reembolso en proceso',
        self::REEMBOLSO_STATUS_TERMINADO  => 'Reembolso Liquidado',
    ];



    public static $alertStatusList = [
        self::STATUS_ACTIVE     => 'danger',
        self::STATUS_PENDIENTE  => 'warning',
        self::STATUS_CERRADO    => 'mint',
    ];

    public $ticket_evidencia_array;

    public $ticket_detalle;

    public $proyecto_text;
    public $producto_text;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ticket';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // [['tipo_id'], 'required'],
            //[['envio_id'], 'required','message' => 'Debes asignar un envío al ticket'],
            [['tipo_id', 'envio_id', 'is_libra_free', 'is_rembolso', 'is_condonacion', 'is_descuento_envio', 'num_rembolso', 'libra_free', 'is_used', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by', 'cliente_id', 'asignado', 'condonacion_especial_id', 'lerco_dev', 'telefono_cliente'], 'integer'],
            [['descripcion', 'proyecto', 'evidencia_', 'email_cliente', 'producto', 'clasificacion'], 'string'],
            [['nota', 'fecha_rembolso', 'fecha_ticket'], 'string'],
            [['ticket_evidencia'], 'string'],
            [['ticket_evidencia_array'], 'file', 'maxFiles' => 5],
            [['clave'], 'string', 'max' => 10],
            [['descuento_envio'], 'number'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['envio_id'], 'exist', 'skipOnError' => true, 'targetClass' => Envio::className(), 'targetAttribute' => ['envio_id' => 'id']],
            [['tipo_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysListaDesplegable::className(), 'targetAttribute' => ['tipo_id' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tipo_id' => 'Tipo',
            'envio_id' => 'Envio ID',
            'cliente_id' => 'Buscar cliente',
            'asignado' => 'Asignado a',
            'descripcion' => 'Descripcion',
            'is_libra_free' => 'Is Libra Free',
            'is_descuento_envio' => 'Is Descuento Envio',
            'libra_free' => 'Libra Free',
            'ticket_evidencia_array' => 'Evidencia del Ticket',
            'descuento_envio' => 'Descuento Envio',
            'is_used' => 'Is Used',
            'is_condonacion' => 'Condonacion',
            'status' => 'Estatus',
            'nota' => 'Notas de seguimiento',
            'ticket_evidencia' => 'Evidencia del Ticket',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCliente()
    {
        return $this->hasOne(Cliente::className(), ['id' => 'cliente_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModelAsignado()
    {
        return $this->hasOne(User::className(), ['id' => 'asignado']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'cliente_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPromocionEspecial()
    {
        return $this->hasOne(ClienteCodigoPromocion::className(), ['id' => 'condonacion_especial_id']);
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
    public function getEnvio()
    {
        return $this->hasOne(Envio::className(), ['id' => 'envio_id']);
    }
    public function getTotalRembolso()
    {
        $rembolsos = json_decode($this->fecha_rembolso);
        $total     = 0;
        if ($rembolsos) {
            foreach ($rembolsos as $key => $rembolso) {
                $total = $total + $rembolso->monto;
            }
        }
        return $total;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTipo()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'tipo_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTicketDetalle()
    {
        return $this->hasMany(TicketDetalle::className(), ['ticket_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    public function uploadTicket()
{
    if (isset($this->ticket_evidencia_array)) {
        $num = 1;
        $ruta = Yii::getAlias('@webroot') . '/ticket/';
        if (!is_dir($ruta)) {
            mkdir($ruta, 0777, true);
        }
        $timestamp = time();
        foreach ($this->ticket_evidencia_array as $file) {
            $file->saveAs($ruta . $timestamp . '-' . $this->clave . "_" . $num . '-ticket.' . $file->extension);
            $num++;
        }
    }
}



    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            if ($insert) {
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity ? Yii::$app->user->identity->id : null;
            } else {

                // Quién y cuando
                $this->updated_at = time();
                $this->updated_by = Yii::$app->user->identity ? Yii::$app->user->identity->id : null;
            }

            if (isset($this->ticket_evidencia_array)) {
                $arrayEvidencia = [];
                $num_clave = 1;
                if ($this->ticket_evidencia) {
                    foreach (json_decode($this->ticket_evidencia, true)  as $key => $file) {
                        $array_name = [
                            "imagen_" . $num_clave => $file[key($file)],
                        ];

                        array_push($arrayEvidencia, $array_name);
                        $num_clave = $num_clave + 1;
                    }
                }

                $num = 1;
                foreach ($this->ticket_evidencia_array as $key => $file) {
                    $array_name = [
                        "imagen_" . $num_clave =>  time() . '-' . $this->clave . "_" . $num . '-ticket.' . $file->extension
                    ];
                    array_push($arrayEvidencia, $array_name);
                    $num = $num + 1;
                }

                $this->ticket_evidencia = json_encode($arrayEvidencia);
            }

            return true;
        } else
            return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
        }
        //Subimos imagenes relacionadas a la sucursal
        $this->uploadTicket();
    }

    public function afterDelete()
    {
        parent::afterDelete();

        if ($this->ticket_evidencia) {
            foreach (json_decode($this->ticket_evidencia, true) as $key => $file) {
                $path = Yii::getAlias('@webroot') . "/ticket/" . $file[key($file)];
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }
    }

    public function getSeguimientos()
    {
        return $this->hasMany(Seguimiento::class, ['ticket_id' => 'id'])->orderBy(['created_at' => SORT_ASC]);
    }
}
