<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\envio\EnvioDetalle;
?>


<table width="100%"  style="font-family: “Helvetica Neue”, Arial, sans-serif; ">
	<tr>
		<td align="left" style="font-weight: bold; font-size: 18px; ">
	 		#PAQUETE
	 	</td>
	</tr>
	<tr>
		<td>
			<h1 style="font-size: 35px"><?= $model["tracked"]?></h1>
			<small>Interno # (<?= $model["count_item_show"] . " / " . $model["paquetes_envio_sucursal"]  ?>)</small>
		</td>
		<td align="right">
			<?= Html::img('@web/img/sea-black.png', ["height"=>"100px", "style" => "margin-top: -50px" ]) ?>
		</td>
	</tr>
	<tr>
		<td>
			<strong>RUTA: </strong>
			<?php if (isset($model["is_reenvio"]) && $model["is_reenvio"]  == EnvioDetalle::REENVIO_ON): ?>
					<small style="font-size: 10px"><strong>Estado:<?= $model["estado"]  ?>, Municipio: <?= $model["municipio"]  ?></strong></small>
				<?php else: ?>
					<small><strong><?= $model["ruta_nombre"]  ?></strong></small>
			<?php endif ?>
		</td>
		<td>
			<strong>SUCURSAL: </strong> <small style="font-size: 10px"><strong><?= $model["sucursal_receptor"]  ?></strong></small>
		</td>
	</tr>
</table>
<hr style="color: black">
<table width="100%"  style="font-family: “Helvetica Neue”, Arial, sans-serif; ">
	<tr>
		<td>
			<h5>ENVIA: <small><?= $model["emisor_nombre"]  ?></small></h5>
			<h5>TELEFONO: <small><?= $model["emisor_telefono"]  ?> / <?= $model["emisor_telefono_movil"]  ?></small></h5>
			<h5>RECIBE: <small><?= $model["receptor_nombre"]  ?></small></h5>
			<h5>TELEFONO: <small><?= $model["receptor_telefono"]  ?> / <?= $model["receptor_telefono_movil"]  ?></small></h5>
			<?php if (isset($model["is_reenvio"]) && $model["is_reenvio"]  == EnvioDetalle::REENVIO_ON): ?>
				<h5>REENVIO: SI / <small style="font-size: 7px"> Estado:<?= $model["estado"]  ?>, Municipio: <?= $model["municipio"]  ?>, Dir: <?= $model["direccion"]  ?></small></h5>
			<?php endif ?>
		</td>
		<td align="center">
			<h2>PIEZA</h2>
			<h4>1</h4>
		</td>
	</tr>
</table>
<hr style="color: black">
<table width="100%"  style="font-family: “Helvetica Neue”, Arial, sans-serif; ">
	<tr>
		<td>
			<h4>DESCRIPCIÓN: </h4>
		</td>
		<td align="center">
			<p style="font-size: 7px"><?= $model["observaciones"]  ?> </p>
		</td>
	</tr>
</table>





