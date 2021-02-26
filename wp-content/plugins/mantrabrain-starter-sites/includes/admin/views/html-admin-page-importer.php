<?php
/**
 * Admin View: Page - Importer
 *
 * @package Mantrabrain_Starter_Sites
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="wrap demo-importer">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Starter Sites', 'mantrabrain-starter-sites' ); ?></h1>
	<?php if ( apply_filters( 'mantrabrain_starter_sites_upcoming_demos', false ) ) : ?>
		<a href="<?php echo esc_url( 'https://mantrabrain.com/upcoming-demos' ); ?>" class="page-title-action" target="_blank"><?php esc_html_e( 'Upcoming Demos', 'mantrabrain-starter-sites' ); ?></a>
	<?php endif; ?>
	<hr class="wp-header-end">
	<div class="error hide-if-js">
		<p><?php _e( 'The starter sites screen requires JavaScript.', 'mantrabrain-starter-sites' ); ?></p>
	</div>
	<h2 class="screen-reader-text hide-if-no-js"><?php _e( 'Filter demos list', 'mantrabrain-starter-sites' ); ?></h2>
	<div class="wp-filter hide-if-no-js">
		<div class="filter-section">
			<div class="filter-count">
				<span class="count theme-count demo-count"></span>
			</div>
			<?php if ( isset( $this->demo_packages['categories']) ) : ?>
				<ul class="filter-links categories">
					<?php foreach ( $this->demo_packages['categories']as $slug => $label ) : ?>
						<li><a href="#" data-sort="<?php echo esc_attr( $slug ); ?>" class="category-tab"><?php echo esc_html( $label ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<div class="filter-section right">
			<?php if ( isset( $this->demo_packages['pagebuilders']) ) : ?>
				<ul class="filter-links pagebuilders">
					<?php foreach ( $this->demo_packages['pagebuilders'] as $slug => $label ) : ?>
						<?php if ( 'default' !== $slug ) : ?>
							<li><a href="#" data-type="<?php echo esc_attr( $slug ); ?>" class="pagebuilder-tab"><?php echo esc_html( $label ); ?></a></li>
						<?php else: ?>
							<li><a href="#" data-type="<?php echo esc_attr( $slug ); ?>" class="pagebuilder-tab tips" data-tip="<?php esc_attr_e( 'Without Page Builder', 'mantrabrain-starter-sites' ); ?>"><?php echo esc_html( $label ); ?></a></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<form class="search-form"></form>
		</div>
	</div>
	<h2 class="screen-reader-text hide-if-no-js"><?php _e( 'Themes list', 'mantrabrain-starter-sites' ); ?></h2>
	<div class="theme-browser content-filterable"></div>
	<div class="theme-install-overlay wp-full-overlay expanded"></div>

	<p class="no-themes"><?php _e( 'No demos found. Try a different search.', 'mantrabrain-starter-sites' ); ?></p>
	<span class="spinner"></span>
</div>

<script id="tmpl-demo" type="text/template">
	<# if ( data.screenshot_url ) { #>
		<div class="theme-screenshot">
			<img src="{{ data.screenshot_url }}" alt="" />
		</div>
	<# } else { #>
		<div class="theme-screenshot blank"></div>
	<# } #>

	<# if ( data.isPro ) { #>
		<span class="premium-demo-banner"><?php _e( 'Premium', 'mantrabrain-starter-sites' ); ?></span>
	<# } #>
    <div class="theme-author">
		<?php
		/* translators: %s: Demo author name */
		printf( __( 'By %s', 'mantrabrain-starter-sites' ), '{{{ data.author }}}' );
		?>
	</div>

	<div class="theme-id-container">
		<# if ( data.active ) { #>
			<h2 class="theme-name" id="{{ data.id }}-name">
				<?php
				/* translators: %s: Demo name */
				printf( __( '<span>Imported:</span> %s', 'mantrabrain-starter-sites' ), '{{{ data.name }}}' );
				?>
			</h2>
		<# } else { #>
			<h2 class="theme-name" id="{{ data.id }}-name">{{{ data.name }}}</h2>
		<# } #>
		<div class="theme-actions">
			<# if ( data.active ) { #>
				<a class="button button-primary live-preview" target="_blank" href="<?php echo home_url( '/' ); ?>"><?php _e( 'Live Preview', 'mantrabrain-starter-sites' ); ?></a>
			<# } else { #>
				<# if ( data.isPro ) { #>
					<a class="button button-primary purchase-now" href="{{ data.homepage }}" target="_blank"><?php _e( 'Buy Now', 'mantrabrain-starter-sites' ); ?></a>
				<# } else if ( data.requiredTheme || data.requiredPlugins ) { #>
					<button data-required-plugins="{{JSON.stringify(data.required_plugins)}}" class="button button-primary preview install-demo-preview"><?php _e( 'Import', 'mantrabrain-starter-sites' ); ?></button>
				<# } else { #>
					<?php
					/* translators: %s: Demo name */
					$aria_label = sprintf( _x( 'Import %s', 'demo', 'mantrabrain-starter-sites' ), '{{ data.name }}' );
					?>
					<a data-required-plugins="{{JSON.stringify(data.required_plugins)}}"  class="button button-primary hide-if-no-js demo-import" href="#" data-name="{{ data.name }}" data-slug="{{ data.id }}" aria-label="<?php echo $aria_label; ?>"><?php _e( 'Import', 'mantrabrain-starter-sites' ); ?></a>
				<# } #>

            <a target="_blank" href="{{ data.preview_url }}"
				 class="button preview installed-demo-preview"><?php _e( 'Preview', 'mantrabrain-starter-sites' ); ?></a>
			<# } #>
		</div>
	</div>

	<# if ( data.imported ) { #>
		<div class="notice notice-success notice-alt"><p><?php _ex( 'Imported', 'demo', 'mantrabrain-starter-sites' ); ?></p></div>
	<# } #>
</script>


<?php
wp_print_request_filesystem_credentials_modal();
wp_print_admin_notice_templates();
mantrabrain_print_admin_notice_templates();
