<?php
/**
 * MINZ - Copyright 2011 Marien Fressinaud
 * Sous licence AGPL3 <http://www.gnu.org/licenses/>
*/

/**
 * Request représente la requête http
 */
class Minz_Request {
	private static $controller_name = '';
	private static $action_name = '';
	private static $params = array();

	private static $default_controller_name = 'index';
	private static $default_action_name = 'index';

	/**
	 * Getteurs
	 */
	public static function controllerName() {
		return self::$controller_name;
	}
	public static function actionName() {
		return self::$action_name;
	}
	public static function params() {
		return self::$params;
	}
	public static function param($key, $default = false, $specialchars = false) {
		if (isset(self::$params[$key])) {
			$p = self::$params[$key];
			if (is_object($p) || $specialchars) {
				return $p;
			} else {
				return Minz_Helper::htmlspecialchars_utf8($p);
			}
		} else {
			return $default;
		}
	}
	public static function paramTernary($key) {
		if (isset(self::$params[$key])) {
			$p = self::$params[$key];
			$tp = trim($p);
			// @phpstan-ignore-next-line
			if ($p === null || $tp === '' || $tp === 'null') {
				return null;
			} elseif ($p == false || $tp == '0' || $tp === 'false' || $tp === 'no') {
				return false;
			}
			return true;
		}
		return null;
	}
	public static function paramBoolean($key) {
		if (null === $value = self::paramTernary($key)) {
			return false;
		}
		return $value;
	}
	/**
	 * Extract text lines to array.
	 *
	 * It will return an array where each cell contains one line of a text. The new line
	 * character is used to break the text into lines. This method is well suited to use
	 * to split textarea content.
	 */
	public static function paramTextToArray($key, $default = []) {
		if (isset(self::$params[$key])) {
			return preg_split('/\R/', self::$params[$key]);
		}
		return $default;
	}
	public static function defaultControllerName() {
		return self::$default_controller_name;
	}
	public static function defaultActionName() {
		return self::$default_action_name;
	}
	public static function currentRequest() {
		return array(
			'c' => self::$controller_name,
			'a' => self::$action_name,
			'params' => self::$params,
		);
	}
	public static function modifiedCurrentRequest(array $extraParams = null) {
		$currentRequest = self::currentRequest();
		if (null !== $extraParams) {
			$currentRequest['params'] = array_merge($currentRequest['params'], $extraParams);
		}
		return $currentRequest;
	}

	/**
	 * Setteurs
	 */
	public static function _controllerName($controller_name) {
		self::$controller_name = $controller_name;
	}
	public static function _actionName($action_name) {
		self::$action_name = $action_name;
	}
	public static function _params($params) {
		if (!is_array($params)) {
			$params = array($params);
		}

		self::$params = $params;
	}
	public static function _param($key, $value = false) {
		if ($value === false) {
			unset(self::$params[$key]);
		} else {
			self::$params[$key] = $value;
		}
	}

	/**
	 * Initialise la Request
	 */
	public static function init() {
		self::initJSON();
	}

	public static function is($controller_name, $action_name) {
		return (
			self::$controller_name === $controller_name &&
			self::$action_name === $action_name
		);
	}

	/**
	 * Return true if the request is over HTTPS, false otherwise (HTTP)
	 */
	public static function isHttps(): bool {
		$header = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
		if ('' != $header) {
			return 'https' === strtolower($header);
		}
		return 'on' === ($_SERVER['HTTPS'] ?? '');
	}

	/**
	 * Try to guess the base URL from $_SERVER information
	 *
	 * @return string base url (e.g. http://example.com)
	 */
	public static function guessBaseUrl(): string {
		$protocol = self::extractProtocol();
		$host = self::extractHost();
		$port = self::extractPortForUrl();
		$prefix = self::extractPrefix();
		$path = self::extractPath();

		return filter_var("{$protocol}://{$host}{$port}{$prefix}{$path}", FILTER_SANITIZE_URL);
	}

	private static function extractProtocol(): string {
		if (self::isHttps()) {
			return 'https';
		}
		return 'http';
	}

	/**
	 * @return string
	 */
	private static function extractHost() {
		if ('' != $host = ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? '')) {
			return parse_url("http://{$host}", PHP_URL_HOST);
		}
		if ('' != $host = ($_SERVER['HTTP_HOST'] ?? '')) {
			// Might contain a port number, and mind IPv6 addresses
			return parse_url("http://{$host}", PHP_URL_HOST);
		}
		if ('' != $host = ($_SERVER['SERVER_NAME'] ?? '')) {
			return $host;
		}
		return 'localhost';
	}

	/**
	 * @return integer
	 */
	private static function extractPort() {
		if ('' != $port = ($_SERVER['HTTP_X_FORWARDED_PORT'] ?? '')) {
			return intval($port);
		}
		if ('' != $proto = ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) {
			return 'https' === strtolower($proto) ? 443 : 80;
		}
		if ('' != $port = ($_SERVER['SERVER_PORT'] ?? '')) {
			return intval($port);
		}
		return self::isHttps() ? 443 : 80;
	}

	private static function extractPortForUrl(): string {
		if (self::isHttps() && 443 !== $port = self::extractPort()) {
			return ":{$port}";
		}
		if (!self::isHttps() && 80 !== $port = self::extractPort()) {
			return ":{$port}";
		}
		return '';
	}

	private static function extractPrefix(): string {
		if ('' != $prefix = ($_SERVER['HTTP_X_FORWARDED_PREFIX'] ?? '')) {
			return rtrim($prefix, '/ ');
		}
		return '';
	}

	private static function extractPath(): string {
		$path = $_SERVER['REQUEST_URI'] ?? '';
		if ($path != '') {
			$path = parse_url($path, PHP_URL_PATH);
			return substr($path, -1) === '/' ? rtrim($path, '/') : dirname($path);
		}
		return '';
	}

	/**
	 * Return the base_url from configuration
	 */
	public static function getBaseUrl(): string {
		$conf = Minz_Configuration::get('system');
		$url = trim($conf->base_url, ' /\\"');
		return filter_var($url, FILTER_SANITIZE_URL);
	}

	/**
	 * Test if a given server address is publicly accessible.
	 *
	 * Note: for the moment it tests only if address is corresponding to a
	 * localhost address.
	 *
	 * @param string $address the address to test, can be an IP or a URL.
	 * @return boolean true if server is accessible, false otherwise.
	 * @todo improve test with a more valid technique (e.g. test with an external server?)
	 */
	public static function serverIsPublic($address) {
		if (strlen($address) < strlen('http://a.bc')) {
			return false;
		}
		$host = parse_url($address, PHP_URL_HOST);
		if (!$host) {
			return false;
		}

		$is_public = !in_array($host, array(
			'localhost',
			'localhost.localdomain',
			'[::1]',
			'ip6-localhost',
			'localhost6',
			'localhost6.localdomain6',
		));

		if ($is_public) {
			$is_public &= !preg_match('/^(10|127|172[.]16|192[.]168)[.]/', $host);
			$is_public &= !preg_match('/^(\[)?(::1$|fc00::|fe80::)/i', $host);
		}

		return (bool)$is_public;
	}

	private static function requestId() {
		if (empty($_GET['rid']) || !ctype_xdigit($_GET['rid'])) {
			$_GET['rid'] = uniqid();
		}
		return $_GET['rid'];
	}

	private static function setNotification($type, $content) {
		Minz_Session::lock();
		$requests = Minz_Session::param('requests', []);
		$requests[self::requestId()] = [
				'time' => time(),
				'notification' => [ 'type' => $type, 'content' => $content ],
			];
		Minz_Session::_param('requests', $requests);
		Minz_Session::unlock();
	}

	public static function setGoodNotification($content) {
		self::setNotification('good', $content);
	}

	public static function setBadNotification($content) {
		self::setNotification('bad', $content);
	}

	public static function getNotification() {
		$notif = null;
		Minz_Session::lock();
		$requests = Minz_Session::param('requests');
		if ($requests) {
			//Delete abandoned notifications
			$requests = array_filter($requests, function ($r) { return isset($r['time']) && $r['time'] > time() - 3600; });

			$requestId = self::requestId();
			if (!empty($requests[$requestId]['notification'])) {
				$notif = $requests[$requestId]['notification'];
				unset($requests[$requestId]);
			}
			Minz_Session::_param('requests', $requests);
		}
		Minz_Session::unlock();
		return $notif;
	}

	/**
	 * Relance une requête
	 * @param array<string,string|array<string,string>> $url l'url vers laquelle est relancée la requête
	 * @param bool $redirect si vrai, force la redirection http
	 *                > sinon, le dispatcher recharge en interne
	 */
	public static function forward($url = array(), $redirect = false) {
		if (!is_array($url)) {
			header('Location: ' . $url);
			exit();
		}

		$url = Minz_Url::checkUrl($url);
		$url['params']['rid'] = self::requestId();

		if ($redirect) {
			header('Location: ' . Minz_Url::display($url, 'php', 'root'));
			exit();
		} else {
			self::_controllerName($url['c']);
			self::_actionName($url['a']);
			self::_params(array_merge(
				self::$params,
				$url['params']
			));
			Minz_Dispatcher::reset();
		}
	}


	/**
	 * Wrappers good notifications + redirection
	 * @param string $msg notification content
	 * @param array<string,string|array<string,string>> $url url array to where we should be forwarded
	 */
	public static function good($msg, $url = array()) {
		Minz_Request::setGoodNotification($msg);
		Minz_Request::forward($url, true);
	}

	/**
	 * Wrappers bad notifications + redirection
	 * @param string $msg notification content
	 * @param array<string,string|array<string,mixed>> $url url array to where we should be forwarded
	 */
	public static function bad($msg, $url = array()) {
		Minz_Request::setBadNotification($msg);
		Minz_Request::forward($url, true);
	}

	/**
	 * Allows receiving POST data as application/json
	 */
	private static function initJSON() {
		if ('application/json' !== self::extractContentType()) {
			return;
		}
		if ('' === $ORIGINAL_INPUT = file_get_contents('php://input', false, null, 0, 1048576)) {
			return;
		}
		if (null === $json = json_decode($ORIGINAL_INPUT, true)) {
			return;
		}

		foreach ($json as $k => $v) {
			if (!isset($_POST[$k])) {
				$_POST[$k] = $v;
			}
		}
	}

	private static function extractContentType(): string {
		return strtolower(trim($_SERVER['CONTENT_TYPE'] ?? ''));
	}

	public static function isPost(): bool {
		return 'POST' === ($_SERVER['REQUEST_METHOD'] ?? '');
	}

	/**
	 * @return array<string>
	 */
	public static function getPreferredLanguages() {
		if (preg_match_all('/(^|,)\s*(?P<lang>[^;,]+)/', $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', $matches)) {
			return $matches['lang'];
		}
		return array('en');
	}
}
