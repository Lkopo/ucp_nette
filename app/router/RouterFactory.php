<?php

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

class RouterFactory
{

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
	    $router = new RouteList;

        $router[] = new Route('[<locale=en cs|en|sk>/]auth/lostpass/<username>/<key>', 'Auth:confirm');
        $router[] = new Route('[<locale=en cs|en|sk>/]auth/activate/<username>/<key>', 'Auth:activate');
		$router[] = new Route('[<locale=en cs|en|sk>/]auth/<action=login>', 'Auth:', Route::ONE_WAY);
		//$router[] = new Route('[<locale=en cs|en|sk>/]my-characters[/<action>][/<guid>]', 'MyCharacters:default');
        $router[] = new Route('[<locale=en cs|en|sk>/]my-logs[/<action>][/page/<page=1>]', 'MyLogs:default');
        $router[] = new Route('[<locale=en cs|en|sk>/]player-logs[/<action>][/page/<page=1>]', 'PlayerLogs:default');
        $router[] = new Route('[<locale=en cs|en|sk>/]chartrade-logs[/<action>][/page/<page=1>]', 'ChartradeLogs:default');
        $router[] = new Route('[<locale=en cs|en|sk>/]my-account/change-password/<key>', 'MyAccount:changePassword');
        $router[] = new Route('[<locale=en cs|en|sk>/]my-account/lock-unlock/<key>', 'MyAccount:lockUnlock');
        $router[] = new Route('[<locale=en cs|en|sk>/]chartrade/verify/<key>', 'Chartrade:verify');
        $router[] = new Route('[<locale=en cs|en|sk>/]<presenter>[/<action>][/<id>]', 'Homepage:default');
        return $router;
	}

}
