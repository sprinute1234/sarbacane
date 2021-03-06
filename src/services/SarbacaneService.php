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
use craft\fields\data\SingleOptionFieldData;
use craft\fields\data\MultiOptionsFieldData;
use craft\fields\MultiSelect;
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

        $curl = $this->setUpCurl($curl, 'GET');

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

        $curl = $this->setUpCurl($curl, 'GET');

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
        $fields = $this->settings['fieldsToSync'];

        foreach ($fields as $field) {
            if ($field['sarbacaneField'] === 'id') {
                return $field['entryField'];
            }
        }

        return null;
    }

    /**
     * @return mixed|null
     */
    protected function getIdSarbacane(Entry $entry)
    {
        $fields = $this->settings['fieldsToSync'];

        foreach ($fields as $field) {
            if ($field['sarbacaneField'] === 'id') {
                return $entry[$field['entryField']];
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

        $fields = $this->settings['fieldsToSync'];

        foreach ($fields as $field) {
            $entryField = $entry[$field['entryField']];
            // for http request PUT sarbacane don't understand id EMAIL_ID
            $sarbacaneField = $field['sarbacaneField'] === 'EMAIL_ID' ? 'email' : $field['sarbacaneField'];
            if ($sarbacaneField !== 'id' && $entryField) {
                switch ($entryField) {
                    case (is_string($entryField)):
                        $data[$sarbacaneField] = $entryField;
                        break;
                    case ($entryField instanceof SingleOptionFieldData && $entryField->serialize() !== null) :
                        $data[$sarbacaneField] = $entryField->serialize();
                        break;
                    case ($entryField instanceof  MultiOptionsFieldData) :
                        $options = [];
                        foreach ($entryField->getOptions() as $option) {
                            if ($option->selected) {
                                $options[] = $option->serialize();
                            }
                        }

                        $data[$sarbacaneField] = $options;
                        break;
                    default:
                        break;
                }
            }
        }

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

        return $this->setUpCurl($curl, 'POST', $data);
    }

    /**
     * @param  Entry  $entry
     *
     * @return mixed
     * @since 0.0.4
     */
    public function updateContact(Entry $entry)
    {
        $data = $this->getDataEntryToSarbacane($entry);
        $idSarbacane = $this->getIdSarbacane($entry);

        $curl = curl_init("https://sarbacaneapis.com/v1/lists/".$this->settings['listId']."/contacts/".$idSarbacane);

        return $this->setUpCurl($curl, 'PUT', $data);
    }

    /**
     * @param  Entry  $entry
     *
     * @return mixed
     */
    public function deleteContact(Entry $entry)
    {
        $idSarbacane = $this->getIdSarbacane($entry);

        $curl = curl_init("https://sarbacaneapis.com/v1/lists/".$this->settings['listId']."/contacts/".$idSarbacane);
        
        return $this->setUpCurl($curl, 'DELETE');
    }

    /**
     * @param $curl
     * @param $data
     *
     * @return mixed
     * @version
     */
    private function setUpCurl($curl, $type, array $data = [])
    {
        if (count($data) > 0) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($curl, CURLOPT_USERPWD, $this->settings['compteId'].":".$this->settings['apiKey']);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        return $curl;
    }
}