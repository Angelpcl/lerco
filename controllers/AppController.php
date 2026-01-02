<?php

namespace app\controllers;

use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use Yii;

/**
 * AppController extends Controller and implements the behaviors() method
 * where you can specify the access control ( AC filter + RBAC ) for your controllers and their actions.
 */
class AppController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Returns a list of behaviors that this component should behave as.
     * Here we use RBAC in combination with AccessControl filter.
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    /*************************
                     * Site
                     *************************/
                    [
                        'controllers' => ['site'],
                        'actions' => ['index', 'acerca-de', 'permisos', 'error', 'login'],
                        'allow' => true,
                    ],

                    /*************************
                     * Admin
                     *************************/
                    // Dashboard
                    [
                        'controllers' => ['admin/dashboard'],
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['dashboardAdmin'],
                    ],
                    // Dashboard
                    [
                        'controllers' => ['admin/user'],
                        'actions' => ['check-promocion-especial', 'promocion-autoriza','login', 'cancel-promocion-especial', 'reenvio-autoriza', 'check-reenvio'],
                        'allow' => true,
                        'roles' => ['configuracionSitio', 'notificacionReenvio'],
                    ],
                    [
                        'controllers' => ['admin/user'],
                        'actions' => ['is-check-cancel'],
                        'allow' => true,
                        'roles' => ['notificacionEnvios'],
                    ],

                    // Usuarios
                    [
                        'controllers' => ['admin/user'],
                        'actions' => ['login', 'signup', 'activate-account', 'request-password-reset', 'reset-password'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'controllers' => ['admin/user'],
                        'actions' => ['logout', 'change-password', "mi-perfil"],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'controllers' => ['admin/search'],
                        'actions' => ['load-script'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'controllers' => ['admin/user'],
                        'actions' => ['index', 'index_proyectos', 'index_productos', 'editar-contactos', 'login', 'users-json-btt', 'view', 'historial-cambios', 'user-ajax', 'enable-acceso-app', 'desabled-acceso-app'],
                        'allow' => true,
                        'roles' => ['userView'],
                    ],
                    [
                        'controllers' => ['admin/user'],
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['userCreate'],
                    ],
                    [
                        'controllers' => ['admin/user'],
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['userUpdate'],
                    ],
                    [
                        'controllers' => ['admin/user'],
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['userDelete'],
                    ],
                    // Perfiles
                    [
                        'controllers' => ['admin/perfil'],
                        'actions' => ['index', 'perfiles-json-btt', 'view'],
                        'allow' => true,
                        'roles' => ['perfilUserView'],
                    ],
                    [
                        'controllers' => ['admin/perfil'],
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['perfilUserCreate'],
                    ],
                    [
                        'controllers' => ['admin/perfil'],
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['perfilUserUpdate'],
                    ],
                    [
                        'controllers' => ['admin/perfil'],
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['perfilUserDelete'],
                    ],
                    // Listas desplegables
                    [
                        'controllers' => ['admin/listas-desplegables'],
                        'actions'     => ['index', 'listas', 'items', 'tabla'],
                        'allow'       => true,
                        'roles'       => ['listaDesplegableView'],
                    ],
                    [
                        'controllers' => ['admin/listas-desplegables'],
                        'actions'     => ['create-ajax'],
                        'allow'       => true,
                        'roles'       => ['listaDesplegableCreate'],
                    ],
                    [
                        'controllers' => ['admin/listas-desplegables'],
                        'actions'     => ['update-ajax', 'sort-ajax'],
                        'allow'       => true,
                        'roles'       => ['listaDesplegableUpdate'],
                    ],
                    [
                        'controllers' => ['admin/listas-desplegables'],
                        'actions'     => ['delete-ajax'],
                        'allow'       => true,
                        'roles'       => ['listaDesplegableDelete'],
                    ],
                    // Configuraciones
                    [
                        'controllers' => ['admin/setting'],
                        'actions' => ['parametros', 'parametos-json-btt'],
                        'allow' => true,
                        'roles' => ['parametrosView'],
                    ],
                    [
                        'controllers' => ['admin/setting'],
                        'actions' => ['parametros-update'],
                        'allow' => true,
                        'roles' => ['parametrosUpdate'],
                    ],
                    // Configuraciones del sitio
                    [
                        'controllers' => ['admin/configuracion'],
                        'actions' => ['configuracion-update'],
                        'allow' => true,
                        'roles' => ['configuracionSitio'],
                    ],
                    [
                        'controllers' => ['admin/configuracion'],
                        'actions' => ['precio-libra-ajax'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    //Seach
                    [
                        'controllers' => ['admin/search'],
                        'actions' => ['index', 'seccion-file', 'accept-text', 'pendiente-text'],
                        'allow' => true,
                        'roles' => ['searchKeylogger'],
                    ],
                    [
                        'controllers' => ['admin/cliente-filter'],
                        'actions' => ['filtrado-cliente', 'view-cliente', 'filter-clientes-json-btt', 'aproved-similitud'],
                        'allow' => true,
                        'roles' => ['clienteDepuracion'],
                    ],
                    // Historial de acceso
                    [
                        'controllers' => ['admin/historial-de-acceso'],
                        'actions' => ['index', 'historial-de-accesos-json-btt'],
                        'allow' => true,
                        'roles' => ['historialAccesosUser'],
                    ],

                    [
                        'controllers' => ['admin/version'],
                        'actions' => ['list'],
                        'allow' => true,
                        'roles' => ['theCreator'],
                    ],

                    [
                        'controllers' => ['admin/masivo'],
                        'actions' => ['change'],
                        'allow' => true,
                        'roles' => ['theCreator'],
                    ],

                    [
                        'controllers' => ['descarga/bodega'],
                        'actions' => ['index', 'create', 'descarga-bodega-json-btt', 'delete'],
                        'allow' => true,
                        'roles' => ['theCreator'],
                    ],


                    /*************************
                     * Apertura y Cierre de caja
                     *************************/
                    // Clientes
                    [
                        'controllers' => ['caja/apertura-cierre'],
                        'actions' => ['create-apertura-ajax', 'info-caja-ajax', 'create-cierre-ajax', 'cliente-mex-ajax'],
                        'allow' => true,
                        'roles' => ['aperturaCierreCaja'],
                    ],
                    [
                        'controllers' => ['caja/apertura-cierre'],
                        'actions' => ['index', 'view', 'aperturas-cierres-json-btt', 'imprimir-ticket'],
                        'allow' => true,
                        // 'roles' => ['showAperturaCierre'],
                        'roles' => ['@'],
                    ],

                    /*************************
                     * Crm
                     *************************/
                    // Clientes
                    [
                        'controllers' => ['crm/cliente'],
                        'actions' => ['index', 'clientes-json-btt', 'view', 'historial-cambios', 'cliente-ajax', 'verifica-zona'],
                        'allow' => true,
                        'roles' => ['clienteView'],
                    ],
                    [
                        'controllers' => ['crm/cliente'],
                        'actions' => [
                            'create', 'cliente-create-ajax', 'import-csv', 'gra-vendedor-cliente-ajax', 'reporte-vendedor-cliente-ajax', 'promocion-create-basic-ajax',
                            'promocion-create-sucursal-ajax', 'promocion-info-ajax', 'promocion-sucursal-info-ajax',
                            'promocion-create-especial-ajax', 'cliente-codigo-ajax', 'reporte-vendedor-asignacion-ajax',
                            'verifica-zona'
                        ],
                        'allow' => true,
                        'roles' => ['clienteCreate'],
                    ],
                    [
                        'controllers' => ['crm/cliente'],
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['clienteUpdate'],
                    ],
                    [
                        'controllers' => ['crm/cliente'],
                        'actions' => ['update-agente', 'promocion-create-basic-ajax', 'promocion-sucursal-info-ajax', 'promocion-create-sucursal-ajax', 'promocion-create-especial-ajax', 'cliente-codigo-ajax'],
                        'allow' => true,
                        'roles' => ['agenteVenta'],
                    ],
                    [
                        'controllers' => ['crm/cliente'],
                        'actions' => ['historico-ventas-json-btt'],
                        'allow' => true,
                        'roles' => ['historicoCliente'],
                    ],

                    [
                        'controllers' => ['crm/cliente'],
                        'actions' => ['historico-promocion-json-btt'],
                        'allow' => true,
                        'roles' => ['historicoPromocion'],
                    ],

                    [
                        'controllers' => ['crm/cliente'],
                        'actions' => ['historico-sucursal-json-btt', 'historico-sucursal-view', 'sucursal-historico-json-btt'],
                        'allow' => true,
                        'roles' => ['historicoSucursal'],
                    ],

                    [
                        'controllers' => ['crm/cliente'],
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['clienteDelete'],
                    ],

                    /*************************
                     * Envios Tierra - Lax
                     *************************/
                    [
                        'controllers' => ['operacion/envio'],
                        'actions' => [
                            'index', 'view', 'imprimir-ticket', 'imprimir-ticket-comprimido', 'envios-json-btt',
                            'imprimir-etiqueta', 'show-direccion-paquete', 'envios-recibidos-json-btt',
                            'show-movimiento-paquete', 'update-direccion-all-ajax', 'update-direccion-ajax',
                            'update-cobro-ajax', 'convertir-envio', 'sucursal-update-ajax', 'imprimir-etiquetas-all',
                            'get-productos-caja'
                        ],
                        'allow' => true,
                        'roles' => ['envioBasicView'],
                    ],
                    [
                        'controllers' => ['operacion/envio'],
                        'actions' => [
                            'create', 'sucursal-info-ajax', 'sucursales-estado-ajax', 'productos-categoria-ajax', 'producto-info-ajax',
                            'promocion-info-ajax', 'promocion-valida-ajax', 'envio-detalle-ajax', 'code-promocion-ajax',
                            'promocion-especial-ajax', 'cobro-envio-ajax', 'categoria-ajax', 'send-producto-ajax',
                            'valoracion-historial-ajax', 'envio-promocion-manual-ajax', 'cliente-info-ajax',
                            'code-promocion-sucursal-ajax', 'esys-direccion-ajax', 'get-caja',
                            'sucursal-promos','cliente-zona'
                        ],
                        'allow' => true,
                        'roles' => ['envioBasicCreate'],
                    ],
                    [
                        'controllers' => ['operacion/envio'],
                        'actions' => ['update', 'sucursal-info-ajax', 'sucursales-estado-ajax', 'productos-categoria-ajax', 'producto-info-ajax', 'promocion-info-ajax', 'promocion-valida-ajax', 'envio-detalle-ajax', 'code-promocion-ajax', 'promocion-especial-ajax', 'cobro-envio-ajax', 'categoria-ajax', 'send-producto-ajax', 'valoracion-historial-ajax', 'envio-promocion-manual-ajax', 'cliente-info-ajax', 'code-promocion-sucursal-ajax', 'esys-direccion-ajax'],
                        'allow' => true,
                        'roles' => ['envioBasicUpdate'],
                    ],
                    [
                        'controllers' => ['operacion/envio'],
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['envioBasicDelete'],
                    ],
                    [
                        'controllers' => ['operacion/envio'],
                        'actions' => ['cancel'],
                        'allow' => true,
                        'roles' => ['envioBasicCancel'],
                    ],



                    [
                        'controllers' => ['movil/app'],
                        'actions' => ['pre-envio', 'code-promocion-sucursal-ajax', 'valoracion-historial-ajax', 'promocion-especial-ajax', 'sucursal-info-ajax', 'sucursales-estado-ajax', 'productos-categoria-ajax', 'producto-info-ajax', 'envio-detalle-ajax', 'categoria-ajax', 'valoracion-historial-ajax', 'cliente-info-ajax', 'esys-direccion-ajax', 'precio-libra-ajax', 'cliente-ajax', 'producto-lax-tierra-ajax', 'promocion-info-ajax', 'promocion-valida-ajax'],
                        'allow' => true,
                        'roles' => ['envioPrecapturaCreate'],
                    ],

                    [
                        'controllers' => ['movil/app'],
                        'actions' => ['update', 'esys-direccion-ajax', 'cobro-envio-ajax', 'envio-detalle-ajax', 'code-promocion-sucursal-ajax', 'valoracion-historial-ajax', 'promocion-especial-ajax', 'sucursal-info-ajax', 'sucursales-estado-ajax', 'productos-categoria-ajax', 'producto-info-ajax', 'envio-detalle-ajax', 'categoria-ajax', 'valoracion-historial-ajax', 'cliente-info-ajax', 'esys-direccion-ajax', 'precio-libra-ajax', 'cliente-ajax', 'producto-lax-tierra-ajax', 'promocion-info-ajax', 'promocion-valida-ajax'],
                        'allow' => true,
                        'roles' => ['envioPrecapturaUpdate'],
                    ],

                    [
                        'controllers' => ['movil/app'],
                        'actions' => ['index', 'view', 'imprimir-ticket'],
                        'allow' => true,
                        'roles' => ['envioPrecapturaView'],
                    ],
                    /*************************
                     * Envios Mex
                     *************************/
                    [
                        'controllers' => ['operacion/envio-mex'],
                        'actions' => ['index', 'view', 'imprimir-ticket', 'imprimir-etiqueta', 'envios-json-btt', 'create-ticket-ajax','imprimir-etiquetas-all'],
                        'allow' => true,
                        'roles' => ['envioMexView'],
                    ],
                    [
                        'controllers' => ['operacion/envio-mex'],
                        'actions' => ['create', 'sucursal-info-ajax', 'sucursales-estado-ajax', 'productos-categoria-ajax', 'producto-info-ajax', 'promocion-info-ajax', 'promocion-valida-ajax', 'envio-detalle-ajax', 'categoria-ajax', 'cliente-info-ajax', 'sucursales-usa-ajax', 'get-precio-libra', 'get-producto-mex', 'form-comentario-extra', 'send-producto-ajax'],
                        'allow' => true,
                        'roles' => ['envioMexCreate'],
                    ],
                    [
                        'controllers' => ['operacion/envio-mex'],
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['envioMexUpdate'],
                    ],
                    [
                        'controllers' => ['operacion/envio-mex'],
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['envioMexDelete'],
                    ],
                    [
                        'controllers' => ['operacion/envio-mex'],
                        'actions' => ['cancel'],
                        'allow' => true,
                        'roles' => ['envioMexCancel'],
                    ],

                    [
                        'controllers' => ['operacion/envio-mex'],
                        'actions' => ['send-contacto-seguimiendo'],
                        'allow' => true,
                        'roles' => ['seguimiento'],
                    ],

                    /*************************
                     * Cobro de Envios Mex
                     *************************/
                    [
                        'controllers' => ['operacion/envio-mex'],
                        'actions' => ['cobro-mex', 'update-envio', 'cobro-envio', 'cobro-reenvio', 'cobro-envio-ajax', 'send-rembolso'],
                        'allow' => true,
                        'roles' => ['envioMexCreate'],
                    ],


                    /*************************
                     * Promocion
                     *************************/
                    [
                        'controllers' => ['promociones/promocion'],
                        'actions' => ['index', 'promociones-json-btt', 'view'],
                        'allow' => true,
                        'roles' => ['promocionView'],
                    ],
                    [
                        'controllers' => ['promociones/promocion'],
                        'actions' => ['create', 'productos-categoria-ajax', 'promocion-info-ajax', 'promocion-detalle-complemento-ajax', 'update-promocion-ajax'],
                        'allow' => true,
                        'roles' => ['promocionCreate'],
                    ],
                    [
                        'controllers' => ['promociones/promocion', 'update-promocion-ajax'],
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['promocionUpdate'],
                    ],
                    [
                        'controllers' => ['promociones/promocion'],
                        'actions' => ['cancel'],
                        'allow' => true,
                        'roles' => ['promocionCancel'],
                    ],

                    /*************************
                     * Empaquetado Mex
                     *************************/
                    [
                        'controllers' => ['operacion/empaquetado'],
                        'actions' => ['index', 'view', 'imprimir-etiqueta', 'envios-json-btt', 'empaquetado-update'],
                        'allow' => true,
                        'roles' => ['empaquetado'],
                    ],

                    /*************************
                     * Caja Mex
                     *************************/
                    [
                        'controllers' => ['operacion/caja'],
                        'actions' => ['index', 'view', 'imprimir-etiqueta', 'producto-remove', 'cajas-json-btt'],
                        'allow' => true,
                        'roles' => ['cajaView'],
                    ],
                    [
                        'controllers' => ['operacion/caja'],
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['cajaCreate'],
                    ],
                    [
                        'controllers' => ['operacion/caja'],
                        'actions' => ['update', 'cerrar-caja'],
                        'allow' => true,
                        'roles' => ['cajaUpdate'],
                    ],
                    [
                        'controllers' => ['operacion/caja'],
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['cajaDelete'],
                    ],

                    /*******************************
                     * PAISES
                     *******************************/
                    [
                        'controllers' => ['paises/paises'],
                        'actions' => ['index', 'view', 'paises-json-btt'],
                        'allow' => true,
                        'roles' => ['paisView'],
                    ],
                    [
                        'controllers' => ['paises/paises'],
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['paisCreate'],
                    ],
                    [
                        'controllers' => ['paises/paises'],
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['paisUpdate'],
                    ],
                    [
                        'controllers' => ['paises/paises'],
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['paisDelete'],
                    ],
                    /*******************************
                     *      ZONAS ROJAS
                     *******************************/
                    [
                        'controllers' => ['zonas/zonas'],
                        'actions' => ['index', 'view', 'paises-json-btt', 'get-pais'],
                        'allow' => true,
                        'roles' => ['zonarojaView'],
                    ],
                    [
                        'controllers' => ['zonas/zonas'],
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['zonarojaCreate'],
                    ],
                    [
                        'controllers' => ['zonas/zonas'],
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['zonarojaUpdate'],
                    ],
                    [
                        'controllers' => ['zonas/zonas'],
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['zonarojaDelete'],
                    ],
                    /*******************************
                     *      PROMOCIONES
                     *******************************/
                    [
                        'controllers' => ['promocionessuc/promocionessuc'],
                        'actions' => ['index', 'view', 'paises-json-btt', 'get-pais'],
                        'allow' => true,
                        'roles' => ['promocionessucView'],
                    ],
                    [
                        'controllers' => ['promocionessuc/promocionessuc'],
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['promocionessucCreate'],
                    ],
                    [
                        'controllers' => ['promocionessuc/promocionessuc'],
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['promocionessucUpdate'],
                    ],
                    [
                        'controllers' => ['promocionessuc/promocionessuc'],
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['promocionessucDelete'],
                    ],

                    /*******************************
                     * Seguimiento
                     *******************************/
                    [
                        'controllers' => ['operacion/seguimiento'],
                        'actions' => ['index', 'view', 'escaneo'],
                        'allow' => true,
                        'roles' => ['seguimiento'],
                    ],


                    /*************************
                     * Escaneo por paquete
                     *************************/
                    [
                        'controllers' => ['operacion/escaneo-basic'],
                        'actions' => ['index', 'escaneo-paquete', 'movimiento-paquete', 'movimiento-caja'],
                        'allow' => true,
                        'roles' => ['escaneoBasic'],
                    ],
                    [
                        'controllers' => ['operacion/escaneo-lote'],
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['escaneoLote'],
                    ],

                    /*************************
                     * Tickets
                     *************************/
                    [
                        'controllers' => ['operacion/ticket'],
                        'actions' => ['index', 'index-proyectos', 'index-productos', 'index-clientes', 'view', 'tickets-json-btt', 'productos-json-btt', 'proyectos-json-btt', 'clientes-json-btt', 'create-rembolso', 'send-rembolso', 'imprimir-cobro', 'promocion-create-especial-ajax'],
                        'allow' => true,
                        'roles' => ['ticketView'],
                    ],
                    
                     [
                        'controllers' => ['operacion/ticket'],
                        'actions' => ['index', 'index-proyectos', 'index-productos', 'index-clientes', 'view', 'tickets-json-btt', 'productos-json-btt', 'proyectos-json-btt', 'clientes-json-btt', 'create-rembolso', 'send-rembolso', 'imprimir-cobro', 'promocion-create-especial-ajax'],
                        'allow' => true,
                        'roles' => ['userView'],
                    ],
                    
                     [
                        'controllers' => ['operacion/ticket'],
                        'actions' => ['index', 'index-proyectos', 'index-productos', 'index-clientes', 'view', 'tickets-json-btt', 'productos-json-btt', 'proyectos-json-btt', 'clientes-json-btt', 'create-rembolso', 'send-rembolso', 'imprimir-cobro', 'promocion-create-especial-ajax'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],

                    [
                        'controllers' => ['operacion/ticket'],
                        'actions' => ['create', 'get-productos-proy', 'get-proyectos-cliente','create-proyecto', 'create-producto', 'create-cliente', 'index-proyectos', 'index-productos', 'search-envio-ajax', 'paquetes-list-ajax'],
                        'allow' => true,
                        'roles' => ['ticketCreate'],
                    ],
                    
                     [
                        'controllers' => ['operacion/ticket'],
                        'actions' => ['create', 'get-productos-proy', 'get-proyectos-cliente','create-proyecto', 'create-producto', 'create-cliente', 'index-proyectos', 'index-productos', 'search-envio-ajax', 'paquetes-list-ajax'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'controllers' => ['operacion/ticket'],
                        'actions' => ['update', 'update-proyecto', 'update-producto', 'update-cliente', 'editar-contactos', 'index-proyectos', 'index-productos', 'search-envio-ajax'],
                        'allow' => true,
                        'roles' => ['ticketUpdate'],
                    ],
                    [
                        'controllers' => ['operacion/ticket'],
                        'actions' => ['delete','delete-proyecto','delete-producto','delete-cliente'],
                        'allow' => true,
                        'roles' => ['ticketDelete'],
                    ],

                    /*******************************
                     * Logistica y seguimiento
                     *******************************/
                    /*************************
                     * Rutas
                     *************************/
                    [
                        'controllers' => ['logistica/ruta'],
                        'actions' => ['index', 'view', 'rutas-json-btt', 'valida-orden-ajax'],
                        'allow' => true,
                        'roles' => ['rutaView'],
                    ],
                    [
                        'controllers' => ['logistica/ruta'],
                        'actions' => ['create', 'sucursal-ruta-asignar-ajax', 'delete-sucursal'],
                        'allow' => true,
                        'roles' => ['rutaCreate'],
                    ],
                    [
                        'controllers' => ['logistica/ruta'],
                        'actions' => ['update', 'sucursal-ruta-asignar-ajax'],
                        'allow' => true,
                        'roles' => ['rutaUpdate'],
                    ],

                    [
                        'controllers' => ['logistica/ruta'],
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['rutaDelete'],
                    ],
                    /*************************
                     * MApeo
                     *************************/
                    [
                        'controllers' => ['logistica/mapeo'],
                        'actions' => ['index', 'view', 'mapeos-json-btt', 'reporte-mapeo-ajax', 'reporte-carga-unidades-ajax', 'reporte-planeacion'],
                        'allow' => true,
                        'roles' => ['mapeoView'],
                    ],
                    [
                        'controllers' => ['logistica/mapeo'],
                        'actions' => ['create', 'ruta-paquete-ajax'],
                        'allow' => true,
                        'roles' => ['mapeoCreate'],
                    ],
                    [
                        'controllers' => ['logistica/mapeo'],
                        'actions' => ['update', 'ruta-paquete-ajax'],
                        'allow' => true,
                        'roles' => ['mapeoUpdate'],
                    ],

                    [
                        'controllers' => ['logistica/mapeo'],
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['mapeoDelete'],
                    ],
                    /*************************
                     * Reparto
                     *************************/
                    [
                        'controllers' => ['logistica/reparto'],
                        'actions' => ['index', 'view', 'set-status-reparto', 'producto-remove', 'repartos-json-btt', 'reparto-add-paquete', 'reporte-reparto-ajax', 'facturas-reparto-ajax', 'reporte-hoja-ruta-excel', 'reporte-dowloand-reparto'],
                        'allow' => true,
                        'roles' => ['repartoView'],
                    ],
                    [
                        'controllers' => ['logistica/reparto'],
                        'actions' => ['create', 'load-ruta'],
                        'allow' => true,
                        'roles' => ['repartoCreate'],
                    ],
                    [
                        'controllers' => ['logistica/reparto'],
                        'actions' => ['update', 'load-ruta'],
                        'allow' => true,
                        'roles' => ['repartoUpdate'],
                    ],
                    [
                        'controllers' => ['logistica/reparto'],
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['repartoDelete'],
                    ],


                    /*************************
                     * Viajes
                     *************************/
                    /*************************
                     * Viajes Tierra
                     *************************/
                    [
                        'controllers' => ['logistica/viaje-tierra'],
                        'actions' => ['index', 'view', 'viajes-tierra-json-btt', 'reporte-viaje-ajax', 'reporte-viaje-verificacion-ajax', 'reporte-viaje-concilacion-ajax', 'reporte-viaje-reetiquetas-ajax', 'reporte-viaje-julio-ajax', 'imprimir-reetiquetas-pdf', 'reporte-viaje-check-list-ajax', 'denegar-paquete', 'close-etapa', 'aprobar-paquete', 'reporte-viaje-carga-ajax', 'reporte-administracion', 'reporte-entrada'],
                        'allow' => true,
                        'roles' => ['viajeTierraView'],
                    ],
                    [
                        'controllers' => ['logistica/viaje-tierra'],
                        'actions' => ['create', 'search-envio-ajax', 'info-envio-ajax', 'paquete-envio-ajax'],
                        'allow' => true,
                        'roles' => ['viajeTierraCreate'],
                    ],
                    [
                        'controllers' => ['logistica/viaje-tierra'],
                        'actions' => ['update', 'set-status-viaje', 'producto-remove'],
                        'allow' => true,
                        'roles' => ['viajeTierraUpdate'],
                    ],

                    [
                        'controllers' => ['logistica/viaje-tierra'],
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['viajeTierraDelete'],
                    ],

                    /*************************
                     * Viajes Lax
                     *************************/
                    [
                        'controllers' => ['logistica/viaje-lax'],
                        'actions' => ['index', 'view', 'viajes-lax-json-btt', 'reporte-viaje-ajax', 'reporte-viaje-julio-ajax', 'imprimir-reetiquetas-pdf'],
                        'allow' => true,
                        'roles' => ['viajeLaxView'],
                    ],
                    [
                        'controllers' => ['logistica/viaje-lax'],
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['viajeLaxCreate'],
                    ],
                    [
                        'controllers' => ['logistica/viaje-lax'],
                        'actions' => ['update', 'set-status-viaje', 'producto-remove'],
                        'allow' => true,
                        'roles' => ['viajeLaxUpdate'],
                    ],

                    [
                        'controllers' => ['logistica/viaje-lax'],
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['viajeLaxDelete'],
                    ],

                    /*************************
                     * Viajes Mex
                     *************************/
                    [
                        'controllers' => ['logistica/viaje-mex'],
                        'actions' => ['index', 'view', 'viajes-mex-json-btt', 'reporte-viaje-ajax'],
                        'allow' => true,
                        'roles' => ['viajeMexView'],
                    ],
                    [
                        'controllers' => ['logistica/viaje-mex'],
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['viajeMexCreate'],
                    ],
                    [
                        'controllers' => ['logistica/viaje-mex'],
                        'actions' => ['update', 'set-status-viaje', 'producto-remove'],
                        'allow' => true,
                        'roles' => ['viajeMexUpdate'],
                    ],

                    [
                        'controllers' => ['logistica/viaje-mex'],
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['viajeMexDelete'],
                    ],
                    /*************************
                     * Productos
                     *************************/
                    [
                        'controllers' => ['productos/producto'],
                        'actions' => ['index', 'productos-json-btt', 'view', 'producto-lax-tierra-ajax', 'reporte-viaje-concilacion-ajax', 'producto-tierra-ajax'],
                        'allow' => true,
                        'roles' => ['productoView'],
                    ],
                    [
                        'controllers' => ['productos/producto'],
                        'actions' => ['create', 'producto-detalle-ajax', 'categoria-ajax'],
                        'allow' => true,
                        'roles' => ['productoCreate'],
                    ],
                    [
                        'controllers' => ['productos/producto'],
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['productoUpdate'],
                    ],
                    [
                        'controllers' => ['productos/producto'],
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['productoDelete'],
                    ],


                    /*************************
                     * Reportes
                     *************************/

                    [
                        'controllers' => ['reportes/reporte'],
                        'actions' => ['cuentas', 'reporte-cuentas-sucursal', 'get-sucursal-paquete'],
                        'allow' => true,
                        'roles' => ['reporteCuenta'],
                    ],

                    [
                        'controllers' => ['reportes/reporte'],
                        'actions' => ['entrega', 'get-paquetes', 'get-paquete'],
                        'allow' => true,
                        'roles' => ['reporteEntrega'],
                    ],

                    [
                        'controllers' => ['reportes/reporte'],
                        'actions' => ['reporte-seguimiento', 'reporte-seguimiento-json-btt', 'reporte-csv-seguimiento'],
                        'allow' => true,
                        'roles' => ['reporteEntrega'],
                    ],


                    [
                        'controllers' => ['reportes/reporte'],
                        'actions' => ['comision', 'reporte-comision-json-btt'],
                        'allow' => true,
                        'roles' => ['reporteComision'],
                    ],


                    /*************************
                     * Pagos
                     *************************/
                    [
                        'controllers' => ['pagos/pago-gasto'],
                        'actions' => ['index', 'pagos-json-btt', 'view'],
                        'allow' => true,
                        'roles' => ['egresoView'],
                    ],
                    [
                        'controllers' => ['pagos/pago-gasto'],
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['egresoCreate'],
                    ],
                    [
                        'controllers' => ['pagos/pago-gasto'],
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['egresoDelete'],
                    ],




                    /*************************
                     * Sucursal
                     *************************/
                    [
                        'controllers' => ['sucursales/sucursal'],
                        'actions' => ['index', 'sucursales-json-btt', 'view', 'historial-cambios', 'imprimir-qr', 'save-precio-mx', 'save-impuesto-mx'],
                        'allow' => true,
                        'roles' => ['sucursalView'],
                    ],
                    [
                        'controllers' => ['sucursales/sucursal'],
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['sucursalCreate'],
                    ],
                    [
                        'controllers' => ['sucursales/sucursal'],
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['sucursalUpdate'],
                    ],
                    [
                        'controllers' => ['sucursales/sucursal'],
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['sucursalDelete'],
                    ],


                ], // rules
            ], // access
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout'      => ['post'],
                    'create-ajax' => ['post'],
                    'update-ajax' => ['post'],
                    'sort-ajax'   => ['put'],
                    'cancel-ajax' => ['post'],
                    'delete-ajax' => ['delete'],
                ],
            ], // verbs
        ]; // return
    } // behaviors

} // AppController
