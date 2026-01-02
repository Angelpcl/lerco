<?php

use yii\helpers\Html;
use yii\helpers\Url;
use Da\QrCode\QrCode;
use app\models\envio\Envio;
use Picqer\Barcode;
use app\models\producto\Producto;

$generator = new Barcode\BarcodeGeneratorPNG();

?>

<?php for ($i = 0; $i < $model->cantidad; $i++) { ?>
	<?php
	 $productos = $model->detalleProducto ? json_decode($model 	->detalleProducto->detalle_json, true) : null;
	$tracked_generado = $model->tracked . "/" . ($i + 1);
	$qrCode = (new QrCode($tracked_generado))
		->setSize(150)
		->setErrorCorrectionLevel('H');

	$code = [];
	$code['qrBase64'] =  $qrCode->writeDataUri();

	?>
	<table width="100%" style="font-family: “Helvetica Neue”, Arial, sans-serif; font-size: 14px; margin-left: 25px;margin-right:  25px; ">
		<tr align="center">
			<td colspan="1" align="left"><img width="20%" src="<?= $code['qrBase64'] ?>" alt='QR' class="qr-code" /></td>
			<td align="center" colspan="3" style="font-size: 24px; font-weight: bold;">
				<h2><?= $tracked_generado ?></h2>
			</td>
			<td colspan="1" align="right"><img width="20%" src="<?= $code['qrBase64'] ?>" alt='QR' class="qr-code" /></td>
		</tr>
		<tr>
			<td></td>
			<td align="center" colspan="3" style="font-size: 24px; font-weight: bold;">
				<?php /* ?>
			 	<table width="100%">
					<tr>
						<td>
				            <?= Html::img('@web/img/logo_cora.jpeg', ["height"=>"150px", "width" => "250px"]) ?>
				        </td>

						<td   style="font-size: 24px; font-weight: bold;">
				        	Tracked: <?= $tracked_generado ?>
				        </td>
					</tr>
			 	</table>
			 	*/ ?>
			</td>
			<td>

			</td>
		</tr>
		<tr>
			<td></td>
			<td align="center" style="font-weight: bold;" colspan="3">
				<img src="<?= $code['qrBase64'] ?>" alt='QR' class="qr-code" />
			</td>
			<td align="center">
				<?php $folio =  explode('-', $model->envio->folio);   ?>
				<?= Envio::createImage($folio[1]) ?>
			</td>
		</tr>
		<tr>

			<td colspan="1" align="left"><img width="20%" src="<?= $code['qrBase64'] ?>" alt='QR' class="qr-code" /></td>
			<td colspan="3" align="center">
			</td>
			<td colspan="1" align="right"><img width="20%" src="<?= $code['qrBase64'] ?>" alt='QR' class="qr-code" /></td>
		</tr>
	</table>
	<table width="100%" style="font-family: “Helvetica Neue”, Arial, sans-serif; font-size: 14px; margin-left: 25px;margin-right:  25px; ">
		<tr>
			<td align="center">
				<?php $redColor = [255, 0, 0]; ?>
				<?= '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($tracked_generado, $generator::TYPE_CODE_128)) . '" >';  ?>
			</td>
		</tr>
		<tr>
			<td style="font-weight: bold;" colspan="1" height="50%" height="200px">
				
				<h3><small>Producto: </small><?= $model->producto->nombre    ?></h3>
				<h3><small>Peso: </small><?= isset($productos[$i]) && $productos[$i]['tipo_producto'] == Producto::TIPO_CAJA_SIN_LIMITE
                                                        ? "Sin límite"
                                                        : (isset($productos[$i]['peso_max']) ? $productos[$i]['peso_max'] . " LB" : "")?></h3>
				<h3><small>Sucursal: </small><?= $model->sucursalReceptor->nombre  ?></h3>
				<h3><small>País de destino: </small><?= $model->pais ? $model->pais->nombre :""   ?></h3>
				<h3><small>Entrega E. : </small><?= isset($model->direccion->estado->singular) ? $model->direccion->estado->singular : $model->clienteReceptor->estadoOutMX ?></h3>
				<h3><small>Entrega M. :</small><?= isset($model->direccion->municipio->singular) ? $model->direccion->municipio->singular : $model->clienteReceptor->municipioOutMX ?></h3>
				<?php /* ?>
				<h3><?= isset($model->sucursalReceptor->direccion->estado->singular) ? $model->sucursalReceptor->direccion->estado->singular : '' ?></h3>
				<h3><?= isset($model->sucursalReceptor->direccion->municipio->singular) ? $model->sucursalReceptor->direccion->municipio->singular : '' ?></h3>
				<h3 style="margin-bottom: 5%;"><?= isset($model->sucursalReceptor->rutaSucursals->ruta->nombre) ? $model->sucursalReceptor->rutaSucursals->ruta->nombre : '' ?></h3>
				*/ ?>
			</td>
		</tr>
	</table>
<?php } ?>