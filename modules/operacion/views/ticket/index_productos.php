<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\BootstrapTableAsset;
use kartik\daterange\DateRangePicker;
use app\models\ticket\Ticket;
use app\models\esys\EsysListaDesplegable;
BootstrapTableAsset::register($this);

/* @var $this yii\web\View */

$this->title = 'Productos';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = 'Tickets LERCO'. ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['productos-json-btt']);
$bttUrlView   = Url::to(['update-producto?id=']);
$bttUrlUpdate = Url::to(['update-producto?id=']);
$bttUrlDelete = Url::to(['delete-producto?id=']);

?>
<?php if (!isset(Yii::$app->user->identity)): ?>

<div class="site-index">

    <div class="jumbotron">
        <h1>Paqueteria</h1>
    </div>
</div>

<?php else: ?>
<p>
  <?= $can['create']?
    Html::a('<i class="fa fa-plus-circle" aria-hidden="true"></i> CREAR PRODUCTO', ['create-producto'], ['class' => 'btn btn-success add']): '' ?>
</p>

<div class="operacion-ticket-index">
   

    <table class="bootstrap-table"></table>
</div>

<script type="text/javascript">
    $(document).ready(function(){

         var  $filters = $('.btt-toolbar :input'),
            can     = JSON.parse('<?= json_encode($can) ?>'),
            actions = function(value, row) { return [
                /*'<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver ticket" class="fa fa-eye"></a>', */
                (can.update? '<a href="<?= $bttUrlUpdate ?>' + row.id + '" title="Editar ticket" class="fa fa-pencil"></a>': ''),
                (can.delete? '<a href="<?= $bttUrlDelete ?>' + row.id + '" title="Eliminar ticket" class="fa fa-trash" data-confirm="¿Confirma que deseas eliminar el producto?" data-method="post"></a>': '')
            ].join(''); },
            columns = [
                {
                    field: 'id',
                    title: 'ID',
                    align: 'center',
                    width: '60',
                    sortable: true,
                    switchable:false,
                },
                {
                    field: 'nombre',
                    title: 'PRODUCTO',
                    sortable: true,
                },
                /*{
                    field: 'folio',
                    title: 'Folio',
                    sortable: true,
                }, */
                {
                    field: 'descripcion',
                    title: 'DESCRIPCION',
                    sortable: true,
                },
               
              
                {
                    field: 'action',
                    title: 'Acciones',
                    align: 'center',
                    switchable: false,
                    width: '100',
                    class: 'btt-icons',
                    formatter: actions,
                    tableexportDisplay:'none',
                },
            ],
            params = {
                id      : 'tickets',
                color   : true,
                status_color : {
                    <?= Ticket::STATUS_ACTIVE ?>    : 'danger',
                    <?= Ticket::STATUS_PENDIENTE ?> : 'warning',
                    <?= Ticket::STATUS_CERRADO ?>   : 'success',
                },
                element : '.operacion-ticket-index',
                url     : '<?= $bttUrl ?>',

                bootstrapTable : {
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                    onDblClickRow : function(row, $element){
                        window.location.href = '<?= $bttUrlView ?>' + row.id;
                    },

                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });


</script>

<?php endif ?>