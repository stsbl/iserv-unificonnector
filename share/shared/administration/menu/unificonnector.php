<?php

declare(strict_types=1);

use IServ\Bundle\AdminIntegration\Config\MenuConfigurator;
use IServ\Bundle\AdminIntegration\Config\MenuIcon;
use IServ\Bundle\AdminIntegration\Menu\Domain\AdminPage;

return static function (MenuConfigurator $config): void {
    $config
        ->get(AdminPage::NETWORK->id())
        ->add(
            'unificonnector',
            _('UniFi Connector'),
            '/admin/unificonnector/',
            new MenuIcon('img/unificonnector.svg', '/usr/share/iserv/stsbl-iserv-unificonnector/app/public/static/manifest.json'),
            'user.hasPrivilege("4ca1d38d-3ff8-4131-a6ef-a07f7994a3c1")',
            'iserv/unificonnector',
        )
    ;
};
