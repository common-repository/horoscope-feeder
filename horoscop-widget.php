<?php
if(!class_exists("HoroscopWidget")) {
	class HoroscopWidget extends WP_Widget
	{
		function HoroscopWidget()
		{
			$widget_ops = array('classname' => 'HoroscopWidget', 'description' => 'Display and custom horoscope feed from one defined URL' );
			$this->WP_Widget('HoroscopWidget', 'Horoscope Feeder Widget', $widget_ops);
		}

		function form($instance)
		{
			$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
			$title = $instance['title'];
			?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">Title: 
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" />
				</label>
			</p>
			<?php
		}

		function update($new_instance, $old_instance)
		{
			$instance = $old_instance;
			$instance['title'] = $new_instance['title'];
			return $instance;
		}

		function widget($args, $instance)
		{
			global $_horoscope;
			extract($args, EXTR_SKIP);
			echo $before_widget;
			$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
			if (!empty($title))
				echo $before_title . $title . $after_title;;
			$_horoscope->rss_parser();
			echo $after_widget;
		}
	}
	add_action('widgets_init', create_function('', 'return register_widget("HoroscopWidget");') );
}
?>