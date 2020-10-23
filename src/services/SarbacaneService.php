<?php
/**
 * Sarbacane plugin for Craft CMS 3.x
 *
 * @link      crochetcedric@gmail.com
 * @copyright Copyright (c) 2020 Sprinute
 */

namespace sprinute1234\sarbacane\services;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use sprinute1234\sarbacane\Sarbacane;
use yii\base\ModelEvent;
use yii\helpers\VarDumper;

/**
 * @author    Sprinute1234
 * @package   Sarbacane
 * @since     0.0.1
 */
class SarbacaneService extends Component
{
    protected $settings;

    /**
     * SarbacaneService constructor.
     */
    public function __construct()
    {
        $this->settings = Sarbacane::getInstance()->getSettings();
    }

    /**
     * @param  Entry  $entry
     * @param  ModelEvent  $e
     *
     * @return bool
     */
    public function checkIsOk(Entry $entry, ModelEvent $e)
    {
        return (
            $this->settings['section']
            && $this->settings['section'] === $entry['sectionId']
            && $e->isValid
        );
    }

    /**
     * @return array
     */
    public function getListeContact()
    {
        $curl = curl_init("https://sarbacaneapis.com/v1/lists");
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        $curl = $this->setUpCurl($curl);

        $listes = [];
        $result = curl_exec($curl);
        curl_close($curl);

        foreach (json_decode($result, true) as $liste) {
            $listes[$liste['id']] = $liste['name'];
        }

        return $listes;
    }

    /**
     * @return array
     */
    public function getListeChamps()
    {
        $curl = curl_init("https://sarbacaneapis.com/v1/lists/".$this->settings['listId']."/fields");
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        $curl = $this->setUpCurl($curl);

        $fields = [];
        $result = curl_exec($curl);
        curl_close($curl);

        foreach (json_decode($result, true)['fields'] as $field) {
            $fields[$field['id']] = $field['caption'];
        }

        return $fields;
    }

    /**
     * @return mixed|null
     */
    public function getChampsId()
    {
        $champs = $this->settings['champsToSync'];

        foreach ($champs as $champ) {
            if ($champ['sarbacaneField'] === 'id') {
                $champSection = Craft::$app->fields->getFieldById($champ['entryField']);
                return $champSection['name'];
            }
        }

        return null;
    }

    /**
     * @return mixed|null
     */
    protected function getIdSarbacane(Entry $entry)
    {
        $champs = $this->settings['champsToSync'];

        foreach ($champs as $champ) {
            if ($champ['sarbacaneField'] === 'id') {
                $idSarbacane = Craft::$app->fields->getFieldById($champ['entryField']);
                return $entry[$idSarbacane['name']];
            }
        }

        return null;
    }

    /**
     * @param  Entry  $entry
     *
     * @return array
     */
    protected function getDataEntryToSarbacane(Entry $entry)
    {
        $data = [];

        return $data;
    }

    /**
     * @param  Entry  $entry
     *
     * @return mixed
     */
    public function addContact(Entry $entry)
    {
        $data = $this->getDataEntryToSarbacane($entry);

        $curl = curl_init("https://sarbacaneapis.com/v1/lists/".$this->settings['listId']."/contacts");

        return $this->setUpCurl($curl, $data);
    }

//    public function editContact(Entry $entry)
//    {
//        $data = [
//            "email" => $entry['email'],
//        ];
//
//        $curl = curl_init("https://sarbacaneapis.com/v1/lists/".$this->settings['listId']."/contacts/".$entry['contactId']);
//        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
//        $this->setUpCurl($curl, $data);
//
//        $curl = curl_init("https://sarbacaneapis.com/v1/lists/".$this->settings['listId']."/contacts");
//        return $this->setUpCurl($curl, $data);
//    }

    /**
     * @param  Entry  $entry
     *
     * @return mixed
     */
    public function deleteContact(Entry $entry)
    {
        $idSarbacane = $this->getIdSarbacane($entry);

        $curl = curl_init("https://sarbacaneapis.com/v1/lists/".$this->settings['listId']."/contacts/".$idSarbacane);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        
        return $this->setUpCurl($curl);
    }

    /**
     * @param $curl
     * @param $data
     *
     * @return mixed
     */
    private function setUpCurl($curl, $data = [])
    {
        if (count($data) > 0) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($curl, CURLOPT_USERPWD, $this->settings['compteId'].":".$this->settings['apiKey']);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        return $curl;
    }
}