<?php

namespace wayborne\dynamicmetaimages\jobs;

use Craft;
use Throwable;
use craft\queue\BaseJob;
use wayborne\dynamicmetaimages\services\ImageService;

class GenerateImage extends BaseJob
{
    public $entryId;
    public $templateString;
    public $siteHandle;

    public function execute($queue): void
    {
        if (!$this->entryId || !$this->templateString) {
            return;
        }

        try {
            $imageService = new ImageService();
            $imageUrl = $imageService->generateImage($this->entryId, $this->templateString, $this->siteHandle);

            if (!$imageUrl) {
                Craft::error("Image URL is empty. Image generation might have failed.", __METHOD__);
                return;
            }

            Craft::info("Image generated successfully: $imageUrl", __METHOD__);
        } catch (Throwable $e) {
            Craft::error("Failed to generate image: {$e->getMessage()}", __METHOD__);
        }
    }

    protected function defaultDescription(): string
    {
        return Craft::t('app', 'Generate dynamic meta image for entry with id ' . $this->entryId);
    }
}
