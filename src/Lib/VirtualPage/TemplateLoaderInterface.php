<?php

namespace Ilabs\Inpost_Pay\Lib\VirtualPage;

interface TemplateLoaderInterface {

	/**
	 * Setup loader for a page objects
	 *
	 * @param PageInterface $page matched virtual page
	 */
	public function init( PageInterface $page );

	/**
	 * Trigger core and custom hooks to filter templates,
	 * then load the found template.
	 */
	public function load();
}
