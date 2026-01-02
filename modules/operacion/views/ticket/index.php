<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\BootstrapTableAsset;
use kartik\daterange\DateRangePicker;
use app\models\ticket\Ticket;
use yii\widgets\Breadcrumbs;
use app\models\esys\EsysListaDesplegable;
use kartik\date\DatePicker;

BootstrapTableAsset::register($this);

/* @var $this yii\web\View */

$bttExport    = 'Tickets LERCO' . ' - Tickets- ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['tickets-json-btt']);
$bttUrlView   = Url::to(['view?id=']);
$bttUrlUpdate = Url::to(['update?id=']);
$bttUrlDelete = Url::to(['delete?id=']);

?>
<?php if (!isset(Yii::$app->user->identity)): ?>

    <div class="site-index">

        <div class="jumbotron">
            <h1>Paqueteria</h1>
        </div>
    </div>

<?php else: ?>



    <!-- Encabezado principal -->
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 style="font-size: 46px; font-weight: 800; color: #0b1f2e; margin: 0;">Tickets</h1>
        <h3 style="font-size: 30px; font-weight: 600; color: #1c3b2c; margin: 0;">
            ¡Bienvenido <b><?= Yii::$app->user->identity->username ?></b>, al Sistema de Tickets!
        </h3>
    </div>
    <div class="operacion-ticket-index">
        <div class="btt-toolbar">
            <div class="ibox mar-btm-5px">
                <div class="ibox-content pad-btm-15px">
                    <!-- Filtros -->
                    <div style="margin-top: 20px; margin-left: 400px; background-color: #e5eaef; padding: 10px; border-radius: 15px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">

                        <strong class="pad-rgt">Filtrar:</strong>

                        <?= Html::dropDownList('status', null, Ticket::$statusList, [
                            'class' => 'form-control',
                            'style' => 'max-width: 170px;',
                            'prompt' => 'Selecciona estatus'
                        ]) ?>

                            <!-- Pendiente agregar filtro por fecha aqui -->

                        <?php
                        $this->registerJs(<<<JS
                            $('#calendario-icono').on('click', function() {
                                $('#input-datepicker').focus();
                            });
                        JS);
                        ?>

                        <?= Html::dropDownList('proyecto', null, Ticket::$proyectosList, [
                            'class' => 'form-control',
                            'style' => 'max-width: 170px; display: none;',
                            'prompt' => 'Selecciona proyecto'
                        ]) ?>

                        <?= Html::dropDownList('producto', null, Ticket::$productoList, [
                            'class' => 'form-control',
                            'style' => 'max-width: 170px; display: none;',
                            'prompt' => 'Selecciona producto'
                        ]) ?>

                        <!-- Botón Crear Ticket -->
                        <?= Html::a('<i class="fa fa-plus"></i> Crear Ticket', ['create'], ['class' => 'btn', 'style' => 'background-color: #0b1f2e; color: white; border-radius: 9999px; padding: 8px 20px; font-weight: 600;']) ?>
                    </div>

                </div>
            </div>
        </div>

        <table class="bootstrap-table"></table>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {

            var $filters = $('.btt-toolbar :input'),
                can = JSON.parse('<?= json_encode($can) ?>'),
                actions = function(value, row) {
                    return [
                        '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver ticket" class="fa fa-eye"></a>',
                        (can.update ? '<a href="<?= $bttUrlUpdate ?>' + row.id + '" title="Editar ticket" class="fa fa-pencil"></a>' : ''),
                        (can.delete ? '<a href="<?= $bttUrlDelete ?>' + row.id + '" title="Eliminar ticket" class="fa fa-trash" data-confirm="¿Confirma que deseas eliminar este ticket?" data-method="post"></a>' : '')
                    ].join('');
                },
                columns = [{
                        field: 'id',
                        title: 'ID',
                        align: 'center',
                        width: '60',
                        sortable: true,
                        switchable: false,
                    },
                    {
                        field: 'cliente_razon_social_name',
                        title: 'EMPRESA',
                        sortable: true,
                    },
                    {
                        field: 'clasificacion',
                        title: 'Tipo',
                        sortable: true,
                    },
                    {
                        field: 'proyecto',
                        title: 'PROYECTO',
                        sortable: true,
                    },
                    {
                        field: 'producto',
                        title: 'PRODUCTO IMPACTADO',
                        sortable: true,
                    },
                    {
                        field: 'status',
                        title: 'Estatus',
                        align: 'left',
                        formatter: btf.status.ticket,
                    },

                    /*{
                    field: 'folio',
                    title: 'ASIGNADO A',
                    //sortable: true,
                }, 

               
                {
                    field: 'is_rembolso',
                    title: '¿Aplico reembolso?',
                    align: 'center',
                    formatter: btf.status.opt_check,
                }, 
                {
                    field: 'num_rembolso_realizados',
                    title: 'N° pagos realizados',
                    align: 'center',
                    sortable: true,
                    visible : false,
                },
                {
                    field: 'total_reembolsado',
                    title: 'Total Reembolsado',
                    align: 'center',
                    sortable: true,
                    visible : false,
                },
                {
                    field: 'total_a_reembolsar',
                    title: 'Total a Reembolsar',
                    align: 'center',
                    sortable: true,
                    visible : false,
                }, */
                    {
                        field: 'created_at',
                        title: 'RECIBIDO',
                        align: 'center',
                        sortable: true,
                        switchable: false,
                        formatter: btf.time.date,
                    },
                    /*{
                    field: 'created_by',
                    title: 'Creado por',
                    sortable: true,
                    formatter: btf.user.created_by,
                },
                {
                    field: 'updated_at',
                    title: 'Modificado',
                    align: 'center',
                    sortable: true,
                    formatter: btf.time.date,
                },
                {
                    field: 'updated_by',
                    title: 'Modificado por',
                    sortable: true,
                    formatter: btf.user.updated_by,
                },*/
                    {
                        field: 'action',
                        title: 'Acciones',
                        align: 'center',
                        switchable: false,
                        width: '100',
                        class: 'btt-icons',
                        formatter: actions,
                        tableexportDisplay: 'none',
                    },
                ],
                params = {
                    id: 'tickets',
                    color: true,
                    status_color: {
                        <?= Ticket::STATUS_ACTIVE ?>: 'danger',
                        <?= Ticket::STATUS_PENDIENTE ?>: 'warning',
                        <?= Ticket::STATUS_CERRADO ?>: 'success',
                    },
                    element: '.operacion-ticket-index',
                    url: '<?= $bttUrl ?>',

                    bootstrapTable: {
                        columns: columns,
                        showRefresh: false,
                        showToggle: false,
                        showColumns: false,
                        showExport: false,
                        showPaginationSwitch: false,
                        search: false,
                        toolbar: false,
                        exportOptions: {
                            "fileName": "<?= $bttExport ?>"
                        },
                        onDblClickRow: function(row, $element) {
                            window.location.href = '<?= $bttUrlView ?>' + row.id;
                        },

                    }
                };

            bttBuilder = new MyBttBuilder(params);
            bttBuilder.refresh();

        });
    </script>

    
<?php if (Yii::$app->session->hasFlash('ticket_creado')): ?>
<div class="modal fade" id="modalTicketCreado" tabindex="-1" role="dialog" aria-labelledby="modalTicketCreadoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius: 20px;">
      <div class="modal-body text-center" style="padding: 40px 20px;">
        <!-- Palomita Font Awesome -->
        <i class="fa fa-check-circle" style="font-size:80px; color:#4BB543; margin-bottom: 10px;"></i>
        <h3 style="color:#217E42; font-weight: bold; margin-top: 10px;">¡Ticket creado con éxito!</h3>
        <p>Tu ticket fue registrado y está en proceso de atención.</p>
        <button type="button" class="btn btn-success mt-3" data-dismiss="modal" style="border-radius: 9999px; padding: 8px 30px; font-weight: 600;">Aceptar</button>
      </div>
    </div>
  </div>
</div>
<script>
$(document).ready(function(){
    $('#modalTicketCreado').modal('show');
});
</script>
<?php endif; ?>

<?php if (Yii::$app->session->hasFlash('warning')): ?>
<!-- Modal para el warning -->
<div class="modal fade" id="warningModal" tabindex="-1" role="dialog" aria-labelledby="warningModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius: 20px;">
      <div class="modal-header bg-warning" style="border-radius: 20px 20px 0 0;">
        <h5 class="modal-title" id="warningModalLabel" style="color: #7d5500; font-weight: bold;">
          Aviso Importante
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center" style="padding: 30px 15px;">
        <i class="fa fa-exclamation-triangle fa-3x" style="color: #f39c12; margin-bottom: 10px;"></i>
        <h4 style="margin-top: 10px; color:#b36b00;"><?= Yii::$app->session->getFlash('warning') ?></h4>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-warning" data-dismiss="modal" style="border-radius: 9999px;">Aceptar</button>
      </div>
    </div>
  </div>
</div>
<script>
  $(document).ready(function(){
      $('#warningModal').modal('show');
  });
</script>
<?php endif; ?>


<?php endif ?>