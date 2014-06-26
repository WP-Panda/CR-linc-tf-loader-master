<?php
/**
 * Plugin Name: click.tf Linc Loader
 * Description: Плагин разбирает текст и подставляет кнопку со ссылкой согласно алгоритма.
 * Version:  1.0.0
 * Author: Максим (WP_Panda) Попов
 * Author URI: http://mywordpress.ru/support/profile.php?id=36230
 * License: A "Slug" license name e.g. GPL2
 */

if(!function_exists( 'cr_parse_content' ) ) {
	function cr_parse_content($content){

		/* получаем файл для запроса*/
		global $post;
		$str = get_the_title($post->ID);
		$pattern = '/\\[.*?\\]/';
		preg_match($pattern, $str,$file );
		$file = $file[0];
		$file = trim($file,'[]');
		$file_reserv = explode('-',$file,2);
		$file_reserv = $file_reserv[0];

		/* выводим html*/

		$append_div = '<div id="file-' . $post->ID . '" class="append-div"></div>
						<span  class="file-acces" data-file="' . $file . '" data-file-reserv="' . $file_reserv . '" data-id="' . $post->ID . '"><i class="dashicons dashicons-download"></i>' . __('Download', 'wp_panda' ) .'</span>';

		return $append_div .$content;


	}

	add_filter('the_content','cr_parse_content');
}

add_action( 'wp_enqueue_scripts', 'load_dashicons_front_end' );
function load_dashicons_front_end() {
wp_enqueue_style( 'dashicons' );
wp_enqueue_style( 'cr-loader-style',plugin_dir_url(__FILE__) .'/assets/css/main-style.css' );
}

if(!function_exists( 'cr_parse_content_script' ) ) {
	function cr_parse_content_script(){

 wp_enqueue_script( 'cr-main-js', plugin_dir_url(__FILE__) .'/assets/js/main-script.js', array('jquery'), '1.0.0', true );
  wp_localize_script( 'cr-main-js', 'MyAjax', array(
    // URL to wp-admin/admin-ajax.php to process the request
    'ajaxurl' => admin_url( 'admin-ajax.php' ),
 
    // generate a nonce with a unique ID "myajax-post-comment-nonce"
    // so that you can check it later when an AJAX request is sent
    'security' => wp_create_nonce( 'my-special-string' )
  ));
}
add_action( 'wp_enqueue_scripts', 'cr_parse_content_script' );
 }

// The function that handles the AJAX request
function my_action_callback() {
  check_ajax_referer( 'my-special-string', 'security' );
 
  $id = $_POST['id'];
  $vals = $_POST['vals'];

  update_post_meta($id, 'hav_files_key', $vals);
  echo 'в поле записи - ' . $id . ' записано - ' . $vals;
  die(); // this is required to return a proper result
}
add_action( 'wp_ajax_my_action', 'my_action_callback' );
add_action( 'wp_ajax_nopriv_my_action', 'my_action_callback' );


function my_action_post_callback() {
  check_ajax_referer( 'my-special-string', 'security' );
 	/**
 	 * The WordPress Query class.
 	 * @link http://codex.wordpress.org/Function_Reference/WP_Query
 	 *
 	 */
 	$args = array(
 		
 		//Type & Status Parameters
 		'post_type'   => 'post',
 		'post_status' => 'publish',
 		//Order & Orderby Parameters
 		'orderby'             => 'rand',
 		'ignore_sticky_posts' => true,
 		//Pagination Parameters
 		'posts_per_page'         => 5,
 		//Custom Field Parameters
 		'meta_key'       => 'hav_files_key',
 		'meta_value'     => 'yes',
 	);
 

 $query_my = new WP_Query( $args );
 echo '<h4>'. __( "К сожалению файл в наcтоящее время не доступен, рекомендуем посмотреть следующие фильмы", "wp_panda" ) .'</h4>';
if ( $query_my->have_posts() ) : while ( $query_my->have_posts() ) : $query_my->the_post(); ?>

<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></br>
<?php endwhile; else: 
 endif; 
 wp_reset_query();
  die(); // this is required to return a proper result
}
add_action( 'wp_ajax_my_action_post', 'my_action_post_callback' );
add_action( 'wp_ajax_nopriv_my_action_post', 'my_action_post_callback' );