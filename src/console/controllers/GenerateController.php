<?php

namespace wayborne\dynamicmetaimages\console\controllers;

use Exception;
use craft\elements\Entry;
use yii\console\ExitCode;
use craft\console\Controller;
use wayborne\dynamicmetaimages\services\ImageService;


class GenerateController extends Controller
{
    public function actionIndex($entryId = null, $templateString = null, $siteHandle = null)
    {
		if (!$entryId) {
			$this->stderr("Entry ID  not not defined.\n");
			return ExitCode::UNSPECIFIED_ERROR;
        }

		if (!$templateString) {
			$this->stderr("templateString not defined.\n");
			return ExitCode::UNSPECIFIED_ERROR;
        }

		if (!$siteHandle) {
			$entry = Entry::find()->id($entryId)->one();
			$siteId = $entry->siteHandle;
		}

		try {
			$imageService = new ImageService();
            $imageUrl = $imageService->generateImage($entryId, $templateString, $siteId);
            $this->stdout("Image generated successfully: $imageUrl\n");
            return ExitCode::OK;
        } catch (Exception $e) {
            $this->stderr("Failed to generate image: {$e->getMessage()}\n");
            return ExitCode::SOFTWARE;
        }
    }
}
