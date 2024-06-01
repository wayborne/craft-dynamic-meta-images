<?php

namespace wayborne\dynamicmetaimages\models;

use Craft;
use craft\base\Model;


class Settings extends Model
{
	public $multiSiteSettings = [];

    public function defineRules(): array
    {
        return [
			['multiSiteSettings', 'each', 'rule' => ['validateSiteSettings']],
        ];
    }

	public function validateSiteSettings($attribute, $params, $validator)
	{
		foreach ($this->$attribute as $siteHandle => $siteSettings) {
            if (!isset($siteSettings['sections']) || !is_array($siteSettings['sections'])) {
                $this->addError($attribute, "The 'sections' for site handle {$siteHandle} must be an array.");
            } else {
				foreach ($siteSettings['sections'] as $sectionId => $templateName) {
                    if (!preg_match('/^section-\d+$/', $sectionId)) {
                        $this->addError("{$attribute}[{$siteHandle}][sections]", "The section key '{$sectionId}' is incorrectly formatted. It must be in the format 'section-<ID>'.");
                    }
                    if (!is_string($templateName)) {
                        $this->addError("{$attribute}[{$siteHandle}][sections]", "The template name for section ID '{$sectionId}' must be a string.");
                    }
                }
			}

            if (isset($siteSettings['assetVolumeId']) && (!is_numeric($siteSettings['assetVolumeId']) || !Craft::$app->volumes->getVolumeById((int)$siteSettings['assetVolumeId']))) {
                $this->addError("{$attribute}[{$siteHandle}]", "The specified asset volume ID '{$siteSettings['assetVolumeId']}' for site ID {$siteHandle} does not exist or is not an integer.");
            }
        }
	}

	public function getSiteSettings($siteHandle) {
		$siteSettings = $this->multiSiteSettings[$siteHandle] ?? [];

		return [
			'sections' => $siteSettings['sections'] ?? [],
			'assetVolumeId' => $siteSettings['assetVolumeId'] ?? null,
		];
	}

	public function setSiteSettings($siteHandle, array $siteSettings) {
        $this->multiSiteSettings[$siteHandle] = $siteSettings;
    }
}
