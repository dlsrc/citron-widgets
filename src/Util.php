<?php declare(strict_types=1);
/**
 * (c) 2005-2026 Dmitry Lebedev <dlsrc.extra@gmail.com>
 * This source code is part of the Citron template engine.
 * Please see the LICENSE file for copyright and licensing information.
 */
namespace Citron;

final class Util {
	public static function getEncodedValue(string $value, int $enc_type): string {
		return match ($enc_type) {
			PHP_QUERY_RFC1738 => urlencode($value),
			PHP_QUERY_RFC3986 => rawurlencode($value),
			default           => $value,
		};
	}
}
