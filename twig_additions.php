<?php
function add_twig_filter($filtername,$function){
    add_action('twig_apply_filters', function($twig) use ($filtername,$function){
        $twig->addFilter(new Twig_SimpleFilter($filtername,$function));
        return $twig;
    });
}
function add_twig_function($filtername,$function){
    add_action('twig_apply_filters', function($twig) use ($filtername,$function){
        $twig->addFunction(new Twig_SimpleFunction($filtername,$function));
        return $twig;
    });
}
function add_twig_filter_and_function($filtername,$function){
    add_twig_filter($filtername,$function);
    add_twig_function($filtername,$function);
}


add_action('twig_apply_filters', 'add_cmab_to_twig');
function add_cmab_to_twig($twig){
    // this is supposed to be added by default, best i can tell. it enables template_from_string()
    $twig->addExtension(new Twig_Extension_StringLoader());
    return $twig;
}


add_twig_filter_and_function('get_uri_host', 'get_uri_host');
add_twig_filter_and_function('array_if_not_already', 'ensure_array_no_falsy');
add_twig_filter_and_function('attr', 'htmlspecialchars');
add_twig_filter_and_function('checkContactPost', 'checkContactPost');
add_twig_filter_and_function('get_first_page_of_pagetype', 'Taxonomy::get_first_page_of_pagetype');
add_twig_filter_and_function('get_image', 'get_image');
add_twig_filter_and_function('get_products_for_filter', 'get_products_for_filter');
add_twig_filter_and_function('get_subcategory_filters', 'Taxonomy::get_subcategory_filters');
add_twig_filter_and_function('get_taxonomy_name', 'Taxonomy::get_taxonomy_name');
add_twig_filter_and_function('get_timber_post', 'get_timber_post');
add_twig_filter_and_function('get_timber_posts', 'get_timber_posts');
add_twig_filter_and_function('getRelatedPosts', 'getRelatedPosts');
add_twig_filter_and_function('isProcessLoggedIn', 'isProcessLoggedIn');
add_twig_filter_and_function('make_anchors', 'make_anchors');
add_twig_filter_and_function('picture', 'write_picture_tag');
add_twig_filter_and_function('pluck', 'ff_array_pluck');
add_twig_filter_and_function('print_pre', 'print_pre');
add_twig_filter_and_function('returnPostTitle', 'returnPostTitle');
add_twig_filter_and_function('masked_svg', 'masked_svg');
add_twig_filter_and_function('inject_rendition_string', 'inject_rendition_string');
add_twig_function('cdn_host', 'get_cdn_host');
add_twig_function('GET', function(){ return $_GET; });
add_twig_function('get_job_listings_by_type', 'Taxonomy::get_job_listings_by_type');
add_twig_function('handle_ingredients', 'handle_ingredients');
add_twig_function('handle_directions', 'handle_directions');
add_twig_function('star', function(){ return '&#9733;';});

//Returns a full list of job categories.  This list is used to filter and categorized WP and SR jobs
add_twig_function('get_job_categories', function(){
		$post_type = 'job';
		$taxonomy_name = 'job-type';
		$args = [
			'orderby'=>'name',
			'hide_empty'=>false,
			];
		//First, get all the taxonomy terms.
		$job_categories = get_terms( $taxonomy_name, $args);
		//Remove not-available ones.
		$job_categories = array_filter($job_categories, function($val){
		return $val->slug !== 'not-available';
		});
		//Remove featured ones.
		$job_categories = array_filter($job_categories, function($val){
		return $val->slug !== 'featured';
		});
		return $job_categories;
	});
	
add_twig_function('get_smart_recruiter_jobs', function(){ 
	//Get SR jobs from remote json file and decode it into an array
	$srjobdata = json_decode(file_get_contents("https://api.smartrecruiters.com/v1/companies/fosterfarms/postings"), true);	
	//Return array to srjobdata variable.  Twig uses this variable to display data 
	return ['srjobdata'=>$srjobdata['content']];
});	

add_twig_filter_and_function('admin_link', function($post){
    //print_pre(current_user_can('edit_post', $post->ID));
    if ( current_user_can('edit_post', $post->ID) ) {
        $link = admin_url() . 'post.php?action=edit&post=' . $post->ID;
        return sprintf("<span class='mobile-no' style='position:absolute;left:0;top:0;z-index:8;padding-left:10px;''>%s: <a href='%s'>Edit</a> <a href='/%s'>View Solo</a></span>", $post->post_title, $link, $post->slug);
    }
    else {
        return "";
    }
});

add_twig_function('current_user_can', function($can_what,$post_id=null){
    if(!$post_id){
        return false;
    }
    if(isset($post_id->ID)){
        $post_id = $post_id->ID;
    }
    return current_user_can($can_what, $post_id);
});

add_twig_filter_and_function('css_name', function($str){
    return strtolower(str_replace(' ', '-', $str));
});

add_twig_filter_and_function('px', function($str){
    if ( strlen($str) <= 0 ) return '';
    return (substr($str, -2) == 'px') ? $str : $str . 'px' ;
});

add_twig_filter_and_function('vw', function($str){
    if ( strlen($str) <= 0 ) return '';
    return (substr($str, -2) == 'vw') ? $str : $str . 'vw' ;
});

add_twig_filter_and_function('percent', function($str){
    if ( strlen($str) <= 0 ) return '';
    return (substr($str, -1) == '%') ? $str : $str . '%' ;
});

add_twig_filter_and_function('no_widows', function($str){
    return preg_replace('/(\S+ \S+)$/', '<nobr>$1</nobr>', $str);
});

add_twig_filter_and_function('rotate_recipe_title', function($arr, $target){
    $ids = ff_array_pluck($arr,'ID');
    $index = array_search($target,$ids);
    return ff_array_rotate($arr,$index - 1);
});

add_twig_function('newsletter', function(){
    //TODO: make this logic work. It was adding linbreaks. we need them absent. Since newsletter() is at the bottom of the page, this isn't dangerous.
    //    $has_filter = has_filter( 'the_content', 'siteorigin_panels_filter_content' );
    //
    //    if ($has_filter) {
    //        remove_filter('the_content', 'siteorigin_panels_filter_content');
    //    }
    //
    //    $content = get_timber_post('newsletter-form')->content;
    //    $content = apply_filters('the_content', $content);
    //
    //    if($has_filter){
    //        //add_filter( 'the_content', 'siteorigin_panels_filter_content' );
    //    }
    //    return $content;
    remove_filter('the_content', 'siteorigin_panels_filter_content');
    return get_timber_post('newsletter-form');
});



add_twig_function('ff', function(){
    global $ff;
    return $ff;
});

add_twig_function('latest_news', function(){
    $posts = get_timber_posts([
        'post_type'=>'news',
        'orderby'=>'date',
        'posts_per_page'=>3
    ]);
    return $posts;
});


add_twig_function('css_critical', function($dir){
    global $root_asset_path;
    $dir = ensure_array_no_falsy($dir);

    $dir = array_map(function($directory) use ($root_asset_path){
        $relative_path = '/css/'. $directory .'/index.critical.css';
        $path = get_home_path() . 'dist' . $relative_path;

        $content = file_exists($path) ? file_get_contents($path) : false;
        if($content){
            return "<style data-from=\"$directory\">$content</style>";
        } else {
            return '<link rel="stylesheet" type="text/css" href="'. $root_asset_path . $relative_path .'">';
        }
    }, $dir);
    return implode('',$dir);
});

add_twig_function('lazy_css', function($dir){
    global $root_asset_path;
    $assets = array('global');

    $dir = ensure_array_no_falsy($dir);

    $dir = array_map(function($directory) use (&$assets){
        array_push($assets, $directory);
    }, $dir);

    return json_encode($assets);
});

add_twig_function('css_link', function($dir){
    global $root_asset_path;
    $dir = ensure_array_no_falsy($dir);

    $dir = array_map(function($directory) use ($root_asset_path){
        return '<link rel="stylesheet" type="text/css" href="'. $root_asset_path .'/css/'. $directory .'/index.css">';
    }, $dir);
    return implode('',$dir);
});

add_twig_function('root_asset_path', function(){
    global $root_asset_path;
    return $root_asset_path;
});


add_twig_function('get_recipe_base', function(){
    $post = get_timber_post(['pagename'=>'recipes']);
    return $post;
});

add_twig_filter_and_function('unserialize', function($val){
    return unserialize($val);
});

add_twig_filter_and_function('truncate', function($string, $size) {
    if (strlen($string) < $size) {
        return $string;
    } else {
        return array_shift(str_split($string, $size)) . "...";
    }
});

add_twig_filter_and_function('nl2fauxul', function($val){
    $temp = preg_split('/[\r\n]+/', $val);
    $temp = array_map(function($row){
        return preg_replace('/^-\s*/','', $row);
    },$temp);
    $temp = array_filter($temp,function($row){
        //return true;
        return preg_match('/^\w/', $row);
    });
    $temp = array_map(function($row){
        return "<span>$row</span><br/>";
    },$temp);

    return implode('', $temp);

    //		$middle = preg_replace("/[\n\r]+/","</li><li>",$val);
    //		return "<ul><li>$middle</li></ul>";
});
add_twig_filter_and_function('nl2p', function($val){
    $temp = preg_split('/[\r\n]+/', $val);
    $temp = array_map(function($row){
        return "<p>$row</p>";
    },$temp);
    return implode('', $temp);
});

add_twig_filter_and_function('add_data_for_pagetype', function($post){
    //passing true so it won't redirect when we're just trying to get the extra data for a post.
    return add_data_for_pagetype($post, true);
});

add_twig_filter_and_function('recipe_background', function($recipe){
    //passing true so it won't redirect when we're just trying to get the extra data for a post.
    if($recipe){
        if(is_numeric($recipe)){
            $recipe = get_timber_post(+$recipe);
        }
        if($recipe && isset($recipe->recipe_image) && is_numeric($recipe->recipe_image)){
            $cdn_host = get_cdn_host(true);
            $recipe_image = get_image($recipe->recipe_image);
            $anchor_to = isset($recipe->recipe_image_anchor_to) ? $recipe->recipe_image_anchor_to : null;
            $anchor_to = $anchor_to?: 'center';
            if(isset($recipe_image->file)){
                return "background:url('$cdn_host/$recipe_image->file') $anchor_to/cover no-repeat;";
            }
        }
    }
    return '';
});

add_twig_filter_and_function('image_to_css_url', function($image){
    if($image && is_numeric($image)){
        $image = get_image(+$image);
    }
    if(!$image){
        return '';
    }else{
        $parts = pathinfo($image->file);
        return "url('".get_cdn_host(true)."/".$parts['filename']."-large-retina.".$parts['extension']."')";
    }
});

/**
 * Created for product flair, but usable anywhere you have a single masked image
 * @param $image
 * @param string $color
 * @param int $width
 * @param int $height
 * @return string
 */
function masked_svg($image, $color='white', $width=100, $height=100){
    if($image && is_numeric($image)){
        $image = get_image(+$image);
    }
    if(!$image){
        return '';
    }

    $path = get_cdn_host()."/".$image->file;
    $id = 'mask'.rand();

    return '<svg width="'.$width.'" height="'.$height.'" baseProfile="full" version="1.2" viewBox="0 0 '.$width.' '.$height.'">'
    .'  <defs>'
    .'   <mask id="'.$id.'" maskUnits="userSpaceOnUse" maskContentUnits="userSpaceOnUse" transform="scale(1)">'
    .'    <image width="'.$width.'" height="'.$height.'" xlink:href="'.$path.'" />'
    .'   </mask>'
    .'  </defs>'
    .'  <rect mask="url(#'.$id.')" width="'.$width.'" height="'.$height.'" style="fill:'.$color.'" />'
    .'</svg>';
}
function handle_directions($directions){
    $directions = preg_split('/\r\n|[\r\n]/', $directions);
    $directions = array_map(function($line){
        $matches = [];
        $line = trim($line);
        if(preg_match('/^(\d+\.)\s*?(.+$)/',$line,$matches)){
            return '<p><b>'.$matches[1].'</b>'.$matches[2].'</p>';
        }else{
            return '<p>'.$line.'</p>';
        }
    }, $directions);
    return implode('',$directions);
}
/**
 * Makes the ingredients list pretty by way of fancy text parsing.
 * @param $ingredients
 * @param array $products
 * @return array|mixed|string
 */
function handle_ingredients($ingredients, $products=[]){
    $i = 0;
    //identify products
    if($products && is_numeric($products)){
        $products = [$products];
    }
    if($products && count($products)){
        $products = array_map(function($id){
            $val = get_timber_post($id);
            if($val){
                add_data_for_pagetype($val,false);
            }
            return $val;
        },$products);

        //swap in product links in index order, so increment $i after you use
        $ingredients = preg_replace_callback('/\{(.+?)\}/',function($a) use (&$i, $products){
            $str = isset($products[$i]->my_permalink) ? '<a href="' .$products[$i]->my_permalink. '" data-product-title="'.$products[$i]->post_title.'" data-product-id="'.$products[$i]->ID.'">' .$a[1]. '</a>' : '<span class="tried">' . $a[1] . '</span>';
            $i++;
            return $str;
        },$ingredients);
    }
    // clean up any extra braces. just in case.
    $ingredients = preg_replace('/[\{\}]/','',$ingredients);
    $f = '½¼¾⅛⅑⅒⅓⅔⅕⅖⅗⅘⅙⅚⅛⅜⅝⅞';
    //Do not split on [\r\n]+ because it would consume empty lines, but we want to respect empty lines.
    $ingredients = preg_split('/\r\n|[\r\n]/', $ingredients);
    $ingredients = array_map(function($val) use ($f){
        $matches = [];
        $val = trim($val);
        if(!$val){
            $val = '<td colspan="2">&nbsp;</td>';
        }elseif(preg_match("/^.+?:\s*$/i",$val,$matches)){
            $val = '<th colspan="2">'.$val.'</th>';
        }elseif(
            preg_match("/^(.+?)\s*\|\s*(.+?)$/",$val,$matches)
            ?: preg_match("/^([\d$f\.\/\s]+(?:-\s*[\d$f\.]+\s+)?(?:(?:cup|can|boxe?|bag|jar|package|pkg|tsp|teaspoon|tbsp|tbl|tablespoon|g\b|kg|gallon|gal|lb|pound|oz|ounce|clove|head)s?)?)(.+)/i",$val,$matches)
        ){
            $matches[1] = str_replace('pound','lb',$matches[1]);
            $matches[1] = str_replace('tablespoon','tbsp',$matches[1]);
            $matches[1] = str_replace('teaspoon','tsp',$matches[1]);
            $matches[1] = str_replace('1/4','¼',$matches[1]);
            $matches[1] = str_replace('2/3','⅔',$matches[1]);
            $val = '<td class="measure">'.$matches[1].'</td><td>'.$matches[2].'</td>';
        }else{
            $val = '<td class="measure" style="color:rgba(0,0,0,.07)">&bull;</td><td colspan="1">'.$val.'</td>';
        }
        return "<tr>$val</tr>";
    },$ingredients);
    $ingredients = implode('',$ingredients);
    $ingredients = "<table>$ingredients</table>";

    return $ingredients;
}
