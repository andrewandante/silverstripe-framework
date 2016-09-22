<?php
/**
 * This file is the Framework constants bootstrap. It will prepare some basic common constants.
 *
 * It takes care of:
 *  - Including _ss_environment.php
 *  - Normalisation of $_SERVER values
 *  - Initialisation of necessary constants (mostly paths)
 *
 * Initialized constants:
 * - BASE_URL: Full URL to the webroot, e.g. "http://my-host.com/my-webroot" (no trailing slash).
 * - BASE_PATH: Absolute path to the webroot, e.g. "/var/www/my-webroot" (no trailing slash).
 *   See Director::baseFolder(). Can be overwritten by Config::inst()->update('Director', 'alternate_base_folder', ).
 * - TEMP_FOLDER: Absolute path to temporary folder, used for manifest and template caches. Example: "/var/tmp"
 *   See getTempFolder(). No trailing slash.
 * - MODULES_DIR: Not used at the moment
 * - MODULES_PATH: Not used at the moment
 * - THEMES_DIR: Path relative to webroot, e.g. "themes"
 * - THEMES_PATH: Absolute filepath, e.g. "/var/www/my-webroot/themes"
 * - FRAMEWORK_DIR: Path relative to webroot, e.g. "framework"
 * - FRAMEWORK_PATH:Absolute filepath, e.g. "/var/www/my-webroot/framework"
 * - FRAMEWORK_ADMIN_DIR: Path relative to webroot, e.g. "framework/admin"
 * - FRAMEWORK_ADMIN_PATH: Absolute filepath, e.g. "/var/www/my-webroot/framework/admin"
 * - THIRDPARTY_DIR: Path relative to webroot, e.g. "framework/thirdparty"
 * - THIRDPARTY_PATH: Absolute filepath, e.g. "/var/www/my-webroot/framework/thirdparty"
 * - TRUSTED_PROXY: true or false, depending on whether the X-Forwarded-* HTTP
 *   headers from the given client are trustworthy (e.g. from a reverse proxy).
 *
 * @package framework
 * @subpackage core
 */

///////////////////////////////////////////////////////////////////////////////
// ENVIRONMENT CONFIG

/**
 * Include _ss_environment.php file
 */
//define the name of the environment file
$envFile = '_ss_environment.php';
//define the dirs to start scanning from (have to add the trailing slash)
// we're going to check the realpath AND the path as the script sees it
$dirsToCheck = array(
	realpath('.'),
	dirname($_SERVER['SCRIPT_FILENAME'])
);
//if they are the same, remove one of them
if ($dirsToCheck[0] == $dirsToCheck[1]) {
	unset($dirsToCheck[1]);
}
foreach ($dirsToCheck as $dir) {
	//check this dir and every parent dir (until we hit the base of the drive)
	// or until we hit a dir we can't read
	while(true) {
		//if it's readable, go ahead
		if (@is_readable($dir)) {
			//if the file exists, then we include it, set relevant vars and break out
			if (file_exists($dir . DIRECTORY_SEPARATOR . $envFile)) {
				define('SS_ENVIRONMENT_FILE', $dir . DIRECTORY_SEPARATOR . $envFile);
				include_once(SS_ENVIRONMENT_FILE);
				//break out of BOTH loops because we found the $envFile
				break(2);
			}
		}
		else {
			//break out of the while loop, we can't read the dir
			break;
		}
		if (dirname($dir) == $dir) {
			// here we need to check that the path of the last dir and the next one are
			// not the same, if they are, we have hit the root of the drive
			break;
		}
		//go up a directory
		$dir = dirname($dir);
	}
}

///////////////////////////////////////////////////////////////////////////////
// GLOBALS AND DEFINE SETTING

function stripslashes_recursively(&$array) {
	foreach($array as $k => $v) {
		if(is_array($v)) stripslashes_recursively($array[$k]);
		else $array[$k] = stripslashes($v);
	}
}

/**
 * Check if a given ip is in a comma-separated list of network ranges
 * @param  string $ip    IP to check eg. 127.0.0.1, ::1
 * @param  string $range IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1 is accepted and /32 assumed
 * @return boolean true if the ip is in this range / false if not.
 */
function ip_in_any_range($ip, $ranges){
	foreach (explode(',', $ranges) as $range) {
		if (strpos($ip, ':')) {
			$in_range = ip_in_ipv6_range($ip, $range);
		} elseif (strpos($ip, '.')) {
			$in_range = ip_in_ipv4_range($ip, $range);
		} else {
			$in_range = false;
		}
		if ($in_range == true) return true;
	}
	return false;
}

function ip_in_ipv4_range($ip, $range) {
	if (strpos($range, '/') == false) {
		return ($ip == $range);
	}
	// $range is in IP/CIDR format eg 127.0.0.1/24
	list($range, $netmask) = explode('/', $range, 2);
	$range_decimal = ip2long($range);
	$ip_decimal = ip2long($ip);
	$wildcard_decimal = pow(2, (32 - $netmask)) - 1;
	$netmask_decimal = ~ $wildcard_decimal;
	return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
}

// converts inet_pton output to string with bits - needed for ipv6 comparison
function inet_to_bits($inet) {
	$unpacked = unpack('A16', $inet);
	$unpacked = str_split($unpacked[1]);
	$binaryip = '';
	foreach ($unpacked as $char) {
		$binaryip .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
	}
	return $binaryip;
}

function ip_in_ipv6_range($ip, $range) {
	$ip = inet_pton($ip);
	$binaryip = inet_to_bits($ip);

	list ($net, $maskbits) = explode('/', $range);
	$net = inet_pton($net);
	$binarynet = inet_to_bits($net);

	$ip_net_bits = substr($binaryip, 0, $maskbits);
	$net_bits = substr($binarynet, 0, $maskbits);

	return ($ip_net_bits == $net_bits);
}
/**
 * Validate whether the request comes directly from a trusted server or not
 * This is necessary to validate whether or not the values of X-Forwarded-
 * or Client-IP HTTP headers can be trusted
 */
if(!defined('TRUSTED_PROXY')) {
	$trusted = true; // will be false by default in a future release

	if(getenv('BlockUntrustedProxyHeaders') // Legacy setting (reverted from documentation)
		|| getenv('BlockUntrustedIPs') // Documented setting
		|| defined('SS_TRUSTED_PROXY_IPS')
	) {
		$trusted = false;

		if(defined('SS_TRUSTED_PROXY_IPS') && SS_TRUSTED_PROXY_IPS !== 'none') {
			if(SS_TRUSTED_PROXY_IPS === '*') {
				$trusted = true;
			} elseif(isset($_SERVER['REMOTE_ADDR'])) {
				if (ip_in_any_range($_SERVER['REMOTE_ADDR'], SS_TRUSTED_PROXY_IPS)) {
					$trusted = true;
					break;
					}
				}
			}
		}
	}

	/**
	 * Declare whether or not the connecting server is a trusted proxy
	 */
	define('TRUSTED_PROXY', $trusted);
}

/**
 * A blank HTTP_HOST value is used to detect command-line execution.
 * We update the $_SERVER variable to contain data consistent with the rest of the application.
 */
if(!isset($_SERVER['HTTP_HOST'])) {
	// HTTP_HOST, REQUEST_PORT, SCRIPT_NAME, and PHP_SELF
	if(isset($_FILE_TO_URL_MAPPING)) {
		$fullPath = $testPath = realpath($_SERVER['SCRIPT_FILENAME']);
		while($testPath && $testPath != '/' && !preg_match('/^[A-Z]:\\\\$/', $testPath)) {
			if(isset($_FILE_TO_URL_MAPPING[$testPath])) {
				$url = $_FILE_TO_URL_MAPPING[$testPath]
					. str_replace(DIRECTORY_SEPARATOR, '/', substr($fullPath,strlen($testPath)));

				$components = parse_url($url);
				$_SERVER['HTTP_HOST'] = $components['host'];
				if(!empty($components['port'])) $_SERVER['HTTP_HOST'] .= ':' . $components['port'];
				$_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = $components['path'];
				if(!empty($components['port'])) $_SERVER['REQUEST_PORT'] = $components['port'];
				break;
			}
			$testPath = dirname($testPath);
		}
	}

	// Everything else
	$serverDefaults = array(
		'SERVER_PROTOCOL' => 'HTTP/1.1',
		'HTTP_ACCEPT' => 'text/plain;q=0.5',
		'HTTP_ACCEPT_LANGUAGE' => '*;q=0.5',
		'HTTP_ACCEPT_ENCODING' => '',
		'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1;q=0.5',
		'SERVER_SIGNATURE' => 'Command-line PHP/' . phpversion(),
		'SERVER_SOFTWARE' => 'PHP/' . phpversion(),
		'SERVER_ADDR' => '127.0.0.1',
		'REMOTE_ADDR' => '127.0.0.1',
		'REQUEST_METHOD' => 'GET',
		'HTTP_USER_AGENT' => 'CLI',
	);

	$_SERVER = array_merge($serverDefaults, $_SERVER);

	/**
	 * If we have an HTTP_HOST value, then we're being called from the webserver and there are some things that
	 * need checking
	 */
} else {
	/**
	 * Fix magic quotes setting
	 */
	if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
		if($_REQUEST) stripslashes_recursively($_REQUEST);
		if($_GET) stripslashes_recursively($_GET);
		if($_POST) stripslashes_recursively($_POST);
		if($_COOKIE) stripslashes_recursively($_COOKIE);
		// No more magic_quotes!
		trigger_error('get_magic_quotes_gpc support is being removed from Silverstripe. Please set this to off in ' .
		' your php.ini and see http://php.net/manual/en/security.magicquotes.php', E_USER_WARNING);
	}

	/**
	 * Fix HTTP_HOST from reverse proxies
	 */
	$trustedProxyHeader = (defined('SS_TRUSTED_PROXY_HOST_HEADER'))
		? SS_TRUSTED_PROXY_HOST_HEADER
		: 'HTTP_X_FORWARDED_HOST';

	if (TRUSTED_PROXY && !empty($_SERVER[$trustedProxyHeader])) {
		// Get the first host, in case there's multiple separated through commas
		$_SERVER['HTTP_HOST'] = strtok($_SERVER[$trustedProxyHeader], ',');
	}
}

// Filter by configured allowed hosts
if (defined('SS_ALLOWED_HOSTS') && php_sapi_name() !== "cli") {
	$all_allowed_hosts = explode(',', SS_ALLOWED_HOSTS);
	if (!isset($_SERVER['HTTP_HOST']) || !in_array($_SERVER['HTTP_HOST'], $all_allowed_hosts)) {
		header('HTTP/1.1 400 Invalid Host', true, 400);
		die();
	}
}

/**
 * Define system paths
 */
if(!defined('BASE_PATH')) {
	// Assuming that this file is framework/core/Core.php we can then determine the base path
	$candidateBasePath = rtrim(dirname(dirname(dirname(__FILE__))), DIRECTORY_SEPARATOR);
	// We can't have an empty BASE_PATH.  Making it / means that double-slashes occur in places but that's benign.
	// This likely only happens on chrooted environemnts
	if($candidateBasePath == '') $candidateBasePath = DIRECTORY_SEPARATOR;
	define('BASE_PATH', $candidateBasePath);
}
if(!defined('BASE_URL')) {
	// Determine the base URL by comparing SCRIPT_NAME to SCRIPT_FILENAME and getting common elements
	$path = realpath($_SERVER['SCRIPT_FILENAME']);
	if(substr($path, 0, strlen(BASE_PATH)) == BASE_PATH) {
		$urlSegmentToRemove = substr($path, strlen(BASE_PATH));
		if(substr($_SERVER['SCRIPT_NAME'], -strlen($urlSegmentToRemove)) == $urlSegmentToRemove) {
			$baseURL = substr($_SERVER['SCRIPT_NAME'], 0, -strlen($urlSegmentToRemove));
			define('BASE_URL', rtrim($baseURL, DIRECTORY_SEPARATOR));
		}
	}

	// If that didn't work, failover to the old syntax.  Hopefully this isn't necessary, and maybe
	// if can be phased out?
	if(!defined('BASE_URL')) {
		$dir = (strpos($_SERVER['SCRIPT_NAME'], 'index.php') !== false)
			? dirname($_SERVER['SCRIPT_NAME'])
			: dirname(dirname($_SERVER['SCRIPT_NAME']));
		define('BASE_URL', rtrim($dir, DIRECTORY_SEPARATOR));
	}
}
define('MODULES_DIR', 'modules');
define('MODULES_PATH', BASE_PATH . '/' . MODULES_DIR);
define('THEMES_DIR', 'themes');
define('THEMES_PATH', BASE_PATH . '/' . THEMES_DIR);
// Relies on this being in a subdir of the framework.
// If it isn't, or is symlinked to a folder with a different name, you must define FRAMEWORK_DIR
if(!defined('FRAMEWORK_DIR')) {
	define('FRAMEWORK_DIR', basename(dirname(dirname(__FILE__))));
}
define('FRAMEWORK_PATH', BASE_PATH . '/' . FRAMEWORK_DIR);
define('FRAMEWORK_ADMIN_DIR', FRAMEWORK_DIR . '/admin');
define('FRAMEWORK_ADMIN_PATH', BASE_PATH . '/' . FRAMEWORK_ADMIN_DIR);

// These are all deprecated. Use the FRAMEWORK_ versions instead.
define('SAPPHIRE_DIR', FRAMEWORK_DIR);
define('SAPPHIRE_PATH', FRAMEWORK_PATH);
define('SAPPHIRE_ADMIN_DIR', FRAMEWORK_ADMIN_DIR);
define('SAPPHIRE_ADMIN_PATH', FRAMEWORK_ADMIN_PATH);

define('THIRDPARTY_DIR', FRAMEWORK_DIR . '/thirdparty');
define('THIRDPARTY_PATH', BASE_PATH . '/' . THIRDPARTY_DIR);

if(!defined('ASSETS_DIR')) {
	define('ASSETS_DIR', 'assets');
}
if(!defined('ASSETS_PATH')) {
	define('ASSETS_PATH', BASE_PATH . '/' . ASSETS_DIR);
}

///////////////////////////////////////////////////////////////////////////////
// INCLUDES

if(defined('CUSTOM_INCLUDE_PATH')) {
	$includePath = '.' . PATH_SEPARATOR . CUSTOM_INCLUDE_PATH . PATH_SEPARATOR
		. FRAMEWORK_PATH . PATH_SEPARATOR
		. FRAMEWORK_PATH . '/parsers' . PATH_SEPARATOR
		. THIRDPARTY_PATH . PATH_SEPARATOR
		. get_include_path();
} else {
	$includePath = '.' . PATH_SEPARATOR . FRAMEWORK_PATH . PATH_SEPARATOR
		. FRAMEWORK_PATH . '/parsers' . PATH_SEPARATOR
		. THIRDPARTY_PATH . PATH_SEPARATOR
		. get_include_path();
}

set_include_path($includePath);

/**
 * Define the temporary folder if it wasn't defined yet
 */
require_once 'core/TempPath.php';

if(!defined('TEMP_FOLDER')) {
	define('TEMP_FOLDER', getTempFolder(BASE_PATH));
}
