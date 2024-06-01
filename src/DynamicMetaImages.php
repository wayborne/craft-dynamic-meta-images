<?php

namespace wayborne\dynamicmetaimages;

use Craft;
use yii\base\Event;
use craft\base\Model;
use craft\helpers\Queue;
use craft\elements\Entry;
use craft\web\UrlManager;
use craft\services\Elements;
use craft\events\ElementEvent;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use wayborne\dynamicmetaimages\models\Settings;
use wayborne\dynamicmetaimages\jobs\GenerateImage;
use craft\console\Application as ConsoleApplication;


class DynamicMetaImages extends Plugin
{
	public static DynamicMetaImages $plugin;
	public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;

    public function init(): void
    {
		parent::init();
        self::$plugin = $this;

        $this->_registerConsoleCommands();
        $this->_registerEventHandlers();

		if (Craft::$app->getRequest()->getIsCpRequest()) {
            $this->registerCpUrlRules();
		}

    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('dynamic-meta-images/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    private function _registerConsoleCommands(): void
    {
        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'wayborne\dynamicmetaimages\console\controllers';
        }
    }

    private function registerCpUrlRules(): void
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules = array_merge([
                    '/settings/plugins/dynamic-meta-images' => 'dynamic-meta-images/settings/edit',
                ],
                    $event->rules
                );
            }
        );
    }

    private function _registerEventHandlers(): void
    {
		Event::on(
			Elements::class,
			Elements::EVENT_AFTER_SAVE_ELEMENT,
			function (ElementEvent $event) {
				$element = $event->element;
				if (
					$element instanceof Entry
					&& !$element->getIsRevision()
					&& !$element->getIsDraft()
					&& !$element->propagating
					&& !$element->resaving
					&& $element->status == 'live'
				)
				{
					$settings = DynamicMetaImages::$plugin->getSettings();

					$currentSiteHandle = $element->site->handle;
					$siteSettings = $settings->getSiteSettings($currentSiteHandle);

					$sectionHandle = 'section-' . $element->sectionId;

					if (isset($siteSettings['sections'][$sectionHandle])) {
						$templateName = $siteSettings['sections'][$sectionHandle];
						// TODO: batch
						Queue::push(new GenerateImage([
							'description' => 'Generating dynamic meta image',
							'templateString' => $templateName,
							'entryId' => $element->id,
							'siteHandle' => $currentSiteHandle
						]));
					}
				}
			}
		);
    }
}
