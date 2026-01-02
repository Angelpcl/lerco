<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use app\models\esys\EsysListaDesplegable;
use app\models\ticket\Ticket;
use app\models\ticket\ClienteRazonSocial;
use app\models\envio\Envio;
use yii\web\JsExpression;
use app\models\ticket\Proyectos;
use yii\helpers\ArrayHelper;

$proyectosList = Proyectos::getProyectosList();
?>

<!-- Encabezado principal -->
<div style="display: flex; justify-content: space-between; align-items: center;">
    <h1 style="font-size: 46px; font-weight: 800; color: #0b1f2e; margin: 0;">Tickets</h1>
    
<!-- Botones de Crear y Eliminar  -->
   <div class="form-group">
    
        <div class="ibox">
             <!-- Botón y logica para crear un nuevo ticket -->
             <?php $form = ActiveForm::begin(['id' => 'form-ticket', 'options' => ['enctype' => 'multipart/form-data'] ]) ?>

             <?= $form->field($model->ticket_detalle, 'ticket_detalle_array')->hiddenInput()->label(false) ?>

            <?= Html::submitButton($model->isNewRecord  ? '<i class=" aria-hidden="true"></i> CREAR TICKET'  : '<i class="fa fa-floppy-o" aria-hidden="true"></i> GUARDAR CAMBIOS',
            [
               'class' => 'btn',
               'id' => 'btnGuardarTicket',
               'style' => 'background-color: #0b1f2e; color: white; border-radius: 9999px; padding: 8px 20px; font-weight: 600;'
            ]
            ) ?>
            <?= Html::a('Cancelar', ['index'], 
            [
               'class' => 'btn',
               'style' => 'background-color: #d9534f; color: white; border-radius: 9999px; padding: 8px 20px; font-weight: 600;'
            ]) ?>

        </div>
    </div>
</div>

<div class="operacion-ticket-form">

   

    <div class="row">
        <div class="col-lg-8 col-md-7 col-sm-7">
            <div class="ibox">
                <div class="ibox-content">
                    <div id="error-add-ticket" class="has-error" style="display: none"></div>
                    <div class="row">
                        <div class="col-lg-6">
                           <?php //CLASIFICACION 
                              echo $form->field($model, 'clasificacion')->widget(Select2::classname(),[
                              'data' => Ticket::$clasificacionList,
                              'language' => 'es',
                              'options' => [
                              'placeholder' => 'Selecciona...',
                               ],
                              'pluginOptions' => [
                              'allowClear' => true
                               ],
                               ])->label("Clasificación:");
                            ?>
                            <?php // $form->field($model, 'tipo_id')->dropDownList(EsysListaDesplegable::getItems('tipo_ticket'), ['prompt' => 'Tipo']) ?>
                                   <?php /* $form->field($model, 'cliente_id')->widget(Select2::classname(),
                                        [
                                        'language' => 'es',
                                            'data' => isset($model->cliente->id) ? [$model->user->id => $model->user->nombre ." ". $model->user->apellidos] : [],
                                            'pluginOptions' => [
                                                'allowClear' => true,
                                                'minimumInputLength' => 3,
                                                'language'   => [
                                                    'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                                                ],
                                                'ajax' => [
                                                    'url'      => Url::to(['/crm/cliente/cliente-ajax']),
                                                    'dataType' => 'json',
                                                    'cache'    => true,
                                                    'processResults' => new JsExpression('function(data, params){ clienteEmisor = data; return {results: data} }'),
                                                ],
                                            ],
                                            'options' => [
                                                'placeholder' => 'Selecciona al usuario...',
                                                'disabled' => $model->id ? true : false,
                                                'id' => 'cliente_razon_id', // Asegúrate de tener un id para el campo Select2
   
                                        ],
                                    ]) */
                                    ?>

                                
                                    <h5 style="font-size: 16px;" id="label_nombre_proyecto"></h5>
                                    
                                  <?= //Selecciona la EMPRESA
                                      $form->field($model, 'cliente_id')->widget(Select2::classname(), [
                                      'data' => ArrayHelper::map(ClienteRazonSocial::find()->all(), 'id', 'nombre'), // Asegúrate que 'id' y 'nombre' existan
                                      'language' => 'es',
                                      'options' => 
                                      [
                                        'placeholder' => 'Selecciona la empresa',
                                        'id' => 'cliente_id', //'value' => $model->proyecto
                                      ],
                                        'pluginOptions' => 
                                        [
                                         'allowClear' => true
                                        ],
                                        ])->label("Selecciona la EMPRESA:"); 
                                    ?>
                                <HR>
                            
                               <?= //SELECCION DEL PROYECTO
                                  $form->field($model, 'proyecto')->widget(Select2::classname(), [
                                  'data' => ArrayHelper::map(Proyectos::find()->all(), 'id', 'nombre'), // Ajusta 'id' y 'nombre' según tu modelo
                                  'language' => 'es',
                                  'options' => [
                                  'placeholder' => 'Selecciona el proyecto',
                                  'id' => 'proyecto-dropdown',
                                  'style' => $model->proyecto == '' ? '' : 'display: none;',
                                  'disabled' => true, // Lo dejas deshabilitado inicialmente
                                   ],
                                  'pluginOptions' => [
                                  'allowClear' => true,
                                   ],
                                   ])->label("Selecciona el Proyecto:");
                                ?>

                                <?= $form->field($model, 'proyecto_text')->textInput([
                                    'id' => 'proyecto_text',
                                    'name' => 'proyecto_text',
                                    'value' => $model->id ? $model-> proyecto : '' ,
                                    'style' => $model->proyecto == '' ? 'display: none;' : '',
                                    'readonly' => true,
                                     ])->label(false);  // Campo oculto para almacenar el texto del proyecto 
                                ?>


                                <?= //PRODUCTO QUE IMPACTA 
                                   $form->field($model, 'producto')->widget(Select2::classname(), [
                                   'data' => [], // Inicialmente vacío
                                   'language' => 'es',
                                   'options' => [
                                   'placeholder' => 'Selecciona un producto',
                                   'id' => 'producto-dropdown',
                                   'value' => $model->producto ? $model->producto : '',
                                   'style' => $model->producto == '' ? '' : 'display: none;',
                                   'disabled' => true,
                                    ],
                                     'pluginOptions' => [
                                     'allowClear' => true,
                                    ],
                                    ])->label("Producto al que impacta:");
                                ?>
                                
                                <?php $userId = Yii::$app->user->id; ?>
                                <?= Html::hiddenInput('user_id', $userId); ?>

                                <?= $form->field($model, 'producto_text')
                                ->textInput(['id' => 'producto_text', 'name' => 'producto_text',
                                 'value' => $model->producto ? $model->producto : '', 
                                 'style' => $model->producto == '' ? 'display: none;' : '',
                                 'readonly' => true ]
                                 )->label(false);  // Campo oculto para almacenar el texto del producto ?>
                                

                                <?php
                                echo $form->field($model, 'status')->dropDownList(
                                    Ticket::$statusList,
                                    [
                                        //'prompt' => 'Selecciona un estado', // Opción predeterminada
                                       'options' => [
                                           '' => ['disabled' => true, 'selected' => true],  // Opción predeterminada deshabilitada
                                        ],
                                        'disabled' => $model->id ? false : true,  // Deshabilitar el dropdown entero si $model->id no está seteado
                                        'value' => $model->id ? $model->status : reset(Ticket::$statusList), // Si está deshabilitado, asignar el primer valor
                                    ]
                                );
                                ?>
                        </div>
                        
                        <div class="col-lg-6" style="max-width: 100%; overflow-x: auto;">
                           
                                <?php // $form->field($model, 'telefono_cliente')->textInput(['type' => 'number'])->label("Telefono del cliente") ?>
                                <?php // $form->field($model, 'email_cliente')->textInput([])->label("Correo electronico del cliente") ?>
                            
                                <h5>CONTACTOS</h5>
                                <hr>
                                <table class="table" style="width: 100%; table-layout: auto; border-collapse: collapse; font-size: 11px;">
                                    <thead>
                                        <tr style="padding: 10px; text-align: left;">
                                            <th>Nombre</th>
                                            <th>Apellidos</th>
                                            <th>Email</th>
                                            <th>Teléfono</th>
                                        </tr>
                                    </thead>
                                    <tbody id="contactos-table-body">
                                        <!-- Las filas de contactos se agregarán aquí -->
                                    </tbody>
                                </table>
                                
                                <style>
                                    table th, table td {
                                        word-wrap: break-word;
                                        white-space: normal;
                                        word-break: break-word;
                                    }
                                </style>
                                <div class="ibox-title" style="background-color: #e5eaef; color: white; padding: 10px; text-align: center; border-radius: 4px; font-weight: bold;">
                                   <h5 style="margin: 0; color: black;">Descripcion</h5>
                               </div>

                                <?= $form->field($model, 'descripcion')->textarea(['rows' => 4,'disabled' => $model->id ? true :  false]) ?>
                                
                                <?php if ($model->id): ?>
                                    <?= $form->field($model, 'nota')->textarea(['rows' => 4]) ?>
                                <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-5 col-sm-5">
            <div class="ibox">
               <div class="ibox-title" style="background-color: #e5eaef; color: white; padding: 10px; text-align: center; border-radius: 4px; font-weight: bold;">
                 <h5 style="margin: 0; color: black;">EVIDENCIAS</h5>
               </div>

                <div class="ibox-content">
                <?php
                   // Definir la clase de estilo dependiendo del permiso
                  $styleAsignado = Yii::$app->user->can('seguimiento') ? '' : 'style="display:none"';
                ?>
                
                <div <?= $styleAsignado ?>>
                    <?php
                    // A QUIEN SE LE ASIGNA 
                    echo $form->field($model, 'asignado')->widget(Select2::classname(), [
                        'language' => 'es',
                        'data' => isset($model->modelAsignado->id) ?
                            [$model->modelAsignado->id => $model->modelAsignado->nombre . " " . $model->modelAsignado->apellidos] : [],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'minimumInputLength' => 3,
                            'language' => [
                                'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                            ],
                            'ajax' => [
                                'url' => Url::to(['/crm/cliente/cliente-ajax']),
                                'dataType' => 'json',
                                'cache' => true,
                                'processResults' => new JsExpression('function(data, params){ return {results: data} }'),
                            ],
                        ],
                        'options' => [
                            'placeholder' => 'Selecciona al usuario...',
                            'readonly' => $model->id ? true : false,
                        ],
                    ]);
                    ?>
                </div>
                <?= $form->field($model, 'evidencia_')->textarea(['rows' => 2,'id' => 'evidencia-url',])->label("Inserta la URL al almacenamiento (DRIVE, DROPBOX) con los archivos y/o capturas correspondientes.") ?>
                <!-- Enlace de previsualización -->
               <div id="preview-url-container" style="width: 100%; padding: 8px; border: 1px solid #ccc; display: <?= $model->evidencia_ ? 'block' : 'none' ?>; word-wrap: break-word; overflow-wrap: break-word;">
                   <strong>Previsualización:</strong><br>
                   <a href="<?= $model->evidencia_ ?>" target="_blank" id="preview-url" style="display: inline-block; max-width: 100%; word-break: break-all;">
                     <?= $model->evidencia_ ?>
                  </a>
              </div>
              
                <?= $form->field($model,'ticket_evidencia_array[]')
                ->fileInput(['multiple' => true,
                "class"=>"form-control btn btn-primary"
                ])  ?>
                <div id="preview-evidencias"></div>

                 
                    <?php if ($model->id): ?>
                                <div class="ibox">
                                    <div class="ibox-title">
                                        <h5 >Archivos relacionados</h5>
                                    </div>
                                    <div class="ibox-content">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th style="text-align: center;">Nombre</th>
                                                    <th style="text-align: center;">Tipo</th>
                                                    <th style="text-align: center;">Descargar</th>
                                                </tr>
                                            </thead>
                                            <tbody  style="text-align: center;">
                                                <?php if ($model->ticket_evidencia): ?>
                                                    <?php foreach (json_decode($model->ticket_evidencia,true) as $key => $item): ?>
                                                        <tr>
                                                            <td><?= $item[key($item)] ?></td>
                                                            <td><?= explode("." ,  $item[key($item)] )[1] ?></td>
                                                            <td><?= Html::a('Ver', ['/ticket/' . $item[key($item)] ], ['class' => 'text-primary', "target" => "_blank"])  ?></td>

                                                        </tr>
                                                    <?php endforeach ?>
                                                <?php endif ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                     <?php endif ?>
                </div> 
            </div>
        </div>
    </div>
    
    <?php ActiveForm::end(); ?>

        <div id="modalAviso" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.25);">
        <div style="background: #fff; width: 360px; max-width: 95vw; margin: 100px auto; border-radius: 14px; box-shadow: 0 6px 32px rgba(0,0,0,.15); padding: 32px 26px; display: flex; flex-direction: column; align-items: center;">
            <div style="font-size: 42px; color:rgb(0, 162, 255); margin-bottom: 14px;">
                &#9888;
            </div>
            <h2 style="margin: 0; font-size: 1.4rem; font-weight: 700; color: #222;">¡AVISO!</h2>
            <p style="margin: 16px 0 30px 0; color: #222; text-align: center; font-size: 1.01rem; line-height: 1.5;">
                Antes de finalizar la creación del ticket, te informamos que, una vez emitida nuestra respuesta, si no recibimos contestación en un plazo de 24 horas, el ticket será cancelado y deberás generar uno nuevo en caso de requerir seguimiento. Para evitar su eliminación visita la parte de detalles del ticket y agrega una nota de seguimiento.
            </p>
            <button id="btnCerrarModalAviso" style="background: #0a1a35; color: #fff; border: none; border-radius: 40px; font-size: 1.1rem; padding: 8px 36px; cursor: pointer; font-weight: 600;">
                Continuar
            </button>
        </div>
    </div>
</div>


<div class="display-none">
    <table>
        <tbody class="template_info_paquete">
            <tr id ="paquete_info_id_{{paquete_info_id}}">
                <td>
                    <p id="trackend_name" style=" text-align: center; font-size: 16px; font-family: Georgia, serif; line-height: 15px"></p>
                </td>
                <td>
                    <p id="producto_name" style=" text-align: center; font-size: 16px; font-family: Georgia, serif; line-height: 15px"></p>
                </td>
                <td>
                    <?= Html::checkbox(
                        "PaqueteRelacioando",
                        false,
                        [
                            "id"    => "paquete_id_{{paquete_id}}",
                            "class" => "view magic-checkbox",
                        ]
                    ) ?>
                    <?= Html::label(null, "paquete_id_{{paquete_id}}", ["style" => "display:inline"]) ?>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Script de previsualización -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.querySelector('input[type="file"][name="ticket_evidencia_array[]"]');
    var preview = document.getElementById('preview-evidencias');
    if (input) {
        input.addEventListener('change', function () {
            preview.innerHTML = '';
            if (input.files.length > 0) {
                var list = document.createElement('ul');
                for (var i = 0; i < input.files.length; i++) {
                    var item = document.createElement('li');
                    item.textContent = input.files[i].name;
                    list.appendChild(item);
                }
                preview.appendChild(list);
            }
        });
    }
});
</script>



<script>
$(document).ready(function () {
    // Función para manejar el cambio en el campo de selección de proyecto
    $('#proyecto-dropdown').change(function () {
        var proyectoId = $(this).val();

        // Verificar si se seleccionó un proyecto
        if (proyectoId) {
            // Hacer la llamada AJAX para obtener los productos relacionados con el proyecto
            $.ajax({
                url: '<?= Url::to(['/operacion/ticket/get-productos-proy']) ?>',  // Ajusta la URL correctamente
                type: 'GET',  // Cambia a GET
                data: { proyectoId: proyectoId },
                success: function (data) {
                    try {
                        var productos = JSON.parse(data);  // Verifica que el formato sea JSON válido
                        var productoDropdown = $("#producto-dropdown");

                        // Limpiar el campo de productos
                        productoDropdown.empty();

                        // Agregar el prompt por defecto
                        productoDropdown.append("<option value=''>Selecciona un producto</option>");

                        // Agregar las opciones de productos
                        $.each(productos, function (id, nombre) {
                            productoDropdown.append("<option value='" + id + "'>" + nombre + "</option>");
                        });

                        // Habilitar el campo de productos
                        productoDropdown.prop("disabled", false);
                    } catch (e) {
                        alert('Error al procesar la respuesta. Verifique el formato del JSON.');
                    }
                },
                error: function () {
                    alert('Error al cargar los productos. Por favor, intente nuevamente.');
                }
            });
        } else {
            // Si no se seleccionó un proyecto, deshabilitar el campo de productos
            $("#producto-dropdown").prop("disabled", true);
        }
    });


        // Evento cuando cambia el valor del campo Select2
        // Evento cuando cambia el valor del campo Select2
        //$('#cliente_razon_id').on('change', function() {
        $('#cliente_id').on('change', function() {
            var selectedClientId = $(this).val(); // Obtener el ID seleccionado
            if (selectedClientId) {
                // Llamada a una función JavaScript pasando el ID del cliente seleccionado
                //alert(selectedClientId);
            }

            var proyectoId = $(this).val();

            if (proyectoId) {
                // Hacer la llamada AJAX para obtener los proyectos, productos y contactos relacionados con el cliente
                $.ajax({
                    url: '<?= Url::to(['/operacion/ticket/get-proyectos-cliente']) ?>',  // Ajusta la URL correctamente
                    type: 'GET',  // Cambia a GET
                    data: { proyectoId: proyectoId },
                    success: function (data) {
                        try {
                            var response = JSON.parse(data);  // Verifica que el formato sea JSON válido
                            
                            // Verificar si hay errores en la respuesta
                            if (response.error) {
                                //alert(response.error);
                                $('#label_nombre_proyecto').text("Este usuario NO esta asociado a un proyecto.");
                                return;
                            }

                            var proyectosList = response.proyectosList;
                            var productosList = response.productosList;
                            var contactosList = response.contactosList; // Obtener los contactos de la respuesta
                            //var nombre_proyecto = response.nombre;

                            //$('#label_nombre_proyecto').text("Cliente: "+nombre_proyecto);

                            var proyectoDropdown = $("#proyecto-dropdown");
                            var productoDropdown = $("#producto-dropdown");
                            var contactosTableBody = $("#contactos-table-body");  // Suponiendo que el <tbody> de la tabla tiene id="contactos-table-body"

                            // Limpiar los campos de proyectos, productos y la tabla de contactos
                            proyectoDropdown.empty();
                            productoDropdown.empty();
                            contactosTableBody.empty(); // Limpiar la tabla

                            // Agregar un prompt por defecto
                            proyectoDropdown.append("<option value=''>Selecciona un proyecto</option>");
                            productoDropdown.append("<option value=''>Selecciona un producto</option>");

                            // Llenar el campo de proyectos
                            $.each(proyectosList, function (id, nombre) {
                                proyectoDropdown.append("<option value='" + id + "'>" + nombre + "</option>");
                            });

                            // Llenar el campo de productos
                            $.each(productosList, function (id, nombre) {
                                productoDropdown.append("<option value='" + id + "'>" + nombre + "</option>");
                            });

                            // Llenar la tabla de contactos
                            $.each(contactosList, function (index, contacto) {
                                var row = "<tr>" +
                                    "<td style='padding: 10px; text-align: left; word-break: break-word; white-space: normal;'>" + contacto.nombre + "</td>" +
                                    "<td style='padding: 10px; text-align: left; word-break: break-word; white-space: normal;'>" + contacto.apellidos + "</td>" +
                                    "<td style='padding: 10px; text-align: left; word-break: break-word; white-space: normal;'>" + contacto.email + "</td>" +
                                    "<td style='padding: 10px; text-align: left; word-break: break-word; white-space: normal;'>" + contacto.telefono + "</td>" +
                                    "</tr>";
                                contactosTableBody.append(row);
                            });

                            // Habilitar los campos
                            proyectoDropdown.prop("disabled", false);
                            productoDropdown.prop("disabled", false);

                        } catch (e) {
                            alert('Error al procesar la respuesta. Verifique el formato del JSON.');
                        }
                    },
                    error: function () {
                        alert('Error al cargar los proyectos, productos y contactos. Por favor, intente nuevamente.');
                    }
                });
            } else {
                // Si no se seleccionó un proyecto, deshabilitar los campos de productos y la tabla
                $("#proyecto-dropdown").prop("disabled", true);
                $("#producto-dropdown").prop("disabled", true);
                $("#contactos-table-body").empty(); // Limpiar la tabla de contactos
            }
        });



   



    $('#proyecto-dropdown').change(function () {
        var proyectoId = $('#proyecto-dropdown').val();
    

        // Actualizar el campo oculto con el texto seleccionado del proyecto
        if (proyectoId) {
            var proyectoText = $('#proyecto-dropdown option:selected').text();
            $('#proyecto_text').val(proyectoText);  // Establecer el texto seleccionado en el campo oculto
        }

    });

    $('#producto-dropdown').change(function () {
        var productoId = $('#producto-dropdown').val();

        // Actualizar el campo oculto con el texto seleccionado del producto
        if (productoId) {
            var productoText = $('#producto-dropdown option:selected').text();
            $('#producto_text').val(productoText);  // Establecer el texto seleccionado en el campo oculto
        }
    });



});


$(document).ready(function() {
    // Ejecutar la función automáticamente cuando se carga la página
    var proyectoId = $('#cliente_id').val();  // Obtener el proyectoId del campo
    //var proyectoId = $('#cliente_razon_id').val();  // Obtener el proyectoId del campo

    if (proyectoId) {
        // Llamar la función AJAX solo si ya existe un proyectoId seleccionado
        getProyectosCliente(proyectoId);
    }


    function getProyectosCliente(proyectoId) {
        $.ajax({
            url: '<?= Url::to(['/operacion/ticket/get-proyectos-cliente']) ?>',  // Ajusta la URL correctamente
            type: 'GET',  // Cambia a GET
            data: { proyectoId: proyectoId },
            success: function (data) {
                try {
                    var response = JSON.parse(data);  // Verifica que el formato sea JSON válido
                    
                    // Verificar si hay errores en la respuesta
                    if (response.error) {
                        //alert(response.error);
                        $('#label_nombre_proyecto').text("Este usuario NO esta asociado a un proyecto.");
                        return;
                    }

                    var contactosList = response.contactosList; // Obtener los contactos de la respuesta
                    //var nombre_proyecto = response.nombre;

                    // Si solo deseas actualizar el nombre del proyecto, puedes usar esto:
                   // $('#label_nombre_proyecto').text("Cliente: " + nombre_proyecto);

                    var contactosTableBody = $("#contactos-table-body");  // Suponiendo que el <tbody> de la tabla tiene id="contactos-table-body"

                    // Limpiar la tabla de contactos
                    contactosTableBody.empty();

                    // Llenar la tabla de contactos
                    $.each(contactosList, function (index, contacto) {
                        var row = "<tr>" +
                            "<td>" + contacto.nombre + "</td>" +
                            "<td>" + contacto.apellidos + "</td>" +
                            "<td>" + contacto.email + "</td>" +
                            "<td>" + contacto.telefono + "</td>" +
                            "</tr>";
                        contactosTableBody.append(row);
                    });

                    // Aquí no habilitamos los campos de proyectos y productos, porque solo estamos trabajando con contactos
                    // El dropdown de proyecto y producto NO se habilita
                    $("#proyecto-dropdown").prop("disabled", true);
                    $("#producto-dropdown").prop("disabled", true);

                } catch (e) {
                    alert('Error al procesar la respuesta. Verifique el formato del JSON.');
                }
            },
            error: function () {
                alert('Error al cargar los proyectos, productos y contactos. Por favor, intente nuevamente.');
            }
        });
    }
});

</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Solo mostrar si es un registro nuevo
    <?php if ($model->isNewRecord): ?>
    var modal = document.getElementById('modalAviso');
    var btn = document.getElementById('btnCerrarModalAviso');
    modal.style.display = 'block';
    btn.onclick = function () {
        modal.style.display = 'none';
    }
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    <?php endif; ?>
});
</script>
