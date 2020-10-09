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
     * @param  Entry  $entry
     *
     * @return mixed
     */
    public function addContact(Entry $entry)
    {
        $data = [
            "email" => $entry['email'],
        ];
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

    public function deleteContact(Entry $entry)
    {
        $data = [
            "email" => $entry['email'],
        ];

        $curl = curl_init("https://sarbacaneapis.com/v1/lists/".$this->settings['listId']."/contacts/".$entry['contactId']);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        
        return $this->setUpCurl($curl, $data);
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