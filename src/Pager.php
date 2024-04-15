<?php declare(strict_types=1);
/**
 * (c) 2005-2024 Dmitry Lebedev <dl@adios.ru>
 * This source code is part of the Citron template engine.
 * Please see the LICENSE file for copyright and licensing information.
 */
namespace Citron;

abstract class Pager {
	abstract public function draw(Component $pager, int $total, int $list): void;
	abstract public function drawMax(Component $pager, int $total, int $list): void;

	protected string $name;
	protected int    $page;
	protected array  $event;
	protected array  $query;
	protected string $raw;
	protected int    $size;
	protected string $prev;
	protected string $next;
	protected string $text;
	protected bool   $error;
	protected string $anchor;
	protected int    $encode;

	public function __construct(string $name = 'page', int $size = 5) {
		$this->name  = $name;
		$this->error = false;
		$this->page  = 1;

		if (isset($_REQUEST[$this->name])) {
			if (!ctype_digit($_REQUEST[$this->name]) || str_starts_with($_REQUEST[$this->name], '0')) {
				$this->error = true;
			}
			else {
				$this->page = (int)$_REQUEST[$this->name];

				if ($this->page < 1) {
					$this->error = true;
					$this->page  = 1;
				}
			}
		}

		$this->event  = [];
		$this->query  = [];
		$this->raw    = '';
		$this->size   = $size;
		$this->prev   = 'назад';
		$this->next   = 'вперед';
		$this->text   = '...';
		$this->anchor = '';
		$this->encode = PHP_QUERY_RFC1738;
	}

	/*
	* Если аргумент TRUE, очистится информация об ошибке
	*/
	public function clean(bool $error = false): void {
		if ($error) {
			$this->error = false;
		}

		$_REQUEST[$this->name] = '1';
		$this->page  = 1;
		$this->event = [];
		$this->query = [];
		$this->raw   = '';
	}

	public function anchor(string $anchor): string {
		return $this->anchor = '#'.$anchor;
	}

	public function isError(): bool {
		return $this->error;
	}

	public function current(int|null $total = null, int|null $size = null): string {
		if (isset($total) && isset($size) && $size > 0) {
			$max = ceil($total / $size);

			if ($this->page > $max) {
				$this->error = true;
				$this->page  = 1;
			}
		}

		return (string)$this->page;
	}

	public function size(int $size): void {
		if ($size < 5) {
			$size = 5;
		}
		elseif (!($size % 2)) {
			$size++;
		}

		$this->size = $size;
	}

	public function prev(string $prev): void {
		$this->prev = $prev;
	}

	public function next(string $next): void {
		$this->next = $next;
	}

	public function text(string $text): void {
		$this->text = $text;
	}

	public function encode(int $type): void {
		if (PHP_QUERY_RFC1738 != $type && PHP_QUERY_RFC3986 != $type) {
			return;
		}

		$this->encode = $type;
	}

	public function event(string $name, int $enc_type = 0): string {
		if (isset($_REQUEST[$name])) {
			if (0 == $enc_type) {
				$enc_type = $this->encode;
			}

			$this->event[$name] = Util::getEncodedValue($_REQUEST[$name], $enc_type);
			return $this->event[$name];
		}

		return '';
	}

	public function target(string $name, string $value, int $enc_type = 0): void {
		if (0 == $enc_type) {
			$enc_type = $this->encode;
		}

		$this->event[$name] = Util::getEncodedValue($value, $enc_type);
	}

	public function query(string $name, $stop_value = null, $default_value = null): void {
		if (isset($_REQUEST[$name])) {
			if ($stop_value != $_REQUEST[$name]) {
				$this->query[$name] = $_REQUEST[$name];
			}
		}
		elseif (isset($default_value)) {
			$this->query[$name] = $default_value;
		}
	}

	public function rawquery(string $raw): void {
		$this->raw = $raw;
	}
}
