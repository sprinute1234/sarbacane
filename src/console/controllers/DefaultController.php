<?php
/**
 * Sarbacane plugin for Craft CMS 3.x
 *
 * Liaison craft sarbacane
 *
 * @link      crochetcedric@gmail.com
 * @copyright Copyright (c) 2020 Sprinute
 */

namespace sprinute1234\sarbacane\console\controllers;

use sprinute1234\sarbacane\Sarbacane;

use Craft;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Default Command
 *
 * @author    Sprinute
 * @package   Sarbacane
 * @since     0.0.1
 */
class DefaultController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Handle sarbacane/default console commands
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $result = 'something';

        echo "Welcome to the console DefaultController actionIndex() method\n";

        return $result;
    }

    /**
     * Handle sarbacane/default/do-something console commands
     *
     * @return mixed
     */
    public function actionDoSomething()
    {
        $result = 'something';

        echo "Welcome to the console DefaultController actionDoSomething() method\n";

        return $result;
    }
}
