<?php

namespace wayborne\dynamicmetaimages\services;

use Craft;
use DOMDocument;
use craft\web\View;
use craft\helpers\App;
use yii\base\Exception;
use craft\base\Component;
use craft\elements\Asset;
use craft\elements\Entry;
use Spatie\Browsershot\Browsershot;
use wayborne\dynamicmetaimages\DynamicMetaImages;


class ImageService extends Component
{
    public function generateImage(string $entryId, string $templateString, string $siteHandle)
    {
		$html = $this->renderTemplateFromEntryId($entryId, $templateString, $siteHandle);

		$settings = DynamicMetaImages::$plugin->getSettings();
        $siteSettings = $settings->getSiteSettings($siteHandle);
        $volumeHandle = $siteSettings['volumeHandle'];
		$filename = $entryId . '.png';
		$title = $entryId;

        $volume = Craft::$app->volumes->getVolumeByHandle($volumeHandle);
        if (!$volume) {
            throw new \Exception('No volume selected for saving images.');
        }
        $folder = Craft::$app->getAssets()->getRootFolderByVolumeId($volume->id);
		if (!$folder) {
			throw new Exception('Failed to get root folder for volume: ' . $volume->name);
		}

		preg_match('/<title>(.*?)<\/title>/s', $html, $matches);
		if (!empty($matches[1])) {
			$title = $matches[1];
			$filename = $title . '.png';
			$title = $title;
		}

        $tempPath = Craft::$app->getPath()->getTempPath() . '/' . $filename;
        $existingAsset = Asset::find()->filename($filename)->folderId($folder->id)->one();

		try {
			Browsershot::html($html)
				->setNodeBinary(App::env('NODE_BINARY'))
				->setNpmBinary(App::env('NPM_BINARY'))
				->windowSize(1200, 675)
				->deviceScaleFactor(3)
				->setOption('args', ['--disable-web-security'])
				->waitUntilNetworkIdle()
				->save($tempPath);

			if ($existingAsset) {
				$existingAsset->tempFilePath = $tempPath;
				$existingAsset->avoidFilenameConflicts = true;
				$existingAsset->setScenario(Asset::SCENARIO_REPLACE);

				$existingAsset->validate();
				Craft::$app->getElements()->saveElement($existingAsset, false);

				// Save the asset with its new file
				if (!Craft::$app->getElements()->saveElement($existingAsset, false)) {
					throw new Exception('Failed to replace file for existing asset: ' . implode(", ", $existingAsset->getErrorSummary(true)));
				}
			} else {
				$asset = new Asset();
				$asset->tempFilePath = $tempPath;
				$asset->filename = $filename;
				$asset->folderId = $folder->id;
				$asset->kind = "image";
				$asset->title = $title;
				$asset->setVolumeId($volume->id);
				$asset->setScenario(Asset::SCENARIO_CREATE);

				$asset->validate();
				Craft::$app->getElements()->saveElement($asset, false);

				// Save the new asset
				if (!Craft::$app->getElements()->saveElement($asset, false)) {
					throw new Exception('Failed to save new asset: ' . implode(", ", $asset->getErrorSummary(true)));
				}
			};

        } catch (\Exception $e) {
            Craft::error('Failed to generate image: ' . $e->getMessage(), __METHOD__);
            throw new Exception('Error generating image. ' . $e->getMessage());
        } finally {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    private function renderTemplateFromEntryId(string $entryId, string $templateString, string $siteHandle)
    {
        // Get the site based on the site handle
        $site = Craft::$app->getSites()->getSiteByHandle($siteHandle);
        if (!$site) {
            Craft::error("Site not found for handle: {$siteHandle}", __METHOD__);
            return null;
        }
        
        // Find the entry in the specific site
        $entry = Entry::find()->id($entryId)->siteId($site->id)->one();
        if (!$entry) {
            Craft::error("Entry not found for ID {$entryId} in site {$siteHandle}.", __METHOD__);
            return null;
        }
    
        try {
            Craft::$app->getView()->setTemplateMode(View::TEMPLATE_MODE_SITE);
            $html = Craft::$app->getView()->renderTemplate($templateString, ['entry' => $entry]);
            Craft::$app->getView()->setTemplateMode(View::TEMPLATE_MODE_CP);
            return $html;
        } catch (\Exception $e) {
            Craft::error('Failed to render template: ' . $e->getMessage(), __METHOD__);
            throw new Exception('Error rendering template.');
        }
    }
}
