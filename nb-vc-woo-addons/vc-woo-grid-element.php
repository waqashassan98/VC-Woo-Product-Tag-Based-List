<?php

/*
Element Description: Woocommerce Tag Based Grid
*/

// Element Class
class vcInfoBox extends WPBakeryShortCode {

    // Element Init
    function __construct() {
        add_action( 'init', array( $this, 'vc_infobox_mapping' ) );
        add_shortcode( 'vc_infobox', array( $this, 'vc_infobox_html' ) );
    }

    // Element Mapping
    public function vc_infobox_mapping() {

        // Stop all if VC is not enabled
        if ( !defined( 'WPB_VC_VERSION' ) ) {
            return;
        }
        //Stop if WooCommerce is not enabled
        if ( !class_exists( 'woocommerce' ) ) { return; }

        //Get all WooCommerce tags
        $terms = get_terms( 'product_tag' );
        $term_array = array();
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
            foreach ( $terms as $term ) {
                $term_array[] = $term->name;
            }
        }
        // Map the block with vc_map()
        vc_map(
            array(
                'name' => __('Tags Based Woo-Product Grid', 'text-domain'),
                'base' => 'vc_infobox',
                'class' => 'wpc-text-class',
                'description' => __('Display WooCommerce Products based on tags', 'text-domain'),
                'category' => __('NextBridge', 'text-domain'),
                'icon' => plugin_dir_path( __FILE__ ) . 'assets/img/note.png',
                'params' => array(

                    array(
                        'type' => 'textfield',
                        'holder' => 'h3',
                        'class' => 'title-class',
                        'heading' => __( 'Number of Products to Show', 'text-domain' ),
                        'param_name' => 'number_of_products',
                        'admin_label' => false,
                        'weight' => 0,
                        'group' => 'Grid Info',
                    ),

                    array(
                        "type" => "dropdown_multi",
                        'holder' => 'div',
                        "heading" => __("Select Tags", 'text-domain'),
                        "param_name" => "woo_tags",
                        "value" => $term_array,
                        'admin_label' => false,
                        'group' => 'Grid Info',
                    ),
                  

                ),
            )
        );

    }

    // Element HTML
    public function vc_infobox_html( $atts, $content ) {

        // Params extraction
        extract(
            shortcode_atts(
                array(
                    'number_of_products'   => '4',
                    'woo_tags' => ''
                ),
                $atts
            )
        );

        ob_start();
        // Define Query Arguments
        $args = array( 
                    'post_type'      => 'product', 
                    'posts_per_page' => $number_of_products, 
                    'product_tag'    => $woo_tags 
                    );
        
        // Create the new query
        $loop = new WP_Query( $args );
        
        // Get products number
        $product_count = $loop->post_count;
        
        // If results
        if( $product_count > 0 ) :
          
            echo '<ul class="nb-vc-woo-product-container">';
            
                // Start the loop
                while ( $loop->have_posts() ) : $loop->the_post(); global $product;
                
                    global $post;
                    $regular_price = get_post_meta( $loop->post->ID, '_regular_price', true );
                    $regular_price = explode('.',$regular_price);

                    $member_price  = get_post_meta( $loop->post->ID, 'member_price', true );
                    $member_price  = explode('.',$member_price);
                    ?>

                     <li class="nb-vc-woo-product-box">
                        <div class="img-container">
                            <?php
                            if (has_post_thumbnail( $loop->post->ID )) 
                                echo  get_the_post_thumbnail($loop->post->ID, 'shop_catalog'); 
                            else 
                                echo '<img class="product-img" src="'.$woocommerce->plugin_url().'/assets/images/placeholder.png" alt="" width="'.$woocommerce->get_image_size('shop_catalog_image_width').'px" height="'.$woocommerce->get_image_size('shop_catalog_image_height').'px" />';

                            ?>
                        </div>
                        <div class="tag"><?php echo $woo_tags;?></div>
                        <div class="retail-price">
                            <span class="retail-pr-label">Retail Price</span>
                            <div class="price"><span class="currency">$</span><span class="pr"><?php echo $regular_price[0];?></span><span class="cents"><?php echo $regular_price[1];?></span></div>
                        </div>
                        <div class="member-price">
                            <span class="member-pr-label">Member Price</span>
                            <div class="price"><span class="currency">$</span><span class="pr"><?php echo $member_price[0];?></span><span class="cents"><?php echo $member_price[1];?></span></div>
                        </div>
                        <div class="product-title"><?php echo $post->post_title;?></div>
                    </li>
                    <?php
            
                endwhile;
            
            echo '</ul><!--/.products-->';
        
        else :
        
            _e('No product matching your criteria.');
        
        endif; // endif $product_count > 0
        
        return ob_get_clean();

    }

} // End Element Class

// Element Class Init
new vcInfoBox();