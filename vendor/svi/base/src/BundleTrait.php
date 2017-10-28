<?php

namespace Svi\BaseBundle;

use Svi\BaseBundle\Manager\SettingManager;
use Svi\BaseBundle\Service\AlertsService;
use Svi\BaseBundle\Service\FormService;
use Svi\BaseBundle\Service\SettingsService;

trait BundleTrait
{
    use \Svi\Service\BundlesService\BundleTrait;

    /**
     * @return FormService
     */
    public function getFormService()
    {
        return $this->get(FormService::class);
    }

    /**
     * @return AlertsService
     */
    public function getAlertsService()
    {
        return $this->get(AlertsService::class);
    }

    /**
     * @return SettingsService
     */
    public function getSettingsService()
    {
        return $this->get(SettingsService::class);
    }

    /**
     * @return SettingManager
     */
    public function getSettingsManager()
    {
        return $this->get(SettingManager::class);
    }
}