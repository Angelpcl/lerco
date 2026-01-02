<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use app\models\esys\EsysListaDesplegable;
use app\models\ticket\Ticket;
use app\models\ticket\Proyectos;
use app\models\ticket\Productos;
use app\models\envio\Envio;
use yii\web\JsExpression;


$items = Productos::getCheckboxList();
$productosSeleccionados = $model->productos ? json_decode($model->productos, true) : [];

$itemsP = Proyectos::getCheckboxList();
$proyectosSeleccionados = $model->proyectos ? json_decode($model->proyectos, true) : [];


?>

<div class="operacion-ticket-form">

    <?php $form = ActiveForm::begin(['id' => 'form-ticket', 'options' => ['enctype' => 'multipart/form-data'] ]) ?>
  
    <div class="row">
        <div class="col-lg-12 col-md-7 col-sm-7">
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información del Cliente</h5>
                </div>
                <div class="ibox-content">
                    <div id="error-add-ticket" class="has-error" style="display: none"></div>
                    <div class="row">
                        <div class="col-lg-6">
                            
                                <?= $form->field($model, 'nombre')->textInput()->label("Nombre del cliente") ?>
                                <?= $form->field($model, 'descripcion')->textInput()->label("Describa brevemente al cliente:") ?>
                                <hr>
                                <h5> CONTACTOS </h5>

                                <table class="table" id="contactos_table">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Apellidos</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($contactos as $contacto): ?>
                                        <tr>
                                            <td><input type="text" name="contactos[<?= $contacto->id ?>][nombre]" value="<?= $contacto->nombre ?>" class="form-control"></td>
                                            <td><input type="text" name="contactos[<?= $contacto->id ?>][apellidos]" value="<?= $contacto->apellidos ?>" class="form-control"></td>
                                            <td><input type="text" name="contactos[<?= $contacto->id ?>][email]" value="<?= $contacto->email ?>" class="form-control"></td>
                                            <td><input type="text" name="contactos[<?= $contacto->id ?>][telefono]" value="<?= $contacto->telefono ?>" class="form-control"></td>
                                            <td><button type="button" class="btn btn-danger" onclick="eliminarContacto(this)">Eliminar</button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>


                            <button type="button" class="btn btn-info" onclick="agregarContacto()"><i class="fa fa-plus-circle" aria-hidden="true"></i> Agregar Contacto</button>
                            <br><br>

        
                       
                        </div>
                        <div class="col-lg-6">
                        <h5 >PROYECTOS DE ESTE CLIENTE</h5>
                            <hr>
                            <div class="checkbox-list">
                                <?php foreach ($itemsP as $itempro): ?>
                                    <div class="checkbox">
                                        <?= Html::checkboxList('proyectos[]', $proyectosSeleccionados, [$itempro->id => $itempro->nombre], ['class' => 'checkbox']) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <hr>
                            <h5 >PRODUCTOS DE ESTE PROYECTO</h5>
                            <hr>
                            <div class="checkbox-list">
                                <?php foreach ($items as $item): ?>
                                    <div class="checkbox">
                                        <?= Html::checkboxList('productos[]', $productosSeleccionados, [$item->id => $item->nombre], ['class' => 'checkbox']) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>


                        

                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
      
    </div>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Crear cliente' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary', 'id' => 'btnGuardarTicket']) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-white']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>







<script>
   let contactoIndex = 0;  // Variable para llevar el contador de índices

// Función para agregar una fila de contacto
function agregarContacto() {
    let nuevaFila = $('<tr>' +
        '<td><input type="text" name="contactos[' + contactoIndex + '][nombre]" class="form-control"></td>' +
        '<td><input type="text" name="contactos[' + contactoIndex + '][apellidos]" class="form-control"></td>' +
        '<td><input type="text" name="contactos[' + contactoIndex + '][email]" class="form-control"></td>' +
        '<td><input type="text" name="contactos[' + contactoIndex + '][telefono]" class="form-control"></td>' +
        '<td><button type="button" class="btn btn-danger" onclick="eliminarContacto(this)">Eliminar</button></td>' +
    '</tr>');
    
    $('#contactos_table tbody').append(nuevaFila);
    
    // Incrementar el índice para el siguiente contacto
    contactoIndex++;
}

// Función para eliminar una fila de contacto
function eliminarContacto(button) {
    $(button).closest('tr').remove();
}


<?php if($model->id): ?>
// Función para guardar los datos de contactos al hacer submit
$('#form-ticket').on('submit', function(e) {

   // alert($(this).serialize());
    e.preventDefault();
    $.ajax({
        type: 'POST',
        url: '<?= Url::to(['editar-contactos', 'id' => $model->id]) ?>',
        data: $(this).serialize(),
        success: function(response) {
            console.log('Los contactos se guardaron correctamente');
            $('#form-ticket')[0].submit();  // Esto enviará el formulario finalmente
        },
        error: function() {
            alert('Hubo un error al guardar los contactos.');
        }
    });
});
<?php endif ?>

</script>
