<?php
/**
 * A class to create a repeater control for message bars.
 * Repeater controls allow you to build repeatable blocks of fields.
 *
 * @author      Mahdi Yazdani
 * @package     GEO Top Bar
 * @since       1.0
 */
// Prevent direct file access
defined('ABSPATH') or exit;
if (class_exists('WP_Customize_Control') && !class_exists('MyPreview_GEO_Top_Bar_Customizer_Message_Bars_Repeater')):
    final class MyPreview_GEO_Top_Bar_Customizer_Message_Bars_Repeater extends WP_Customize_Control

    {
		public $type = 'repeater';
		public $repeater_label = '';
		public $repeater_add_new = '';

		/**
		 * The fields that each container row will contain.
		 * No Defaults added.
		 *
		 * @since 1.0
		 */
		public $fields = array();
		/**
		 * Repeater controller.
		 *
		 * @since 1.0
		 */
		public function __construct($manager, $id, $args = array() , $fields = array())

		{
			$this->fields = $fields;
			$this->repeater_label = $args['repeater_label'];
			$this->repeater_add_new = $args['repeater_add_new'];
			parent::__construct($manager, $id, $args);
		}
		/**
		 * Display set of fields with repeater markup.
		 *
		 * @since 1.0
		 */
		public function render_content()

		{
			$values = json_decode($this->value());
		?>
			<span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
			<?php if ($this->description): ?>
				<span class="description customize-control-description">
				<?php
					echo wp_kses_post($this->description); ?>
				</span>
			<?php endif; ?>
			<ul class="mypreview-geo-top-bar-repeater-field-control-wrap">
				<?php $this->get_repeater_fields(); ?>
			</ul>
			<input type="hidden" <?php esc_attr($this->link()); ?> class="mypreview-geo-top-bar-repeater-collector" value="<?php echo esc_attr($this->value()); ?>" />
			<button type="button" class="button mypreview-geo-top-bar-add-control-field"><?php echo esc_html($this->repeater_add_new); ?></button>
		<?php
		}
		/**
		 * Retrieve repeater(s) fields.
		 * Set of fields that will contain checkbox, text, textfield and switch field types. 
		 * User will then be able to add “Message”, and each message will contain defined field types.
		 *
		 * @since 1.0
		 */
		private function get_repeater_fields()

		{
			$fields = $this->fields;
			$values = json_decode($this->value());
			if (is_array($values)):
				foreach($values as $value):
				?>
					<li class="mypreview-geo-top-bar-repeater-field-control">
						<h3 class="mypreview-geo-top-bar-repeater-field-title"><?php echo esc_html($this->repeater_label); ?></h3>
						<div class="mypreview-geo-top-bar-repeater-fields">
							<?php
							foreach($fields as $key => $field):
								$class = isset($field['class']) ? $field['class'] : '';
							?>
							<div class="mypreview-geo-top-bar-fields mypreview-geo-top-bar-type-<?php echo esc_attr($field['type']) . ' ' . $class; ?>">
								<?php
									$label = isset($field['label']) ? $field['label'] : '';
									$description = isset($field['description']) ? $field['description'] : '';
									if ($field['type'] != 'checkbox'): 
								?>
										<span class="customize-control-title">
											<?php echo esc_html($label); ?>
										</span>
										<span class="description customize-control-description">
											<?php echo esc_html($description); ?>
										</span>
								<?php
									endif;
									$new_value = isset($value->$key) ? $value->$key : '';
									$default = isset($field['default']) ? $field['default'] : '';
									switch ($field['type']):
									// Text field type
									case 'text':
										echo '<input data-default="' . esc_attr($default) . '" data-name="' . esc_attr($key) . '" type="text" value="' . esc_attr($new_value) . '"/>';
										break;
									// Textarea field type
									case 'textarea':
										echo '<textarea data-default="' . esc_attr($default) . '"  data-name="' . esc_attr($key) . '">' . esc_textarea($new_value) . '</textarea>';
										break;
									// Checkbox field type
									case 'checkbox':
										echo '<label>';
										echo '<input data-default="' . esc_attr($default) . '" value="'.$new_value.'" data-name="' . esc_attr($key) . '" type="checkbox" ' . checked($new_value, 'yes', false) . '/>';
										echo esc_html( $label );
										echo '<span class="description customize-control-description">'.esc_html( $description ).'</span>';
										echo '</label>';
										break;
									// Select field type
									case 'select':
										$options = $field['options'];
										echo '<select  data-default="' . esc_attr($default) . '"  data-name="' . esc_attr($key) . '">';
				                            foreach ( $options as $option => $val ):
				                                printf('<option value="%s" %s>%s</option>', esc_attr($option), selected($new_value, $option, false), esc_html($val));
				                            endforeach;
				                  		echo '</select>';
										break;
									// Switch field type
									case 'switch':
										$switch = $field['switch'];
										$switch_class = ($new_value == 'on') ? 'switch-on' : '';
										echo '<div class="onoffswitch ' . $switch_class . '">';
										echo '<div class="onoffswitch-inner">';
										echo '<div class="onoffswitch-active">';
										echo '<div class="onoffswitch-switch">' . esc_html($switch['on']) . '</div>';
										echo '</div>';
										echo '<div class="onoffswitch-inactive">';
										echo '<div class="onoffswitch-switch">' . esc_html($switch['off']) . '</div>';
										echo '</div>';
										echo '</div>';
										echo '</div>';
										echo '<input data-default="' . esc_attr($default) . '" type="hidden" value="' . esc_attr($new_value) . '" data-name="' . esc_attr($key) . '"/>';
										break;

									default:
										break;
									endswitch;
							?>
							</div>
							<?php endforeach; ?>
							<div class="clearfix mypreview-geo-top-bar-repeater-footer">
								<div class="alignright">
									<a class="mypreview-geo-top-bar-repeater-field-remove" href="#remove">
									<?php _e('Delete', 'mypreview-geo-top-bar') ?>
									</a> |
									<a class="mypreview-geo-top-bar-repeater-field-close" href="#close">
										<?php _e('Close', 'mypreview-geo-top-bar') ?>
									</a>
								</div>
							</div>
						</div>
					</li>
		<?php
				endforeach;
			endif;
		}
	}
endif;