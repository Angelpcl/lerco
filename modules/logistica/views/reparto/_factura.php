<?php
use yii\helpers\Html;
use app\models\reparto\RepartoDetalle;
use Da\QrCode\QrCode;
/*$qrCode = (new QrCode($model->id))
            ->setSize(150)
            ->setMargin(0)
            ->setErrorCorrectionLevel('medium');
$code = [];
$code['qrBase64'] =  $qrCode->writeDataUri(); */

$paquete_count 	= count(RepartoDetalle::getRepartoDetalleSucursal($model->id,$sucursal->id));
$paquete_max	= 3;
$paquete_add	= ceil($paquete_count / $paquete_max);
$paquete_show 	= 0;
?>

	<!DOCTYPE html>
	<html lang="en">
		<body>
<?php for ($i=0; $i < $paquete_add; $i++) {  ?>
			<table style="border-style: solid; border-width: 2px; padding: 5px;margin:0; border-spacing: 0px" width="100%">
				<tr>
					<td style=" padding: 10px;" align="center" >
						<?= Html::img('@web/img/sea-black.png', ["height"=>"80px"]) ?>
					</td>
					<td colspan="2" align="center" style="background-color: #000; padding: 0px; border:0px; color: #fff; padding: 20px;">
						<h3>CARTA PORTE</h3>
						<h3>SEA Paqueteria S.A de C.V</h3>
					</td>
				</tr>
			</table>
			<table style="border-style: solid; border-width: 2px; padding: 0;margin:0; border-spacing: 0px" width="100%">
				<tr>
					<td colspan="2" >
						<table width="100%">
							<tr>
								<td width="10%"><strong>R.F.C.</strong></td>
								<td width="25%" style="border-bottom: 2px; border-style: solid; text-align: center; "><p >xxxxxxxxxxxxx</p></td>
								<td width="10%"><strong>C.U.R.P</strong></td>
								<td width="25%" style="border-bottom: 2px; border-style: solid; text-align: center; "><p>xxxxxxxxxxxxxxxxxxxx</p></td>
							</tr>
						</table>
					</td>
					<td align="center">
						<table width="100%" style="border-spacing: 0px">
							<tr>
								<td style="background-color: #000; padding: 0px; border:0px; color: #fff; padding: 20px; border-spacing: 0px">FOLIO</td>
							</tr>
							<tr><td><?= $model->id  ?></td></tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="2" >
						<table width="100%">
							<tr >
								<td><strong>Regimen</strong></td>
								<td style="border-bottom: 2px; border-style: solid; text-align: center; "><p>PERSONA FISICA DE REGIMEN DE ACTIVIDAD EMPRESARIAL</p></td>
							</tr>
							<tr >
								<td><strong>Domicilio</strong></td>
								<td style="border-bottom: 2px; border-style: solid; text-align: center; "> PUEBLA, PUE. </td>
							</tr>
						</table>
					</td>
					<td align="center" >
						<table width="100%" style="border-spacing: 0px">
							<tr >
								<td style="background-color: #000; padding: 0px; border:0px; color: #fff; padding: 20px; border-spacing: 0px">FECHA</td>
							</tr>
							<tr><td><?= date("Y-m-d")  ?></td></tr>
						</table>
					</td>
				</tr>
			</table>
			<br>
			<br>
			<table style="border-style: solid; border-width: 2px; padding: 0;margin:0; border-spacing: 0px" width="100%">
				<tr >
					<td width="25%" style="background-color: #000;  color: #fff; padding: 10px; text-align: center;">NOMBRE</td>
					<td colspan="3" style="border-style: solid; border-width: 1px;"><?= isset($sucursal->encargadoSucursal->nombreCompleto) ? $sucursal->nombre ." [". $sucursal->encargadoSucursal->nombreCompleto ."]" : $sucursal->nombre  ?></td>
				</tr>
				<tr>
					<td width="25%" style="background-color: #000;  color: #fff; padding: 10px; text-align: center;">DOMICILIO</td>
					<td style="border-style: solid; border-width: 1px;"><p style="font-size: 9px"><?=  isset($sucursal->direccion->direccion) ? $sucursal->direccion->direccion : ''  ?></p></td>
					<td width="20%" style="background-color: #000;  color: #fff; padding: 10px;text-align: center;" >CIUDAD</td>
					<td width="20%" style="border-style: solid; border-width: 1px; text-align: center;"><?= isset($sucursal->direccion->municipio->singular) ? $sucursal->direccion->municipio->singular : ''  ?></td>
				</tr>
				<tr>
					<td width="25%" style="background-color: #000;  color: #fff; padding: 10px; text-align: center;">R.F.C.</td>
					<td colspan="3"></td>
				</tr>
			</table>
			<br>
			<br>
			<?php
				$RepartoDetalle = RepartoDetalle::getRepartoDetalleSucursal($model->id,$sucursal->id);
			 ?>
			<table style="border-style: solid; border-width: 2px; padding: 0;margin:0; border-spacing: 0px" width="100%">
				<thead>
					<tr >
						<td style="background-color: #000; padding: 0px; border:0px; color: #fff; padding: 20px; text-align: center;"># PAQUETE</th>
						<td style="background-color: #000; padding: 0px; border:0px; color: #fff; padding: 20px; text-align: center;">PRODUCTO</th>
						<td style="background-color: #000; padding: 0px; border:0px; color: #fff; padding: 20px; text-align: center;">PRECIO U.</th>
						<td style="background-color: #000; padding: 0px; border:0px; color: #fff; padding: 20px; text-align: center;">IMPORTE</th>
					</tr>
				</thead>
				<tbody>
					<?php $precio_importe = 0;  ?>
					<?php $precio_unitario = 0;  ?>

					<?php $item_add = 0;  ?>

					<?php foreach ($RepartoDetalle as $key => $paquete): ?>
						<?php if ($item_add < $paquete_max): ?>
							<?php if ($paquete_show == $key ): ?>
								<tr>
									<td align="center" style="padding: 20px;  border-right-style: solid; border-right: 1px" ><?= $paquete->tracked  ?></td>
									<td align="center" style="padding: 20px;  border-right-style: solid; border-right: 1px;"><p style="font-size: 9px"><?= $paquete->paquete->producto->nombre  ?></p></td>
									<td align="center" style="padding: 20px;  border-right-style: solid; border-right: 1px"><?= number_format($paquete->paquete->envio->total,2)   ?></td>
									<td align="center" style="padding: 20px;  border-right-style: solid; border-right: 1px"><?= number_format($paquete->paquete->valor_declarado,2)   ?></td>
								</tr>
								<?php $precio_importe = $precio_importe +  $paquete->paquete->valor_declarado;  ?>
								<?php $precio_unitario = $precio_unitario +  $paquete->paquete->envio->total;  ?>

							<?php $item_add 	= $item_add + 1;  ?>
							<?php $paquete_show = $paquete_show + 1;  ?>
							<?php endif ?>
						<?php endif ?>
					<?php endforeach ?>
				</tbody>
				<tfoot >
					<tr>
						<td colspan="2" style="border-style: solid; border-width: 1px;"></td>
						<td style="background-color: #000; padding: 0px; border:0px; color: #fff; padding: 20px;">
							<table width="100%" style="border-spacing: 0px" width="100%">
								<tr><td style="background-color: #000; padding: 0px; border:0px; color: #fff; padding: 20px;"><strong><small>IMPORTE</small></strong></td></tr>
								<tr><td style="background-color: #000; padding: 0px; border:0px; color: #fff; padding: 20px;"><strong><small>I.V.A</small></strong></td></tr>
								<tr><td style="background-color: #000; padding: 0px; border:0px; color: #fff; padding: 20px;"><strong><small>TOTAL</small></strong></td></tr>
							</table>
						</td>
						<td  style=" padding: 20px;">
							<table style="border-spacing: 0px" >
								<tr ><td style="padding: 20px; text-align: center;"><?= number_format($precio_importe,2) ?></td></tr>
								<tr  ><td style="padding: 20px; text-align: center;"><?= number_format($precio_unitario,2)  ?></td></tr>
								<tr  ><td style="padding: 20px; text-align: center;"><?= number_format($precio_importe + $precio_unitario, 2) ?></td></tr>
							</table>
						</td>
					</tr>
				</tfoot>
			</table>
			<br>
			<br>
			<br>
			<table width="100%" >
				<tr>
					<td colspan="2" align="center" valign="top" width="50%">

						<?= Html::img('https://static-unitag.com/images/help/QRCode/qrcode.png?mh=07b7c2a2', ["width"=>"17%"]) ?>
					</td>
					<td>
						<table width="100%" style="border-style: solid; border-width: 1px;  border-spacing: 0px">
							<tr>
								<td style="padding: 20px; background-color: #000;  color: #fff; ">
									CANTIDAD TOTAL CON LETRA
								</td>
							</tr>
							<tr>
								<td style="padding: 20px;  ">

								</td>
							</tr>
						</table>

					</td>
				</tr>
			</table>
			<table>
				<tr>
					<td>
						<strong><small>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Nobis praesentium itaque alias asperiores</small></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong><small>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Nobis praesentium itaque alias asperiores</small></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong><small>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Nobis praesentium itaque alias asperiores</small></strong>
					</td>
				</tr>
			</table>
<?php } ?>
		</body>
	</html>

