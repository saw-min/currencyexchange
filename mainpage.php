<?php
# set timezone
@date_default_timezone_set("GMT"); 

# Set CONSTANTS
# set the base rate
define('BASE', 'GBP');

# set the intial live currency codes
define ('LIVE', array(
	'AUD', 'BRL', 'CAD','CHF',
	'CNY', 'DKK', 'EUR','GBP',
	'HKD', 'HUF', 'INR','JPY',
	'MXN', 'MYR', 'NOK','NZD',
	'PHP', 'RUB', 'SEK','SGD',
	'THB', 'TRY', 'USD','ZAR'
));

$currenthour = date ('H');
$currentmin = date ('i');
$currenthourinsec = $currenthour * 3600;
$currentmininsec = $currentmin * 60;
$currenttime = $currenthourinsec + $currentmininsec ; 
$currenttimeinhour = $currenttime/3600;
if ($currenttimeinhour >= 12)

{
# set the API URL's to constants
define ('API_URL', 'https://api.currencyapi.com/v3/latest?apikey=tqWb45zGO6KqEUtHoeWs7ff8drOYAmluAaxbhUQl');
define ('ISO_XML', 'https://www.six-group.com/dam/download/financial-information/data-center/iso-currrency/lists/list-one.xml');

# set the name of the output file
define('RATES', 'rates.xml');

# get the rates as JSON and make PHP array
$api_rates = json_decode(file_get_contents(API_URL), true);

# since USD=1 in the array we calculate the GBP conversion rate 
$gbp_rate = 1/$api_rates['data'][BASE]['value'];

# we intiaise an array to hold the converted rates (GBP as BASE)
$rates = [];

# we get the last update time and push it into the array using ts as key
$rates['ts'] = strtotime($api_rates['meta']['last_updated_at']);

# we now convert each USD value to GBP and push into the 
# rates array using the currency code as key
foreach($api_rates['data'] as $k=>$v) {
	$rates[$v['code']] = $gbp_rate * $v['value'];
}

# get the iso currencies xml file
$iso_xml = simplexml_load_file(ISO_XML) or die("Error: Cannot load currencies file");   

# get all the currency codes
$iso_codes = $iso_xml->xpath("//CcyNtry/Ccy");

$codes=[];
#foreach ($iso_codes as $code) {
#	$codes[] = (string) $code;
#}
$codes = array_unique($codes);

#sort ($codes);
#print_r($codes);
#exit;

# make a array of unique (sorted) codes
foreach ($iso_codes as $code) {
	if (!in_array($code, $codes)) {
		$codes[] = (string) $code;
	}
}

sort ($codes);

# use PHP's XML write library and build the document in memory
$writer = new XMLWriter();
$writer->openMemory();
$writer->startDocument("1.0", "UTF-8");
$writer->startElement("rates");
$writer->writeAttribute('ts', $rates['ts']);
$writer->writeAttribute('base', BASE);

foreach ($codes as $code) { 

	# use XPATH to pull all currencies that matches the current code
	$nodes = $iso_xml->xpath("//Ccy[.='$code']/parent::*");
	
	# get the code value from the first entry 
	$cname =  $nodes[0]->CcyNm;

	$writer->startElement('currency');
	
	if (isset($rates[$code])) {			
		$writer->writeAttribute('rate', $rates[$code]);
	}
			
	if (in_array($code, LIVE)) {
		$writer->writeAttribute('live', 1);
	}
	else {
		$writer->writeAttribute('live', 0);
	}
	
	$writer->startElement('code');
	$writer->text($code);
	$writer->endElement();
	$writer->startElement("curr");
	$writer->text($cname);
	$writer->endElement();

	$writer->startElement("loc");
			
	# used to skip comma - line 113
	$last = count($nodes) - 1;
			
	# group countries together using the same code
	# and lowercase first letter in name and 
	# then write it out with the first letter upper-cased
	foreach ($nodes as $index=>$node) {
		$writer->text(mb_convert_case($node->CtryNm, MB_CASE_TITLE, "UTF-8"));
		if ($index!=$last) {$writer->text(', ');}
	}
	
	# end the loc element
	$writer->endElement();
	
	# end the currency element
	$writer->endElement();
}

# end the root element and document
$writer->endElement();
$writer->endDocument();

# write out and save the files
file_put_contents(RATES, $writer->outputMemory());



# this pretty prints the date and rates array - not required but nice
echo 'Rates pulled @ ' . date('d m Y H:i', $rates['ts']) . ' and converted to GBP=1';
echo '<pre>';
print_r($rates);
echo '</pre>';
}










?>