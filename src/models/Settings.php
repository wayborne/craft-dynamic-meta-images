<?php

namespace wayborne\dynamicmetaimages\models;

use Craft;
use craft\base\Model;


class Settings extends Model
{
	public $sites = [];

    public function defineRules(): array
    {
        return [
			['sites', 'each', 'rule' => ['validateSiteSettings']],
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

            if (isset($siteSettings['volumeHandle']) && (!is_string($siteSettings['volumeHandle']) || !Craft::$app->volumes->getVolumeByHandle($siteSettings['volumeHandle']))) {
                $this->addError("{$attribute}[{$siteHandle}]", "The specified asset volume ID '{$siteSettings['volumeHandle']}' for site '{$siteHandle}' does not exist or is not an integer.");                $this->addError("{$attribute}[{$siteHandle}]", "The specified asset volume ID '{$siteSettings['assetVolumeId']}' for site ID {$siteHandle} does not exist or is not an integer.");
            }
        }
	}

	public function getSiteSettings($siteHandle) {
		$siteSettings = $this->sites[$siteHandle] ?? [];

		return [
			'sections' => $siteSettings['sections'] ?? [],
			'volumeHandle' => $siteSettings['volumeHandle'] ?? null,
		];
	}

	public function setSiteSettings($siteHandle, array $siteSettings) {
        $this->sites[$siteHandle] = $siteSettings;
    }
}
