<?php
use yii\helpers\Html;
use app\models\Esys;
use app\models\sucursal\Sucursal;
use app\models\viaje\Viaje;
use app\models\movimiento\MovimientoPaquete;
?>
<!DOCTYPE html>
<html lang="en">
	<body style="font-family: “Helvetica Neue”, Arial, sans-serif;font-size: 17px;width: 1400px;margin: 0 auto;">
		<table style="border-collapse:collapse;border: none;padding: 5px;margin:0; border-spacing: 0px" width="100%">
			<tr>
				<td  width="30%" style=" padding: 10px; border-style: solid; border: 2px"  >
					<?= Html::img('@web/img/sea-black.png', ["height"=>"80px"]) ?>
				</td>
				<td width="70%" style="border-style: solid; border: 2px; padding: 0">
					<table width="100%" style="border-collapse:collapse;border: none; padding: 0">
						<tr>
							<td style="padding: 15px"><h4> PAQUETERIA SEA-EXPRESS </h4></td>
						</tr>
						<tr style="border-top-style: solid; border: 2px">
							<td style="padding: 15px"><h5>Viaje #<?= $model->id  ?> / <?= $model->nombre_chofer  ?></h5></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>

		<table class="table table-striped">
	        <thead>
	            <tr>
	                <th style="text-align: center;">#</th>
	                <th style="text-align: center;">SUCURSAL</th>
	                <th style="text-align: center;">TRACKED</th>
	                <th style="text-align: center;">PRODUCTO</th>
	                <th style="text-align: center;">PESO APROX.</th>
	                <th style="text-align: center;">ESTATUS</th>
	            </tr>
	        </thead>
	        <tbody  style="text-align: center; ">
	        <?php $count = 0; ?>
			<?php foreach (Sucursal::getItemsMexico() as $sucursal_id => $sucursal): ?>
				<?php foreach (Viaje::getSucursalPaqueteAll($model->id, $sucursal_id) as $key => $paquete): ?>
					<?php  $count++  ?>
					<tr>
						<td><?= $count  ?></td>
						<td style="text-align: center;"><?= $paquete["sucursal_receptor"] ?></td>
						<td style="text-align: center;"><?= $paquete["tracked"] ?></td>
						<td style="text-align: center;"><?= $paquete["producto"] ?></td>
						<td style="text-align: center;"><?= $paquete["peso_unitario"] ?></td>
						<td style="text-align: center;"><?= MovimientoPaquete::$tipoLaxTierList[$paquete["tipo_movimiento_top"]]  ?></td>
					</tr>
				<?php endforeach ?>
			<?php endforeach ?>
			</tbody>
		</table>
	</body>
</html>