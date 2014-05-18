<?php
namespace Themosis\Page;

defined('DS') or die('No direct script access.');

class PageRenderer
{
	/**
	 * Page data object
	*/
	private $data;

    /**
     * The PageRenderer constructor.
     *
     * @param PageData $data The page datas.
     */
	public function __construct(PageData $data)
	{
		$this->data = $data;
	}

	/**
	 * Handle page main display.
     *
     * @return void
	 */
	public function page()
	{
		?>

		<div class="wrap">
			<div class="icon32" id="icon-options-general"></div>
			<h2><?php echo($this->data->get('title')); ?></h2>

			<?php
			// Handle settings notices
			settings_errors();

			// Check for tabs
			if (isset($_GET['tab'])) {
			    $activeTab = $_GET['tab'];
			} else {
				$activeTab = $this->data->get('sections');
				$activeTab = $activeTab[0]['name'];
			}

			// Display as tabs if there is
			// more than 1 section
			if (count($this->data->get('sections')) > 1) {
				?>
				<h2 class="nav-tab-wrapper">
					<?php
						foreach ($this->data->get('sections') as $section) {
							$class = ($activeTab === $section['name']) ? 'nav-tab-active': '';
							?>
								<a href="?page=<?php echo($this->data->get('slug')); ?>&tab=<?php echo($section['name']); ?>" class="nav-tab <?php echo($class); ?>"><?php echo($section['title']); ?></a>
							<?php
						}
					?>
				</h2>
				<?php
			}
			?>

			<form action="options.php" method="post">

				<?php

					submit_button();

					foreach ($this->data->get('sections') as $section) {
						if ($section['name'] === $activeTab) {
							settings_fields($section['name']);
							do_settings_sections($section['name']);
						}
					}

					submit_button();
				?>

			</form>
		</div>

		<?php
	}

	/**
	 * Handle settings display.
	 *
	 * @param array $args The option field properties.
     * @return void
	 */
	public function settings($args)
	{
		extract($args);
		// Check for a default value for everything excepts checkboxes.
		// If none exists, give the $default standard value from the settings
		// defined by the developer.
		$setting = get_option($args['section']);

		// Check if there is a value set for the setting
		// If not, assign the 'default' one if exists
		if (!isset($setting[$name]) && $type != 'checkbox') {
			$setting[$name] = $default;
		} elseif (!isset($setting[$name]) && $type === 'checkbox') {
			$setting[$name] = 'off';
		}

		// Display each settings and switch by their input type
		// "type" is defined by the developer
		switch ($type) {
			case 'text':

				static::displayText($args['section'].'['.$name.']', $name, $setting[$name]);
				break;

			case 'textarea':

				static::displayTextarea($args['section'].'['.$name.']', $name, $setting[$name]);
				break;

			case 'checkbox':

				static::displayCheckbox($args['section'].'['.$name.']', $name, $setting[$name]);
				break;

			case 'checkboxes':

				static::displayCheckboxes($args['section'].'['.$name.']', $name, $options, $setting[$name]);
				break;

			case 'radio':

				static::displayRadio($args['section'].'['.$name.']', $name, $options, $setting[$name]);
				break;

			case 'select':

				static::displaySelect($args['section'].'['.$name.']', $name, $options, $setting[$name], $multiple);
				break;

			case 'media':

				static::displayMedia($args['section'].'['.$name.']', $name, $setting[$name]);
				break;

			case 'infinite':

				static::displayInfinite($args['section'].'['.$name.']', $fields, $setting[$name]);
				break;

			default:
				?>
					<input class="regular-text" type="text" name="<?php echo($this->data->get('slug').'['.$name.']'); ?>" value="<?php echo($setting[$name]); ?>" id="<?php echo($name); ?>" />
				<?php
				break;
		} // END SWITCH

		// Display the description
		if ($info) {
			echo('<div>'.$info.'</div>');
		}

	} // END SETTINGS METHOD

    /**
     * Display labels.
     *
     * @param string $name The label field name. Used in the 'for' attribute.
     * @param string $title The display text.
     * @return void
     */
	private static function displayLabel($name, $title = '')
	{
		?>
			<label for="<?php echo($name); ?>"><?php echo(ucfirst($title)); ?> :</label>
		<?php
	}

    /**
     * Display text input.
     *
     * @param string $name The text input name.
     * @param string $id The text input 'id' attribute.
     * @param string $value The text input saved value.
     * @return void
     */
	private static function displayText($name, $id, $value = '')
	{
		?>
			<input type="text" name="<?php echo($name); ?>" data-type="text" class="large-text" id="<?php echo($id); ?>" value="<?php if (isset($value)) echo(esc_attr($value)); ?>" />
		<?php
	}

    /**
     * Display textarea input.
     *
     * @param string $name The textarea tag name.
     * @param string $id The textarea 'id' attribute.
     * @param string $value The textarea saved value.
     * @return void
     */
	private static function displayTextarea($name, $id, $value = '')
	{
		?>
			<textarea class="large-text themosis-regular-text" name="<?php echo $name; ?>" data-type="textarea" id="<?php echo($id); ?>" rows="10"><?php if (isset($value)) echo(esc_attr($value)); ?></textarea>
		<?php
	}

    /**
     * Display one checkbox
     *
     * @param string $name The checkbox input name.
     * @param string $id The checkbox 'id' attribute.
     * @param string $value The checkbox saved value.
     * @return void
     */
	private static function displayCheckbox($name, $id, $value = 'off')
	{
		?>

			<input type="checkbox" name="<?php echo $name; ?>" data-type="checkbox" id="<?php echo $id; ?>" <?php checked('on', $value); ?>>

		<?php
	}

    /**
     * Display multiple checkboxes.
     *
     * @param string $name The checkbox input name.
     * @param string $id The checkbox 'id' attribute.
     * @param array $options The checkbox options.
     * @param array $value The checkbox saved values.
     * @return void
     */
	private static function displayCheckboxes($name, $id, array $options, array $value = array())
	{

		$i = 0;

		foreach ($options as $option) :

			$label = ucfirst($option);
			$option = sanitize_key($option);

		?>

			<label>
				<input type="checkbox" name="<?php echo $name; ?>[]" data-type="checkboxes" id="<?php echo $id.$i; ?>" value="<?php echo $option; ?>" <?php if (isset($value) && is_array($value) && in_array($option, $value)) echo "checked='checked'"; ?> />
				<?php echo $label; ?>
			</label><br />

		<?php
			$i++;

		endforeach;

	}

    /**
     * Display radio buttons.
     *
     * @todo Why the 'id' parameter is not used?
     *
     * @param string $name The radio input name.
     * @param string $id The radio input 'id' attribute.
     * @param array $options The radio input options.
     * @param array $value The radio input saved value.
     * @return void
     */
	private static function displayRadio($name, $id, array $options, array $value = array())
	{

		$i = 0;

		foreach ($options as $option) :

			$label = ucfirst($option);
			$option = sanitize_key($option);

		?>
			<label>
				<input type="radio" name="<?php echo $name; ?>[]" id="<?php echo $name . $i; ?>" value="<?php echo $option; ?>" <?php if (isset($value) && is_array($value) && in_array($option, $value)) echo "checked='checked'"; ?>>
				<?php echo $label; ?>
			</label><br />

		<?php

			$i++;

		endforeach;

	}

    /**
     * Display select input.
     *
     * @todo Why 'id' parameter not used?
     *
     * @param string $name The select input name.
     * @param string $id The select input 'id' attribute.
     * @param array $options The select input option tags.
     * @param string|array $value A single value or an array if multiple selection.
     * @param bool $multiple False by default. True to allow multiple selection.
     * @return void
     */
    private static function displaySelect($name, $id, $options, $value = null, $multiple = false)
    {
        ?>

        <select name="<?php echo($name); if ($multiple) { echo('[]'); } ?>" id="<?php echo($name); ?>-id" <?php if ($multiple) { echo('multiple="multiple"'); } ?>>

			<?php

				foreach ($options as $key => $option) :

					/**
					 * If $key is numeric, apply the $key for the <option> value attribute.
					 * Else if $key is an array, apply the subarray keys as value attribute.
					 * WARNING : the subarray MUST be an associative array.
					 * This structure is made in order to give flexibility to the developers so
					 * they can define their own values for the <select> tag.
					*/
                    // If $key is a string, we define a <optgroup> tag.
					if (is_string($key)):

                    ?>

						<optgroup label="<?php echo(ucfirst($key)); ?>">

                        <?php

                            // Use this array in order to define the selected <option> tag
                            $mergedOptions = array();

                            foreach($options as $groupName => $groupValues):

                                foreach($groupValues as $k => $val):

                                    if (is_string($k)):

                                        // Merge the options in one array for associative array
                                        $mergedOptions[$k] = $val;

                                    else:

                                        // Merge the options in one array for indexed array
                                        array_push($mergedOptions, $val);

                                    endif;

                                endforeach;

                            endforeach;

                            // Display the <option> tags
                            foreach($option as $subKey => $subValue):

                                // If the $subKey is a string, then we use an associative array
                                // with custom values as keys.
                                if(is_string($subKey)):

                                    // Value to add at the value attribute of the <option> tag
                                    $subKey = trim($subKey);

                                    if (is_array($value)):
                                    ?>
                                        <option value="<?php echo($subKey); ?>" <?php if (in_array($subKey, $value)) { echo('selected="selected"'); } ?>><?php echo(ucfirst($subValue)); ?></option>
                                    <?php

                                    else:

                                    ?>
                                        <option value="<?php echo($subKey); ?>" <?php if (!empty($value) && $value === $subKey) { echo('selected="selected"'); } ?>><?php echo(ucfirst($subValue)); ?></option>
                                    <?php

                                    endif;

                                else:

                                    // Real value to add to the value attribute of the <option> tag
                                    $tagValue = array_search($subValue, $mergedOptions);

                                    if (is_array($value)):

                                        // Return true or false - Help display the selected <option> tag
                                        $selected = (in_array($tagValue, $value) && !empty($value)) ? true : false;
                                    ?>
                                        <option value="<?php echo($tagValue); ?>" <?php if ($selected) { echo('selected="selected"'); } ?>><?php echo(ucfirst($subValue)); ?></option>
                                    <?php

                                    else:

                                ?>
                                        <option value="<?php echo($tagValue); ?>" <?php if (!empty($value) && $mergedOptions[$value] === $subValue){ echo('selected="selected"'); } ?>><?php echo(ucfirst($subValue)); ?></option>
                                <?php
                                    endif;

                                endif;

                            endforeach;

                        ?>

						</optgroup>

				<?php
				    // No <optgroup> tag
					else :

					    /**
						 * We can pass either an array with key/value pair in order to define our own
						 * <option> value attribute or we use the given array of strings.
					    */
					    if (is_array($option)) :

					        foreach ($option as $subKey => $subValue) :

					            if (is_array($value)) :
					            ?>
					                <option value="<?php echo($subKey); ?>" <?php if (in_array($subKey, $value)) { echo('selected="selected"'); } ?>><?php echo(ucfirst($subValue)); ?></option>
					            <?php

					            else:

                                ?>
									<option value="<?php echo($subKey); ?>" <?php if ($value == $subKey) { echo('selected="selected"'); } ?>><?php echo(ucfirst($subValue)); ?></option>
                            <?php
							    endif;

							endforeach;

                        elseif (is_string($option)) :

                            if (is_array($value)):
                        ?>
                                <option value="<?php echo($key); ?>" <?php if (in_array($key, $value)) { echo('selected="selected"'); } ?>><?php echo(ucfirst($option)); ?></option>
                        <?php

                            else:

                        ?>
								<option value="<?php echo($key); ?>" <?php if ((int)$value === $key){ echo('selected="selected"'); } ?>><?php echo(ucfirst($option)); ?></option>
                        <?php
                            endif;

                        endif;

					endif;

				endforeach;
			?>

		</select>

    <?php

    }

    /**
     * Display media input.
     *
     * @param string $name The media field name.
     * @param string $id The media field 'id' attribute.
     * @param string $value The media field saved value.
     * @return void
     */
	private static function displayMedia($name, $id, $value = '')
	{
		?>
			<table class="themosis-media-table">
				<tbody>
					<tr>
						<td class="themosis-media-input"><input type="text" name="<?php echo($name); ?>" data-type="text" class="large-text" id="<?php echo($id); ?>" value="<?php if (isset($value)) echo(esc_attr($value)); ?>" /></td>
						<td><button type="button" class="button-primary themosis-media-button"><?php _e('Add', THEMOSIS_TEXTDOMAIN); ?></button></td>
						<td><button type="button" class="button themosis-media-clear"><?php _e('Clear', THEMOSIS_TEXTDOMAIN); ?></button></td>
					</tr>
				</tbody>
			</table>
		<?php
	}

    /**
     * Display infinite field.
     *
     * @param string $name The infinite field name.
     * @param array $fields The infinite fields to repeat.
     * @param array $value The infinite field saved values.
     * @return void
     */
	private static function displayInfinite($name, array $fields, array $value = array())
	{
		?>
			<div class="themosis-infinite-container">
				<table class="themosis-infinite" id="themosis-infinite-sortable">
					<tbody>
					<?php
					if (isset($value) && !empty($value)) {

						$rowNum = count($value);

					} else {
						$rowNum = 1;
					}

					// RENDER THE ROWS
					for ($i = 1; $i <= $rowNum ; $i++) {

					?>
						<!-- ROW -->
						<tr class="themosis-infinite-row">
							<td class="themosis-infinite-order">
								<span><?php echo($i); ?></span>
							</td>
							<td class="themosis-infinite-inner">
								<table>
									<tbody>
										<?php
										foreach ($fields as $index => $field) :

											$nameAttr = $name.'[row'.$i.'][fields]['.$field['name'].']';

										?>
										<tr>
											<td class="themosis-infinite-label">
												<?php
													if (isset($field['title']) && !empty($field['title']) && is_string($field['title'])) {
														static::displayLabel($nameAttr, $field['title']);
													} else {
														$labelTitle = ucfirst($field['name']);
														static::displayLabel($nameAttr, $labelTitle);
													}
												?>
											</td>
											<td class="themosis-infinite-input" data-type="<?php echo $field['type']; ?>">
												<?php
													// Method name has changed compare
													// to the MetaboxRenderer class
													$signature = ucfirst($field['type']);
													$signature = 'display'.$signature;

													/*
													* Set the appropriate value depending on the field type
													*/
													switch($field['type']){
														case 'text':
														case 'textarea':
														case 'media':

															if (isset($value) && !empty($value) && isset($value['row'.$i]['fields'][$field['name']])) {
																$inputValue = $value['row'.$i]['fields'][$field['name']];
															} else {
																$inputValue = '';
															}

															static::$signature($nameAttr, $nameAttr, $inputValue);

															break;

														case 'checkbox':

															$val = (isset($value) && isset($value['row'.$i]['fields'][$field['name']])) ? $value['row'.$i]['fields'][$field['name']] : 'off';

															static::$signature($nameAttr, $nameAttr, $val);

															break;

														case 'checkboxes':

															if (isset($value) && !empty($value)) {
																if (isset($value['row'.$i]['fields'][$field['name']])) {
																	$val = $value['row'.$i]['fields'][$field['name']];
																} else {
																	$val = array();
																}
															} else {
																$val = array();
															}

															static::$signature($nameAttr, $nameAttr, $field['options'], $val);

															break;

														default :
															break;
													} // End switch inputValue

												?>
											</td>
										</tr>
										<?php
										endforeach; // End inputs loop
										?>
									</tbody>
								</table>
							</td>
							<td class="themosis-infinite-options">
								<span class="themosis-infinite-add"></span>
								<span class="themosis-infinite-remove"></span>
							</td>
						</tr>
						<!-- END ROW -->

					<?php

					} // END FOR LOOP - ROWS

					?>
					</tbody>
				</table>
				<div class="themosis-infinite-add-field-container">
					<button type="button" class="button-primary">Add Field</button>
				</div>
			</div>

		<?php

	} // End displayInfinite

}

?>