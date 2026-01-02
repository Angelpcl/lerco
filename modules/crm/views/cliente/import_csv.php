<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Esys;

$errores      = 0;
$sobrescritos = 0;
$no_trc_id    = 0;

$this->title = 'Importar clientes';
$this->params['breadcrumbs'][] = 'Crm';
$this->params['breadcrumbs'][] = ['label' => 'Clientes', 'url' => ['index']];
?>

<div class="aerovias-comisiones-create">
	<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
	<div class="row">
		<div class="ibox col-lg-6 col-md-7">
			<div class="ibox-title">
				<h5 >Importar CSV clientes</h5>

			</div>
			<div class="ibox-content" style="text-align: center;">
					<i class="demo-pli-upload-to-cloud icon-5x"></i>
					<?= $form->field($model, 'csv_file')->fileInput(['class' => 'btn btn-default mar-btm', 'accept' => '.csv, .txt']) ?>
					<?= Html::submitButton('<i class="fa fa-cloud-download mar-rgt-5px"></i> Importar ahora', ['class' => 'btn btn-success' ]) ?>
			</div>
		</div>
	</div>
	<?php ActiveForm::end(); ?>

	<?php if ($model->rows_details): ?>
		<div id="results"></div>

		<div class="ibox rows-details">
			<div class="ibox-content">
				<div class="table-responsive">
					<table class="table table-striped">
						<thead>
							<tr>
								<th style="text-align: center;">#</th>
								<th style="text-align: center;">Id</th>
								<th style="text-align: center;">Nombre completo</th>
								<th style="text-align: center;">Origen</th>
								<th style="text-align: center;">Email</th>
								<th style="text-align: center;">Estatus</th>
								<th style="text-align: center;">Message</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($model->rows_details as $key => $value):
								if($value['error']){
									$errores++;
									$estatus  = '<span class="label label-danger">No fue posible crear el registro.</span>';
									$estatus .= '<br><span class="label label-danger">' . $value['error'] . '</span>';

								}else{
									$estatus = '<span class="label label-success">Se agrego correctamente el nuevo cliente.</span>';
								}
							?>
								<tr>
									<td align="center"><?= $key +1 ?></td>
									<td align="center"><?= Html::a($value['id'], ['/crm/cliente/view', 'id' => $value['id']], ['class' => 'text-primary', 'target'=>'_blank']) ?></td>
									<td align="center"><?= $value['nombre_completo'] ?></td>
									<td align="center"><?= $value['origen'] ?></td>
									<td align="center"><?= $value['email'] ?></td>
									<td align="center"><?= $value['status'] ?></td>
									<td align="center"><?= $estatus ?></td>
								</tr>
							<?php endforeach ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<div class="ibox results">
			<div class="ibox-content">
				<h5 >Registros de Clientes importados</h5>
			</div>
			<div class="ibox-content">
				<p>Registros procesados: <strong><?= count($model->rows_details) ?></strong></p>
				<p>Procesados correctamente: <strong><?= count($model->rows_details) - $errores ?></strong></p>
				<p>Errores: <strong><?= $errores ?></strong></p>
			</div>
		</div>


	<script type="text/javascript">
    	$(document).ready(function(){
    		$('.results')
    			.detach()
    			.appendTo('#results');
    	});
	</script>

	<?php endif ?>
</div>
