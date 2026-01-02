<?php
namespace app\models\cliente;

use Yii;

/**
 * This is the model class for table "cliente_csd".
 *
 * @property int $id Id
 * @property int $cliente_id Cliente
 * @property string $rfc RFC
 * @property string $password Contraseña
 * @property string $serie_num No. de Serie
 * @property string $nombre Nombre Fiscal
 * @property int $regimenfiscal Regimen Fiscal (por defecto)
 * @property int $fecha_ini Fecha inicial
 * @property int $fecha_fin Fecha final
 * @property int $status Estatus
 * @property int $created_at Creado
 * @property int $updated_at Modificado
 *
 * @property Cliente $cliente
 * @property ClientePaqueteCsd[] $clientePaqueteCsds
 */
class ClienteCsd extends \yii\db\ActiveRecord
{
    // the list of status values that can be stored in user table
    const STATUS_ACTIVE   = 10;
    const STATUS_INACTIVE = 1;
    const STATUS_DELETED  = 0;   

    /**
     * List of names for each status.
     * @var array
     */
    public static $statusList = [
        self::STATUS_ACTIVE   => 'Habilitado',
        self::STATUS_INACTIVE => 'Inhabilitado',
        //self::STATUS_DELETED  => 'Eliminado'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cliente_csd';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cliente_id', 'rfc', 'password', 'serie_num', 'regimenfiscal', 'fecha_ini', 'fecha_fin', 'status', 'created_at'], 'required'],
            [['cliente_id', 'regimenfiscal', 'fecha_ini', 'fecha_fin', 'status', 'created_at', 'updated_at'], 'integer'],
            [['password'], 'string'],
            [['rfc'], 'string', 'max' => 20],
            [['serie_num'], 'string', 'max' => 25],
            [['nombre'], 'string', 'max' => 200],
            [['cliente_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cliente::className(), 'targetAttribute' => ['cliente_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'cliente_id' => 'Cliente',
            'rfc' => 'RFC',
            'password' => 'Contraseña',
            'serie_num' => 'No. de Serie',
            'nombre' => 'Nombre Fiscal',
            'regimenfiscal' => 'Regimen Fiscal (por defecto)',
            'fecha_ini' => 'Fecha inicial',
            'fecha_fin' => 'Fecha final',
            'status' => 'Estatus',
            'created_at' => 'Creado',
            'updated_at' => 'Modificado',
        ];
    }

    public function getCliente()
    {
        return $this->hasOne(Cliente::className(), ['id' => 'cliente_id']);
    }

    public function getClientePaqueteCsds()
    {
        return $this->hasMany(ClientePaqueteCsd::className(), ['csd_id' => 'id']);
    }
}
