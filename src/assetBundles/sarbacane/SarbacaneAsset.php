<?php
/**
 * Sarbacane plugin for Craft CMS 3.x
 *
 * Liaison craft sarbacane
 *
 * @link      crochetcedric@gmail.com
 * @copyright Copyright (c) 2020 Sprinute1234
 */

namespace sprinute1234\sarbacane\assetbundles\sarbacane;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Sprinute1234
 * @package   Sarbacane
 * @since     0.0.1
 */
class SarbacaneAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@sprinute1234/sarbacane/assetbundles/sarbacane/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Sarbacane.js',
        ];

        $this->css = [
            'css/Sarbacane.css',
        ];

        parent::init();
    }
}
