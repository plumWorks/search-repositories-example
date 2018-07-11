<?php

namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }

    /**
     * @param string|null $q
     * @return array
     */
    public function actionIndex(string $q = null): array
    {
        return [
            "hello world!"
        ];
    }
}
