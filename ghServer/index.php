<?php
	use \Blocktrail\SDK\BlocktrailSDK;
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

    $app->get('/db', function() use ($app) {
    	$results = DB::query("SELECT * FROM impressions ORDER BY timestamp DESC LIMIT 1");
    	$app->render(200,$results);
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

		$results = DB::query("SELECT siteRegex, cpId FROM contentProviderSites");
		foreach ($results as $row) {
			$siteRegex = $row['siteRegex'];
			$pos = strpos($url, $siteRegex);

			if ($pos !== false) {

				$registeredCp = true;
				$cpId = $row['cpId'];
				
				DB::insert("impressions", array(
					'url' => $url,
					'userId' => $userId,
					'cpId' => $cpId
					));
				break;
			}
		}

        $app->render(200,array(
        	'registered' => $registeredCp
		));
    });

    /*
    $app->post('/init', function() use ($app) {

    	$client = new BlocktrailSDK("400fefd490da4d00f5da380d7960b0ac451f33ca", "3cbb43fd825610db22732d9f979aa14db19deffc", "BTC", false);
    	list($wallet, $primaryMnemonic, $backupMnemonic, $blocktrailPublicKeys) = $client->createNewWallet("givehabit", "etSUCKS#1");

    	$app->render(200,array(
    		'wallet' => $wallet,
    		'primaryMnemonic' => $primaryMnemonic,
    		'backupMnemonic' => $backupMnemonic,
    		'blocktrailPublicKeys' => $blocktrailPublicKeys
    	));
    });
    */

    $app->post('/makePayment', function() use ($app) {

    	$request = $app->request();
    	$body = $request->getBody();
		$input = json_decode($body); 

		$userId = (string)$input->userId;
		$check = DB::query("SELECT monthlyBalance FROM users where userId=%i", $userId);
		if (count($check) === 0) {
			$app->halt(401);
		}

		$paymentDone = false;
		$balance = BlocktrailSDK::toSatoshi($check[0]['monthlyBalance']);

		if ($balance === 0) {
			$app->halt(404);
		}

    	$client = new BlocktrailSDK("400fefd490da4d00f5da380d7960b0ac451f33ca", "3cbb43fd825610db22732d9f979aa14db19deffc", "BTC", false);
    	$wallet = $client->initWallet("givehabit", "etSUCKS#1");
    	list($confirmedBalance, $unconfirmedBalance) = $wallet->getBalance();

    	if ($balance > BlocktrailSDK::toSatoshi($confirmedBalance)) {
    		$app->halt(500, "Not enough balance[$confirmedBalance]");
    	}

		$query = DB::query("SELECT count(*) as total FROM impressions where userId=%i and paid=%i", $userId, 0);
		$totalImpressions = $query[0]['total'];

		$payments = DB::query("SELECT cpId, count(*) as total FROM impressions where userId=%i and paid=%i GROUP BY cpId", $userId, 0);
		
		foreach ($payments as $row) {
			$cpId = $row['cpId'];
			$percent = $row['total'] / $totalImpressions;

			$cpDeposit = (int)round($balance * $percent);
			echo "cpDeposit: $cpDeposit";

			$walletAddressQuery = DB::query("SELECT walletAddress FROM contentProviders where cpId=%i", $cpId);
			$walletAddress = $walletAddressQuery[0]['walletAddress'];

    		$wallet->pay(array($walletAddress => $cpDeposit));
    		DB::update('impressions', array('paid' => 1), "userId=%i and paid=0", $userId);

    		$paymentDone = true;
		}
		
    	$app->render(200,array(
    		'paymentDone' => $paymentDone
    	));

    });

    $app->get('/getCategories/:userId', function($userId) use ($app) {
    	$query = DB::query("SELECT t1.categoryName, count(*) AS total FROM categories t1, contentProviders t2, impressions t3 WHERE t1.categoryId = t2.categoryId AND t3.cpId = t2.cpID AND t3.userId=%i AND t3.paid=0 GROUP BY t3.cpId", $userId);
    	foreach ($query as $row) {
    		$array[$row['categoryName']] = $row['total'];
    	}
    	$app->render(200,$array);
    });

	$app->run();