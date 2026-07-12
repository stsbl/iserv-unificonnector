<?php

declare(strict_types=1);

use IServ\UnifiConnector\Controller\HomeController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    //    // Routes can be defined here or with the #Route attribute in the controller
    //    $routes->add('home_welcome', '/')
    //        // the controller value has the format [controller_class, method_name]
    //        //->controller([HomeController::class, 'welcome'])
    //
    //        // if the action is implemented as the __invoke() method of the
    //        // controller class, you can skip the 'method_name' part:
    //        ->controller(HomeController::class)
    //    ;
};
