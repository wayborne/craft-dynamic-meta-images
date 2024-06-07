<?php

namespace wayborne\dynamicmetaimages\controllers;

use Craft;
use craft\web\Controller;
use wayborne\dynamicmetaimages\DynamicMetaImages;
use yii\web\Response;

class SettingsController extends Controller
{
	protected int|bool|array $allowAnonymous = false;

	public function actionEdit(): ?Response
    {
		$siteHandle = Craft::$app->request->getParam('site');
		$currentSiteHandle = Craft::$app->getSites()->getSiteByHandle($siteHandle)->handle ?? Craft::$app->getSites()->getPrimarySite()->handle;

        $settings = DynamicMetaImages::$plugin->getSettings();
        $siteSettings = $settings->getSiteSettings($currentSiteHandle);

        return $this->renderTemplate('dynamic-meta-images/_settings', [
			'config' => Craft::$app->getConfig()->getConfigFromFile('dynamic-meta-images'),
            'siteSettings' => $siteSettings,
            'currentSiteHandle' => $currentSiteHandle,
        ]);
	}

	public function actionSave(): Response
	{
		$this->requirePostRequest();
		$request = Craft::$app->getRequest();
		$settings = DynamicMetaImages::$plugin->getSettings();

		$currentSiteHandle = $request->getBodyParam('currentSiteHandle');
		$postedSiteSettings = $request->getBodyParam('siteSettings', []);


		$reformattedSectionSettings = [];
		foreach ($postedSiteSettings['sections'] as $section) {
			if($section['templateName']){
				$sectionId = $section['id'];
				$reformattedSectionSettings[$sectionId] = $section['templateName'];
			}
		}

		$postedSiteSettings['sections'] = $reformattedSectionSettings;
		$siteSettings = $settings->getSiteSettings($currentSiteHandle);

		$siteSettings['sections'] = $postedSiteSettings['sections'];

        $postedId= $postedSiteSettings['volumeHandle'];
        $volume = Craft::$app->getVolumes()->getVolumeById((int)$postedId);
        
        if($volume){
            $siteSettings['volumeHandle'] = $volume['handle'];
        }

		$settings->setSiteSettings($currentSiteHandle, $siteSettings);

		if (!$settings->validate()) {
			$errorMessages = implode(", ", $settings->getErrorSummary(true));
			Craft::$app->getSession()->setError("Couldn’t save plugin settings. Validation errors: {$errorMessages}");
			return $this->redirectToPostedUrl();
		}

		if (!Craft::$app->getPlugins()->savePluginSettings(DynamicMetaImages::$plugin, ['sites' => $settings->sites])) {
			Craft::$app->getSession()->setError('Couldn’t save plugin settings.');
			return $this->redirectToPostedUrl();
		}

		Craft::$app->getSession()->setNotice('Plugin settings saved.');
		return $this->redirectToPostedUrl();
	}
}
