<?php
/**
 * Created by PhpStorm.
 * User: plum
 * Date: 11/07/2018
 * Time: 11:56
 */

namespace app\tests;

use app\controllers\SiteController;
use http\Exception\BadQueryStringException;
use PHPUnit\Framework\TestCase;
use yii\web\Application;
use yii\web\ServerErrorHttpException;

/**
 * Class SiteControllerTest
 * @package app\tests
 *
 * @property Application $app
 * @property SiteController $controller
 */
class SiteControllerTest extends TestCase
{
    protected $app;
    protected $controller;

    protected function setUp()
    {
        defined('YII_DEBUG') or define('YII_DEBUG', true);
        defined('YII_ENV_DEV') or define('YII_ENV_DEV', true);

        $config = require __DIR__ . '/../config/web.php';
        $this->app = new Application($config);

        $this->controller = $this->app->createController($this->app->defaultRoute);

        if (is_bool($this->controller)) {
            $this->assertTrue($this->controller, "Can't get controller for default route");
        }

        $this->controller = $this->controller[0];
        $this->assertInstanceOf(SiteController::class, $this->controller,
            "Given controller isn't of instance SiteController");
    }

    public function testQuery()
    {
        $exceptionThrown = function ($code): bool  {
            try {
                $code();
            } catch (\Exception $e) {
                return true;
            } catch (\TypeError $e) {
                return true;
            }

            return false;
        };

        $query = "plumWorks/search-repositories-example";
        $invalidExamples = [
            "sort" => ["", -1, 0.5],
            "order" => [-1, 0.5, "main", "", null],
            "page" => [-1, 0.5, "", null],
            "pageSize" => [-10, 0.5, 300, "", null]
        ];

        foreach ($invalidExamples["sort"] as $value) {
            $this->assertTrue($exceptionThrown(function () use ($query, $value) {
                $this->controller->actionIndex($query, $value);
            }), "Given value passed for 'sort': {$value}");
        }

        foreach ($invalidExamples["order"] as $value) {
            $this->assertTrue($exceptionThrown(function () use ($query, $value) {
                $this->controller->actionIndex($query, 'stars', $value);
            }), "Given value passed for 'order': {$value}");
        }

        foreach ($invalidExamples["page"] as $value) {
            $this->assertTrue($exceptionThrown(function () use ($query, $value) {
                $this->controller->actionIndex($query, 'stars', 'desc', $value);
            }), "Given value passed for 'page': {$value}");
        }

        foreach ($invalidExamples["pageSize"] as $value) {
            $this->assertTrue($exceptionThrown(function () use ($query, $value) {
                $this->controller->actionIndex($query, 'stars', 'desc', 1, $value);
            }), "Given value passed for 'pageSize': {$value}");
        }
    }

    public function testResponse() {
        $result = $this->controller->actionIndex("plumWorks/search-repositories-example");

        $this->assertArrayHasKey('items', $result);
        $this->assertTrue(count($result["items"]) == 1, "There is more than one item.");
        $result = $result["items"][0];

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

    public function testPageAndSize() {
        $result = $this->controller->actionIndex("hello", 'best', 'desc', 1, 10);

        $this->assertArrayHasKey('lastPage', $result);
        $lastPage = $result['lastPage'];

        $this->expectExceptionMessage('Page is out of range');
        $this->controller->actionIndex('hello', 'best', 'desc', $lastPage + 1, 10);
    }
}
