<?php
use Da\QrCode\QrCode;

$qrCode = (new QrCode($model->id))
            ->setSize(230)
            ->setMargin(2)
            ->setErrorCorrectionLevel('medium');
$code = [];
$code['qrBase64'] =  $qrCode->writeDataUri();
?>
<body>
	<table width="100%">
		<tr width="100%" align="center">
			<td width="100%" align="center"><img width="100%" align="center" src="<?= $code['qrBase64'] ?>" alt='QR'  class="qr-code" /></td>
		</tr>
	</table>
</body>
