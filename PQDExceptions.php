<?php
namespace PQD;
/**
 *
 * @author Willker Moraes Silva
 * @since 2012-07-26
 *
 */
class PQDExceptions {
	/**
	 * @var array[\Exception]
	 */
	private static $exceptions = array();

	/**
	 * @return the $exceptions
	 */
	public function getExceptions() {
		return self::$exceptions;
	}

	/**
	 * @return string
	 */
	public function getHtmlExceptions($development = IS_DEVELOPMENT) {
		return PQDUtil::getErrorLikeHTML($this, $development);
	}

	/**
	 * @return string
	 */
	public function getJsonExceptions($development = IS_DEVELOPMENT) {
		return PQDUtil::getErrorLikeJSON($this, $development);
	}

	/**
	 * @param \Exception $exception
	 */
	public function setException(\Exception $exception) {
		self::$exceptions[] = $exception;
	}

	/**
	 * @param array[\Exception] $exceptions
	 */
	public function setExceptions(array $exceptions) {
		self::$exceptions = $exceptions;
	}

	/**
	 * @return int
	 */
	public function count() {
		return count(self::$exceptions);
	}
}