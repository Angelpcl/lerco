<?php
namespace app\models\descarga;

use Yii;

/**
 * This is the model class for table "descarga_bodega".
 *
 * @property int $id Descarga bodega
 * @property int $estado_id Estado
 * @property int $municipio_id Municipio
 * @property int $bodega_descarga Bodega descarga
 */
class DescargaBodega extends \yii\db\ActiveRecord
{
    const DESCARGA_PUEBLA       = 10;
    const DESCARGA_SAN_JUAN     = 20;
    const DESCARGA_OAXACA       = 30;

    const DESCARGA_ESTADO       = 10;
    const DESCARGA_MUNICIPIO    = 20;

    public static $descargaList = [
        self::DESCARGA_PUEBLA   => 'BODEGA PUEBLA',
        self::DESCARGA_SAN_JUAN => 'BODEGA CENTRAL',
        self::DESCARGA_OAXACA => 'BODEGA OAXACA',
    ];


     public static $tipoList = [
        self::DESCARGA_ESTADO   => 'SELECCION POR ESTADO',
        self::DESCARGA_MUNICIPIO => 'SELECCION POR MUNICIPIO Y MUNICIPIO',
    ];


    public static $descargaClaveList = [
        self::DESCARGA_PUEBLA   => 'PUE',
        self::DESCARGA_SAN_JUAN  => 'JUA',
        self::DESCARGA_OAXACA   => 'OAX',
        //self::STATUS_DELETED  => 'Eliminado'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'descarga_bodega';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['estado_id', 'bodega_descarga','tipo'], 'required'],
            [['estado_id', 'municipio_id', 'bodega_descarga','tipo'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'estado_id' => 'Estado',
            'municipio_id' => 'Municipio',
            'tipo' => 'ZONA',
            'bodega_descarga' => 'Bodega DISTRIBUIDORAS',
        ];
    }
}
