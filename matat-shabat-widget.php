<?php
    
    /*
     * Plugin Name: Shabat time widget
     * Description: Shabbat entry and exit time
     * Version: 1.5
     * Author: Amit Matat
     * Author URI: https://textme.co.il
     * Author Email: amit@matat.co.il
     * Text Domain: shabat-time-widget
     */
    
    class matat_shabat_time extends WP_Widget
    {
        
        
        /** constructor -- name this the same as the class above */
        //    function matat_shabat_time()
        //    {
        //        parent::WP_Widget(false, $name = __('Shabbat times','matat-shabat-widget'));
        //    }
        
        public function __construct()
        {
            // Load plugin text domain
            add_action('init', array($this, 'widget_textdomain'));
            add_action('wp_enqueue_scripts', array($this, 'register_widget_styles'));
            
            
            parent::__construct(
                                'shabat-time-widget',
                                __('Shabbat times', 'shabat-time-widget'),
                                array(
                                      'classname' => 'shabat-time-widget',
                                      'description' => __('Shabbat entry and exit time', 'shabat-time-widget')
                                      )
                                );
            
        }
        
        /** @see WP_Widget::widget -- do not rename this */
        function widget($args, $instance)
        {
            
            //Call the css & js
            // wp_enqueue_style('widget-style', plugins_url('wp-widget/wp-widget-template.css'));
            //   wp_enqueue_script('matat-shabat-script', plugins_url('js/scripts.js'));
            
            
            extract($args);
            $title = apply_filters('widget_title', $instance['title']);
            $message = $instance['message'];
            
            $tel_aviv = $this->build_query('http://www.hebcal.com/shabbat/?cfg=json&geonameid=293397');
            
            
            $jerusalem = $this->build_query('http://www.hebcal.com/shabbat/?cfg=json&geonameid=281184');
            
            
            $berr = $this->build_query('http://www.hebcal.com/shabbat/?cfg=json&geonameid=295530');
            
            
            $haifa = $this->build_query('http://www.hebcal.com/shabbat/?cfg=json&geonameid=294801');
            
            
            ?>
<?php echo $before_widget; ?>
<?php if ($title)
    echo $before_title . $title . $after_title; ?>
<div class="shabat_wrapper">
<div class="parash">
<?php esc_html_e('Weekly Torah Portion: ', 'shabat-time-widget');
    echo $tel_aviv['parsha']; ?>

</div>
<div class="shabat_city">
<div class="city_name">
</div>
<div class="shabat_enter shabat_bold"><?php esc_html_e('Entrance:', 'shabat-time-widget'); ?></div>
<div class="shabat_exit shabat_bold"><?php esc_html_e('End:', 'shabat-time-widget'); ?></div>
</div>
<div class="shabat_city">
<div class="city_name">
<?php esc_html_e('Tel aviv', 'shabat-time-widget'); ?>
</div>
<div class="shabat_enter"><?php echo $tel_aviv['enter']; ?></div>
<div class="shabat_exit"><?php echo $tel_aviv['exit']; ?></div>
</div>
<div class="shabat_city">
<div class="city_name">
<?php esc_html_e('Jerusalem', 'shabat-time-widget'); ?>
</div>
<div class="shabat_enter"><?php echo $jerusalem['enter']; ?></div>
<div class="shabat_exit"><?php echo $jerusalem['exit']; ?></div>
</div>
<div class="shabat_city">
<div class="city_name">
<?php esc_html_e('Haifa', 'shabat-time-widget'); ?>
</div>
<div class="shabat_enter"><?php echo $haifa['enter']; ?></div>
<div class="shabat_exit"><?php echo $haifa['exit']; ?></div>
</div>
<div class="shabat_city">
<div class="city_name">
<?php esc_html_e('Beer sheva', 'shabat-time-widget'); ?>
</div>
<div class="shabat_enter"><?php echo $berr['enter']; ?></div>
<div class="shabat_exit"><?php echo $berr['exit']; ?></div>
</div>
</div>
<?php echo $after_widget; ?>
<?php
    
    
    }
    
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['message'] = strip_tags($new_instance['message']);
        return $instance;
    }
    
    
    /** @see WP_Widget::form -- do not rename this */
    function form($instance)
    {
        
        $title = esc_attr($instance['title']);
        $message = esc_attr($instance['message']);
        ?>
<p>
<label for="<?php echo $this->get_field_id('title'); ?>"><?php esc_html_e('Title:'); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>"/>
</p>
<?php
    }
    
    /**
     * Loads the Widget's text domain for localization and translation.
     *
     * @since    1.2
     */
    public function widget_textdomain()
    {
        
        load_plugin_textdomain('shabat-time-widget');
        
        
    } // end widget_textdomain
    
    /**
     * Registers and enqueues widget-specific styles.
     *
     * @since    1.2
     */
    public function register_widget_styles()
    {
        
        $url = plugins_url(__FILE__);
        //  wp_enqueue_script('matat-shabat-scripts', plugins_url('js/scripts.js', __FILE__), array('jquery'), '1.0', true);
        wp_enqueue_style('matat-shabat-style', plugins_url('css/shabat_style.css', __FILE__), '1.0');
        
        
    }
    
    
    private function build_query($url)
    {
        
        $str = file_get_contents($url);
        $build_query = json_decode($str, true); // decode the JSON into an associative array
        
        $enter_time='';
        $exit_time='';
        $parsha='';
        foreach ($build_query['items'] as $item) {
            if ($item['category'] == 'candles') {
                $enter_time = explode('+', $item['date']);
                $enter_time = strtotime($enter_time[0]);
            }
            if ($item['category'] == 'havdalah') {
                $exit_time = explode('+', $item['date']);
                $exit_time = strtotime($exit_time[0]);
            }
            if ($item['category'] == 'parashat') {
                $parsha = $item['hebrew'];
            }
        }
        
        
        return array(
                     'enter' => date('H:i', $enter_time),
                     'exit' => date('H:i', $exit_time),
                     'parsha' => $parsha
                     
                     );
        
    }
    
    
    } // end class example_widget
    add_action('widgets_init', create_function('', 'return register_widget("matat_shabat_time");'));
    
    
    ?>
