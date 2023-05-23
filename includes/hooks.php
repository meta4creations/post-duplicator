<?php
function add_custom_button_to_post_settings() {
		?>
		<div class="custom-button-container">
				<button class="custom-button">Custom Button</button>
		</div>
		<?php
}
add_action('edit_form_top', 'add_custom_button_to_post_settings');		