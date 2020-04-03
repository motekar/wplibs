<?php
namespace Motekar\WPLibs;

/**
 * Routable Trait
 *
 * Example of use:
 *
 * <code>
 * // init routable in __construct
 * $this->route_init( 'rootslug' );
 *
 * // register routes in route_register function
 *
 * $this->route( 'GET foo/{id}/{slug}', function( $id, $slug ) {
 *     // available actions:
 *     // - directly echo the output
 *     // - return file path, will be included automatically
 *     // - return array or object, will be sent as JSON using wp_send_json
 *     // - return true // let WP process the request
 * }, [
 *     [ 'regex' => [ 'id' => '\d+' ] ] // by default the regex is [^/]+
 * ] );
 * </code>
 *
 * @author  Fadlul Alim <fad.lee@hotmail.com>
 *
 * @since 1.0.0
 */
trait Routable_Trait {
	var $root_slug = '';
	var $routes = array();

	public function route_init( $root_slug, $callback ) {
		$this->root_slug = $root_slug;

		if ( preg_match("#^{$this->root_slug}#", $this->get_current_uri() ) ) {
			// callback for route registration
			call_user_func( $callback );

			if ( $this->route_resolve( false ) ) {
				$this->register_route_filters();
			}
		}
	}

	private function register_route_filters() {
		\add_action( 'after_setup_theme', function() {
			if ( ! \current_user_can( 'manage_options' ) && ! is_admin() ) {
				\show_admin_bar(false);
			}
			\remove_action( 'wp_print_styles', 'print_emoji_styles' );
			\remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		} );

		\add_action( 'wp_enqueue_scripts', function() {
			\wp_dequeue_style( 'wp-block-library' );
 			\wp_dequeue_style( 'wp-block-library-theme' );
		} );

		// Codes below are inspired from typerocket Routes.php
		// template_include is needed to keep admin bar
		\add_action( 'template_include', function( $template ) {
			$return = $this->route_resolve();
			return $template;
		} );

		// disable auto redirect to trailingslashed url
		\add_filter( 'redirect_canonical', function( $redirect_url, $requested_url ) {
			return false;
		}, 10, 2);

		// below is the code to disable unwanted SQL query
		\add_filter( 'query_vars', function( $vars ) {
			$vars[] = $this->root_slug . '_route_var';
			return $vars;
		} );

		\add_action('option_rewrite_rules', function( $rules ) {
			$add = [];
			$key = '^' . $this->get_current_uri() . '/?$';
			$add[$key] = "index.php?{$this->root_slug}_route_var=1";

			if( is_array( $rules ) ) {
				$rules = array_merge( $add, $rules );
			} else {
				$rules = $add;
			}

			return $rules;
		} );

		\add_filter( 'posts_request', function( $sql, $q ) {
			/** @var WP_Query $q */
			if ( $q->is_main_query() && !empty($q->query[$this->root_slug . '_route_var']) ) {
				// disable row count
				$q->query_vars['no_found_rows'] = true;

				// disable cache
				$q->query_vars['cache_results'] = false;
				$q->query_vars['update_post_meta_cache'] = false;
				$q->query_vars['update_post_term_cache'] = false;

				\add_filter('body_class', function($classes) { array_push($classes, 'custom-route'); return $classes; });
				return false;
			}
			return $sql;
		}, 10, 3 );
	}

	function route( $path, $callback, $args = [] ) {
		if ( preg_match( '#^(get|post|put|patch|delete) (.+)#i', $path, $matches ) ) {
			$method = strtolower( $matches[1] );
			$path = $matches[2];
		} else {
			$method = 'get';
		}

		$regex  = isset( $args['regex']) ? $args['regex'] : '';
		$this->routes[] = compact( 'path', 'callback', 'method', 'regex' );
	}

	function route_resolve( $execute = true ) {
		// parse request uri
		$current_uri	= trim( preg_replace( "#^{$this->root_slug}/?#", '', $this->get_current_uri() ), '/' );
		$current_method = strtolower( $_SERVER['REQUEST_METHOD'] );

		// method spoofing, inspired by Laravel
		if ( preg_match( '#^(put|patch|delete)#i', filter_input( INPUT_POST, '_method' ) ) ) {
			$current_method = filter_input( INPUT_POST, '_method' );
		}

		$route_matched = '';
		$route_params  = [];

		foreach ( $this->routes as $route ) {
			if ( $route['method'] != $current_method ) {
				continue;
			}

			if ( $route['path'] == $current_uri ) {
				$route_matched = $route;
			} elseif ( strpos( $route['path'], '{' ) ) {
				// route with parameter
				list( $route_matched, $route_params ) =
					$this->route_match_regex( $route, $current_uri );
			}
		}

		if ( is_array( $route_matched ) ) {
			if ( $execute ) {
				$this->route_execute( $route_matched['callback'], $route_params );
			} else {
				return true;
			}
		}

		return false;
	}

	function route_match_regex( $route, $request_uri ) {
		$route_matched = '';
		$route_params = [];

		// match route with parameters
		if ( preg_match_all( '/\{(.*?)\}/', $route['path'], $matches ) ) {
			// build regex
			$regex = '#' . str_replace( '/', '\\/', $route['path'] ) . '#';
			foreach ( $matches[1] as $param ) {
				$route_regex = empty( $route['regex'][$param] ) ? '[^/]+' : $route['regex'][$param];
				$regex = str_replace( '{'. $param . '}', '(' . $route_regex . ')', $regex );
			}

			// match regex with request uri
			if ( preg_match( $regex, $request_uri, $route_params ) ) {
				$route_matched = $route;
				array_shift( $route_params );
			}
		}
		return array( $route_matched, $route_params );
	}

	function route_execute( $callback, $params ) {
		ob_start();
		$return = call_user_func_array( $callback, $params );
		$buffer = ob_get_clean();

		// return is template file
		if ( is_string( $return ) ) {
			if ( file_exists( $return ) ) {
				include $return;
			} else {
				echo $return;
			}
		}

		if ( is_array( $return ) || is_object( $return ) ) {
			\wp_send_json( $return );
		}

		echo $buffer;

		if ( $return === true ) {
			// don't exit, allow WP to run
		} else {
			exit();
		}
	}

	function route_for_user( $redirect_to = '/login' ) {
		if ( ! \is_user_logged_in() ) {
			\wp_redirect( $this->get_url( $redirect_to ) );
			exit;
		}
	}

	function route_for_non_user( $redirect_to = '/' ) {
		if ( is_user_logged_in() ) {
			\wp_redirect( $this->get_url( $redirect_to ) );
			exit;
		}
	}

	function get_url( $path = '/' ) {
		$path = ltrim( $path, '/' );
		return \home_url( "/{$this->root_slug}/{$path}" );
	}

	function get_current_uri() {
		return \untrailingslashit( ltrim( explode('?', $_SERVER['REQUEST_URI'] )[0], '/' ) );
	}

	function allowed_or_redirect( $redirect_to = '/login' ) {
		if ( ! \is_user_logged_in() ) {
			\wp_redirect( $this->get_url( $redirect_to ) );
			exit;
		}
		return true;
	}
}
