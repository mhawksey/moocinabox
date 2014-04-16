<?php 
/**
 * Construct post type custom fields
 *
 * @since 0.1.1
 *
 * @package Reader_C
 */
 
$user_option_count = 0;
?>
<div id="<?php if (is_admin()) { echo 'eh-admin'; } else { echo 'eh-form'; }?>">
    <fieldset id="reader_c_options"> 
    <?php
    foreach($sub_options as $name => $option) : // foreach suboption render form part    
        $user_option_count++;
        $name = "reader_c_$name"; // prefix for reader hub fields
        $style = "eh_$name"; // style string
        $post_id = isset($post->ID) ? $post->ID : NULL;
        $value = "";
        $maphtml = "";
        // WP stores extra data as terms and post_meta
        // If we have a post get the value depending on type
        if (isset($post)){
            if (isset($option['save_as']) && $option['save_as'] == 'term'){
                $terms = wp_get_object_terms($post->ID, $name); 
                if (!empty($terms) && !is_wp_error($terms)){
                    // if terms count > 1 it has multiple values, extract slugs into array
                    if (count($terms)>1){
                        $value = array();
                        foreach ($terms as $single) {
                            $value[] = $single->slug; 
                        } 
                    } else {
                        // get single slug as string
                        $value = $terms[0]->slug;
                    }
                }
            } else {
                // current assumption is all post meta is singles
                $value = get_post_meta( $post->ID, $name, true );
            }
        } 
    // common label html ?>      
    <div class="eh_input <?php echo $style; ?>">
        <label for="<?php echo $name; ?>" class="eh_label"><?php Reader_C::format_label($option);?></label> 	
            <span class="eh_input_field">
    <?php if ($option['type'] == 'text') : ?>
        <input
            class="eh text <?php if (isset($option['lookup'])) { echo "lookup "; } ?>"
            type="text"
            name="<?php echo $name; ?>"
            id="<?php echo $name; ?>"
            value="<?php if (isset($_GET['url'])) { echo $_GET['url']; } else { echo htmlentities($value); } ?>"
        />
    <?php elseif ($option['type'] == 'html') : ?>
     	<?php wp_editor( $value, $name, array('media_buttons' => false,
											  'textarea_rows' => 10,)); ?>
	<?php elseif ($option['type'] == 'int') : ?>
            <input
                class="eh int"
                type="text"
                name="<?php echo $name; ?>"
                id="<?php echo $name; ?>"
                value="<?php echo htmlentities($value); ?>"
            />
    <?php elseif ($option['type'] == 'date') : ?>
            <input
                class="eh date"
                type="text"
                name="<?php echo $name; ?>"
                id="<?php echo $name; ?>"
                value="<?php echo $value; ?>"
                placeholder="dd/mm/yyyy"
            />
     <?php elseif ($option['type'] == 'project') : ?>
            <input
                class="eh newtag form-input-tip"
                type="text"
                autocomplete="off"
                name="<?php echo $name; ?>_field"
                id="<?php echo $name; ?>_field"
                value="<?php if ($value) echo get_the_title($value); ?>"
                placeholder="Start typing a <?php echo $option['type'];?>"
            /><?php if ($option['descr']) echo '<span class="description">'.$option['descr'].'</span>'; ?>
            <input
                type="hidden"
                name="<?php echo $name; ?>"
                id="<?php echo $name; ?>"
                value="<?php if ($value) echo $value; ?>"
            />
            <div id="menu-container" style="position:absolute; width: 256px;"></div>
            <?php 
                $latLong = array(0,0);
                $zoom = 13;
                if ($value){
                    $latLong[0] = get_post_meta($value, '_pronamic_google_maps_latitude', true );
                    $latLong[1] = get_post_meta($value, '_pronamic_google_maps_longitude', true );
                    $zoom = get_post_meta($value, '_pronamic_google_maps_zoom', true );
                } 
                $maphtml = sprintf('<div id="MapHolder" style="height:260px;%s"></div><script>var map = L.map("MapHolder").setView([%s], %s);
                L.tileLayer("http://{s}.tile.osm.org/{z}/{x}/{y}.png", {attribution: "&copy; <a href=\"http://osm.org/copyright\">OpenStreetMap</a> contributors"}).addTo(map);
                var marker = L.marker([%s]).addTo(map);</script>', ($value) ? '' : 'display:none', implode(",",$latLong), $zoom, implode(",",$latLong));     
            ?>
    <?php elseif ($option['type'] == 'select') : ?> 
            <select
                name="<?php echo $name; ?>"
                id="<?php echo $name; ?>">
                <option value=""></option>
                <?php foreach ($option['options'] as $optionValue => $text) { 
                        $itemValue = ($option['save_as'] != 'term') ? $optionValue : $text->slug; 
                        $itemName = ($option['save_as'] != 'term') ? $text : $text->name; 
                        $loc_country = wp_get_object_terms(get_post_meta( $post_id, 'reader_c_location_id', true ), 'reader_c_country');
                ?>
                <option
                    value="<?php echo $itemValue; ?>"
                     <?php if ((is_array($value) && in_array($itemValue, $value)) || ($value == $itemValue)) echo 'selected'; ?>/>
                        <?php echo $itemName; ?>     
                </option>
                <?php }
            ?>
            </select>
    <?php elseif ($option['type'] == 'select-posttype' && is_admin()) : ?> 
    		<?php
				$args = array(
						   'public'   => true,
						   '_builtin' => false
						);
				$post_types	= get_post_types( $args, 'objects', 'and' );
			?>
            <select
            	name="<?php echo $name; ?>"
                id="<?php echo $name; ?>">>
						<?php foreach ( $post_types as $post_type => $pt ) : ?>

							<?php if ( ! current_user_can( $pt->cap->publish_posts ) ) continue; ?>
								<?php if (esc_attr( $pt->name ) !== "hypothesis"): ?>
									<option value="<?php echo esc_attr( $pt->name ); ?>" <?php selected( get_post_type(), $post_type ); ?>><?php echo esc_html( $pt->labels->singular_name ); ?></option>
								<?php endif; ?>
						<?php endforeach; ?>

					</select>
    <?php elseif ($option['type'] == 'multi-select') : ?>
        <ul>
            <li>
                <select name="<?php echo $name; ?>[]">
                    <option value=""></option>
                    <?php foreach ($option['options'] as $optionValue => $text) { 
                            $itemValue = ($option['save_as'] != 'term') ? $optionValue : $text->slug; 
                            $itemName = ($option['save_as'] != 'term') ? $text : $text->name; 
                    ?>
                        <option
                            value="<?php echo($itemValue) ?>"
                            <?php if ($itemValue == $itemSelected) echo 'selected'; ?>
                        >
                            <?php echo $itemName; ?>
                        </option>
                    <?php } ?>
                </select>
            </li>
        </ul>
        <a class="add-another" href="#">add another</a>
        <script>
            jQuery('#reader_c_options .add-another').click(function() {
                var list = jQuery(this).prev();
                var item = jQuery(jQuery('li:first', list).clone()).appendTo(list);
                jQuery('select', item).val('');
                return false;
            });
        </script>
    <?php elseif ($option['type'] == 'multi-checkbox') : ?>
        <ul>
            <?php foreach ($option['options'] as $optionValue => $text) { ?>
            <?php 
            $itemValue = ($option['save_as'] != 'term') ? $optionValue : $text->slug; 
            $itemName = ($option['save_as'] != 'term') ? $text : $text->name; 
            ?>
            <li><label>
                <input
                    type="checkbox"
                    name="<?php echo $name; ?>[]"
                    id="<?php echo $name; ?>"
                    value="<?php echo($itemValue) ?>"
                    <?php if ((is_array($value) && in_array($itemValue, $value)) || ($value == $itemValue)) echo 'checked'; ?>></input><?php echo $itemName; ?>
            </label></li>
            <?php } ?>
        </ul>
    <?php elseif ($option['type'] == 'single-checkbox') : ?>
        <ul>
            <?php foreach ($option['options'] as $optionValue => $text) { ?>
            <?php 
            $itemValue = ($option['save_as'] != 'term') ? $optionValue : $text->slug; 
            $itemName = ($option['save_as'] != 'term') ? $text : $text->name; 
            ?>
            <li><label>
                <input
                    type="radio"
                    name="<?php echo $name; ?>"
                    id="<?php echo $name; ?>"
                    value="<?php echo($itemValue) ?>"
                    <?php if ((is_array($value) && in_array($itemValue, $value)) || ($value == $itemValue)) echo 'checked'; ?>></input><?php echo $itemName; ?>
            </label></li>
            <?php } ?>
        </ul>
    <?php elseif ($option['type'] == 'boolean') : ?>
        <input
            type="checkbox"
            name="<?php echo $name; ?>"
            id="<?php echo $name; ?>"
            <?php if ($value) echo 'checked'; ?>
        />
    <?php elseif ($option['type'] == 'pgm') : ?>
    	<?php Reader_C_hub::wpufe_gmaps(); ?>
    <?php else: ?>
        <p>unknown option type <?php $option['type']; ?> </p>
    <?php endif; ?>
        </span>
    </div>
    <?php if ($maphtml) echo $maphtml; ?>
    <?php endforeach; // end foreach suboption?>
    <?php  if (!$user_option_count) { ?>
        <p>There aren't any ReaderC options for <?php echo $this->plural; ?>.</p>
    <?php } ?>
    </fieldset>
</div>