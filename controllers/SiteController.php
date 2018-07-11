<?php

namespace app\controllers;

use http\Exception\BadQueryStringException;
use linslin\yii2\curl\Curl;
use yii\web\Controller;
use yii\web\ServerErrorHttpException;

class SiteController extends Controller
{
    static $sortValues = ['stars', 'forks', 'updated'];
    static $orderValues = ["desc", "asc"];

    protected $api = "https://api.github.com/search/repositories";

    /**
     * @param string $query
     * @param string|null $sort
     * @param string $order
     *
     * @return array
     * @throws BadQueryStringException|ServerErrorHttpException
     */
    public function actionIndex(string $query,
                                string $sort = null,
                                string $order = 'desc'): array
    {
        $params = ["q" => $query];

        if (!is_null($sort)) {
            if (!in_array($sort, self::$sortValues)) {
                $acceptableValues = implode(", ", self::$sortValues);
                throw new BadQueryStringException(
                    "Sort parameter can only be given with this values: {$acceptableValues}");
            }

            $params["sort"] = $sort;
        }

        if (!in_array($order, self::$orderValues)) {
            $acceptableValues = implode(", ", self::$orderValues);
            throw new BadQueryStringException(
                "Order parameter can only be given with this values: {$acceptableValues}");
        }
        $params["order"] = $order;

        $curl = new Curl();
        $curl->setGetParams($params);
        $response = $curl->get($this->api, false);

        if (is_null($curl->errorCode)) {
            return array_map(function($element) {
                $newElement = [
                    "href" => $element["html_url"],
                    "img" => $element["owner"]["avatar_url"],
                    "name" => $element["name"],
                    "fullName" => $element["full_name"]
                ];

                return $newElement;
            }, $response["items"]);
        } else {
            throw new ServerErrorHttpException($curl->errorText);
        }
    }
}
