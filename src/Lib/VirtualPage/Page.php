<?php

namespace Ilabs\Inpost_Pay\Lib\VirtualPage;

use WP_Post;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class Page implements PageInterface {

	private string $url;
	private string $title;
	private string $content;
	private string $template;
	private $wp_post;

	public function __construct( $url, $title = 'Untitled', $template = 'page.php' ) {
		$this->url = filter_var( $url, FILTER_SANITIZE_URL );
		$this->setTitle( $title );
		$this->setTemplate( $template);
	}

	public function getUrl() {
		return $this->url;
	}

	public function getTemplate(): string {
		return $this->template;
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function setTitle( $title ): Page {
		$this->title = htmlspecialchars( $title );
		return $this;
	}

	public function setContent( $content ): Page {
		$this->content = $content;
		return $this;
	}

	public function setTemplate( $template ): Page {
		$this->template = $template;
		return $this;
	}

	public function asWpPost(): WP_Post {
		if ( is_null( $this->wp_post ) ) {
			$post = array(
				'ID'             => 0,
				'post_title'     => $this->title,
				'post_name'      => sanitize_title( $this->title ),
				'post_content'   => $this->content ? : '',
				'post_excerpt'   => '',
				'post_parent'    => 0,
				'menu_order'     => 0,
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'comment_count'  => 0,
				'post_password'  => '',
				'to_ping'        => '',
				'pinged'         => '',
				'guid'           => home_url( $this->getUrl() ),
				'post_date'      => current_time( 'mysql' ),
				'post_date_gmt'  => current_time( 'mysql', 1 ),
				'post_author'    => is_user_logged_in() ? get_current_user_id() : 0,
				'is_virtual'     => TRUE,
				'filter'         => 'raw'
			);
			$this->wp_post = new WP_Post( (object) $post );
		}
		return $this->wp_post;
	}
}
