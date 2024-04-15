<?php declare(strict_types=1);
/**
 * (c) 2005-2024 Dmitry Lebedev <dl@adios.ru>
 * This source code is part of the Citron template engine.
 * Please see the LICENSE file for copyright and licensing information.
 */
namespace Citron;

use Ultra\Error;

final class Widget {
	private array $_stack = [];

	public function __construct(string $branch) {
		$page = Page::open();
		
		if (Error::class == $page::class) {
			return;
		}

		if (str_contains($branch, '.')) {
			$comp = explode('.', $branch);
			$size = sizeof($comp);

			if (!$page->isComponent($comp[0])) return;

			$this->_stack[0] = $page->{$comp[0]};

			for ($i = 1; $i < $size; $i++) {
				if (!$this->_stack[0]->isComponent($comp[$i])) {
					$this->_stack = [];
					return;
				}
				else {
					array_unshift($this->_stack, $this->_stack[0]->{$comp[$i]});
				}
			}
		}
		elseif ($page->isComponent($branch)) {
			$this->_stack[0] = $page->$branch;
		}
	}

	public function ready(string $branch = '') {
		if ('' == $branch) {
			foreach ($this->_stack as $comp) {
				$comp->ready();
			}
		}
		else {
			if (str_contains($branch, '.')) {
				$comp = array_values(array_reverse(explode('.', $branch)));

				foreach ($comp as $id => $name) {
					if (!isset($this->_stack[$id]) || $this->_stack[$id]->getName() != $name) {
						return;
					}
				}

				foreach (array_keys($comp) as $id) {
					$this->_stack[$id]->ready();
				}
			}
			elseif ($this->_stack[0]->getName() == $branch) {
				$this->_stack[0]->ready();
			}
		}
	}

	public function getStack(): array {
		return $this->_stack;
	}

	public function getSnippet(): Component|false {
		if (isset($this->_stack[0])) {
			return $this->_stack[0];
		}

		return false;
	}

	public static function stack(string $branch): Component|false {
		$page = Page::open();

		if (Error::class == $page::class) {
			return false;
		}

		$comp = explode('.', $branch);
		$size = sizeof($comp);

		if (!$page->isComponent($comp[0])) {
			return false;
		}

		$open[0] = $page->{$comp[0]};

		for ($i = 1; $i < $size; $i++) {
			if (!$open[0]->isComponent($comp[$i])) {
				$open = [];
				return false;
			}
			else {
				array_unshift($open, $open[0]->{$comp[$i]});
			}
		}

		return $open[0];
	}
}
