<?php
//屏蔽谷歌字体读取
function coolwp_remove_open_sans_from_wp_core() {
    wp_deregister_style( 'open-sans' );
    wp_register_style( 'open-sans', false );
    wp_enqueue_style('open-sans','');
}
add_action( 'init', 'coolwp_remove_open_sans_from_wp_core' );

//独立菜单设置
register_nav_menus(
    array(
        'PrimaryMenu'=>'导航',
        'friendlinks'=>'友情链接',
        'footer_nav'=>'页脚导航'
    )
    );
add_theme_support('nav_menus');


#AJAX请求
require_once('ajax.php');

header('Content-Type:text/html;charset=utf-8');
header("Access-Control-Allow-Origin: *"); 
header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');

#获取分类
add_action('wp_ajax_get_catlist', 'get_catlist');
add_action('wp_ajax_nopriv_get_catlist', 'get_catlist');

#分类详情
add_action('wp_ajax_get_catdetail', 'get_catdetail');
add_action('wp_ajax_nopriv_get_catdetail', 'get_catdetail');

#获取菜单
add_action('wp_ajax_get_menu', 'get_menu');
add_action('wp_ajax_nopriv_get_menu', 'get_menu');

#获取文章列表 
add_action('wp_ajax_get_article_list', 'get_article_list');
add_action('wp_ajax_nopriv_get_article_list', 'get_article_list');

#搜索文章
add_action('wp_ajax_wp_search', 'wp_search');
add_action('wp_ajax_nopriv_wp_search', 'wp_search');

#标签列表
add_action('wp_ajax_get_taglist', 'get_taglist');
add_action('wp_ajax_nopriv_get_taglist', 'get_taglist');

#相关文章
add_action('wp_ajax_related_articles', 'related_articles');
add_action('wp_ajax_nopriv_related_articles', 'related_articles');

#留言
add_action('wp_ajax_comment_callback', 'comment_callback');
add_action('wp_ajax_nopriv_comment_callback', 'comment_callback');

#获取评论列表
add_action('wp_ajax_cus_get_comment', 'cus_get_comment');
add_action('wp_ajax_nopriv_cus_get_comment', 'cus_get_comment');

#登录
add_action('wp_ajax_login', 'login');
add_action('wp_ajax_nopriv_login', 'login');

#注册
add_action('wp_ajax_register', 'register');
add_action('wp_ajax_nopriv_register', 'register');


/**
 * 获取文章访问量
 * @param $postID int 文章ID
 * @return int
 */
function getPostViews($postID){
    $count_key = 'views';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
        return "0";
    }
    return $count;
}
/**
 * 文章访问量+1
 * @param int $postID 文章ID
 */
function setPostViews($postID) {
    $count_key = 'views';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
    }else{
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}

/**
 * 关键词标红
 */
function set_red($key, $str){
    $replace = '<font color="red">'.$key.'</font>';
    $search = strtolower($key);
    return str_ireplace($search, $replace, $str);
}
