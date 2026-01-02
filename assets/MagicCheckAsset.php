<?php

namespace app\assets;

use yii\web\AssetBundle;

class MagicCheckAsset extends AssetBundle
{
    // 🔴 EVITA que Yii busque en vendor/bower
    public $sourcePath = null;

    // usamos CSS local
    public $css = [
        'css/magic-check.css',
    ];
}
