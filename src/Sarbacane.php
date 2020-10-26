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
use sprinute1234\sarbacane\services\SarbacaneService;
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
    public $schemaVersion = '0.0.2';

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

        $this->setComponents(['sarbacane' => SarbacaneService::class]);

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Event::on(
            Entry::class,
            Element::EVENT_BEFORE_SAVE,
            function (ModelEvent $e) {
                $entry = $e->sender;

                if(ElementHelper::isDraftOrRevision($entry)) {
                    return;
                }

                $service = $this->getSarbacaneService();
                if ($entry instanceof Entry && $service->checkIsOk($entry, $e) && $e->isNew && $entryChamp = $service->getChampsId()) {
                    $curl = $service->addContact($entry);
                    try {
                        $response = curl_exec($curl);
                        $entry[$entryChamp] = json_decode($response)[0];
                    } catch (Exception $e) {
                        throw Error();
                    }
                    curl_close($curl);
                }
            }
        );

        Event::on(
            Entry::class,
            Element::EVENT_BEFORE_DELETE,
            function (ModelEvent $e) {
                $entry = $e->sender;

                if (ElementHelper::isDraftOrRevision($entry)) {
                    return;
                }

                $service = $this->getSarbacaneService();
                if ($entry instanceof Entry && $service->checkIsOk($entry, $e)) {
                    $curl = $service->deleteContact($entry);

                    try {
                        $response = curl_exec($curl);
                    } catch (\Exception $e) {
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
        $allSections = Craft::$app->sections->allSections;

        foreach ($allSections as $section) {
            $sections[$section['id']] = $section['name'];
        }

        $listeSarbacane = [];
        if ($this->getSettings()['apiKey'] && $this->getSettings()['compteId']) {
            $listeSarbacane = $this->getSarbacaneService()->getListeContact();
        }

        $champsSection = [];
        $champsSarbacane = [];
        if ($this->getSettings()['listId'] && $section = $this->getSettings()['section']) {
            $champsSarbacane = $this->getSarbacaneService()->getListeChamps();
            $champs = Craft::$app->sections->getSectionById($section)->getEntryTypes()['0']->getFieldLayout()->getFields();
            foreach ($champs as $champ) {
                $champsSection[$champ['handle']] = $champ['name'];
            }
        }


        return Craft::$app->view->renderTemplate(
            'sarbacane/settings',
            [
                'settings' => $this->getSettings(),
                'allSections' => $sections,
                'listeSarbacane' => $listeSarbacane,
                'champsSarbacane' => $champsSarbacane,
                'champsSection' => $champsSection
            ]
        );
    }

    protected function getSarbacaneService()
    {
        $service = $this->get('sarbacane');
        return $service;
    }
}
