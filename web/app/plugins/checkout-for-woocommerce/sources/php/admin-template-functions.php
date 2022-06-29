<?php

function cfw_admin_page_section( string $title, string $description, string $content ) {
	?>
	<div>
		<div class="md:grid md:grid-cols-3 md:gap-6">
			<div class="md:col-span-1">
				<div class="px-4 sm:px-0">
					<h3 class="text-lg font-medium leading-6 text-gray-900">
						<?php echo esc_html( $title ); ?>
					</h3>
					<p class="mt-1 text-sm text-gray-600">
						<?php echo $description; ?>
					</p>
				</div>
			</div>
			<div class="mt-5 md:mt-0 md:col-span-2">
				<div class="shadow sm:rounded-md sm:overflow-hidden">
					<div class="px-4 py-5 bg-white space-y-6 sm:p-6">
						<?php echo $content; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}