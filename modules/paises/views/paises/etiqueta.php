<?php

use yii\helpers\Url;
use Da\QrCode\QrCode;

$qrCode = (new QrCode($model->folio))
            ->setSize(150)
            ->setMargin(0)
            ->setErrorCorrectionLevel('medium');
$code = [];
$code['qrBase64'] =  $qrCode->writeDataUri();
?>
<table width="100%"  style="font-family: “Helvetica Neue”, Arial, sans-serif; font-size: 14px;margin: 0 auto;">
	<tr>
	 	<td>
		 	<table width="100%">
				<tr style="font-size: 14px; font-weight: bold;">
					Folio: <?= $model->folio  ?>
				</tr>
		 	</table>
	 	</td>
    </tr>
	<tr>
		<th valign="top" align="center"><img src="<?= $code['qrBase64'] ?>" alt='QR' class="qr-code" /></th>
	</tr>
</table>








