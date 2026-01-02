<?php
namespace app\models\cliente;

use Yii;
use app\models\user\User;
use app\models\esys\EsysCambioLog;
use app\models\esys\EsysCambiosLog;
use app\models\esys\EsysListaDesplegable;

/**
 * This is the model class for table "cliente".
 *
 * @property int $id Id
 * @property int $titulo_personal_id Titulo personal
 * @property string $email Correo electrónico
 * @property string $email2 Correo secundario
 * @property string $empresa Empresa
 * @property string $nombre Nombre
 * @property string $apellidos Apellidos
 * @property string $sexo Sexo
 * @property string $cargo Cargo
 * @property string $departamento Departamento
 * @property int $origen_id Se entero través de
 * @property int $asignado_a_id Asignado a
 * @property string $tel Teléfono trabajo
 * @property string $tel_ext Extensión
 * @property string $tel2 Otro teléfono
 * @property string $movil Teléfono movil
 * @property string $pag_web Página web
 * @property string $notas Notas / Comentarios
 * @property string $auth_key
 * @property string $password_hash
 * @property string $account_activation_token
 * @property string $password_reset_token
 * @property string $api_username
 * @property string $api_password_hash
 * @property int $status Estatus
 * @property int $created_at Creado
 * @property int $created_by Creado por
 * @property int $updated_at Modificado
 * @property int $updated_by Modificado por
 *
 * @property User $asignadoA
 * @property User $createdBy
 * @property EsysListaDesplegable $origen
 * @property EsysListaDesplegable $tituloPersonal
 * @property User $updatedBy
 * @property ClienteChild[] $clienteChildren
 * @property ClienteChild[] $clienteChildren0
 * @property ClientePaquete[] $clientePaquetes
 * @property FactCfdi[] $factCfdis
 */
class Cliente extends ClienteIdentity
{
    const SCENARIO_CREATE          = 'create';
    const SCENARIO_UPDATE          = 'update';
    const SCENARIO_UPDATE_PASSWORD = 'update-password';

    // the list of status values that can be stored in cliente table
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
     * We made this property so we do not pull hashed password from db when updating
     * @var string
     */
    public $password;
    public $api_password;

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
            [['email', 'nombre'], 'required'],
            ['password', 'required', 'on' => self::SCENARIO_CREATE],
            [[], 'required', 'on' => self::SCENARIO_UPDATE],

            [['sexo'], 'default', 'value' => null],
            [['empresa', 'email', 'email2', 'nombre', 'apellidos', 'departamento', 'tel', 'tel2', 'movil', 'cargo', 'pag_web', 'tel_ext'], 'filter', 'filter' => 'trim'],

            [['titulo_personal_id', 'origen_id', 'asignado_a_id', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],

            [['empresa', 'password_hash', 'account_activation_token', 'password_reset_token', 'api_password_hash'], 'string', 'max' => 255],
            [['email', 'email2', 'nombre', 'apellidos', 'departamento'], 'string', 'max' => 100],
            [['tel', 'tel2', 'movil'], 'string', 'max' => 50],
            [['cargo', 'pag_web'], 'string', 'max' => 150],
            [['tel_ext'], 'string', 'max' => 20],
            [['auth_key'], 'string', 'max' => 32],
            [['notas'], 'string'],

            [['email', 'email2'], 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'message' => 'Esta dirección de correo electrónico ya se ha tomado.'],

            $this->passwordStrengthRule(), // method to determine password strength

            ['api_username', 'string', 'min' => 3, 'max' => 255],
            /*
            ['api_username', 'match',  'not' => true,
                // we do not want to allow users to pick one of spam/bad usernames
                'pattern' => '/\b(' . Yii::$app->params['user.spamNames'] . ')\b/i',
                'message' => 'Es imposible usar ese nombre de usuario'],
            */
            ['api_username', 'unique', 'message' => 'Esta nombre de usuario ya se ha tomado.'],

            $this->apiPasswordStrengthRule(), // method to determine api password strength

            [['account_activation_token', 'password_reset_token'], 'unique'],

            [['titulo_personal_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysListaDesplegable::className(), 'targetAttribute' => ['titulo_personal_id' => 'id']],
            [['origen_id'], 'exist', 'skipOnError' => true, 'targetClass' => EsysListaDesplegable::className(), 'targetAttribute' => ['origen_id' => 'id']],
            [['asignado_a_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['asignado_a_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * Returns the attribute labels.
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'titulo_personal_id' => 'Titulo Personal',
            'email' => 'Correo electrónico',
            'email2' => 'Correo electrónico (Secundario)',
            'empresa' => 'Empresa',
            'nombre' => 'Nombre',
            'apellidos' => 'Apellidos',
            'sexo' => 'Sexo',
            'cargo' => 'Cargo',
            'departamento' => 'Departamento',
            'origen_id' => 'Origen',
            'asignado_a_id' => 'Asignado',
            'tel' => 'Teléfono',
            'tel_ext' => 'Tel. Extención',
            'tel2' => 'Telefono (Secundario)',
            'movil' => 'Tel. Celular',
            'pag_web' => 'Página Web',
            'notas' => 'Notas / Comentarios',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'account_activation_token' => 'Account Activation Token',
            'password_reset_token' => 'Password Reset Token',
            'api_username' => 'Username (API)',
            'api_password_hash' => 'Contraseña (API)',
            'status' => 'Estatus',
            'created_at' => 'Creado',
            'created_by' => 'Creado por',
            'updated_at' => 'Modificado',
            'updated_by' => 'Modificado por',
        ];
    }

    /**
     * Set password rule based on our setting value (Force Strong Password).
     *
     * @return array Password strength rule.
     */
    private function passwordStrengthRule()
    {
        // get setting value for 'Force Strong Password'
        $fsp = Yii::$app->params['fsp'];

        // password strength rule is determined by StrengthValidator
        // presets are located in: vendor/kartik-v/yii2-password/presets.php
        $strong = ['password', "kartik\password\StrengthValidator", 'preset' => 'normal', 'userAttribute' => 'email'];

        // normal yii rule
        $normal = ['password', 'string', 'min' => 6];

        // if 'Force Strong Password' is set to 'true' use $strong rule, else use $normal rule
        return $fsp? $strong: $normal;
    }

    private function apiPasswordStrengthRule()
    {
        // setting value for 'Force Strong Password'
        return Yii::$app->params['fsp']?
            // password strength rule is determined by StrengthValidator
            // presets are located in: vendor/kartik-v/yii2-password/presets.php
            ['api_password', "kartik\password\StrengthValidator", 'preset' => 'normal', 'userAttribute' => 'api_username']:
            // normal yii rule
            // if 'Force Strong Password' is set to 'true' use $strong rule, else use $normal rule
            ['api_password', 'string', 'min' => 6];
    }


//------------------------------------------------------------------------------------------------//
// IdentityInterface
//------------------------------------------------------------------------------------------------//
    /**
     * Finds an identity by the given ID.
     *
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     */
    /*
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }
    */

    /**
     * Finds an identity by the given token.
     *
     * @param string $token the token to be looked for
     * @return IdentityInterface|null the identity object that matches the given token.
     */
    /*
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }
    */

    /**
     * @return int|string current user ID
     */
    /*
    public function getId()
    {
        return $this->id;
    }
    */

    /**
     * @return string current user auth key
     */
    /*
    public function getAuthKey()
    {
        return $this->auth_key;
    }
    */

    /**
     * @param string $authKey
     * @return bool if auth key is valid for current user
     */
    /*
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
    */


//------------------------------------------------------------------------------------------------//
// Relaciones
//------------------------------------------------------------------------------------------------//
    /*
        public function getAsignadoA()
        {
            return $this->hasOne(User::className(), ['id' => 'asignado_a_id']);
        }

        public function getCreatedBy()
        {
            return $this->hasOne(User::className(), ['id' => 'created_by']);
        }

        public function getOrigen()
        {
            return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'origen_id']);
        }

        public function getTituloPersonal()
        {
            return $this->hasOne(EsysListaDesplegable::className(), ['id' => 'titulo_personal_id']);
        }

        public function getUpdatedBy()
        {
            return $this->hasOne(User::className(), ['id' => 'updated_by']);
        }

        public function getClienteChildren()
        {
            return $this->hasMany(ClienteChild::className(), ['child_id' => 'id']);
        }

        public function getClienteChildren0()
        {
            return $this->hasMany(ClienteChild::className(), ['cliente_id' => 'id']);
        }

        public function getClientePaquetes()
        {
            return $this->hasMany(ClientePaquete::className(), ['cliente_id' => 'id']);
        }

        public function getFactCfdis()
        {
            return $this->hasMany(FactCfdi::className(), ['cliente_id' => 'id']);
        }
    */

    public function getCambiosLog()
    {
        return EsysCambioLog::find()->andWhere(['modulo' => $this->tableName(), 'idx' => $this->id])->all();
    }


//------------------------------------------------------------------------------------------------//
// HELPERS
//------------------------------------------------------------------------------------------------//
    public function getNombreCompleto()
    {
        return $this->nombre . ' ' . $this->apellidos;
    }

    /**
     * Returns the cliente status in nice format.
     *
     * @param  integer $status Status integer value.
     * @return string          Nicely formatted status.
     */
    public function getStatusName($status)
    {
        return $this->statusList[$status];
    }

    public static function getAvatar()
    {
        if(Yii::$app->user->identity){
            switch (Yii::$app->user->identity->sexo) {
                case 'hombre':
                    return '@web/img/profile-photos/2.png';
                    break;

                case 'mujer':
                    return '@web/img/profile-photos/8.png';
                    break;

                default:
                    return '@web/img/profile-photos/5.png';
                    break;
            }
        }
    }

    /**
     * @return JSON string
     */
    public static function getItems($params = [])
    {
        $params['id']   = array_key_exists('id', $params)? $params['id']: false;

        $query = Self::find()
            ->select([
                "id",
                new Expression("CONCAT_WS(' ', `nombre`, `apellidos`, CONCAT('[', `id`, ']')) as text"),
            ])
            ->asArray()
            ->orderBy('nombre, apellidos');

        if($params['id'])
            $query->where(['id' => $params['id']]);

        //echo ($query->createCommand()->rawSql) . '<br/><br/>';

        return ArrayHelper::map($query->all(), 'id', 'text');
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
                            if($value['old'])
                                $this->CambiosLog->updateValue($attribute, 'old', EsysListaDesplegable::find()->select(['singular'])->where(['id' => $value['old']])->one()->singular);

                            if($value['dirty'])
                                $this->CambiosLog->updateValue($attribute, 'dirty', EsysListaDesplegable::find()->select(['singular'])->where(['id' => $value['dirty']])->one()->singular);
                            break;

                        case 'status':
                            $this->CambiosLog->updateValue($attribute, 'old', self::$statusList[$value['old']]);
                            $this->CambiosLog->updateValue($attribute, 'dirty', self::$statusList[$value['dirty']]);
                            break;

                        case 'password_hash':
                        case 'password_reset_token':
                        case 'account_activation_token':
                        case 'api_password_hash';
                            $this->CambiosLog->updateValue($attribute, 'old', '********');
                            $this->CambiosLog->updateValue($attribute, 'dirty', '*********');
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

        if ($insert) {

        }else{
            // Guardamos un registro de los cambios
            $this->CambiosLog->createLog($this->id);
        }
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
