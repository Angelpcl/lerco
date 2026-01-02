<?php
use yii\helpers\Html;
use app\models\Esys;
use app\models\sucursal\Sucursal;
use app\models\envio\Envio;
$librasTotalMx = 0;
$TotalComision = 0;
$paqueteLax    = 0;
$paqueteMex    = 0;
$paqueteTier   = 0;
$costoLax      = 0;
$costoMex      = 0;
$costoTie      = 0;

$SucursalPaqueteLaxTierra = Sucursal::getEstadoCuentaSucursalLaxTierra($model->id,$date_ini, $date_fin);
$SucursalPaqueteMex = Sucursal::getEstadoCuentaSucursalMex($model->id,$date_ini, $date_fin);

foreach ($SucursalPaqueteLaxTierra as $key => $paquete){
   	$TotalComision = $TotalComision + floatval($paquete["comision"]);
	$librasTotalMx = $librasTotalMx + floatval($paquete["peso_mx_viaje"]);
	if ($paquete["tipo_envio"] == Envio::TIPO_ENVIO_TIERRA ) {
		$paqueteTier    = $paqueteTier + 1;
		$costoTie    	= $costoTie + floatval($paquete["comision"]);

	}
	if ($paquete["tipo_envio"] == Envio::TIPO_ENVIO_LAX ) {
		$paqueteLax    	= $paqueteLax + 1;
		$costoLax 		= $costoLax + floatval($paquete["comision"]);
	}
}

foreach ($SucursalPaqueteMex as $key => $paquete){
	$TotalComision = $TotalComision + floatval($paquete["comision"]);
	$librasTotalMx = $librasTotalMx + floatval($paquete["peso_mx_viaje"]);
	$costoMex    	= $costoMex + floatval($paquete["comision"]);
	$paqueteMex 	= $paqueteMex + 1;
}

?>

<!DOCTYPE html>
<html lang="en">
	<body style="font-family: “Helvetica Neue”, Arial, sans-serif;font-size: 17px;width: 1400px;margin: 0 auto;">
		<table style="border-collapse:collapse;border: none;padding: 5px;margin:0; border-spacing: 0px" width="100%">
			<tr>
				<td style=" padding: 10px;"  >
					<?= Html::img('@web/img/logo-cora_dark.png', ["height"=>"80px"]) ?>
				</td>
			</tr>
			<tr>
				<td width="50%">
					<table style="border-collapse:collapse;border: none;">
						<tr>
							<td>
								<h4>COMERCIALIZADORA LA "CORA"</h4>
								<h5>Victor Manuel Garcia Pedraza</h5>
							</td>
						</tr>
					</table>
				</td>
				<td width="50%" style="background-color: #000; border-radius: 25px;  ">
					<table style="border-collapse:collapse;	border: none;">
						<tr >
							<td style="color:#fff;padding: 20px">
								<h4 style="font-weight: 800;font-family: inherit; ">ESTADO DE CUENTA</h4>
								<p style="font-size: 10px"><strong>Sucursal: </strong> <?= $model->nombre  ?></p>
								<p style="font-size: 10px"><strong>Encargado:</strong> <?=   isset($model->encargadoSucursal->id) ?  $model->encargadoSucursal->nombreCompleto  : '' ?></p>
								<p style="font-size: 10px"><strong>Dirección: </strong> <?=  isset($model->direccion->direccion) ? $model->direccion->direccion : '' ?></p>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<br>
		<br>

		 <h4 style="text-align: center; background-color: #000; color: #fff; padding:15px; ">Resumen de Cuenta </h4>
	 	<?php if ($date_ini && $date_fin): ?>
			<h5>Estado de cuenta <strong><?= Esys::fecha_en_texto($date_ini)  ?> - <?= Esys::fecha_en_texto($date_fin)  ?></strong></h5>
		<?php endif ?>

		<table width="100%">
			<tr>
				<td width="50%">
					<table  width="100%" >
						<tr>
							<td><h5>Paquetes LAX</h5></td>
							<td style="text-align: center;"><strong>#<?=  $paqueteLax ?></strong></td>

						</tr>
						<tr>
							<td><h5>Paquetes TIERRA</h5></td>
							<td style="text-align: center;"><strong>#<?= $paqueteTier  ?></strong></td>
						</tr>
						<tr>
							<td><h5>Paquetes MEX</h5></td>
							<td style="text-align: center;"><strong>#<?= $paqueteMex  ?></strong></td>
						</tr>
					</table>
				</td>
				<td width="50%">
					<table width="100%" >
						<tr>
							<td><h5>Paquetes LAX</h5></td>
							<td style="text-align: center;"><strong>$<?= $costoLax  ?></strong></td>

						</tr>
						<tr>
							<td><h5>Paquetes TIERRA</h5></td>
							<td style="text-align: center;"><strong>$<?= $costoTie  ?></strong></td>
						</tr>
						<tr>
							<td><h5>Paquetes MEX</h5></td>
							<td style="text-align: center;"><strong>$<?= $costoMex  ?></strong></td>
						</tr>
					</table>

				</td>
			</tr>
		</table>

		 <h4 style="text-align: center; background-color: #000; color: #fff; padding:15px;">Detalles de Operación </h4>
		<table style="border-collapse:collapse;	border: none;border-style: solid; border-width: 2px; padding: 0;margin:0; border-spacing: 0px" width="100%">
			<thead>
				<tr >
					<td style="background-color: #000; padding: 0px; border:0px; color: #fff; padding: 5px; text-align: center;"># PAQUETE</th>
					<td style="background-color: #000; padding: 0px; border:0px; color: #fff; padding: 5px; text-align: center;">SERVICIO</th>
					<td style="background-color: #000; padding: 0px; border:0px; color: #fff; padding: 5px; text-align: center;">PESO MX</th>
					<td style="background-color: #000; padding: 0px; border:0px; color: #fff; padding: 5px; text-align: center;">COMISIÓN</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($SucursalPaqueteLaxTierra as $key => $paquete): ?>
					<tr>
						<td align="center" style="padding: 5px;  border-right-style: solid; border-right: 1px" ><?= $paquete["tracked"
						]  ?></td>
						<td align="center" style="padding: 5px;  border-right-style: solid; border-right: 1px;"><p style="font-size: 9px"><?= Envio::$tipoList[ $paquete["tipo_envio"]]  ?></p></td>
						<td align="center" style="padding: 5px;  border-right-style: solid; border-right: 1px"><?= number_format($paquete["peso_mx_viaje"],2)   ?></td>
						<td align="center" style="padding: 5px;  border-right-style: solid; border-right: 1px"><?= number_format($paquete["comision"],2)   ?></td>
					</tr>

				<?php endforeach ?>
				<?php foreach ($SucursalPaqueteMex as $key => $paquete): ?>
					<tr>
						<td align="center" style="padding: 5px;  border-right-style: solid; border-right: 1px" ><?= $paquete["tracked"
						]  ?></td>
						<td align="center" style="padding: 5px;  border-right-style: solid; border-right: 1px;"><p style="font-size: 9px"><?= Envio::$tipoList[ $paquete["tipo_envio"]]  ?></p></td>
						<td align="center" style="padding: 5px;  border-right-style: solid; border-right: 1px"><?= number_format($paquete["peso_mx_viaje"],2)   ?></td>
						<td align="center" style="padding: 5px;  border-right-style: solid; border-right: 1px"><?= number_format($paquete["comision"],2)   ?></td>
					</tr>
				<?=  $TotalComision = $TotalComision + floatval($paquete["comision"])  ?>
				<?=  $librasTotalMx = $librasTotalMx + floatval($paquete["peso_mx_viaje"]) ?>
				<?php endforeach ?>

			</tbody>
			<tfoot>
				<tr>
					<td colspan="2" style=""></td>
					<td style="background-color: #000; padding: 0px; border:0px; color: #fff; padding: 5px;">
						<table width="100%" style="border-collapse:collapse;border: none;border-spacing: 0px" width="100%">
							<tr><td style="background-color: #000; padding: 0px; border:0px; color: #fff; padding: 5px;"><strong><small>TOTAL (LIBRAS)</small></strong></td></tr>
							<tr><td style="background-color: #000; padding: 0px; border:0px; color: #fff; padding: 5px;"><strong><small>TOTAL (COMISIÓN)</small></strong></td></tr>
						</table>
					</td>
					<td  style=" padding: 5px;">
						<table style="border-spacing: 0px" >
							<tr  ><td style="padding: 10px; text-align: center;font-size: 14px"><strong><?= number_format($librasTotalMx,2)  ?> lb</strong></td></tr>
							<tr  ><td style="padding: 10px; text-align: center;font-size: 14px"><strong>$ <?= number_format($TotalComision, 2) ?></strong></td></tr>
						</table>
					</td>
				</tr>
			</tfoot>
		</table>
		<br>
		<br>
		<br>
		<br>
		<table width="100%">
			<tr>
				<td width="50%">
					<table  width="100%" >
						<tr >
							<td style="text-align: center;">____________________________________</td>
						</tr>
						<tr>
							<td style="text-align: center;"><h5>Firma quien recibe</h5></td>
						</tr>
					</table>
				</td>
				<td width="50%">
					<table width="100%" >
						<tr >
							<td style="text-align: center;">____________________________________</td>
						</tr>
						<tr >
							<td style="text-align: center;"><h5>Firma de quien entrega</h5></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<br>
	</body>
</html>

