<?php
if(!class_exists("horoscop_fields")) {
	class horoscop_fields {
		public function create_field($args){
			global $h_settings;
			extract( $args );
			if($type=="text") {
				switch($group) {
					case "normal": {
						$val = $h_settings[$id];
						if(!isset($val)) {
							$val = $value;
						}
						$description = (!empty($desc))?" <span class='description'>$desc</span>":"";
						echo "
						<div class='$group-wrapper'>
							<input type='text' id='$id' name='h_settings[$id]' value='$val' class='$class' style='$style' />
							$description
						</div>
						";
						break;
					}
					case "picker": {
						$val = empty($h_settings[$id])?"#FFFFFF":$h_settings[$id];
						if(!isset($val)) {
							$val = $value;
						}
						$description = (!empty($desc))?" <span class='description'>$desc</span>":"";
						echo "
						<div class='$group-wrapper'>
							<input type='text' id='$id' name='h_settings[$id]' value='$val' class='$class popup-colorpicker' style='$style' />
							<div id='".$id."picker' class='color-picker'></div>
							$description
						</div>
						";					
						break;
					}
				}
			}
			if($type=="hidden") {
				switch($group) {
					case "normal": {
						$val = $h_settings[$id];
						if(!isset($val)) {
							$val = $value;
						}
						$description = " <span class='description'>Not configurable or depend of other settings</span>";
						echo "
						<div class='$group-wrapper'>
							<input type='hidden' id='$id' name='h_settings[$id]' value='$val' class='$class' style='$style' />
							$description
						</div>
						";
						break;
					}
				}
			}
			if($type=="checkbox") {
				switch($group) {
					case "normal": {
						if(!isset($h_settings[$id])) {
							$val = $value;
						} else {
							$val = $h_settings[$id];
						}
						$description = (!empty($desc))?" <span class='description'>$desc</span>":"";
						echo "
						<div class='$group-wrapper'>
							<select id='$id' style='$style' class='$class' name='h_settings[$id]'>
						";
						foreach($values as $sel_name => $sel_value) {
							$selected = (($sel_name==$val)?TRUE:FALSE);
							echo "
								<option ".($selected?"selected='selected'":"")." value='$sel_name'>$sel_value</option>
							";
						}
						echo "
							</select>
							$description
						</div>
						";
						break;
					}
				}
			}
		}
	}
}
?>