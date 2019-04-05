<?php
namespace OCA\YandexLogin\Migration;

use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;
use OCP\IDBConnection;
use OCP\IConfig;

class SeparateProvidersNameAndTitle implements IRepairStep
{
    /** @var IConfig */
    private $config;

    /** @var IDBConnection */
    private $db;

    public function __construct(IConfig $config, IDBConnection $db)
    {
        $this->config = $config;
        $this->db = $db;
    }

    public function getName()
    {
        return 'Separate user configured providers internal name and title. Also removes old unnecessary user config.';
    }

    public function run(IOutput $output)
    {
        $this->setProvidersName('openid_providers');
        $this->setProvidersName('custom_oidc_providers');

        //Removes old user config "password"
        $sql = "DELETE FROM `*PREFIX*preferences` WHERE `appid` = 'yandexlogin' AND `configkey` = 'password'";
        $this->db->executeUpdate($sql);
    }

    private function setProvidersName($configKey)
    {
        $providers = json_decode($this->config->getAppValue('yandexlogin', $configKey), true);
        if (is_array($providers)) {
            foreach ($providers as &$provider) {
                if (!isset($provider['name'])) {
                    $provider['name'] = $provider['title'];
                }
            }
            $this->config->setAppValue('yandexlogin', $configKey, json_encode($providers));
        }
    }
}
