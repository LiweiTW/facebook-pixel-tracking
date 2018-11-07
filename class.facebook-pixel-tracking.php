<?php
class FacebookPixelTracking {

    const tracking_code = '!function(e,t,n,c,o,a,f){e.fbq||(o=e.fbq=function(){o.callMethod?o.callMethod.apply(o,arguments):o.queue.push(arguments)},e._fbq||(e._fbq=o),o.push=o,o.loaded=!0,o.version="2.0",o.queue=[],(a=t.createElement(n)).async=!0,a.src="https://connect.facebook.net/en_US/fbevents.js",(f=t.getElementsByTagName(n)[0]).parentNode.insertBefore(a,f))}(window,document,"script"),';
    const noscript_tracking_code = '<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=340637196493231&ev=PageView&noscript=1" /></noscript>';
    static $instance = false;

//    Constructor.  Initializes WordPress hooks
    private function __construct() {
        add_action( 'admin_init', array( __CLASS__, 'facebook_pixel_tracking_setting_initialization' ) );
        add_action('wp_head', array( __CLASS__, 'facebook_pixel_tracking_code') );
        add_filter( 'the_content', array( __CLASS__, 'facebook_pixel_content_view') );
    }

    public static function init() {
        if ( ! self::$instance ) {
            self::$instance = new FacebookPixelTracking;
        }
        return self::$instance;
    }

    private static function get_pixel_id() {
        return get_option( 'facebook-pixel-tracking-pixel-id', '' );
    }

    private static function is_track_content_view() {
        return get_option( 'facebook-pixel-tracking-content-view', false  );
    }


    function facebook_pixel_tracking_setting_initialization(){
        add_settings_section(
            'facebook-pixel-tracking-setting',
            'Facebook Pixel Tracking Setting',
            'facebook_pixel_tracking_setting_label',
            'reading'
        );
        add_settings_field(
            'facebook-pixel-tracking-pixel-id',
            'Pixel Id',
            array( __CLASS__, 'facebook_pixel_tracking_setting_pixel_id' ),
            'reading',
            'facebook-pixel-tracking-setting'
        );
        add_settings_field(
            'facebook-pixel-tracking-content-view',
            'ContentView',
            array( __CLASS__, 'facebook_pixel_tracking_setting_events' ),
            'reading',
            'facebook-pixel-tracking-setting',
            array(
                "facebook-pixel-tracking-content-view"
            )
        );

        register_setting( 'reading', 'facebook-pixel-tracking-pixel-id' );
        register_setting( 'reading', 'facebook-pixel-tracking-content-view' );
    }

    function facebook_pixel_tracking_setting_label() {
        echo '<p>Please input your Facebook pixel id :</p>';
    }

    function facebook_pixel_tracking_setting_pixel_id() {
        echo '<input type="text" id="facebook-pixel-tracking-pixel-id" name="facebook-pixel-tracking-pixel-id" value="' . self::get_pixel_id() . '">';
    }

    function facebook_pixel_tracking_setting_events($arg) {
        echo '<input name="' . $arg[0] .'" id="' . $arg[0] . '" type="checkbox" value="1"' . checked( 1, self::is_track_content_view(), false ) . ' />';
    }

    function facebook_pixel_tracking_code() {
        $pixel_id = self::get_pixel_id();
        if ($pixel_id !== "") {
            echo '<script>' . self::tracking_code . 'fbq("init","' . $pixel_id . '"),fbq("track","PageView");' . '</script>' . self::noscript_tracking_code;
        }
    }

    function facebook_pixel_content_view($content) {
        if ( ( is_page() || is_single() ) && self::is_track_content_view()) {
            $content .= '<script>fbq("track", "ViewContent", {title:"'
                . self::get_tracking_title() . '",author:"' . self::get_tracking_author()
                . '", categories:"' . self::get_tracking_cates() . '"});</script>';
        }
        return $content;
    }

    function get_tracking_title(){
        return get_the_title();
    }

    function get_tracking_author(){
        $authorId = get_post_field( 'post_author', get_the_ID());
        $authorName = get_the_author_meta('user_nicename', $authorId );

        return $authorId . " | " . $authorName;
    }
    function get_tracking_cates(){
        $categories = get_the_category();
        $categoriesString = "";
        if ( ! empty( $categories ) ) {
            foreach( $categories as $category ) {
                $categoriesString .= esc_html( $category->name )." | ";
            }
        }
        return $categoriesString;
    }

}

?>