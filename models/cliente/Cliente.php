<?php

namespace app\models\cliente;

use Yii;
use yii\db\Expression;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;
use app\models\user\User;
use app\models\esys\EsysDireccion;
use app\models\esys\EsysListaDesplegable;
use app\models\esys\EsysDireccionCodigoPostal;
use app\models\esys\EsysCambiosLog;
use app\models\esys\EsysCambioLog;
use app\models\sucursal\Sucursal;
use app\models\pais\ZonasRojas;
use app\models\pais\PaisesLatam;
/**
 * This is the model class for table "cliente".
 *
 * @property int $id ID
 * @property string $nombre Nombre
 * @property string $apellidos Apellidos
 * @property string $email Email
 * @property int $sexo Sexo
 * @property string $telefono Telefono
 * @property string $movil Movil
 * @property int $status Estatus
 * @property string $notas Comentario / Observaciones
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 *
 * @property User $createdBy
 * @property User $updatedBy
 */
class Cliente extends \yii\db\ActiveRecord
{


    const STATUS_ACTIVE   = 10;
    const STATUS_INACTIVE = 1;

    const ORIGEN_USA = 1;
    const ORIGEN_MX = 2;

    const SEXO_HOMBRE = 10;
    const SEXO_MUJER = 20;

    const SERVICIO_MEX = 1;
    const SERVICIO_LAX = 2;
    const SERVICIO_TIERRA = 3;

    public static $statusList = [
        self::STATUS_ACTIVE   => 'Habilitado',
        self::STATUS_INACTIVE => 'Inhabilitado',
        //self::STATUS_DELETED  => 'Eliminado'
    ];

     public static $sexoList = [
        self::SEXO_HOMBRE   => 'Hombre',
        self::SEXO_MUJER => 'Mujer',
    ];

    public static $origenList = [
        self::ORIGEN_MX   => 'México',
        self::ORIGEN_USA  => 'United States',
    ];

    public static $servicioList = [
        self::SERVICIO_MEX     => 'Servicio Méx',
        self::SERVICIO_LAX     => 'Servicio Lax',
        self::SERVICIO_TIERRA  => 'Servicio Tierra',
    ];

    const IS_NOT_ZONA_RIESGO = 10;
    const IS_ZONA_RIESGO = 20;

    public static $isZonaRiesgoList = [
        self::IS_NOT_ZONA_RIESGO   => 'No es zona de riesgo',
        self::IS_ZONA_RIESGO  => 'Si es zona de riesgo',
    ];


    public $dir_obj;
    public $cliente_call;

    public $csv_file;
    public $rows_details = [];

    public $omit_telefono_movil;

    private $num_rows = 0;
    private $csv_column_name = [];

    private $CambiosLog;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cliente';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sexo','atraves_de_id', 'servicio_preferente','status', 
            'created_at', 'created_by', 'updated_at', 'updated_by','origen',
            'titulo_personal_id','asignado_id','tipo_cliente_id',
            'omit_telefono_movil','country_id','is_zona_riesgo'], 'integer'],
            [['notas'], 'string'],
            [['nombre','apellidos','country_id'],'required'],
            [['nombre', 'apellidos'], 'string', 'max' => 150],
            [['email','costo_venta'], 'string', 'max' => 50],
            [['telefono'], 'string', 'max' => 20],
            [['telefono_movil'], 'string', 'min' => 10, 'max' => 10 ,'message' => 'El telefono movíl debe ser  a 10 catacteres'],
            //[['email'], 'unique'],
            //['telefono_movil', 'unique', 'message' => 'El telefono movíl ya ha sido relacionado con otro cliente, ingrese otro nuevamente.', 'when' => function($model) {
                //return self::find()->andWhere(['telefono_movil' => $model->telefono_movil])->andWhere(['status' => self::STATUS_ACTIVE ])->count() > 0 ? true : false;
            //}],
            [['telefono_movil'], 'required'],
            [['asignado_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['asignado_id' => 'id']],
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
            'is_zona_riesgo' => 'Zona de riesgo',
            'country_id' => 'Pais',
            'id' => 'ID',
            'titulo_personal_id'=> 'Titulo personal',
            'atraves_de_id'=> 'Se entero a través de',
            'nombre' => 'Nombre',
            'apellidos' => 'Apellidos',
            'email' => 'Email',
            'sexo' => 'Sexo',
            'telefono' => 'Telefono Casa',
            'servicio_preferente' => 'Servicio preferente',
            'movil' => 'Movil',
            'telefono_movil' => 'Telefono Movil',
            'status' => 'Estatus',
            'origen' => 'Origen',
            'notas' => 'Comentario / Observaciones',
            'tituloPersonal.singular' => 'Titulo personal',
            'asignado_id' => 'Asignado a :',
            'costo_venta'=>'Costo de venta',
            'tipo_cliente_id' => 'Tipo de cliente',

            'medio_contacto_id' => 'Medio de contacto',
            'status_venta_id' => 'Estatus de venta',
            'comportamiento_id' => 'Comportamiento de cliente',

            'created_at' => 'Creado',
            'created_by' => 'Creado por',
            'updated_at' => 'Modificado',
            'updated_by' => 'Modificado por',
            'csv_file' => 'Examinar CSV',
        ];
    }

    public static function isZonaRiesgo($country_id,$code,$cli_id = null){
        
        // Encuentra la zona roja basada en el país y el código postal
        $zonaRoja = ZonasRojas::find()->where(['pais_id' => $country_id, 'code' => $code])->one();
        return $zonaRoja ? true : false;
    }

    public function getNombreCompleto()
    {
        return $this->nombre . ' ' . $this->apellidos;
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public static function validInfoCreate($telefono_movil, $telefono_casa)
    {
        return Sucursal::find()->andWhere(["or",
            ["=","telefono", $telefono_casa],
            ["=","telefono_movil", $telefono_movil],
        ])->andWhere(["or",
            ["=","telefono", $telefono_movil],
            ["=","telefono_movil", $telefono_casa],
        ])->one();

    }

    public function getPais(){
        return $this->hasOne(PaisesLatam::className(), ['id' => 'country_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }
    public function getAsignadoCliente()
    {
        return $this->hasOne(User::className(), ['id' => 'asignado_id']);
    }

    public function getTituloPersonal()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'titulo_personal_id']);
    }
    public function getAtravesDe()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'atraves_de_id']);
    }

    public static function getSearchTelefono($id,$telefono_search){
        return Cliente::find()->andWhere(["<>","id",$id])
                            ->andWhere(["status" => Cliente::STATUS_ACTIVE])
                            ->andWhere(['SUBSTRING(`telefono_movil`,1,8)' => new \yii\db\Expression('SUBSTRING('.$telefono_search.',1,8)') ])->all();
    }

    public static function getSearchTelefonoCasa($id,$telefono_search){
        return Cliente::find()->andWhere(["<>","id",$id])
                            ->andWhere(["status" => Cliente::STATUS_ACTIVE])
                            ->andWhere(['SUBSTRING(`telefono`,1,8)' => new \yii\db\Expression('SUBSTRING('.$telefono_search.',1,8)') ])->all();
    }

    public static function getSearchNombre($id,$nombre,$apellidos){
        return Cliente::find()->andWhere(["<>","id",$id])
                            ->andWhere(["status" => Cliente::STATUS_ACTIVE])
                            ->andWhere(['concat_ws(" ",nombre,apellidos)' => $nombre.' '.$apellidos  ])->all();
    }

    public static function getSearchEmail($id,$email){
        return Cliente::find()->andWhere(["<>","id",$id])
                            ->andWhere(["status" => Cliente::STATUS_ACTIVE])
                            ->andWhere(['<>','email',''])
                            ->andWhere(['email' => $email  ])->all();
    }

    public function getDireccion()
    {
        return $this->hasOne(EsysDireccion::className(), ['cuenta_id' => 'id'])
            ->where(['cuenta' => EsysDireccion::CUENTA_CLIENTE, 'tipo' => EsysDireccion::TIPO_PERSONAL]);
    }
    public function getCambiosLog()
    {
        return EsysCambioLog::find()
            ->andWhere(['or',
                ['modulo' => $this->tableName(), 'idx' => $this->id],
                ['modulo' => EsysDireccion::tableName(), 'idx' => $this->direccion->id],
            ])
            ->all();
    }


    public function getHistorialCall(){
        return $this->hasMany(ClienteHistoricoCall::className(), ['cliente_id' => 'id']);
    }

    public function getTipo()
    {
        return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'tipo_cliente_id']);
    }


    public function importCVSFile()
    {
        $this->csv_file = UploadedFile::getInstance($this, 'csv_file');

        // Es archivo se cargo correctamente
        if (isset($this->csv_file->error) && !$this->csv_file->error) {
            if (($gestor = fopen($this->csv_file->tempName, 'r')) !== FALSE) {
                while (!feof($gestor)) {
                    $row = fgetcsv($gestor, 1000, ',');

                    // Primer fila, nombre de las columnas
                    if (++$this->num_rows == 1){
                        $this->csv_column_name = $row;
                        continue;
                    }


                    // Obtenemos # del Ticket de la fila Praxis
                    if ($row[$this->praxisColumn('NOMBRE')])
                        $row = array_map("utf8_encode", $row); //added

                    if($row[$this->praxisColumn('NOMBRE')]){
                        // Buscamos si ya existe el Ticket en Praxis
                        //$Comision = Comisiones::find()->where(['ticket' => $ticket, 'cpn' => $cpn])->one();

                        $CrmCliente = new Cliente([
                            'nombre'      => isset($row[$this->praxisColumn('NOMBRE')])   ? $row[$this->praxisColumn('NOMBRE')]: null,

                            'apellidos'   => isset($row[$this->praxisColumn('APELLIDO')]) ? $row[$this->praxisColumn('APELLIDO')]: null,

                            'email'       => isset($row[$this->praxisColumn('CORREO')])   ? $row[$this->praxisColumn('CORREO')]: null,

                            'tipo_cliente_id'  => isset($row[$this->praxisColumn('tipo_cliente')])   ? $row[$this->praxisColumn('tipo_cliente')]: null,

                            'sexo'        => isset($row[$this->praxisColumn('SEXO')]) ?  $row[$this->praxisColumn('SEXO')] == 'HOMBRE' ? 10 : 2 : null,

                            'origen'      => isset($row[$this->praxisColumn('ORIGEN')])    ?       $row[$this->praxisColumn('ORIGEN')]: null,

                            'telefono'    => isset($row[$this->praxisColumn('TELEFONO_CASA')])    ?  $row[$this->praxisColumn('TELEFONO_CASA')] : null,

                            'telefono_movil' => isset($row[$this->praxisColumn('TELEFONO_MOVIL')])?   $row[$this->praxisColumn('TELEFONO_MOVIL')] : null,

                            'status'      => Cliente::STATUS_ACTIVE,

                        ]);

                         $CrmCliente->dir_obj = new EsysDireccion([
                            'cuenta' => EsysDireccion::CUENTA_CLIENTE,
                            'tipo'   => EsysDireccion::TIPO_PERSONAL,
                        ]);

                        if ($CrmCliente->origen == self::ORIGEN_MX) {

                            if (isset($row[$this->praxisColumn('CP')])) {
                                $EsysDireccionCodigoPostal = EsysDireccionCodigoPostal::find()->where(['codigo_postal' =>  $row[$this->praxisColumn('CP')]  ])->one();

                                $CrmCliente->dir_obj->estado_id = $EsysDireccionCodigoPostal->estado_id;
                                $CrmCliente->dir_obj->municipio_id = $EsysDireccionCodigoPostal->municipio_id;
                                $CrmCliente->dir_obj->codigo_postal_id = $EsysDireccionCodigoPostal->id;

                            }

                        }else
                            $CrmCliente->dir_obj->codigo_postal_usa = $row[$this->praxisColumn('CP')];

                        $CrmCliente->dir_obj->direccion = isset($row[$this->praxisColumn('DIRECCION_COMPLETA')])?   $row[$this->praxisColumn('DIRECCION_COMPLETA')]: null;

                        $CrmCliente->dir_obj->num_ext = isset($row[$this->praxisColumn('NUMERO_EXT')])?   $row[$this->praxisColumn('NUMERO_EXT')]: null;

                        $CrmCliente->dir_obj->num_int = isset($row[$this->praxisColumn('NUMERO_INT')])?   $row[$this->praxisColumn('NUMERO_INT')]: null;

                        $CrmCliente->dir_obj->referencia = isset($row[$this->praxisColumn('REFERENCIA')])?   $row[$this->praxisColumn('REFERENCIA')]: null;


                        // Respuesta de la insersión en Praxis
                        $rows_details = [
                            'nombre_completo'   => $CrmCliente->nombre .' '. $CrmCliente->apellidos,
                            'origen'            => self::$origenList[$CrmCliente->origen],
                            'email'             => $CrmCliente->email,
                            'status'             => self::$statusList[$CrmCliente->status],
                            'id'         => false,
                            'error'      => false,
                        ];

                        $CrmCliente->save();

                        // NO se guardo correctamente
                        if($CrmCliente->errors)
                            $rows_details['error'] = current($CrmCliente->errors)[0];

                        $rows_details['id'] = $CrmCliente->id;

                        // Agregamos a la lista, $this->rows_details
                        $this->rows_details[] = $rows_details;
                    }

                }
                fclose($gestor);

                // Guardamos CSV praxis file
                /*$archivo_id = EsysArchivo::add_file([
                    'file'   => $this->csv_file,
                    'param1' => 'praxis',
                ]);*/

                return true;
            }
        }
        return false;
    }


    private function praxisColumn($column_name)
    {
        foreach ($this->csv_column_name as $key => $value) {
            if($value == $column_name)
                return $key;
        }
    }

    /**
     * @return JSON string
     */
    public static function getAsiganadoA()
    {
        $query = User::find()
            ->select('id,  nombre, apellidos')
            ->leftJoin('auth_assignment','`user`.`id` = `auth_assignment`.`user_id`')
            ->andWhere([
               'item_name' => 'Asesor ventas'
            ])
            ->orderBy('id asc');

        return ArrayHelper::map($query->all(), 'id', function($value){
            return '['.$value->id.'] '.$value->nombre .' '.$value->apellidos;
        });
    }
    public function  getEstadoOutMX(){  
        $model = EsysDireccion::find()
        ->where([
            'cuenta_id' => $this->id,
        ])
        ->One();

        return $model ? $model->estado_usa : "";
    }
    public function  getMunicipioOutMX(){
        $model = EsysDireccion::find()
        ->where([
            'cuenta_id' => $this->id,
        ])
        ->One();

        return $model ? strtoupper( $model->municipio_usa) : "";
    }
    public function  getCalleOutMX(){
        $model = EsysDireccion::find()
        ->where([
            'cuenta_id' => $this->id,
        ])
        ->One();

        return $model ? $model->direccion : "";
    }

    public function  getColoniaOutMX(){
        $model = EsysDireccion::find()
        ->where([
            'cuenta_id' => $this->id,
        ])
        ->One();

        return $model ? $model->colonia_usa : "";
    }

    //------------------------------------------------------------------------------------------------//
    // ACTIVE RECORD
    //------------------------------------------------------------------------------------------------//
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {

            if ($insert) {
                $this->created_at = time();
                $this->created_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;

            }else{
                // Creamos objeto para log de cambios
                $this->CambiosLog = new EsysCambiosLog($this);

                // Remplazamos manualmente valores del log de cambios
                foreach($this->CambiosLog->getListArray() as $attribute => $value) {
                    switch ($attribute) {
                        case 'titulo_personal_id':
                        case 'atraves_de_id':
                        case 'medio_contacto_id':
                        case 'status_venta_id':
                        case 'comportamiento_id':
                        case 'tipo_cliente_id':
                            if($value['old'])
                                $this->CambiosLog->updateValue($attribute, 'old', EsysListaDesplegable::find()->select(['singular'])->where(['id' => $value['old']])->one()->singular);

                            if($value['dirty'])
                                $this->CambiosLog->updateValue($attribute, 'dirty', EsysListaDesplegable::find()->select(['singular'])->where(['id' => $value['dirty']])->one()->singular);
                            break;

                        case 'fecha_nac':
                            if($value['old'])
                                $this->CambiosLog->updateValue($attribute, 'old', Esys::unixTimeToString($value['old']));

                            if($value['dirty'])
                                $this->CambiosLog->updateValue($attribute, 'dirty', Esys::unixTimeToString($value['dirty']));
                            break;

                        case 'status':
                            $this->CambiosLog->updateValue($attribute, 'old', self::$statusList[$value['old']]);
                            $this->CambiosLog->updateValue($attribute, 'dirty', self::$statusList[$value['dirty']]);
                            break;

                        case 'sexo':
                            $this->CambiosLog->updateValue($attribute, 'old',  isset(self::$sexoList[$value['old']]) ? self::$sexoList[$value['old']]:'');

                            $this->CambiosLog->updateValue($attribute, 'dirty', self::$sexoList[$value['dirty']]);
                            break;

                        case 'origen':
                            $this->CambiosLog->updateValue($attribute, 'old', isset(self::$origenList[$value['old']]) ? self::$origenList[$value['old']] :'');
                            $this->CambiosLog->updateValue($attribute, 'dirty', self::$origenList[$value['dirty']]);
                            break;

                        case 'servicio_preferente':
                            $this->CambiosLog->updateValue($attribute, 'old', isset(self::$servicioList[$value['old']]) ? self::$servicioList[$value['old']]:'');
                            $this->CambiosLog->updateValue($attribute, 'dirty', self::$servicioList[$value['dirty']]);
                            break;

                    }
                }


                // Quién y cuando
                $this->updated_at = time();
                $this->updated_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;
            }

            return true;

        } else
            return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if($insert)
            $this->dir_obj->cuenta_id = $this->id;
        else
            // Guardamos un registro de los cambios
            $this->CambiosLog->createLog($this->id);


            // Guardar dirección
        $this->dir_obj->save();
    }

    public function afterDelete()
    {
        parent::afterDelete();

        $this->direccion->delete();

        foreach ($this->cambiosLog as $key => $value) {
           $value->delete();
        }
    }
}
