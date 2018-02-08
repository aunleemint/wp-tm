<?php
/**
 * GeoDirectory Best of widget
 *
 * @since 1.3.9
 *
 * @package GeoDirectory
 */

/**
 * GeoDirectory Best of widget widget class.
 *
 * @since 1.3.9
 */
class tamzang_place_widget extends WP_Widget
{
    /**
     * Register the best of widget with WordPress.
     *
     * @since 1.3.9
     * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
     */
    public function __construct()
    {
        $widget_ops = array('classname' => 'tamzang_place_widget', 'description' => __('GD > Tamzang Place Widget', 'geodirectory'));
        parent::__construct(
            'tamzang_place_widget', // Base ID
            __('GD > Tamzang Place Widget', 'geodirectory'), // Name
            $widget_ops// Args
        );
    }

    /**
     * Front-end display content for best of widget.
     *
     * @since 1.3.9
     * @since 1.5.1 Added filter to view all link.
     * @since 1.5.1 Declare function public.
     *
     * @param array $args Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance)
    {
        extract($args);
        /**
         * Filter the best of widget tab layout.
         *
         * @since 1.3.9
         *
         * @param string $instance ['tab_layout'] Best of widget tab layout name.
         */
        $tab_layout = empty($instance['tab_layout']) ? 'bestof-tabs-on-top' : apply_filters('bestof_widget_tab_layout', $instance['tab_layout']);
        echo '<div class="tamzang-place-widget-layout">';
        echo $before_widget;
        $loc_terms = geodir_get_current_location_terms();
        if (!empty($loc_terms)) {
            $cur_location = ' : ' . geodir_ucwords(str_replace('-', ' ', end($loc_terms)));
        } else {
            $cur_location = '';
        }

        /**
         * Filter the current location name.
         *
         * @since 1.3.9
         *
         * @param string $cur_location Current location name.
         */
        $cur_location = apply_filters('bestof_widget_cur_location', $cur_location);

        /**
         * Filter the widget title.
         *
         * @since 1.3.9
         *
         * @param string $instance ['title'] The widget title.
         */
        $title = empty($instance['title']) ? wp_sprintf(__('Best of %s', 'geodirectory'), get_bloginfo('name') . $cur_location) : apply_filters('bestof_widget_title', __($instance['title'], 'geodirectory'));

        /**
         * Filter the post type.
         *
         * @since 1.3.9
         *
         * @param string $instance ['post_type'] The post type.
         */
        $post_type = empty($instance['post_type']) ? 'gd_place' : apply_filters('bestof_widget_post_type', $instance['post_type']);

        /**
         * Filter the excerpt type.
         *
         * @since 1.5.4
         *
         * @param string $instance ['excerpt_type'] The excerpt type.
         */
        $excerpt_type = empty($instance['excerpt_type']) ? 'show-desc' : apply_filters('bestof_widget_excerpt_type', $instance['excerpt_type']);


        /**
         * Filter the listing limit.
         *
         * @since 1.3.9
         *
         * @param int $instance ['post_limit'] No. of posts to display.
         */
        $post_limit = empty($instance['post_limit']) ? '5' : apply_filters('bestof_widget_post_limit', $instance['post_limit']);

        /**
         * Filter the category limit.
         *
         * @since 1.3.9
         *
         * @param int $instance ['categ_limit'] No. of categories to display.
         */
        $use_viewing_post_type = !empty($instance['use_viewing_post_type']) ? true : false;

        /**
         * Filter the use of location filter.
         *
         * @since 1.3.9
         *
         * @param int|bool $instance ['add_location_filter'] Filter listings using current location.
         */
        $add_location_filter = empty($instance['add_location_filter']) ? '1' : apply_filters('bestof_widget_location_filter', $instance['add_location_filter']);

        // set post type to current viewing post type
        if ($use_viewing_post_type) {
            $current_post_type = geodir_get_current_posttype();
            if ($current_post_type != '' && $current_post_type != $post_type) {
                $post_type = $current_post_type;
            }
        }

        if (isset($instance['character_count'])) {
            /**
             * Filter the widget's excerpt character count.
             *
             * @since 1.3.9
             *
             * @param int $instance ['character_count'] Excerpt character count.
             */
            $character_count = apply_filters('bestof_widget_list_character_count', $instance['character_count']);
        } else {
            $character_count = '';
        }

        $category_taxonomy = geodir_get_taxonomies($post_type);

        $term_args = array(
            'hide_empty' => false,
            'parent' => 0
        );

        $term_args = apply_filters('bestof_widget_term_args', $term_args);

        if (is_tax()) {
            $taxonomy = get_query_var('taxonomy');
            $cur_term = get_query_var('term');
            $term_data = get_term_by('name', $cur_term, $taxonomy);
            $term_args['parent'] = $term_data->term_id;
        }

        $terms = get_terms($category_taxonomy[0], $term_args);

        $query_args = array(
            'posts_per_page' => $post_limit,
            'is_geodir_loop' => true,
            'post_type' => $post_type,
            'gd_location' => $add_location_filter ? true : false,
            'order_by' => 'high_review'
        );
        if ($character_count >= 0) {
            $query_args['excerpt_length'] = $character_count;
        }

        $layout = array();
        if ($tab_layout == 'bestof-tabs-as-dropdown') {
            $layout[] = $tab_layout;
        } else {
            $layout[] = 'bestof-tabs-as-dropdown';
            $layout[] = $tab_layout;
        }


        //echo $before_title . __($title,'geodirectory') . $after_title;
        echo $before_title . $after_title;

        $tamzang_categories = explode(",", $instance['tamzang_categories']);
        //print_r($tamzang_categories);
        //term navigation - start
        echo '<div class="geodir-place-list-in clearfix">';
        echo '<div class="geodir-cat-list clearfix">';
        echo '<ul class="geodir-popular-cat-list tamzang-place">';
        $final_html = '';
        $nav_html = '';
        $term_icon = geodir_get_term_icon();
        $cat_count = 0;
        if (!empty($terms)) {
          foreach ($tamzang_categories as $tamzang_cat) {
            $tc = array_search($tamzang_cat, array_column($terms, 'name'));
            if($tc){
              $cat = $terms[$tc];
              $term_icon_url = !empty($term_icon) && isset($term_icon[$cat->term_id]) ? $term_icon[$cat->term_id] : '';
              $nav_html .= '<li class="geodir-popular-cat-list"><a data-termid="' . $cat->term_id . '" href="' . get_term_link($cat, $cat->taxonomy) . '">';
              $nav_html .= '<img alt="' . $cat->name . ' icon" style="height:20px;vertical-align:middle;" src="' . $term_icon_url . '"/> ';
              $nav_html .= '<span class="cat-link">'.$cat->name . '</span> <span class="geodir_term_class geodir_link_span geodir_category_class_' . $post_type . '_' . $cat->term_id . '"></span></a></li>';
            }
          }
          $final_html .= $nav_html;
        }
        echo $final_html;
        echo '</ul>';
        echo '</div>';
        echo '</div>';
        //term navigation - end

        //first term listings by default - start
        $first_term = '';
        if ($terms) {
            foreach ($tamzang_categories as $tamzang_cat) {
              $tc = array_search($tamzang_cat, array_column($terms, 'name'));
              if($tc){
                $first_term = $terms[$tc];
                break;
              }
            }
            $tax_query = array(
                'taxonomy' => $category_taxonomy[0],
                'field' => 'id',
                'terms' => $first_term->term_id
            );
            $query_args['tax_query'] = array($tax_query);
        }

        ?>
        <input type="hidden" id="bestof_widget_post_type" name="bestof_widget_post_type"
               value="<?php echo $post_type; ?>">
        <input type="hidden" id="bestof_widget_excerpt_type" name="bestof_widget_excerpt_type"
               value="<?php echo $excerpt_type; ?>">
        <input type="hidden" id="bestof_widget_post_limit" name="bestof_widget_post_limit"
               value="<?php echo $post_limit; ?>">
        <input type="hidden" id="bestof_widget_taxonomy" name="bestof_widget_taxonomy"
               value="<?php echo $category_taxonomy[0]; ?>">
        <input type="hidden" id="bestof_widget_location_filter" name="bestof_widget_location_filter"
               value="<?php if ($add_location_filter) {
                   echo 1;
               } else {
                   echo 0;
               } ?>">
        <input type="hidden" id="bestof_widget_char_count" name="bestof_widget_char_count"
               value="<?php echo $character_count; ?>">
        <div class="geo-bestof-contentwrap geodir-tabs-content" style="position: relative; z-index: 0;">
            <p id="geodir-bestof-loading" class="geodir-bestof-loading"><i class="fa fa-cog fa-spin"></i></p>
            <?php
            echo '<div id="geodir-bestof-places">';
            if ($terms) {
                $view_all_link = add_query_arg(array('sort_by' => 'rating_count_desc'), get_term_link($first_term, $first_term->taxonomy));
                /**
                 * Filter the page link to view all lisitngs.
                 *
                 * @since 1.5.1
                 *
                 * @param array $view_all_link View all listings page link.
                 * @param array $post_type The Post type.
                 * @param array $first_term The category term object.
                 */
                $view_all_link = apply_filters('geodir_bestof_widget_view_all_link', $view_all_link, $post_type, $first_term);

                echo '<h3 class="bestof-cat-title">' . $first_term->name . '<a href="' . esc_url($view_all_link) . '">' . __("View all", 'geodirectory') . '</a></h3>';
            }
            if ($excerpt_type == 'show-reviews') {
                add_filter('get_the_excerpt', 'tamzang_best_of_show_review_in_excerpt');
            }
            tamzang_place_places_by_term($query_args);
            if ($excerpt_type == 'show-reviews') {
                remove_filter('get_the_excerpt', 'tamzang_best_of_show_review_in_excerpt');
            }
            echo "</div>";
            ?>
        </div>
        <?php //first term listings by default - end
        ?>
        <?php echo $after_widget;
        echo "</div>";
    }

    /**
     * Sanitize best of widget form values as they are saved.
     *
     * @since 1.3.9
     * @since 1.5.1 Declare function public.
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['post_type'] = strip_tags($new_instance['post_type']);
        $instance['post_limit'] = strip_tags($new_instance['post_limit']);
        $instance['character_count'] = $new_instance['character_count'];
        $instance['tab_layout'] = 'bestof-tabs-on-top';
        $instance['excerpt_type'] = $new_instance['excerpt_type'];
        $instance['tamzang_categories'] = strip_tags($new_instance['tamzang_categories']);
        if (isset($new_instance['add_location_filter']) && $new_instance['add_location_filter'] != '')
            $instance['add_location_filter'] = strip_tags($new_instance['add_location_filter']);
        else
            $instance['add_location_filter'] = '0';
        $instance['use_viewing_post_type'] = isset($new_instance['use_viewing_post_type']) && $new_instance['use_viewing_post_type'] ? 1 : 0;
        return $instance;
    }

    /**
     * Back-end best of widget settings form.
     *
     * @since 1.3.9
     * @since 1.5.1 Declare function public.
     *
     * @param array $instance Previously saved values from database.
     */
    public function form($instance)
    {
        $instance = wp_parse_args((array)$instance,
            array(
                'title' => '',
                'post_type' => '',
                'post_limit' => '5',
                'character_count' => '20',
                'add_location_filter' => '1',
                'tab_layout' => 'bestof-tabs-on-top',
                'excerpt_type' => 'show-desc',
                'use_viewing_post_type' => '',
                'tamzang_categories' => ''
            )
        );
        $title = strip_tags($instance['title']);
        $post_type = strip_tags($instance['post_type']);
        $post_limit = strip_tags($instance['post_limit']);
        $character_count = strip_tags($instance['character_count']);
        $tab_layout = 'bestof-tabs-on-top';
        $excerpt_type = strip_tags($instance['excerpt_type']);
        $add_location_filter = strip_tags($instance['add_location_filter']);
        $use_viewing_post_type = isset($instance['use_viewing_post_type']) && $instance['use_viewing_post_type'] ? true : false;
        $tamzang_categories = strip_tags($instance['tamzang_categories']);

        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'geodirectory');?>

                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                       name="<?php echo $this->get_field_name('title'); ?>" type="text"
                       value="<?php echo esc_attr($title); ?>"/>
            </label>
        </p>

        <p>
            <label
                for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e('Post Type:', 'geodirectory');?>

                <?php $postypes = geodir_get_posttypes();
                /**
                 * Filter the post types to display in widget.
                 *
                 * @since 1.3.9
                 *
                 * @param array $postypes Post types array.
                 */
                $postypes = apply_filters('geodir_post_type_list_in_p_widget', $postypes); ?>

                <select class="widefat" id="<?php echo $this->get_field_id('post_type'); ?>"
                        name="<?php echo $this->get_field_name('post_type'); ?>"
                        onchange="geodir_change_category_list(this)">

                    <?php foreach ($postypes as $postypes_obj) { ?>

                        <option <?php if ($post_type == $postypes_obj) {
                            echo 'selected="selected"';
                        } ?> value="<?php echo $postypes_obj; ?>"><?php $extvalue = explode('_', $postypes_obj);
                            echo geodir_utf8_ucfirst($extvalue[1]); ?></option>

                    <?php } ?>

                </select>
            </label>
        </p>

        <p>

            <label
                for="<?php echo $this->get_field_id('post_limit'); ?>"><?php _e('Number of posts:', 'geodirectory');?>

                <input class="widefat" id="<?php echo $this->get_field_id('post_limit'); ?>"
                       name="<?php echo $this->get_field_name('post_limit'); ?>" type="text"
                       value="<?php echo esc_attr($post_limit); ?>"/>
            </label>
        </p>

        <p>

            <label
                for="<?php echo $this->get_field_id('tamzang_categories'); ?>"><?php _e('Categories:', 'geodirectory');?>

                <input class="widefat" id="<?php echo $this->get_field_id('tamzang_categories'); ?>"
                       name="<?php echo $this->get_field_name('tamzang_categories'); ?>" type="text"
                       value="<?php echo esc_attr($tamzang_categories); ?>"/>
            </label>
        </p>

        <p>
            <label
                for="<?php echo $this->get_field_id('character_count'); ?>"><?php _e('Post Content excerpt character count :', 'geodirectory');?>
                <input class="widefat" id="<?php echo $this->get_field_id('character_count'); ?>"
                       name="<?php echo $this->get_field_name('character_count'); ?>" type="text"
                       value="<?php echo esc_attr($character_count); ?>"/>
            </label>
        </p>

        <p>
            <label
                for="<?php echo $this->get_field_id('excerpt_type'); ?>"><?php _e('Excerpt Type:', 'geodirectory');?>

                <select class="widefat" id="<?php echo $this->get_field_id('excerpt_type'); ?>"
                        name="<?php echo $this->get_field_name('excerpt_type'); ?>">

                    <option <?php if ($excerpt_type == 'show-desc') {
                        echo 'selected="selected"';
                    } ?> value="show-desc"><?php _e('Show Description', 'geodirectory'); ?></option>
                    <option <?php if ($excerpt_type == 'show-reviews') {
                        echo 'selected="selected"';
                    } ?> value="show-reviews"><?php _e('Show Reviews if Available', 'geodirectory'); ?></option>
                </select>
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('add_location_filter'); ?>">
                <?php _e('Enable Location Filter:', 'geodirectory');?>
                <input type="checkbox" id="<?php echo $this->get_field_id('add_location_filter'); ?>"
                       name="<?php echo $this->get_field_name('add_location_filter'); ?>" <?php if ($add_location_filter) echo 'checked="checked"';?>
                       value="1"/>
            </label>
        </p>

        <p>
            <label
                for="<?php echo $this->get_field_id('use_viewing_post_type'); ?>"><?php _e('Use current viewing post type:', 'geodirectory'); ?>
                <input type="checkbox" id="<?php echo $this->get_field_id('use_viewing_post_type'); ?>"
                       name="<?php echo $this->get_field_name('use_viewing_post_type'); ?>" <?php if ($use_viewing_post_type) {
                    echo 'checked="checked"';
                } ?>  value="1"/>
            </label>
        </p>
    <?php
    }
} // class geodir_bestof_widget

register_widget('tamzang_place_widget');

/**
 * Display the best of widget listings using the given query args.
 *
 * @since 1.3.9
 *
 * @global object $post The current post object.
 * @global array $map_jason Map data in json format.
 * @global array $map_canvas_arr Map canvas array.
 * @global string $gridview_columns_widget The girdview style of the listings for widget.
 * @global object $gd_session GeoDirectory Session object.
 *
 * @param array $query_args The query array.
 */
function tamzang_place_places_by_term($query_args)
{
    global $gd_session;

    /**
     * This action called before querying widget listings.
     *
     * @since 1.0.0
     */
    do_action('geodir_bestof_get_widget_listings_before');

    $widget_listings = geodir_get_widget_listings($query_args);

    /**
     * This action called after querying widget listings.
     *
     * @since 1.0.0
     */
    do_action('geodir_bestof_get_widget_listings_after');

    $character_count = isset($query_args['excerpt_length']) ? $query_args['excerpt_length'] : '';

    if (!isset($character_count)) {
        /** This filter is documented in geodirectory-widgets/geodirectory_bestof_widget.php */
        $character_count = $character_count == '' ? 50 : apply_filters('bestof_widget_character_count', $character_count);
    }

    /** This filter is documented in geodirectory-functions/general_functions.php */
    $template = apply_filters("geodir_template_part-tamzang-widget-listing-listview", geodir_locate_template('tamzang-widget-listing-listview'));

    global $post, $map_jason, $map_canvas_arr, $gridview_columns_widget, $geodir_is_widget_listing;
    $current_post = $post;
    $current_map_jason = $map_jason;
    $current_map_canvas_arr = $map_canvas_arr;
    $current_grid_view = $gridview_columns_widget;
    $gridview_columns_widget = null;

    $gd_listing_view_set = $gd_session->get('gd_listing_view') ? true : false;
    $gd_listing_view_old = $gd_listing_view_set ? $gd_session->get('gd_listing_view') : '';

    $gd_session->set('gd_listing_view', '1');
    $geodir_is_widget_listing = true;

    /**
     * Includes the template for the listing listview.
     *
     * @since 1.3.9
     */
    include($template);

    $geodir_is_widget_listing = false;

    $GLOBALS['post'] = $current_post;
    if (!empty($current_post)) {
        setup_postdata($current_post);
    }
    if ($gd_listing_view_set) { // Set back previous value
        $gd_session->set('gd_listing_view', $gd_listing_view_old);
    } else {
        $gd_session->un_set('gd_listing_view');
    }
    $map_jason = $current_map_jason;
    $map_canvas_arr = $current_map_canvas_arr;
    $gridview_columns_widget = $current_grid_view;
}

//Ajax functions
add_action('wp_ajax_tamzang_place', 'tamzang_place_callback');
add_action('wp_ajax_nopriv_tamzang_place', 'tamzang_place_callback');

/**
 * Get the best of widget content using ajax.
 *
 * @since 1.3.9
 * @since 1.5.1 Added filter to view all link.
 *
 * @return string Html content.
 */
function tamzang_place_callback()
{
    check_ajax_referer('tamzang-place-nonce', 'tamzang_place_nonce');
    //set variables
    $post_type = strip_tags(esc_sql($_POST['post_type']));
    $post_limit = strip_tags(esc_sql($_POST['post_limit']));
    $character_count = strip_tags(esc_sql($_POST['char_count']));
    $taxonomy = strip_tags(esc_sql($_POST['taxonomy']));
    $add_location_filter = strip_tags(esc_sql($_POST['add_location_filter']));
    $term_id = strip_tags(esc_sql($_POST['term_id']));
    $excerpt_type = strip_tags(esc_sql($_POST['excerpt_type']));

    $query_args = array(
        'posts_per_page' => $post_limit,
        'is_geodir_loop' => true,
        'post_type' => $post_type,
        'gd_location' => $add_location_filter ? true : false,
        'order_by' => 'high_review'
    );

    if ($character_count >= 0) {
        $query_args['excerpt_length'] = $character_count;
    }

    $tax_query = array(
        'taxonomy' => $taxonomy,
        'field' => 'id',
        'terms' => $term_id
    );

    $query_args['tax_query'] = array($tax_query);
    if ($term_id && $taxonomy) {
        $term = get_term_by('id', $term_id, $taxonomy);
        $view_all_link = add_query_arg(array('sort_by' => 'rating_count_desc'), get_term_link($term));
        /** This filter is documented in geodirectory-widgets/geodirectory_bestof_widget.php */
        $view_all_link = apply_filters('geodir_bestof_widget_view_all_link', $view_all_link, $post_type, $term);

        echo '<h3 class="bestof-cat-title">' . $term->name . '<a href="' . esc_url($view_all_link) . '">' . __("View all", 'geodirectory') . '</a></h3>';
    }
    if ($excerpt_type == 'show-reviews') {
        add_filter('get_the_excerpt', 'tamzang_best_of_show_review_in_excerpt');
    }
    tamzang_place_places_by_term($query_args);
    if ($excerpt_type == 'show-reviews') {
        remove_filter('get_the_excerpt', 'tamzang_best_of_show_review_in_excerpt');
    }
    gd_die();
}

//Javascript
add_action('wp_footer', 'tamzang_place_js');

/**
 * Adds the javascript in the footer for best of widget.
 *
 * @since 1.3.9
 */
function tamzang_place_js()
{
    $ajax_nonce = wp_create_nonce("tamzang-place-nonce");
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function () {
            jQuery('.geodir-popular-cat-list a').on("click change", function (e) {
                var widgetBox = jQuery(this).closest('.tamzang_place_widget');
                var loading = jQuery(widgetBox).find("#geodir-bestof-loading");
                var container = jQuery(widgetBox).find('#geodir-bestof-places');

                jQuery(document).ajaxStart(function () {
                    //container.hide(); // Not working when more then one widget on page
                    //loading.show();
                }).ajaxStop(function () {
                    loading.hide();
                    container.fadeIn('slow');
                });

                e.preventDefault();

                //var activeTab = jQuery(this).closest('dl').find('dd.geodir-tab-active');
                //activeTab.removeClass('geodir-tab-active');
                //jQuery(this).parent().addClass('geodir-tab-active');

                var term_id = 0;
                if (e.type === "change") {
                    term_id = jQuery(this).val();
                } else if (e.type === "click") {
                    term_id = jQuery(this).attr('data-termid');
                }

                var post_type = jQuery(widgetBox).find('#bestof_widget_post_type').val();
                var excerpt_type = jQuery(widgetBox).find('#bestof_widget_excerpt_type').val();
                var post_limit = jQuery(widgetBox).find('#bestof_widget_post_limit').val();
                var taxonomy = jQuery(widgetBox).find('#bestof_widget_taxonomy').val();
                var char_count = jQuery(widgetBox).find('#bestof_widget_char_count').val();
                var add_location_filter = jQuery(widgetBox).find('#bestof_widget_location_filter').val();

                var data = {
                    'action': 'tamzang_place',
                    'tamzang_place_nonce': '<?php echo $ajax_nonce; ?>',
                    'post_type': post_type,
                    'excerpt_type': excerpt_type,
                    'post_limit': post_limit,
                    'taxonomy': taxonomy,
                    'geodir_ajax': true,
                    'term_id': term_id,
                    'char_count': char_count,
                    'add_location_filter': add_location_filter
                };

                container.hide();
                loading.show();

                jQuery.post(geodir_var.geodir_ajax_url, data, function (response) {
                    container.html(response);
                    jQuery(widgetBox).find('.geodir_category_list_view li .geodir-post-img .geodir_thumbnail img').css('display', 'block');

                    // start lazy load if it's turned on
                    if(geodir_var.geodir_lazy_load==1){
                        geodir_init_lazy_load();
                    }

                });
            })
        });
        jQuery(document).ready(function () {
            if (jQuery(window).width() < 660) {
                if (jQuery('.bestof-widget-tab-layout').hasClass('bestof-tabs-on-left')) {
                    jQuery('.bestof-widget-tab-layout').removeClass('bestof-tabs-on-left').addClass('bestof-tabs-as-dropdown');
                } else if (jQuery('.bestof-widget-tab-layout').hasClass('bestof-tabs-on-top')) {
                    jQuery('.bestof-widget-tab-layout').removeClass('bestof-tabs-on-top').addClass('bestof-tabs-as-dropdown');
                }
            }
        });
    </script>
<?php
}

function tamzang_best_of_show_review_in_excerpt($excerpt)
{
    global $wpdb, $post;
    $review_table = GEODIR_REVIEW_TABLE;
    $request = "SELECT comment_ID FROM $review_table WHERE post_id = $post->ID ORDER BY post_date DESC, id DESC LIMIT 1";
    $comments = $wpdb->get_results($request);

    if ($comments) {
        foreach ($comments as $comment) {
            // Set the extra comment info needed.
            $comment_extra = $wpdb->get_row("SELECT * FROM $wpdb->comments WHERE comment_ID =$comment->comment_ID");
            $comment_content = $comment_extra->comment_content;
            $excerpt = strip_tags($comment_content);
        }
    }
    return $excerpt;
}
