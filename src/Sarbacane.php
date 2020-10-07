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
use craft\services\Elements;
use craft\events\ElementEvent;
use yii\base\ModelEvent;
use craft\helpers\ElementHelper;
use craft\elements\Entry;
use craft\base\Element;
use yii\base\Event;

use mysql_xdevapi\Exception;
use sprinute1234\sarbacane\models\Settings;
use yii\helpers\VarDumper;

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
    public $hasCpSettings = true;

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

        Event::on(
            Elements::class,
            Elements::EVENT_BEFORE_SAVE_ELEMENT,
            function (ElementEvent $event) {
                $entry = $event->sender;

//                VarDumper::dump($event->isNew);die();
                if ($event->element instanceof Entry && $event->isNew) {
//                    if (ElementHelper::isDraftOrRevision($entry)) {
//                        return;
//                    }
                    $data = [
                        "email" => "test@teshgf.com",
                        "phone" => "0102030405"
                    ];
                    $curl = curl_init("https://sarbacaneapis.com/v1/lists/".$this->getSettings()['listId']."/contacts");
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                    curl_setopt($curl, CURLOPT_USERPWD, $this->getSettings()['compteId'].":".$this->getSettings()['apiKey']);
                    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json'
                    ]);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                    try {
                        $response = curl_exec($curl);
                    } catch (Exception $e) {
                        throw Error();
                    }
                    curl_close($curl);
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

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
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
