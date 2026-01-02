<?php

namespace app\modules\operacion\controllers;

use app\models\cliente\ClienteCodigoPromocion;
use app\models\cobro\CobroRembolsoEnvio;
use app\models\envio\Envio;
use app\models\envio\ViewEnvio;
use app\models\ticket\ClienteRazonSocial;
use app\models\ticket\ContactosCliente;
use app\models\ticket\Productos;
use app\models\ticket\Proyectos;
use app\models\ticket\Ticket;
use app\models\ticket\TicketDetalle;
use app\models\ticket\ViewTicket;
use app\models\user\User;
use app\models\ticket\Seguimiento;
use kartik\mpdf\Pdf;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * Default controller for the `operacion` module
 */
class TicketController extends \app\controllers\AppController
{

  private $can;

  public function init()
  {
    parent::init();

    $this->can = [
      'create' => Yii::$app->user->can('ticketCreate'),
      'update' => Yii::$app->user->can('ticketUpdate'),
      'delete' => Yii::$app->user->can('ticketDelete'),
    ];
  }

  /**
   * Renders the index view for the module
   * @return string
   */
  public function actionIndex()
  {
    return $this->render('index', [
      'can' => $this->can,
    ]);
  }

  public function actionIndexProyectos()
  {
    return $this->render('index_proyectos', [
      'can' => $this->can,
    ]);
  }

  public function actionIndexProductos()
  {
    return $this->render('index_productos', [
      'can' => $this->can,
    ]);
  }

  public function actionIndexClientes()
  {
    return $this->render('index_clientes', [
      'can' => $this->can,
    ]);
  }

  public function actionCreate()
  {
    $model                 = new Ticket();
    $model->ticket_detalle = new TicketDetalle();

    $proyecto        = Proyectos::getId($model->proyecto);
    $model->proyecto = $proyecto;

    if ($model->load(Yii::$app->request->post()) && $model->ticket_detalle->load(Yii::$app->request->post())) {
      $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
      $caracteres .= "1234567890";
      $clave                         = "";
      $longitud                      = 6;
      $model->ticket_evidencia_array = null;
      $model->tipo_id                = "2690";

      for ($i = 0; $i < $longitud; $i++) {
        $clave .= $caracteres[rand(0, strlen($caracteres) - 1)];
      }

      $model->clave    = $clave;
      $val             = Yii::$app->request->post();
      $model->producto = $val['producto_text'];
      $model->proyecto = $val['proyecto_text'];

      if (! $model->status) {
        $model->status = Ticket::STATUS_ACTIVE;
      }

      $model->ticket_evidencia_array = UploadedFile::getInstances($model, 'ticket_evidencia_array');

      if ($model->save()) {
        $postData = Yii::$app->request->post();

        $empresa = ClienteRazonSocial::getNombre($postData['Ticket']['cliente_id']);
        if ($postData['Ticket']['asignado']) {
          $ado = User::getNombre($postData['Ticket']['asignado']);
        }
        $creador = User::getNombre($postData['user_id']);

        $dateTime = new \DateTime('now', new \DateTimeZone('America/Mexico_City'));
        $dateTime->modify('-1 hour');
        $fechaCorreo = $dateTime->format('d-m-Y H:i:s');


        $ticketInfo = '
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:auto;background:#fff;font-family:Arial,sans-serif;border-radius:10px;overflow:hidden;box-shadow:0 4px 12px rgba(32,36,62,.12)">
                    <tr>
                        <td style="background:#001142;padding:24px 0;text-align:center">
                            <h2 style="color:#fff;font-size:28px;margin:0;">NUEVO TICKET REGISTRADO</h2>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 24px;">
                            <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Fecha y Hora:</strong> ' . $fechaCorreo . '</p>
                            <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Clasificación:</strong> ' . ($postData['Ticket']['clasificacion'] ?? 'N/A') . '</p>
                            <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Cliente:</strong> ' . ($empresa['nombre'] ?? 'N/A') . '</p>
                            <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Proyecto:</strong> ' . ($postData['proyecto_text'] ?? 'N/A') . '</p>
                            <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Producto:</strong> ' . ($postData['producto_text'] ?? 'N/A') . '</p>
                            <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Descripción:</strong> ' . ($postData['Ticket']['descripcion'] ?? 'N/A') . '</p>
                            ' . (isset($ado) ? '
                                <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Asignado a:</strong> ' . (isset($ado['nombre']) && isset($ado['apellidos']) ? $ado['nombre'] . ' ' . $ado['apellidos'] : 'N/A') . '</p>
                            ' : '') . '
                            <div style="margin-top:40px;text-align:center">
                                <a href="https://dev.tickets.lercomx.com/" style="background:#001142;color:#fff;padding:12px 36px;text-decoration:none;border-radius:8px;font-size:18px;font-weight:bold;display:inline-block;letter-spacing:1px;">Ver Ticket Completo</a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f7f9fa;padding:16px;text-align:center;color:#888;font-size:13px;">
                            Este es un mensaje automático, por favor no responda directamente a este correo.
                        </td>
                    </tr>
                </table>
                ';

        $destinatarios = ['manuel.castillo.lerco@gmail.com', 'brenda.rojas.lerco@gmail.com', 'alvaradolunadaniela7@gmail.com', 'josafath@lerco.mx', 'samantha.velez.lerco@gmail.com']; // Agregar el correo fijo

        if (isset($ado) && ! empty($ado['email'])) {
          $destinatarios[] = $ado['email'];
        }

        if (! empty($creador['email'])) {
          $destinatarios[] = $creador['email'];
        }

        // **Enviar el correo solo si hay destinatarios**
        if (! empty($destinatarios)) {
          Yii::$app->mailer->compose()
            ->setFrom('tickets@lercomx.com')
            ->setTo(array_values($destinatarios))
            ->setSubject('NUEVO TICKET REGISTRADO ' . $fechaCorreo)
            ->setHtmlBody($ticketInfo) // Cambiado de setTextBody a setHtmlBody
            ->send();
        } else {
          Yii::warning("No se envió el correo porque no hay destinatarios válidos.", 'email');
        }

        Yii::$app->session->setFlash('ticket_creado', true);
        if ($model->ticket_detalle->trickend_detalle_array_save($model->id)) {
          return $this->redirect(['index']);
        }
      }
    }
    return $this->render('create', [
      'model' => $model,
    ]);
  }

  public function actionCreateProyecto()
  {
    $model = new Proyectos();

    if ($model->load(Yii::$app->request->post())) {

      $productos = Yii::$app->request->post('productos', []);

      // Puedes realizar alguna acción con los productos seleccionados si es necesario
      $model->productos = json_encode($productos); // Aquí se convierte el array a JSON

      /* echo "<pre>";
            print_r($model->productos);
            die(); */

      if ($model->save()) {
        Yii::$app->session->setFlash('success', "Proyecto guardado exitosamente.");
        return $this->redirect(['index-proyectos']);
      }
    }
    return $this->render('update_proyecto', [
      'model' => $model,
    ]);
  }

  public function actionCreateProducto()
  {
    $model = new Productos();

    if ($model->load(Yii::$app->request->post())) {

      if ($model->save()) {
        Yii::$app->session->setFlash('success', "Producto guardado exitosamente");
        return $this->redirect(['index-productos']);
      } else {
        // Obtener los errores de validación y convertirlos en un mensaje
        $errorMessage = implode(', ', $model->errors ? array_map(function ($errors) {
          return implode(' ', $errors);
        }, $model->errors) : []);

        Yii::$app->session->setFlash('error', "No se pudo guardar el proyecto. Razón: " . $errorMessage);
      }
    }
    return $this->render('update_producto', [
      'model' => $model,
    ]);
  }

  public function actionCreateCliente()
  {
    $model     = new ClienteRazonSocial();
    $contactos = []; // Aquí no se necesita cambiar nada en este código

    if ($model->load(Yii::$app->request->post())) {
      $proyectos        = Yii::$app->request->post('proyectos', []);
      $model->proyectos = json_encode($proyectos); // Convertir a JSON si es necesario

      $productos        = Yii::$app->request->post('productos', []);
      $model->productos = json_encode($productos); // Convertir a JSON si es necesario

      if ($model->save()) {
        // Ahora que el cliente está guardado, podemos acceder a su ID
        $clienteId = $model->id;

        // Llamamos a la función que guarda los contactos asociados a este cliente
        $contactos = Yii::$app->request->post('contactos', []);

        // Estructurar los datos para que queden dentro de un arreglo 'contactos'
        $contactos = ['contactos' => $contactos];

        // Verificar si el arreglo 'contactos' no está vacío
        if (! empty($contactos['contactos'])) {
          $this->guardarContactos($contactos, $clienteId); // Guardar los contactos con el ID del cliente
        }

        Yii::$app->session->setFlash('success', "Cliente y contactos guardados exitosamente.");
        return $this->redirect(['index-clientes']);
      }
    }

    return $this->render('update_cliente', [
      'model'     => $model,
      'contactos' => $contactos,
    ]);
  }

  public function actionView($id)
  {
    /*return $this->render('view', [
            'model' => $this->findModel($id),
            'can'   => $this->can,
        ]);*/
    $model = $this->findModel($id);

    if (!$model) {
      // Si el ticket fue borrado por inactividad, ya hay flash (por beforeAction)
      Yii::$app->session->setFlash('warning', 'El ticket fue eliminado automáticamente por inactividad de 24 horas.');
      return $this->redirect(['index']);
    }

    return $this->render('view', [
      'model' => $model,
      'can'   => $this->can,
    ]);
  }


  // funcion encargada de mandar los correos al actualizar el estatus de un ticket
  public function actionUpdate($id)
  {
    $model = $this->findModel($id);
    $model->ticket_detalle = new TicketDetalle();

    if ($model->load(Yii::$app->request->post())) {
      $model->ticket_evidencia_array = UploadedFile::getInstances($model, 'ticket_evidencia_array');
      $val = Yii::$app->request->post();

      $model->producto = $val['producto_text'];
      $model->proyecto = $val['proyecto_text'];

      if ($model->save()) {
        // FECHA AJUSTADA
        $dateTime = new \DateTime('now', new \DateTimeZone('America/Mexico_City'));
        $dateTime->modify('-1 hour');
        $fechaCorreo = $dateTime->format('d-m-Y H:i:s');

        // INFO DE CLIENTE Y USUARIOS
        $cliente      = ClienteRazonSocial::findOne($model->cliente_id);
        $empresa      = $cliente ? $cliente->nombre : 'N/A';
        $creador      = User::findOne($model->created_by);
        $emailCreador = $creador ? $creador->email : null;
        $postData     = Yii::$app->request->post();

        $asignado = null;
        if (!empty($postData['Ticket']['asignado'])) {
          $asignado = User::findOne($postData['Ticket']['asignado']);
        }

        // CORREO PRINCIPAL DE ACTUALIZACIÓN
        $ticketInfo = '
            <table width="100%" ...>
                <tr>
                    <td style="background:#1648a0;padding:24px 0;text-align:center">
                        <h2 style="color:#fff;font-size:28px;margin:0;">ACTUALIZACIÓN DE TICKET #' . $model->id . '</h2>
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px 24px;">
                        <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Fecha y Hora:</strong> ' . $fechaCorreo . '</p>
                        <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Clasificación:</strong> ' . ($postData['Ticket']['clasificacion'] ?? 'N/A') . '</p>
                        <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Cliente:</strong> ' . $empresa . '</p>
                        <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Proyecto:</strong> ' . ($postData['proyecto_text'] ?? 'N/A') . '</p>
                        <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Producto:</strong> ' . ($postData['producto_text'] ?? 'N/A') . '</p>
                        ' . ($asignado ? '<p style="font-size:16px;color:#222;margin:8px 0;"><strong>Asignado a:</strong> ' . $asignado->nombre . ' ' . $asignado->apellidos . '</p>' : '') . '
                        <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Nota de actualización:</strong> ' . ($model->nota ?? 'Sin respuesta') . '</p>
                        <div style="margin-top:40px;text-align:center">
                            <a href="https://dev.tickets.lercomx.com/" style="background:#1648a0;color:#fff;padding:12px 36px;text-decoration:none;border-radius:8px;font-size:18px;font-weight:bold;display:inline-block;letter-spacing:1px;">Ver Ticket Completo</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f7f9fa;padding:16px;text-align:center;color:#888;font-size:13px;">
                        Este es un mensaje automático, por favor no responda directamente a este correo.<br>
                        &copy; ' . date('Y') . ' Sistema de Tickets - Lercomx. Todos los derechos reservados.
                    </td>
                </tr>
            </table>
            ';

        // CORREO ESPECIAL PARA ASIGNADO 
        $asignacionHtml = '
            <table width="100%" ...>
                <tr>
                    <td style="background:#27ae60;padding:24px 0;text-align:center">
                        <h2 style="color:#fff;font-size:28px;margin:0;">¡Nuevo Ticket Asignado!</h2>
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px 24px;">
                        <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Fecha y Hora:</strong> ' . $fechaCorreo . '</p>
                        <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Cliente:</strong> ' . $empresa . '</p>
                        <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Proyecto:</strong> ' . ($postData['proyecto_text'] ?? 'N/A') . '</p>
                        <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Descripción:</strong> ' . ($postData['Ticket']['descripcion'] ?? 'N/A') . '</p>
                        <p style="font-size:16px;color:#222;margin:8px 0;">Has sido asignado como responsable de este ticket. Por favor revisa los detalles y actualiza el estado según corresponda.</p>
                        <div style="margin-top:40px;text-align:center">
                            <a href="https://dev.tickets.lercomx.com/" style="background:#27ae60;color:#fff;padding:12px 36px;text-decoration:none;border-radius:8px;font-size:18px;font-weight:bold;display:inline-block;letter-spacing:1px;">Ver Ticket Completo</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f7f9fa;padding:16px;text-align:center;color:#888;font-size:13px;">
                        Este es un mensaje automático, por favor no responda directamente a este correo.<br>
                        &copy; ' . date('Y') . ' Sistema de Tickets - Lercomx. Todos los derechos reservados.
                    </td>
                </tr>
            </table>
            ';

        // CORREO ESPECIAL PARA ESPERA DE PRUEBAS
        $statusEsperaHtml = '
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:auto;background:#fff;font-family:Arial,sans-serif;border-radius:10px;overflow:hidden;box-shadow:0 4px 12px rgba(252,185,62,.12)">
    <tr>
        <td style="background:#f39c12;padding:24px 0;text-align:center">
            <h2 style="color:#fff;font-size:28px;margin:0;">⏳ TICKET EN ESPERA DE PRUEBAS #' . $model->id . '</h2>
        </td>
    </tr>
    <tr>
        <td style="padding:32px 24px;">
            <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Fecha y Hora:</strong> ' . $fechaCorreo . '</p>
            <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Cliente:</strong> ' . $empresa . '</p>
            <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Proyecto:</strong> ' . ($postData['proyecto_text'] ?? 'N/A') . '</p>
            <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Producto:</strong> ' . ($postData['producto_text'] ?? 'N/A') . '</p>
            <p style="font-size:16px;color:#222;margin:8px 0;">
                El ticket ya se encuentra en su entorno. Estamos en <strong>espera de pruebas</strong> para continuar.<br>
                Por favor responde a este correo o entra al sistema para dar seguimiento.
            </p>
            <div style="margin-top:40px;text-align:center">
                <a href="https://dev.tickets.lercomx.com/"
                   style="background:#f39c12;color:#fff;padding:12px 36px;text-decoration:none;border-radius:8px;font-size:18px;font-weight:bold;display:inline-block;letter-spacing:1px;">
                    Ver Ticket
                </a>
            </div>
        </td>
    </tr>
    <tr>
        <td style="background:#f7f9fa;padding:16px;text-align:center;color:#888;font-size:13px;">
            Este es un mensaje automático, por favor no responda directamente a este correo.<br>
            &copy; ' . date('Y') . ' Sistema de Tickets - Lercomx. Todos los derechos reservados.
        </td>
    </tr>
</table>
';


        // CORREO ESPECIAL PARA TICKET CERRADO
        $statusCerradoHtml = '
            <table width="100%" ...>
                <tr>
                    <td style="background:#27ae60;padding:24px 0;text-align:center">
                        <h2 style="color:#fff;font-size:28px;margin:0;">✅ TICKET CERRADO #' . $model->id . '</h2>
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px 24px;">
                        <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Fecha y Hora de Cierre:</strong> ' . $fechaCorreo . '</p>
                        <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Cliente:</strong> ' . $empresa . '</p>
                        <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Proyecto:</strong> ' . ($postData['proyecto_text'] ?? 'N/A') . '</p>
                        <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Producto:</strong> ' . ($postData['producto_text'] ?? 'N/A') . '</p>
                        <p style="font-size:16px;color:#222;margin:8px 0;"><strong>Descripción:</strong> ' . ($postData['Ticket']['descripcion'] ?? 'N/A') . '</p>
                        <p style="font-size:16px;color:#222;margin:8px 0;">
                            El ticket ha sido <strong>cerrado</strong> satisfactoriamente.<br>
                            Si necesitas más ayuda, puedes generar un nuevo ticket desde el sistema.
                        </p>
                        <div style="margin-top:40px;text-align:center">
                            <a href="https://dev.tickets.lercomx.com/"
                            style="background:#27ae60;color:#fff;padding:12px 36px;text-decoration:none;border-radius:8px;font-size:18px;font-weight:bold;display:inline-block;letter-spacing:1px;">
                                Ver Ticket
                            </a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f7f9fa;padding:16px;text-align:center;color:#888;font-size:13px;">
                        Este es un mensaje automático, por favor no responda directamente a este correo.<br>
                        &copy; ' . date('Y') . ' Sistema de Tickets - Lercomx. Todos los derechos reservados.
                    </td>
                </tr>
            </table>
            ';

        // --- ENVÍO DE CORREO PRINCIPAL ---
        $destinatarios = ['josafath@lerco.mx', 'brenda.rojas.lerco@gmail.com', 'alvaradolunadaniela7@gmail.com'];

        if ($emailCreador) $destinatarios[] = $emailCreador;
        if ($asignado && !empty($asignado->email)) $destinatarios[] = $asignado->email;
        $destinatariosUnicos = array_unique($destinatarios);

        if (!empty($destinatariosUnicos)) {
          Yii::$app->mailer->compose()
            ->setFrom('tickets@lercomx.com')
            ->setTo($destinatariosUnicos)
            ->setSubject('ACTUALIZACIÓN DE TICKET #' . $model->id . ' | ' . $fechaCorreo)
            ->setHtmlBody($ticketInfo)
            ->send();
        }

        // --- ENVÍO DE CORREO ESPECIAL AL ASIGNADO ---
        if ($asignado && !empty($asignado->email) && $asignado->email != $emailCreador) {
          Yii::$app->mailer->compose()
            ->setFrom('tickets@lercomx.com')
            ->setTo($asignado->email)
            ->setSubject('NUEVO TICKET ASIGNADO #' . $model->id . ' | ' . $fechaCorreo)
            ->setHtmlBody($asignacionHtml)
            ->send();
        }

        // --- NOTIFICACIONES DE CAMBIO DE STATUS AL CREADOR ---
        if ($emailCreador) {
          if ((int)$model->status === 30) { // En espera de pruebas
            Yii::$app->mailer->compose()
              ->setFrom('tickets@lercomx.com')
              ->setTo($emailCreador)
              ->setSubject('TICKET EN ESPERA DE PRUEBAS #' . $model->id . ' | ' . $fechaCorreo)
              ->setHtmlBody($statusEsperaHtml)
              ->send();
          } elseif ((int)$model->status === 40) { // Cerrado
            Yii::$app->mailer->compose()
              ->setFrom('tickets@lercomx.com')
              ->setTo($emailCreador)
              ->setSubject('TICKET CERRADO #' . $model->id . ' | ' . $fechaCorreo)
              ->setHtmlBody($statusCerradoHtml)
              ->send();
          }
        }

        return $this->redirect(['view', 'id' => $model->id]);
      }
    }

    return $this->render('update', [
      'model' => $model,
    ]);
  }


  public function actionUploadEvidencia($id)
  {
    $model = Ticket::findOne($id);
    $file = UploadedFile::getInstanceByName('archivo');

    if ($file) {
      $filename = uniqid() . '.' . $file->extension;
      $path = Yii::getAlias('@webroot') . '/uploads/tickets/' . $filename;

      if ($file->saveAs($path)) {
        $lista = $model->ticket_evidencia ? json_decode($model->ticket_evidencia, true) : [];
        $lista[] = [$filename => $filename];
        $model->ticket_evidencia = json_encode($lista);
        $model->save(false);
      }
    }

    return $this->redirect(['view', 'id' => $id]);
  }


  public function actionUpdateProducto($id)
  {
    $model = Productos::findOne($id);

    // Si no se enviaron datos POST o no pasa la validación, cargamos formulario
    if ($model->load(Yii::$app->request->post())) {
      if ($model->save()) {
        Yii::$app->session->setFlash('success', "Cambios guardados en el producto: " . $id);
        return $this->redirect(['index-productos']);
      }
    }
    return $this->render('update_producto', [
      'model' => $model,
    ]);
  }

  public function actionUpdateProyecto($id)
  {
    // Cargar el modelo de Proyecto por su ID
    $model = Proyectos::findOne($id);

    // Si no se encontró el proyecto
    if (! $model) {
      Yii::$app->session->setFlash('error', 'El proyecto no existe.');
      return $this->redirect(['index-proyectos']);
    }

    // Si se enviaron datos por POST (como el formulario del proyecto)
    if ($model->load(Yii::$app->request->post())) {

      // Obtener los productos seleccionados del formulario
      $productos = Yii::$app->request->post('productos', []);

      // Convertir el array de productos a JSON y actualizar el modelo
      $model->productos = json_encode($productos); // Guardar como JSON

      // Guardar el modelo de proyecto con los productos actualizados
      if ($model->save()) {
        Yii::$app->session->setFlash('success', "Cambios guardados en el proyecto: " . $id);
        return $this->redirect(['index-proyectos']);
      } else {
        Yii::$app->session->setFlash('error', "Hubo un error al guardar el proyecto.");
      }
    }

    // Renderizar el formulario de actualización
    return $this->render('update_proyecto', [
      'model' => $model,
    ]);
  }

  public function actionUpdateCliente($id)
  {
    // Cargar el modelo de Proyecto por su ID
    $model = ClienteRazonSocial::findOne($id);

    $contactos = ContactosCliente::find()->where(['user_id' => $id])->all();
    // Si no se encontró el proyecto
    if (! $model) {
      Yii::$app->session->setFlash('error', 'El proyecto no existe.');
      return $this->redirect(['index-clientes']);
    }

    // Si se enviaron datos por POST (como el formulario del proyecto)
    if ($model->load(Yii::$app->request->post())) {

      $proyectos = Yii::$app->request->post('proyectos', []);

      // Puedes realizar alguna acción con los productos seleccionados si es necesario
      $model->proyectos = json_encode($proyectos); // Aquí se convierte el array a JSON

      $productos = Yii::$app->request->post('productos', []);

      // Puedes realizar alguna acción con los productos seleccionados si es necesario
      $model->productos = json_encode($productos); // Aquí se convierte el array a JSON

      // Guardar el modelo de proyecto con los productos actualizados
      if ($model->save()) {
        Yii::$app->session->setFlash('success', "Cambios guardados en el proyecto: " . $id);
        return $this->redirect(['index-clientes']);
      } else {
        Yii::$app->session->setFlash('error', "Hubo un error al guardar el proyecto.");
      }
    }

    return $this->render('update_cliente', [
      'model'     => $model,
      'contactos' => $contactos,
    ]);
  }

  public function actionEditarContactos($id)
  {
    if (Yii::$app->request->isAjax) {
      $postData = Yii::$app->request->post();

      /*Verificar si el arreglo de contactos está vacío
            if (empty($postData['contactos'])) {
            
            }else{
                
            // Si el arreglo no está vacío, proceder a guardar los contactos
            $this->guardarContactos($postData, $id);

            } */
      $this->guardarContactos($postData, $id);
    }
  }

  private function guardarContactos($data, $userId)
  {
    // Eliminar los contactos anteriores asociados al usuario
    ContactosCliente::deleteAll(['user_id' => $userId]);

    if (empty($data['contactos'])) {
      ContactosCliente::deleteAll(['user_id' => $userId]);
    } else {
      // Guardar o actualizar los contactos del cliente
      foreach ($data['contactos'] as $contacto) {

        // Verificar si existe un contacto con este ID
        if (isset($contacto['id']) && $contacto['id'] != '') {
          // Actualizar (esto no será necesario debido a la eliminación previa)
          $contactoCliente = ContactosCliente::findOne($contacto['id']);
        } else {
          // Crear nuevo contacto
          $contactoCliente          = new ContactosCliente();
          $contactoCliente->user_id = $userId;
        }

        // Asignar los valores de cada contacto
        $contactoCliente->nombre         = $contacto['nombre'];
        $contactoCliente->apellidos      = $contacto['apellidos'];
        $contactoCliente->email          = $contacto['email'];
        $contactoCliente->telefono       = $contacto['telefono'];
        $contactoCliente->user_id        = $userId;
        $contactoCliente->fecha_register = time(); // Establecer la fecha de registro

        // Guardar el contacto
        $contactoCliente->save();
      }
    }
  }

  public function actionGetProyectosCliente__()
  {
    if (Yii::$app->request->isAjax) {
      // Obtener el ID del proyecto desde los parámetros GET
      $proyectoId = Yii::$app->request->get('proyectoId');
      $user       = User::getProyecto($proyectoId);

      $proyecto = ClienteRazonSocial::findOne($user);

      // Verificar si se encontró el proyecto
      if (! $proyecto) {
        return json_encode(['error' => 'Proyecto no encontrado']);
      }

      // Obtener el campo 'productos', que es un JSON con los IDs de los productos
      $proyectosJson           = $proyecto->proyectos;              // Suponiendo que el campo 'productos' es un JSON con los IDs
      $prooyectosSeleccionados = json_decode($productosJson, true); // Decodificamos el JSON a un array

      $productosJson          = $proyecto->productos;              // Suponiendo que el campo 'productos' es un JSON con los IDs
      $productosSeleccionados = json_decode($productosJson, true); // Decodificamos el JSON a un array

      if (json_last_error() !== JSON_ERROR_NONE) {
        return json_encode(['error' => 'El JSON de productos no es válido']);
      }

      // Obtener los productos relacionados con los IDs seleccionados
      $proyectos = Proyectos::find()
        ->where(['id' => $proyectosSeleccionados]) // Filtrar por los productos seleccionados
        ->select(['id', 'nombre'])                 // Solo obtener los campos id y nombre
        ->indexBy('id')                            // Usar el ID del producto como clave
        ->asArray()                                // Obtener los resultados como un array
        ->all();                                   // Recuperar todos los productos seleccionados

      // Ahora, transformamos el resultado para que sea un array donde el ID es la clave y el nombre el valor
      $proyectosList = [];
      foreach ($proyectos as $proyecto) {
        $proyectosList[$proyecto['id']] = $proyecto['nombre'];
      }

      // Verificar si se encontraron productos
      if (empty($proyectosList)) {
        return json_encode(['error' => 'No se encontraron productos asignados al proyecto']);
      }

      // Obtener los productos relacionados con los IDs seleccionados
      $productos = Productos::find()
        ->where(['id' => $productosSeleccionados]) // Filtrar por los productos seleccionados
        ->select(['id', 'nombre'])                 // Solo obtener los campos id y nombre
        ->indexBy('id')                            // Usar el ID del producto como clave
        ->asArray()                                // Obtener los resultados como un array
        ->all();                                   // Recuperar todos los productos seleccionados

      // Ahora, transformamos el resultado para que sea un array donde el ID es la clave y el nombre el valor
      $productosList = [];
      foreach ($productos as $producto) {
        $productosList[$producto['id']] = $producto['nombre'];
      }

      // Verificar si se encontraron productos
      if (empty($proyectosList)) {
        return json_encode(['error' => 'No se encontraron proyectos asignados a la razon social del cliente']);
      }

      if (empty($productosList)) {
        return json_encode(['error' => 'No se encontraron productos asignados al proyecto']);
      }

      // Devolver los productos encontrados en formato JSON (una lista de nombres)
      return json_encode($productosList);
    }

    // Si no es una solicitud AJAX, retornar un error
    return json_encode(['error' => 'No es una solicitud AJAX válida']);
  }

  public function actionGetProyectosCliente()
  {
    if (Yii::$app->request->isAjax) {
      // Obtener el ID del proyecto desde los parámetros GET
      $proyectoId = Yii::$app->request->get('proyectoId');
      $user       = User::getProyecto($proyectoId);

      // Obtener el proyecto
      $proyecto = ClienteRazonSocial::findOne($proyectoId);
      //$proyecto = $proyectoId;

      // Verificar si se encontró el proyecto
      if (! $proyecto) {
        return json_encode(['error' => 'Usuario no asociado a cliente.']);
      }

      // Obtener los campos 'proyectos' y 'productos', que son JSON con los IDs
      $proyectosJson          = $proyecto->proyectos;              // Suponiendo que el campo 'proyectos' es un JSON con los IDs
      $proyectosSeleccionados = json_decode($proyectosJson, true); // Decodificamos el JSON a un array

      $productosJson          = $proyecto->productos;              // Suponiendo que el campo 'productos' es un JSON con los IDs
      $productosSeleccionados = json_decode($productosJson, true); // Decodificamos el JSON a un array

      // Verificar si el JSON es válido
      if (json_last_error() !== JSON_ERROR_NONE) {
        return json_encode(['error' => 'El JSON de productos o proyectos no es válido']);
      }

      // Obtener los proyectos relacionados con los IDs seleccionados
      $proyectos = Proyectos::find()
        ->where(['id' => $proyectosSeleccionados]) // Filtrar por los proyectos seleccionados
        ->select(['id', 'nombre'])                 // Solo obtener los campos id y nombre
        ->indexBy('id')                            // Usar el ID del proyecto como clave
        ->asArray()                                // Obtener los resultados como un array
        ->all();                                   // Recuperar todos los proyectos seleccionados

      // Transformamos el resultado para que sea un array donde el ID es la clave y el nombre el valor
      $proyectosList = [];
      foreach ($proyectos as $proyecto) {
        $proyectosList[$proyecto['id']] = $proyecto['nombre'];
      }

      // Verificar si se encontraron proyectos
      if (empty($proyectosList)) {
        $proyectosList = [];
      }

      // Obtener los productos relacionados con los IDs seleccionados
      $productos = Productos::find()
        ->where(['id' => $productosSeleccionados]) // Filtrar por los productos seleccionados
        ->select(['id', 'nombre'])                 // Solo obtener los campos id y nombre
        ->indexBy('id')                            // Usar el ID del producto como clave
        ->asArray()                                // Obtener los resultados como un array
        ->all();                                   // Recuperar todos los productos seleccionados

      // Transformamos el resultado para que sea un array donde el ID es la clave y el nombre el valor
      $productosList = [];
      foreach ($productos as $producto) {
        $productosList[$producto['id']] = $producto['nombre'];
      }

      // Verificar si se encontraron productos
      if (empty($productosList)) {
        $productosList = [];
      }

      // Buscar los contactos del cliente (tabla contactos_cliente) donde el user_id coincide con el cliente_razon_social del proyecto
      $contactos = ContactosCliente::find()
        //->where(['user_id' => $user->cliente_razon_social])  // Verificamos que el user_id del contacto coincida con el cliente_razon_social
        ->where(['user_id' => $proyectoId])
        ->all(); // Obtenemos todos los contactos que coinciden

      // Preparar la lista de contactos
      $contactosList = [];
      foreach ($contactos as $contacto) {
        $contactosList[] = [
          'id'        => $contacto->id,
          'nombre'    => $contacto->nombre,
          'apellidos' => $contacto->apellidos,
          'email'     => $contacto->email,
          'telefono'  => $contacto->telefono,
        ];
      }

      if (empty($contactosList)) {
        $contactosList = [];
      }

      /*  $cliente_rs = ClienteRazonSocial::findOne($user);
        if ($cliente_rs) {
            $nombre = $cliente_rs['nombre'] ? $cliente_rs['nombre'] : 'USUARIO NO ASOCIADO A CLIENTE';
        } else {
            $nombre = 'USUARIO NO ASOCIADO A CLIENTE'; // Si el proyecto no existe, asignar este valor
        }*/

      // Devolver los productos, proyectos y contactos encontrados en formato JSON
      return json_encode([
        'productosList' => $productosList, // Productos disponibles
        'proyectosList' => $proyectosList, // Proyectos disponibles
        'contactosList' => $contactosList, // Contactos del cliente
        //'nombre' => $nombre
      ]);
    }
  }

  public function actionGetProductosProy()
  {
    if (Yii::$app->request->isAjax) {
      // Obtener el ID del proyecto desde los parámetros GET
      $proyectoId = Yii::$app->request->get('proyectoId');

      // Buscar el proyecto en la base de datos por su ID
      $proyecto = Proyectos::findOne($proyectoId);

      // Verificar si se encontró el proyecto
      if (! $proyecto) {
        return json_encode(['error' => 'Proyecto no encontrado']);
      }

      // Obtener el campo 'productos', que es un JSON con los IDs de los productos
      $productosJson = $proyecto->productos; // Suponiendo que el campo 'productos' es un JSON con los IDs

      // Decodificar el JSON para obtener los IDs de los productos
      $productosSeleccionados = json_decode($productosJson, true); // Decodificamos el JSON a un array

      // Verificar que el JSON se haya decodificado correctamente
      if (json_last_error() !== JSON_ERROR_NONE) {
        return json_encode(['error' => 'El JSON de productos no es válido']);
      }

      // Obtener los productos relacionados con los IDs seleccionados
      $productos = Productos::find()
        ->where(['id' => $productosSeleccionados]) // Filtrar por los productos seleccionados
        ->select(['id', 'nombre'])                 // Solo obtener los campos id y nombre
        ->indexBy('id')                            // Usar el ID del producto como clave
        ->asArray()                                // Obtener los resultados como un array
        ->all();                                   // Recuperar todos los productos seleccionados

      // Ahora, transformamos el resultado para que sea un array donde el ID es la clave y el nombre el valor
      $productosList = [];
      foreach ($productos as $producto) {
        $productosList[$producto['id']] = $producto['nombre'];
      }

      // Verificar si se encontraron productos
      if (empty($productosList)) {
        return json_encode(['error' => 'No se encontraron productos asignados al proyecto']);
      }

      // Devolver los productos encontrados en formato JSON (una lista de nombres)
      return json_encode($productosList);
    }

    // Si no es una solicitud AJAX, retornar un error
    return json_encode(['error' => 'No es una solicitud AJAX válida']);
  }

  public function actionCerrarCaja($id)
  {
    $model         = $this->findModel($id);
    $model->status = Ticket::STATUS_INACTIVE;

    $model->update();

    Yii::$app->session->setFlash('success', "Se ha cerrado la caja #" . $model->folio);

    return $this->redirect([
      'view',
      'id' => $model->id,
    ]);
  }

  public function actionDelete($id)
  {
    try {
      // Eliminamos el usuario
      $this->findModel($id)->delete();

      Yii::$app->session->setFlash('success', "Se ha eliminado correctamente el Ticket #" . $id);
    } catch (\Exception $e) {
      if ($e->getCode() === 23000) {
        Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden la eliminación del ticket.');

        header("HTTP/1.0 400 Relation Restriction");
      } else {
        throw $e;
      }
    }

    return $this->redirect(['index']);
  }

  public function actionDeleteProyecto($id)
  {
    try {
      // Eliminamos el usuario
      Proyectos::findOne($id)->delete();

      Yii::$app->session->setFlash('success', "Se ha eliminado correctamente el proyecto:" . $id);
    } catch (\Exception $e) {
      if ($e->getCode() === 23000) {
        Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden la eliminación.');

        header("HTTP/1.0 400 Relation Restriction");
      } else {
        throw $e;
      }
    }

    return $this->redirect(['index-proyectos']);
  }

  public function actionDeleteCliente($id)
  {
    try {
      // Eliminamos el usuario
      ClienteRazonSocial::findOne($id)->delete();

      Yii::$app->session->setFlash('success', "Se ha eliminado correctamente el proyecto:" . $id);
    } catch (\Exception $e) {
      if ($e->getCode() === 23000) {
        Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden la eliminación.');

        header("HTTP/1.0 400 Relation Restriction");
      } else {
        throw $e;
      }
    }

    return $this->redirect(['index-clientes']);
  }

  public function actionDeleteProducto($id)
  {
    try {
      // Eliminamos el usuario
      Productos::findOne($id)->delete();

      Yii::$app->session->setFlash('success', "Se ha eliminado correctamente el producto:" . $id);
    } catch (\Exception $e) {
      if ($e->getCode() === 23000) {
        Yii::$app->session->setFlash('danger', 'Existen dependencias que impiden la eliminación.');

        header("HTTP/1.0 400 Relation Restriction");
      } else {
        throw $e;
      }
    }

    return $this->redirect(['index-productos']);
  }

  public function actionCreateRembolso()
  {
    $request                    = Yii::$app->request;
    Yii::$app->response->format = Response::FORMAT_JSON;
    if ($request->validateCsrfToken() && $request->isAjax) {

      if (Yii::$app->request->post()["number_rembolso"] && Yii::$app->request->post()["rembolso_array"] && Yii::$app->request->post()["ticket_id"]) {
        $model = $this->findModel(Yii::$app->request->post()["ticket_id"]);

        $model->is_rembolso    = Ticket::REMBOLSO_ON;
        $model->num_rembolso   = Yii::$app->request->post()["number_rembolso"];
        $model->fecha_rembolso = Yii::$app->request->post()["rembolso_array"];
        if ($model->save()) {
          $response = ["code" => 202, "message" => "Se creo correctamente el rembolso "];
        } else {
          $response = ["code" => 20, "message" => "Ocurrio un error al crear el rembolso, intente nuevamente"];
        }
      } else {
        $response = ["code" => 30, "message" => "Error al crear rembolso, verifique su información"];
      }

      return $response;
    }
    throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
  }

  public function actionImprimirCobro($id, $envio_id, $ticket_id)
  {

    $model  = CobroRembolsoEnvio::findOne($id);
    $envio  = Envio::findOne($envio_id);
    $ticket = Ticket::findOne($ticket_id);

    $lengh = 370;
    $width = 72;

    $content = $this->renderPartial('_ticket_cobro', ["model" => $model, "envio" => $envio, "ticket" => $ticket]);

    $pdf = new Pdf([
      // set to use core fonts only
      'mode'        => Pdf::MODE_CORE,
      // A4 paper format
      'format'      => [$width, $lengh], //Pdf::FORMAT_A4,
      // portrait orientation
      'orientation' => Pdf::ORIENT_PORTRAIT,
      // stream to browser inline
      'destination' => Pdf::DEST_BROWSER,
      // your html content input
      'content'     => $content,
      // format content from your own css file if needed or use the
      // enhanced bootstrap css built by Krajee for mPDF formatting
      'cssFile'     => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
      // any css to be embedded if required
      'cssInline'   => '.kv-heading-1{font-size:18px}',
      // set mPDF properties on the fly
      'options'     => ['title' => 'Ticket de envio'],
      // call mPDF methods on the fly
      'methods'     => [
        'SetHeader' => ['Reembolso realizado ' . date('Y-m-d', $model->created_at)],
        //'SetFooter'=>['{PAGENO}'],
      ],
    ]);

    $pdf->marginLeft  = 3;
    $pdf->marginRight = 3;

    $pdf->setApi();

    /*$pdf_api = $pdf->getApi();
        $pdf_api->SetWatermarkImage(Yii::getAlias('@web').'/img/marca_agua_cora.png');
        $pdf_api->showWatermarkImage = true;*/

    // return the pdf output as per the destination setting
    return $pdf->render();
  }

  public function actionTicketsJsonBtt()
  {
    return ViewTicket::getJsonBtt(Yii::$app->request->get());
  }

  public function actionProyectosJsonBtt()
  {
    return Proyectos::getJsonBtt(Yii::$app->request->get());
  }

  public function actionProductosJsonBtt()
  {
    return Productos::getJsonBtt(Yii::$app->request->get());
  }

  public function actionClientesJsonBtt()
  {
    return ClienteRazonSocial::getJsonBtt(Yii::$app->request->get());
  }

  public function actionSearchEnvioAjax()
  {
    return ViewEnvio::getJsonBtt(Yii::$app->request->get());
  }

  public function actionPaquetesListAjax()
  {
    return ViewEnvio::getEnvioDetalleAjax(Yii::$app->request->get());
  }

  public function actionSendRembolso()
  {
    Yii::$app->response->format = Response::FORMAT_JSON;

    if ($response = Yii::$app->request->post()) {

      $CobroRembolsoEnvio                 = new CobroRembolsoEnvio();
      $CobroRembolsoEnvio->envio_id       = $response["envio_id"];
      $CobroRembolsoEnvio->tipo           = CobroRembolsoEnvio::TIPO_DEVOLUCION;
      $CobroRembolsoEnvio->metodo_pago    = CobroRembolsoEnvio::COBRO_EFECTIVO;
      $CobroRembolsoEnvio->cantidad       = $response["rembolso"];
      $CobroRembolsoEnvio->ticket_item_id = $response["item_id"];
      $CobroRembolsoEnvio->nota           = $response["comentario"];
      if ($CobroRembolsoEnvio->save()) {
        return [
          'code'    => 202,
          'message' => 'Se registro correctamente',
        ];
      } else {
        return [
          'code'    => 10,
          'message' => 'Error en el registro',
        ];
      }
    }
  }

  public function actionPromocionCreateEspecialAjax()
  {
    $request                    = Yii::$app->request;
    Yii::$app->response->format = Response::FORMAT_JSON;

    if ($request->validateCsrfToken() && $request->isAjax) {

      if (isset(Yii::$app->request->post()["id"]) && Yii::$app->request->post()["id"]) {
        $cliente_id = Yii::$app->request->post()["id"];
        $ticket_id  = Yii::$app->request->post()["ticket_id"];

        $fecha_ini = strtotime(substr(Yii::$app->request->post()["date_range"], 0, 10));
        $fecha_fin = strtotime(substr(Yii::$app->request->post()["date_range"], 13, 23)) + 86340;

        $ClienteCodigoPromocion                   = new ClienteCodigoPromocion();
        $ClienteCodigoPromocion->cliente_id       = $cliente_id;
        $ClienteCodigoPromocion->requiered_libras = 1;
        $ClienteCodigoPromocion->descuento        = Yii::$app->request->post()["descuento"];
        $ClienteCodigoPromocion->fecha_rango_ini  = $fecha_ini;
        $ClienteCodigoPromocion->fecha_rango_fin  = $fecha_fin;
        $ClienteCodigoPromocion->tipo_condonacion = Yii::$app->request->post()["tipo_condonacion"];
        $ClienteCodigoPromocion->nota             = Yii::$app->request->post()["nota_ticket"];

        $ClienteCodigoPromocion->tipo   = ClienteCodigoPromocion::TIPO_ESPECIAL;
        $ClienteCodigoPromocion->status = ClienteCodigoPromocion::STATUS_PROGESO;

        if ($ClienteCodigoPromocion->validate()) {
          if ($ClienteCodigoPromocion->save()) {
            $response                        = ["code" => 10, "message" => "Se genero correctamente la solicitud"];
            $ticket                          = Ticket::findOne($ticket_id);
            $ticket->condonacion_especial_id = $ClienteCodigoPromocion->id;
            $ticket->is_condonacion          = Ticket::CONDONACION_ON;
            $ticket->update();
          }
        } else {
          $response = ["code" => 20, "message" => "Error al guardar promocion, verifique su información"];
        }
      }

      return $response;
    }
    throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
  }

  //------------------------------------------------------------------------------------------------//
  // HELPERS
  //------------------------------------------------------------------------------------------------//
  /**
   * Finds the model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @return Model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id, $_model = 'model')
  {
    switch ($_model) {
      case 'model':
        $model = Ticket::findOne($id);
        break;

      case 'view':
        $model = ViewTicket::findOne($id);
        break;
    }

    /*if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('La página solicitada no existe.');
        }*/
    return $model;
  }

  public function actionEnviarNota()
  {
    $request = Yii::$app->request;

    if ($request->isAjax) {
      Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    }

    $ticketId = $request->post('ticket_id', $request->get('id'));
    $mensaje = $request->post('mensaje');

    if (!$ticketId || !$mensaje) {
      if ($request->isAjax) {
        return ['success' => false, 'error' => 'Datos incompletos.'];
      } else {
        Yii::$app->session->setFlash('error', 'Datos incompletos.');
        return $this->redirect(['view', 'id' => $ticketId]);
      }
    }

    $seguimiento = new \app\models\ticket\Seguimiento();
    $seguimiento->ticket_id = $ticketId;
    $seguimiento->user_id = Yii::$app->user->id;
    $seguimiento->mensaje = $mensaje;

    if ($seguimiento->save()) {
      if ($request->isAjax) {
        return ['success' => true];
      } else {
        Yii::$app->session->setFlash('success', 'Mensaje agregado.');
        return $this->redirect(['view', 'id' => $ticketId]);
      }
    } else {
      if ($request->isAjax) {
        return ['success' => false, 'error' => $seguimiento->getErrors()];
      } else {
        Yii::$app->session->setFlash('error', 'Error al guardar el mensaje.');
        return $this->redirect(['view', 'id' => $ticketId]);
      }
    }
  }

  public function behaviors()
  {
    return [
      'access' => [
        'class' => \yii\filters\AccessControl::class,
        'only' => ['index', 'view', 'create', 'update', 'delete', 'enviar-nota'],
        'rules' => [
          [
            'allow' => true,
            'actions' => ['index', 'view', 'create', 'update', 'delete', 'enviar-nota'],
            'roles' => ['@'],
          ],
        ],
      ],
      'verbs' => [
        'class' => \yii\filters\VerbFilter::class,
        'actions' => [
          'delete' => ['POST'],
        ],
      ],
    ];
  }

  public function beforeAction($action)
  {
    $ticketIdBorrado = $this->borrarTicketsInactivosAutomatica();
    if ($ticketIdBorrado && Yii::$app->controller->action->id == 'view') {
      Yii::$app->session->setFlash('warning', 'El ticket fue eliminado automáticamente por inactividad de 24 horas.');
      return Yii::$app->getResponse()->redirect(['index']);
    }
    return parent::beforeAction($action);
  }

  private function borrarTicketsInactivosAutomatica()
  {
    // Parámetros de borrado 
    // $fechaLimite = time() - (24 * 60 * 60); // 24 horas en segundos
    // $fechaInicio = strtotime('2025-06-03 00:00:00');

    // $tickets = \app\models\ticket\Ticket::find()
    //   ->where(['<', 'created_at', $fechaLimite])
    //   ->andWhere(['>', 'created_at', $fechaInicio])
    //   ->all();

    // $ticketIdBorrado = null;

    // foreach ($tickets as $ticket) {
    //   if (empty($ticket->seguimientos)) {
    //     // Si el usuario está intentando ver este ticket
    //     if (Yii::$app->request->get('id') == $ticket->id) {
    //       $ticketIdBorrado = $ticket->id;
    //     }
    //     $ticket->delete();
    //   }
    // }

    // return $ticketIdBorrado;
    return null;
  }

  /*public function actionUploadEvidenciaAjax()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    $file = UploadedFile::getInstanceByName('archivo');

    if ($file) {
        $dir = Yii::getAlias('@webroot/ticket/');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $filename = time() . '-' . uniqid() . '.' . $file->extension;
        $ruta = $dir . $filename;

        if ($file->saveAs($ruta)) {
            // Devuelve la URL relativa para mostrarla y el nombre
            return [
                'success' => true,
                'nombre' => $filename,
                'url' => Yii::getAlias("@web/ticket/$filename"),
            ];
        }
    }

    return ['success' => false, 'error' => 'Error al subir el archivo.'];
}*/
}
