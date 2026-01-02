<?php
namespace app\models\cliente;

use Yii;

/**
 * This is the model class for table "cliente_paquete".
 *
 * @property int $id Id
 * @property int $cliente_id Cliente
 * @property int $tipo Tipo de paquete
 * @property string $referencia Referencia
 * @property string $factura Factura
 * @property string $costo unitraio Costo unitario
 * @property string $precio Precio
 * @property int $impuestos Impuestos
 * @property int $creditos Créditos
 * @property int $creditos_usados Creditos usados
 * @property int $creditos_limite Limite de maximo de timbrados
 * @property string $notas Notas / Comentarios
 * @property int $created_at Creado
 * @property int $created_by Vendedor
 * @property int $updated_at Modificado
 * @property int $updated_by Modifcado por
 *
 * @property Cliente $cliente
 * @property User $createdBy
 * @property User $updatedBy
 * @property FactCfdi[] $factCfdis
 */
class ClientePaquete extends \yii\db\ActiveRecord
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

    // the list of tipos values that can be stored in user table
    const TIPO_PRE_PAGO    = 1;
    const TIPO_POR_CONSUMO = 2;

    /**
     * List of names for each tipos.
     * @var array
     */
    public static $tipoList = [
        self::TIPO_PRE_PAGO    => 'Pre-pago',
        self::TIPO_POR_CONSUMO => 'Por consumo',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cliente_paquete';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cliente_id', 'tipo', 'impuestos', 'creditos', 'creditos_usados', 'created_by'], 'required'],
            [['cliente_id', 'tipo', 'impuestos', 'creditos', 'creditos_usados', 'creditos_limite', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['costo unitraio', 'precio'], 'number'],
            [['notas'], 'string'],
            [['referencia'], 'string', 'max' => 255],
            [['factura'], 'string', 'max' => 30],
            [['cliente_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cliente::className(), 'targetAttribute' => ['cliente_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
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
            'cliente_id' => 'Cliente ID',
            'tipo' => 'Tipo',
            'referencia' => 'Referencia',
            'factura' => 'Factura',
            'costo unitraio' => 'Costo Unitraio',
            'precio' => 'Precio',
            'impuestos' => 'Impuestos',
            'creditos' => 'Creditos',
            'creditos_usados' => 'Creditos Usados',
            'creditos_limite' => 'Creditos Limite',
            'notas' => 'Notas',
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
    public function getFactCfdis()
    {
        return $this->hasMany(FactCfdi::className(), ['cliente_paquete_id' => 'id']);
    }
}
