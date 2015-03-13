<?php
	require 'vendor/autoload.php';
	DB::$user = 'ghUser';
	DB::$password = 'etKAKA';
	DB::$dbName = 'ghDatabase';

	header("Access-Control-Allow-Origin: *");

	$app = new \Slim\Slim();

	$app->view(new \JsonApiView());
    $app->add(new \JsonApiMiddleware());

    $app->get('/ping', function() use ($app) {
    	$app->render(200,array(
		));
    });

    $app->post('/sendImpression', function() use ($app) {

    	$request = $app->request();
    	$body = $request->getBody();
		$input = json_decode($body); 

		$userId = (string)$input->userId;
		$url = (string)$input->impressionURL;

		$check = DB::query("SELECT 'a' FROM users where userId=%i", $userId);
		if (count($check) === 0) {
			$app->halt(401);
		}

		$registeredCp = false;

		$results = DB::query("SELECT siteRegex FROM contentProviderSites");
		foreach ($results as $row) {
			$siteRegex = $row['siteRegex'];
			$pos = strpos($url, $siteRegex);

			if ($pos !== false) {
				$registeredCp = true;
				// $cpId = $row['cpId'];
				//TODO write impression in database
				break;
			}
		}

        $app->render(200,array(
        	'registered' => $registeredCp
		));
    });

	$app->run();