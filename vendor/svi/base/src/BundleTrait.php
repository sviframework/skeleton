<?php

namespace Svi\Base;

use Svi\Base\Manager\SettingManager;
use Svi\Base\Service\AlertsService;
use Svi\Base\Service\FormService;
use Svi\Base\Service\SettingsService;

trait BundleTrait
{
    use \Svi\BundleTrait;

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