<?php

namespace app\controllers;

use linslin\yii2\curl\Curl;
use yii\web\Controller;
use yii\web\ServerErrorHttpException;

class SiteController extends Controller
{
    static $sortValues = ['stars', 'forks', 'updated', 'best'];
    static $orderValues = ["desc", "asc"];

    protected $api = "https://api.github.com/search/repositories";

    /**
     * Action check parameters for api call with check if error message is'nt throw, then map result with last page
     *
     * @param string $query
     * @param string|null $sort
     * @param string $order
     * @param int $page
     * @param int $pageSize
     *
     * @return array
     * @throws \Exception
     */
    public function actionIndex(string $query,
                                string $sort = null,
                                string $order = 'desc',
                                int $page = 1,
                                int $pageSize = 20): array
    {
        $params = ["q" => $query];

        if (!is_null($sort)) {
            if (!in_array($sort, self::$sortValues) || empty($sort)) {
                $acceptableValues = implode(", ", self::$sortValues);
                throw new \Exception(
                    "Sort parameter can only be given with this values: {$acceptableValues}.");
            } else if ($sort !== 'best') {
                $params["sort"] = $sort;
            }
        }

        if (!in_array($order, self::$orderValues)) {
            $acceptableValues = implode(", ", self::$orderValues);
            throw new \Exception(
                "Order parameter can only be given with this values: {$acceptableValues}.");
        }
        $params["order"] = $order;

        if ($page < 1) {
            throw new \Exception("Page can't be bellow 1");
        }
        $params["page"] = $page;

        if ($pageSize < 1) {
            throw new \Exception("PageSize can't be negative");
        } else if ($pageSize > 100) {
            throw new \Exception("PageSize can't cross more than 100 results");
        }
        $params["per_page"] = $pageSize;

        $curl = new Curl();
        $curl->setGetParams($params);
        $response = $curl->get($this->api, false);

        if (is_null($curl->errorCode)) {
            if (isset($response["message"])) {
                switch ($response["message"]) {
                    case "Only the first 1000 search results are available":
                        throw new \Exception("Page is out of range");
                    default:
                        throw new \Exception($response["message"]);
                }
            }

            $items = array_map(function($element) {
                $newElement = [
                    "href" => $element["html_url"],
                    "img" => $element["owner"]["avatar_url"],
                    "name" => $element["name"],
                    "fullName" => $element["full_name"]
                ];

                return $newElement;
            }, $response["items"]);

            $lastPage = 1;

            if (isset($curl->responseHeaders["Link"])) {
                $links = $curl->responseHeaders["Link"];
                $re = '/page=(\d+)\&per_page\=\d+\>\;\srel\=\"last\"/m';

                if (preg_match_all($re, $links, $matches)) {
                    if (count($matches[1]) == 1) {
                        $lastPage = $matches[1][0];
                    } else {
                        throw new \Exception("There is more than one match for link");
                    }
                } else {
                    $lastPage = $page;
                }
            }

            return [
                "lastPage" => $lastPage,
                "items" => $items
            ];
        } else {
            throw new ServerErrorHttpException($curl->errorText);
        }
    }
}
