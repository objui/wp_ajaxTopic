<?php
if (!function_exists('get_catlist')) {
    /**
     * 获取分类列表
     * @param number $cid
     */
    function get_catlist() {
        try {
            $post = wp_unslash( $_POST );
            $args_default = array(
                'parent'           => 0,
                'noin_arr'         => ''
            );
            $args = array_merge($args_default, $post);
            
            $list =[];
            $noin_arr = explode(",", $args['noin_arr']);
            $cat_list = get_categories($args);
            foreach($cat_list as $val){
                if (!in_array($val->slug, $noin_arr)){
                    $list[] = [
                        'id'            => $val->term_id,
                        'name'          => $val->name,
                        'pid'           => $val->parent,
                        'slug'          => $val->slug,
                        'url'           => get_category_link($val->term_id),
                        'count'         => $val->count,
                        'description'   => $val->description
                    ];
                }
            }
            
            $json = [
                'code'  =>200,
                'msg'   =>'成功',
                'data'  => $list
            ];
        } catch (Exception $e) {
            $json = [
                'code'  =>500,
                'msg'   =>'系统繁忙，请稍后再试~'
            ];
        }
        echo  json_encode($json);
        exit;
    }
}

if (!function_exists('get_catdetail')) {
    /**
     * 分类ID获取分类详情
     */
    function get_catdetail(){
        try {
            $post = wp_unslash( $_POST );
            $cat_id = isset($post['cat_id']) ? intval($post['cat_id']) : 0;
            $info = get_category($cat_id);
            $info = (array) $info;
            $json = [
                'code'  =>200,
                'msg'   =>'成功',
                'data'  => $info
            ];
        } catch (Exception $e) {
            $json = [
                'code'  =>500,
                'msg'   =>'系统繁忙，请稍后再试~'
            ];
        }
        echo  json_encode($json);
        exit;
        
        
    }
}

if (!function_exists('get_menu')) {
    /**
     * 获取菜单列表
     */
    function get_menu() {
        try {
            $post = wp_unslash( $_POST );
            $args_default = array(
                'menu'          => '',
                'container'     => false,
                'echo'          => false,
                'items_wrap'    => '%3$s',
                'depth'         => 0
            );
            $args = array_merge($args_default, $post);
            
            $nav = strip_tags(wp_nav_menu( $args ), '<a>' );
            $nav = explode("\n", $nav);
            $new_nav = [];
            $name = [];
            $href = [];
            $target = [];
            foreach($nav as $val){
                preg_match_all("/>(.*)<\/a>/", $val, $name, PREG_SET_ORDER);
                preg_match_all("/href=\"([^\"]+)/", $val, $href, PREG_SET_ORDER);
                preg_match_all("/target=\"([^\"]+)/", $val, $target, PREG_SET_ORDER);
                if($name[0][1] != '') {
                    $new_nav[] = [
                        'name'      => @$name[0][1],
                        'url'       => @$href[0][1],
                        'target'    => @$target[0][1]
                    ];
                }
            }
            
            $json = [
                'code'  =>200,
                'msg'   =>'成功',
                'data'  => $new_nav
            ];
        } catch (Exception $e) {
            $json = [
                'code'  =>500,
                'msg'   =>'系统繁忙，请稍后再试~'
            ];
        }
        echo  json_encode($json);
        exit;
    }
}

if (!function_exists('get_article_list')) {
    /**
     * 获取文章列表
     */
    function get_article_list(){
        try {
            if(!empty($_POST)) {
                $args = wp_unslash($_POST);
                $args_default=array(
                    'posts_per_page'   => 5,
                    'offset'           => 1,
                    'category'         => 0,
                    'orderby'          => 'date',
                    'order'            => 'DESC',
                );
               
                $args = array_merge($args_default, $args);
                $curr = $args['offset'];
                $args['offset'] =  ($args['offset']- 1) * $args['posts_per_page'];
                $list = get_posts($args);
                
                $newArr = [];
                
                foreach($list as $val){
                    $catlist = get_the_category($val->ID);
                    $cat_list = [];
                    foreach($catlist as $cat_val){
                        $cat_list [] = [
                            'id'    =>$cat_val->term_id,
                            'name'  =>$cat_val->name,
                            'link'  => get_category_link($cat_val->term_id)
                        ];
                    }
                   
                    $remark = get_the_excerpt($val->ID);
                    $remark = !empty($remark) ? $remark : get_post($val->ID)->post_content;
                    $remark = strip_tags($remark);
                    $img = array_values(get_attached_media( '',$val->ID ))[0]->guid;
                    $newArr[] = [
                        'id'        => $val->ID,
                        'name'      => $val->post_title,
                        'img'       => $img,
                        'date'      => date('Y/m/d',strtotime($val->post_date)),
                        'url'       => $val->guid,
                        'remark'    => get_the_excerpt($val->ID),
                        'category'  => $cat_list,
                        'hits'      => getPostViews($val->ID),
                        'content'   => $val->post_content,
                        'comment_count'=>$val->comment_count
                    ];
                }
             
                $limits = intval($args['posts_per_page']);
                unset($args['posts_per_page']);
                unset($args['offset']);
                $args['numberposts'] = -1;
                $total =   count(get_posts($args));
                
                $pages = ceil($total/$limits);
                $json = [
                    'code'  =>200,
                    'msg'   =>'成功',
                    'total' => intval($total),
                    'curr'  => intval($curr),
                    'limits'=> $limits,
                    'pages' => intval($pages),
                    'data'  => $newArr,
                ];
            }
        
        } catch (Exception $e) {
            $json = [
                'code'  =>500,
                'msg'   =>'系统繁忙，请稍后再试~'
            ];
        }
        echo  json_encode($json);
        exit;
        
    }
}
if (!function_exists('wp_search')) {
    /**
     * 搜索文章
     */
    function wp_search(){
       
        // 指定返回头
        header("Content -Type: application/json");
        
        try{
            $args = wp_unslash($_POST);
            $args_default     = [
                'posts_per_page'      => -1,
                'ignore_sticky_posts' => 1,
                'post_type'           => 'post',
                'post_status'         => 'publish',
                'category'         => 0,
                'orderby'          => 'date',
                'order'            => 'DESC',
            ];
            
            $keyword = $args['keyword'] ?$args['keyword']:'';
            $args = array_merge($args_default, $args);
            
            $result   = new WP_Query($args);
            $articles = [];
            if ($result->have_posts()) {
                while ($result->have_posts()) {
                    $result->the_post();
                    
                    global $post;
                    $post_title = $post->post_title;
                    $post_content = $post->post_content;
                    
                    if (mb_stripos($post_title, $keyword) !== false || mb_stripos($post_content, $keyword) !== false) {
                        $img = array_values(get_attached_media( '',$post->ID ))[0]->guid;
                        
                        $post_title = set_red($keyword, $post_title);
                        $post_content = set_red($keyword, $post_content);
                        $articles[] = [
                            'id'        => $post->ID,
                            'name'      => $post_title,
                            'img'       => $img,
                            'date'      => date('Y/m/d',strtotime($post->post_date)),
                            'url'       => $post->guid,
                            'remark'    => get_the_excerpt($post->ID),
                            'hits'      => getPostViews($post->ID),
                            'content'   => $post_content,
                            'comment_count'=>$post->comment_count
                           
                        ];
                    }
                }
            }
            wp_reset_query();
            
            $curr = intval($args['curr']);
            $limits = intval($args['limits']);
            $total = count($articles);
            $pages = intval(ceil($total/$limits)); 
       
            $list = array_slice($articles, ($curr-1)*$pages, $limits);
    
            $json = [
                'code'  =>200,
                'msg'   =>'成功',
                'total' => $total,
                'curr'  => $curr,
                'limits'=> $limits,
                'pages' => $pages,
                'data'  => $list,
            ];
        } catch (Exception $e) {
            $json = [
                'code'  =>500,
                'msg'   =>'系统繁忙，请稍后再试~'
            ];
        }
        echo  json_encode($json);
        exit;   
    }
}

if (!function_exists('related_articles')) {
    /**
     * 相关文章
     */
    function related_articles($post_id = 0, $limit=6){
        if(!empty($_POST)) {
            //global $post, $wpdb;
            global $wpdb;
            $post_id = intval($_POST['post_id']);
            $limit = intval($_POST['limit']);
            $post_tags = wp_get_post_tags($post_id);
            if ($post_tags) {
                $tag_list = '';
                foreach ($post_tags as $tag) {
                    // 获取标签列表
                    $tag_list .= $tag->term_id.',';
                }
                $tag_list = substr($tag_list, 0, strlen($tag_list)-1);
            
                $list = $wpdb->get_results("
                    SELECT DISTINCT ID, post_title, post_date, guid
                    FROM {$wpdb->prefix}posts, {$wpdb->prefix}term_relationships, {$wpdb->prefix}term_taxonomy
                    WHERE {$wpdb->prefix}term_taxonomy.term_taxonomy_id = {$wpdb->prefix}term_relationships.term_taxonomy_id
                    AND ID = object_id
                    AND taxonomy = 'post_tag'
                    AND post_status = 'publish'
                    AND post_type = 'post'
                    AND term_id IN (" . $tag_list . ")
                    AND ID != '" . $post_id . "'
                    ORDER BY RAND()
                    LIMIT {$limit}");
                $json = [];
                if ( $list ) {
                    foreach ($list as $val) {
                        $json[] = [
                            'id'        => $val->ID,
                            'name'      => $val->post_title,
                            'img'       => array_values(get_attached_media( '',$val->ID ))[0]->guid,
                            'date'      => date('Y/m/d',strtotime($val->post_date)),
                            'url'       => $val->guid,
                            'remark'    => get_the_excerpt($val->ID),
                        ];
                    }
                }
                echo  json_encode(array(
                    'code'  =>200, 
                    'msg'   =>'成功', 
                    'data'  =>$json
                ));
        
            }   
        } 
        exit;
    }
}

if (!function_exists('comment_callback')) {
    /**
     * 提交文章评论
     */
    function comment_callback(){
        $comment = wp_handle_comment_submission( wp_unslash( $_POST ) );
        if ( is_wp_error( $comment ) ) {
            $code = $comment->get_error_data();
            if ( ! empty( $code ) ) {
                echo  json_encode(array(
                    'code'  =>$code, 
                    'msg'   =>$comment->get_error_message()
                ));
            } 
            exit;
        }
        
        $user = wp_get_current_user();
        do_action('set_comment_cookies', $comment, $user);
        echo  json_encode(array(
            'code'  => 200, 
            'msg'   => '恭喜，评论成功！您的评论我们会在第 一时间审核，审核通过后会在列表中展示！'
        ));
        exit;
    }
}

if (!function_exists('cus_get_comment')) {
    /**
     * 获取评论列表
     */
    function cus_get_comment(){
        $post = wp_unslash( $_POST );
        $args_default=array(
            'number'            => 5,
            'offset'            => 1,
            'status'            => 'approve'
        );
        $args = array_merge($args_default, $post);
		$curr = $args['offset'];
		$limits = $args['number'];
        $args['offset'] =  ($args['offset']- 1) * $args['number'];
		$list = get_comments($args);
        unset($args['number']);
        unset($args['offset']);
        $total = count(get_comments($args));
		$pages = ceil($total/$limits);
        $json = [];
        foreach ($list as $val) {
            $json[] = [
                'comment_ID' 			=> 	$val->comment_ID,
                'comment_author' 		=> 	$val->comment_author,
                'comment_author_email'	=> 	$val->comment_author_email,
                'comment_author_IP'		=>	$val->comment_author_IP,
                'comment_date'			=>	$val->comment_date,
                'comment_content'		=> 	$val->comment_content,
                'user_id'				=>	$val->user_id
            ];
        }
        echo  json_encode(array(
            'code'  =>200, 
            'msg'   =>'成功', 
            'total' => intval($total), 
            'curr'  => intval($curr), 
            'limits'=> intval($limits),
			'pages' => intval($pages),
            'data'  => $json
        ));
        exit;
    }
}


if (!function_exists('get_taglist')) {
    /**
     * 获取标签列表
     */
    function get_taglist(){
        $args = wp_unslash( $_POST );
        $default = array(
            'orderby' => 'count',
            'order'   => 'DESC'
        );
        $args = array_merge($default, $args);
        $tags = get_tags($args);
        $list = [];
        foreach($tags as $val){
             $list[] = [
                'id'        => $val->term_id,
                'name'      => $val->name,
                'count'     => $val->count,
                'link'      => get_tag_link($val->term_id)
            ];
        }
        echo  json_encode(array(
            'code'  =>200, 
            'msg'   =>'成功', 
            'data'  => $list
        ));
        exit;
    }    
}


