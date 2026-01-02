<?php
use yii\helpers\Html;
use yii\helpers\Url;
use Da\QrCode\QrCode;
?>

<?php for ($i=0; $i < $model->cantidad; $i++) { ?>
<?php
$tracked_generado = $model->tracked ."/". ($i + 1);
$qrCode = (new QrCode( $tracked_generado ))
            ->setSize(150)
            ->setErrorCorrectionLevel('H');

$code = [];
$code['qrBase64'] =  $qrCode->writeDataUri();

 ?>
	<table width="100%"  style="font-family: “Helvetica Neue”, Arial, sans-serif; font-size: 14px;margin: 0 auto;">
		<tr align="center" >
			<td colspan="1" align="left" ><img width="20%" src="<?= $code['qrBase64'] ?>" alt='QR' class="qr-code" /></td>
			<td colspan="3"></td>
			<td colspan="1" align="right" ><img width="20%" src="<?= $code['qrBase64'] ?>" alt='QR' class="qr-code" /></td>
		</tr>
		<tr>
			<td></td>
			<td  align="center" colspan="3"  style="font-size: 24px; font-weight: bold;">
		 		<h2><?= $tracked_generado ?></h2>
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
			 	*/?>
		 	</td>
			<td></td>
	    </tr>
		<tr>
			<td></td>
			<td align="center" style="font-weight: bold;" colspan="3">
				<img  src="<?= $code['qrBase64'] ?>" alt='QR' class="qr-code" />
			</td>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<td align="center" style="font-weight: bold;" colspan="3">
				<h3><?= $model->producto->nombre ?></h3>
			</td>
			<td></td>
		</tr>
		<tr  >
			<td colspan="1" align="left"><img width="20%" src="<?= $code['qrBase64'] ?>" alt='QR' class="qr-code" /></td>
			<td colspan="3"></td>
			<td colspan="1" align="right"><img  width="20%" src="<?= $code['qrBase64'] ?>" alt='QR' class="qr-code" /></td>
		</tr>
	</table>

<?php } ?>




