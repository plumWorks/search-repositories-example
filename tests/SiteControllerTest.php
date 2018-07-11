<?php
/**
 * Created by PhpStorm.
 * User: plum
 * Date: 11/07/2018
 * Time: 11:56
 */

namespace app\tests;

use app\controllers\SiteController;
use PHPUnit\Framework\TestCase;
use yii\web\Application;
use yii\web\ServerErrorHttpException;

/**
 * Class SiteControllerTest
 * @package app\tests
 *
 * @property Application $app
 */
class SiteControllerTest extends TestCase
{
    protected $app;

    protected function setUp()
    {
        defined('YII_DEBUG') or define('YII_DEBUG', true);
        defined('YII_ENV_DEV') or define('YII_ENV_DEV', true);

        $config = require __DIR__ . '/../config/web.php';
        $this->app = new Application($config);
    }

    public function testActionIndex()
    {
        $controller = $this->app->createController($this->app->defaultRoute);

        if (is_bool($controller)) {
            $this->assertTrue($controller, "Can't get controller for default route");
        }

        $query = "plumWorks/search-repositories-example";

        /**
         * @var $controller SiteController
         */
        $controller = $controller[0];
        $this->assertInstanceOf(SiteController::class, $controller,
            "Given controller isn't of instance SiteController");

        $result = null;

        try {
            $result = $controller->actionIndex($query);
        } catch (\Exception $e) {
            $this->throwException($e);
        }

        $this->assertTrue(count($result) == 1, "There is more than one item.");
        $result = $result[0];

        $expectedResult = [
            "href" => "https://github.com/plumWorks/search-repositories-example",
            "name" => "search-repositories-example",
            "fullName" => "plumWorks/search-repositories-example"
        ];

        $this->assertArrayHasKey("img", $result, "Missing key 'img' in response.");

        foreach ($expectedResult as $key => $value) {
            $this->assertArrayHasKey($key, $result, "Missing key '{$key} in response.'");
            $this->assertEquals($value, $result[$key], "Values are not equal at: '{$key}'.");
        }
    }
}
