<?php

namespace Nasc\Setup\Step;

use Nasc\Repo\CountryRepo;
use Nasc\Repo\DomainRepo;
use Nasc\Repo\SettingRepo;
use Nasc\Repo\StateProvinceRepo;
use Nasc\Service\DrupalSettingService;

class NascSiteConfigurationSetup implements StepInterface
{
    /**
     * @var CountryRepo
     */
    private $countryRepo;

    /**
     * @var StateProvinceRepo
     */
    private $stateRepo;

    /**
     * @var SettingRepo
     */
    private $settingRepo;

    /**
     * @var DomainRepo
     */
    private $domainRepo;

    /**
     * @var DrupalSettingService
     */
    private $cmsSettingService;

    /**
     * @param CountryRepo $countryRepo
     * @param StateProvinceRepo $stateRepo
     * @param SettingRepo $settingRepo
     * @param DomainRepo $domainRepo
     * @param DrupalSettingService $cmsSettingService
     */
    public function __construct(
        CountryRepo $countryRepo,
        StateProvinceRepo $stateRepo,
        SettingRepo $settingRepo,
        DomainRepo $domainRepo,
        DrupalSettingService $cmsSettingService
    ) {
        $this->countryRepo = $countryRepo;
        $this->stateRepo = $stateRepo;
        $this->settingRepo = $settingRepo;
        $this->domainRepo = $domainRepo;
        $this->cmsSettingService = $cmsSettingService;
    }

    public function apply()
    {
        $irelandId = $this->countryRepo->findOneBy(['name' => 'Ireland'])['id'];
        $corkId = $this->stateRepo->findOneBy(['name' => 'Cork', 'country_id' => $irelandId])['id'];

        $settingParams = [
            "defaultCurrency" => "EUR",
            "languageLimit" => [],
            "defaultContactCountry" => $irelandId,
            "defaultContactStateProvince" => $corkId,
            "dateInputFormat" => "dd/mm/yy",
            "dateformatshortdate" => "%d/%m/%Y",
        ];
        $this->settingRepo->create($settingParams);

        $domainParams = [
            "name" => "Nasc Ireland",
            "domain_email" => "reception@nascireland.org",
            "domain_phone" => [
                "phone_type" => "Phone",
                "phone" => "021 4273594"
            ],
            "domain_address" => [
                "street_address" => "34 Paul St",
                "city" => "Cork",
                "state_province_id" => $corkId,
                "postal_code" => "T12 W14H",
                "country_id" => $irelandId,
            ],
        ];
        $domain = $this->domainRepo->findOneBy([]);
        $domainParams['id'] = $domain['id'];
        $this->domainRepo->create($domainParams);

        $this->cmsSettingService->setVariable('site_frontpage', 'civicrm/dashboard');
    }

    public function remove()
    {
        // do nothing as we do not store defaults before step was applied
    }
}