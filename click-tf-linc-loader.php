<?php
/**
 * Plugin Name: click.tf Linc Loader
 * Description: Плагин разбирает текст и подставляет кнопку со ссылкой согласно алгоритма, так же укарачивает ссылку с помощю safelinking.net или wcrypt.com
 * Version:  1.0.1
 * Author: Максим (WP_Panda) Попов
 * Author URI: http://mywordpress.ru/support/profile.php?id=36230
 * License: A "Slug" license name e.g. GPL2
 */

if( ! function_exists( 'cr_parse_content' ) ) {
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

/* подключаем ксс и иконки */
add_action( 'wp_enqueue_scripts', 'load_dashicons_front_end' );

function load_dashicons_front_end() {
    wp_enqueue_style( 'dashicons' );
    wp_enqueue_style( 'cr-loader-style',plugin_dir_url(__FILE__) .'/assets/css/main-style.min.css' );
}

/* подключаем ajax */

if(!function_exists( 'cr_parse_content_script' ) ) {
    function cr_parse_content_script(){

        wp_enqueue_script( 'cr-main-js', plugin_dir_url(__FILE__) .'/assets/js/main-script.min.js', array('jquery'), '1.0.0', true );
        wp_localize_script( 'cr-main-js', 'MyAjax', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'security' => wp_create_nonce( 'my-special-string' )
        ));
    }
    add_action( 'wp_enqueue_scripts', 'cr_parse_content_script' );
}

/* запись сосотяния файла */
function my_action_callback() {
    check_ajax_referer( 'my-special-string', 'security' );

    $id = $_POST['id'];
    $vals = $_POST['vals'];

    update_post_meta($id, 'hav_files_key', $vals);
    echo 'в поле записи - ' . $id . ' записано - ' . $vals;
    die();
}

add_action( 'wp_ajax_my_action', 'my_action_callback' );
add_action( 'wp_ajax_nopriv_my_action', 'my_action_callback' );

/* вывод постов с живыми ссылками */
function my_action_post_callback() {
    check_ajax_referer( 'my-special-string', 'security' );

    $args = array(

        'post_type'   => 'post',
        'post_status' => 'publish',
        'orderby'             => 'rand',
        'ignore_sticky_posts' => true,
        'posts_per_page'         => 5,
        'meta_key'       => 'hav_files_key',
        'meta_value'     => 'yes',
    );


    $query_my = new WP_Query( $args );
    echo '<h4>'. __( "Sorry, but this file is not available now, we recommend to look at the following films:", "wp_panda" ) .'</h4>';
    if ( $query_my->have_posts() ) : while ( $query_my->have_posts() ) : $query_my->the_post(); ?>

        <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" targer="_blank"><?php the_title(); ?></a></br>
    <?php endwhile; else:
    endif;
    wp_reset_query();
    die();
}

add_action( 'wp_ajax_my_action_post', 'my_action_post_callback' );
add_action( 'wp_ajax_nopriv_my_action_post', 'my_action_post_callback' );

/*замена картинки в записи*/
function like_content($content) {
    global $post;
    if( ! is_singular() ) $content = str_replace('pl.jpg', 'ps.jpg', $content);
    return $content;
}
add_filter( 'the_content', 'like_content' );

function add_style_cat(){
    if( ! is_singular() )  { ?>
        <style>

            p.first-p{
                margin-left:170px;
            }

            .post_content img {
                float: left;
                height: auto;
                margin-top: -133px;
                max-width: 100%;
                width: auto;
            }

            .file-acces {
                background-color: #c8c8c8;
                color: #fff;
                cursor: pointer;
                display: inline-block;
                font-family: arial;
                font-size: 18px;
                margin-bottom: 18px;
                margin-left: 170px;
                padding: 8px 30px;
                text-transform: uppercase;
            }

            .append-div{margin-bottom: 74px;}
        </style>
    <?php }
}

add_action( 'wp_head', 'add_style_cat' );

function add_parag() {

    if( ! is_singular()  )  { ?>
        <script>
            (function($){
                $('.file-acces').next('p').addClass('first-p')
            })(jQuery);
        </script>

    <?php }
}

add_action( 'wp_footer', 'add_parag' );

/* ссылка через wcrypt */
/*
function wcrypt_com_action_callback(){
	check_ajax_referer( 'my-special-string', 'security' );
	$url = $_POST['url'];

	$linc = file_get_contents('http://wcrypt.com/api.php?auth=229.d7588d624b5971c61c12eff81fb70856&act=add&url='. $url);
	print_r($linc);
	die();

}

add_action( 'wp_ajax_wcrypt_com_action', 'wcrypt_com_action_callback' );
add_action( 'wp_ajax_nopriv_wcrypt_com_action', 'wcrypt_com_action_callback' );
*/

/* ссылка через safelinking */
function wcrypt_com_action_callback(){
    check_ajax_referer( 'my-special-string', 'security' );
    $urls = $_POST['url'];

    $url = 'http://safelinking.net/api?links-to-protect='. $urls;
    if(!$xml=simplexml_load_file($url)) die('Ошибка загрузки XML');

    $link = $xml->d_links_short;
    echo $link;

    die();

}

add_action( 'wp_ajax_wcrypt_com_action', 'wcrypt_com_action_callback' );
add_action( 'wp_ajax_nopriv_wcrypt_com_action', 'wcrypt_com_action_callback' );