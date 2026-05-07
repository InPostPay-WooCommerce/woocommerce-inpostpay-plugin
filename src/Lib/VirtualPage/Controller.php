<?php

namespace Ilabs\Inpost_Pay\Lib\VirtualPage;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class Controller implements ControllerInterface {

	private \SplObjectStorage $pages;
	private TemplateLoaderInterface $loader;
	private $matched;

	public function __construct( TemplateLoaderInterface $loader ) {
		$this->pages  = new \SplObjectStorage();
		$this->loader = $loader;
	}

	public function init(): void {
		do_action( 'inpost_pay_virtual_pages', $this );
	}

	public function addPage( PageInterface $page ): PageInterface {
		$this->pages->attach( $page );
		return $page;
	}

	public function dispatch( $bool, \WP $wp ): bool {
		if ( $this->checkRequest() && $this->matched instanceof Page ) {
			$this->loader->init( $this->matched );
			$wp->virtual_page = $this->matched;
			do_action( 'parse_request', $wp );
			$this->setupQuery();
			do_action( 'wp', $wp );
			$this->loader->load();
			$this->handleExit();
		}
		return $bool;
	}

	private function checkRequest() {
		$this->pages->rewind();
		$path = trim( strtok( $this->getPathInfo(), '?' ), '/' );
		while ( $this->pages->valid() ) {
			if ( trim( $this->pages->current()->getUrl(), '/' ) === $path ) {
				$this->matched = $this->pages->current();
				return true;
			}
			$this->pages->next();
		}
	}

	private function getPathInfo() {
		$home_path = parse_url( home_url(), PHP_URL_PATH );
		return preg_replace( "#^/?{$home_path}/#", '/', add_query_arg( array() ) );
	}

	private function setupQuery(): void {
		global $wp_query;
		$wp_query->init();
		$wp_query->is_page        = true;
		$wp_query->is_singular    = true;
		$wp_query->is_home        = false;
		$wp_query->found_posts    = 1;
		$wp_query->post_count     = 1;
		$wp_query->max_num_pages  = 1;
		$posts                    = (array) apply_filters(
			'the_posts',
			array( $this->matched->asWpPost() ),
			$wp_query
		);
		$post                     = $posts[0];
		$wp_query->posts          = $posts;
		$wp_query->post           = $post;
		$wp_query->queried_object = $post;
		$GLOBALS['post']          = $post;
		$wp_query->virtual_page   = $post instanceof \WP_Post && isset( $post->is_virtual )
			? $this->matched
			: null;
	}

	public function handleExit(): void {
		exit();
	}
}
