<?php
/**
 * Sarbacane plugin for Craft CMS 3.x
 *
 * Liaison craft sarbacane
 *
 * @link      crochetcedric@gmail.com
 * @copyright Copyright (c) 2020 Sprinute
 */

namespace sprinute1234\sarbacane\models;

use sprinute1234\sarbacane\Sarbacane;

use Craft;
use craft\base\Model;

/**
 * @author    Sprinute
 * @package   Sarbacane
 * @since     0.0.1
 */
class Settings extends Model
{
    /**
     * @var string
     */
    public $apiKey = '';

    /**
     * @var string
     */
    public $listId = '';

    /**
     * @var string
     */
    public $compteId = '';

    /**
     * @var Section
     */
    public $section = '';

    /**
     * @var array
     */
    public $champsToSync = [];

    /**
     * @return array[]
     */
    public function rules(): array
    {
        return [
            [['apiKey', 'listId', 'compteId', 'section'], 'string'],
            [['apiKey', 'compteId'], 'required'],
        ];
    }
}
