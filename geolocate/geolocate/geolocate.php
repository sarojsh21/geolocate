<?php

	ini_set('display_errors', 'On');
	error_reporting(E_ALL);

	
	include('openCage/AbstractGeocoder.php');
	include('openCage/Geocoder.php');

	$geocoder = new \OpenCage\Geocoder\Geocoder('8fa841951dd14bff968a42059a548148');

	$result = $geocoder->geocode($_REQUEST['q']='51.4564,0.8534',[$_REQUEST['lang'] = 'en']);

	if (in_array($result['status']['code'], [401,402,403,429])) {

		$handle = curl_init('https://geocoder.ls.hereapi.com/6.2/geocode.json?searchtext=' . urlencode($_REQUEST['q']) . '&gen=9&language=' . $_REQUEST['lang'] . '&locationattributes=tz&locationattributes=tz&apiKey=3a-30Zv1XS6W1oOiLxhsIfSudk2mDak6bfVQmOrPvjA');

        curl_setopt($handle, CURLOPT_HTTPHEADER, array('Content-Type: text/plain; charset=UTF-8'));
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $json_result = curl_exec($handle);
		
		$searchResult = [];
		$searchResult['results'] = [];

		$temp = [];

		$r = json_decode($json_result, true);

		foreach ($r['Response']['View'][0]['Result'] as $result) {

			$temp['source'] = 'here';
			$temp['formatted'] = $result['Location']['Address']['Label'];
			$temp['geometry']['lat'] = $result['Location']['DisplayPosition']['Latitude'];
			$temp['geometry']['lng'] = $result['Location']['DisplayPosition']['Longitude'];
			$temp['countryCode'] = getCountryCode($result['Location']['Address']['Country']);
			$temp['timezone'] = $result['Location']['AdminInfo']['TimeZone']['id'];

			array_push($searchResult['results'], $temp);

		}

	} else {

		$searchResult = [];
		$searchResult['results'] = [];

		$temp = [];

		foreach ($result['results'] as $entry) {

			$temp['source'] = 'opencage';
			$temp['formatted'] = $entry['formatted'];
			$temp['geometry']['lat'] = $entry['geometry']['lat'];
			$temp['geometry']['lng'] = $entry['geometry']['lng'];
			$temp['countryCode'] = strtoupper($entry['components']['country_code']);
			$temp['timezone'] = $entry['annotations']['timezone']['name'];

			array_push($searchResult['results'], $temp);

		}

	}

	header('Content-Type: application/json; charset=UTF-8');
	
	echo json_encode($searchResult, JSON_UNESCAPED_UNICODE);

	// functions

	function getCountryCode($countryCode) {
	    
	    $iso3 = ['AFG','ALA','ALB','DZA','ASM','AND','AGO','AIA','ATA','ATG','ARG','ARM','ABW','AUS','AUT','AZE','BHS','BHR','BGD','BRB','BLR','BEL','BLZ','BEN','BMU','BTN','BOL','BIH','BWA','BVT','BRA','IOT','VGB','BRN','BGR','BFA','BDI','KHM','CMR','CAN','CPV','CYM','CAF','TCD','CHL','CHN','CXR','CCK','COL','COM','COG','COD','COK','CRI','CIV','HRV','CUB','CYP','CZE','DNK','DJI','DMA','DOM','ECU','EGY','SLV','GNQ','ERI','EST','ETH','FLK','FRO','FJI','FIN','FRA','GUF','PYF','ATF','GAB','GMB','GEO','DEU','GHA','GIB','GRC','GRL','GRD','GLP','GUM','GTM','GGY','GIN','GNB','GUY','HTI','HMD','VAT','HND','HKG','HUN','ISL','IND','IDN','IRN','IRQ','IRL','IMN','ISR','ITA','JAM','JPN','JEY','JOR','KAZ','KEN','KIR','PRK','KOR','KWT','KGZ','LAO','LVA','LBN','LSO','LBR','LBY','LIE','LTU','LUX','MAC','MKD','MDG','MWI','MYS','MDV','MLI','MLT','MHL','MTQ','MRT','MUS','MYT','MEX','FSM','MDA','MCO','MNG','MNE','MSR','MAR','MOZ','MMR','NAM','NRU','NPL','NLD','ANT','NCL','NZL','NIC','NER','NGA','NIU','NFK','MNP','NOR','OMN','PAK','PLW','PSE','PAN','PNG','PRY','PER','PHL','PCN','POL','PRT','PRI','QAT','REU','ROU','RUS','RWA','SHN','KNA','LCA','SPM','VCT','BLM','MAF','WSM','SMR','STP','SAU','SEN','SRB','SYC','SLE','SGP','SVK','SVN','SLB','SOM','ZAF','SGS','SSD','ESP','LKA','SDN','SUR','SJM','SWZ','SWE','CHE','SYR','TWN','TJK','TZA','THA','TLS','TGO','TKL','TON','TTO','TUN','TUR','TKM','TCA','TUV','UGA','UKR','ARE','GBR','USA','URY','UMI','UZB','VUT','VEN','VNM','VIR','WLF','ESH','YEM','ZMB','ZWE'];
	    
		$iso2 = ['AF','AX','AL','DZ','AS','AD','AO','AI','AQ','AG','AR','AM','AW','AU','AT','AZ','BS','BH','BD','BB','BY','BE','BZ','BJ','BM','BT','BO','BA','BW','BV','BR','IO','VG','BN','BG','BF','BI','KH','CM','CA','CV','KY','CF','TD','CL','CN','CX','CC','CO','KM','CG','CD','CK','CR','CI','HR','CU','CY','CZ','DK','DJ','DM','DO','EC','EG','SV','GQ','ER','EE','ET','FK','FO','FJ','FI','FR','GF','PF','TF','GA','GM','GE','DE','GH','GI','GR','GL','GD','GP','GU','GT','GG','GN','GW','GY','HT','HM','VA','HN','HK','HU','IS','IN','ID','IR','IQ','IE','IM','IL','IT','JM','JP','JE','JO','KZ','KE','KI','KP','KR','KW','KG','LA','LV','LB','LS','LR','LY','LI','LT','LU','MO','MK','MG','MW','MY','MV','ML','MT','MH','MQ','MR','MU','YT','MX','FM','MD','MC','MN','ME','MS','MA','MZ','MM','NA','NR','NP','NL','AN','NC','NZ','NI','NE','NG','NU','NF','MP','NO','OM','PK','PW','PS','PA','PG','PY','PE','PH','PN','PL','PT','PR','QA','RE','RO','RU','RW','SH','KN','LC','PM','VC','BL','MF','WS','SM','ST','SA','SN','RS','SC','SL','SG','SK','SI','SB','SO','ZA','GS','SS','ES','LK','SD','SR','SJ','SZ','SE','CH','SY','TW','TJ','TZ','TH','TL','TG','TK','TO','TT','TN','TR','TM','TC','TV','UG','UA','AE','GB','US','UY','UM','UZ','VU','VE','VN','VI','WF','EH','YE','ZM','ZW'];
		
		if (strlen(trim($countryCode)) == 2) {
		    
		   return $iso3[array_search($countryCode, $iso2)];
		    
		} else {
		    
		   return $iso2[array_search($countryCode, $iso3)];
		
		}

	}

?>
