<?php
/**
 * Sarbacane plugin for Craft CMS 3.x
 *
 * Liaison craft sarbacane
 *
 * @link      crochetcedric@gmail.com
 * @copyright Copyright (c) 2020 Sprinute
 */

namespace sprinute1234\sarbacane;


use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\console\Application as ConsoleApplication;

use sprinute1234\sarbacane\models\Settings;
use yii\base\Event;

/**
 * Class Sarbacane
 *
 * @author    Sprinute
 * @package   Sarbacane
 * @since     0.0.1
 *
 */
class Sarbacane extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Sarbacane
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '0.0.1';

    /**
     * @var bool
     */
    public $hasCpSettings = false;

    /**
     * @var bool
     */
    public $hasCpSection = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'sprinute1234\sarbacane\console\controllers';
        }

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Craft::info(
            Craft::t(
                'sarbacane',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================
    protected function createSettingsModel()
    {
        return new Settings();
    }

    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'sarbacane/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
