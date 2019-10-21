<?php
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() {
 
    $parent_style = 'parent-style'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.
 
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}

function paymentImages() {
?>
    <script language="JavaScript">
        var myGates = document.getElementsByClassName('rcp_gateway_option_label');
        var htmlPaypal = "<input id=\"rcp_gateway_paypal\" name=\"rcp_gateway\" type=\"radio\" class=\"rcp_gateway_option_input\" value=\"paypal\" data-supports-recurring=\"yes\" data-supports-trial=\"yes\" checked=\"checked\"><img src=\"/wp-content/themes/aspen-child/paypal.jpeg\" height=\"120\" width=\"180\">";
        var htmlStripe =  "<input id=\"rcp_gateway_stripe\" name=\"rcp_gateway\" type=\"radio\" class=\"rcp_gateway_option_input\" value=\"stripe\" data-supports-recurring=\"yes\" data-supports-trial=\"yes\"> <img src=\"/wp-content/themes/aspen-child/stripe.jpeg\" height=\"60\" width=\"180\">"
        
        myGates[0].innerHTML = htmlPaypal;
        myGates[1].innerHTML = htmlStripe;
    </script>


<?php
}
add_action( 'wp_footer', 'paymentImages' );
    