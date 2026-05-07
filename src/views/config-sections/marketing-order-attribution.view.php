<?php
/**
 * View: Marketing order attribution settings.
 *
 * @package Ilabs\Inpost_Pay
 */

use Ilabs\Inpost_Pay\Lib\config\analytics\AnalyticsConfig;
use Ilabs\Inpost_Pay\Lib\config\attribution\AttributionConfig;
use Ilabs\Inpost_Pay\Lib\config\attribution\AttributionOverridesConfig;

$attribution_config           = new AttributionConfig();
$attribution_enable           = $attribution_config->get_form_field();
$attribution_overrides_config = new AttributionOverridesConfig();
$attribution_overrides        = $attribution_overrides_config->get_form_field();
$analytics_config             = new AnalyticsConfig();
$analytics_enable             = $analytics_config->get_form_field();
?>

<div>
	<p><?php $attribution_enable->print_label_text(); ?></p>
	<div class="toggleWrapper">
		<?php $attribution_enable->print_field(); ?>
		<label for="<?php echo esc_attr( $attribution_enable->get_label_name() ); ?>"></label>
	</div>
	<div><?php echo esc_html( $attribution_config->get_description() ); ?></div>
</div>

<div>
	<p><?php $attribution_overrides->print_label_text(); ?></p>
	<div class="toggleWrapper">
		<?php $attribution_overrides->print_field(); ?>
		<label for="<?php echo esc_attr( $attribution_overrides->get_label_name() ); ?>"></label>
	</div>
	<div><?php echo esc_html( $attribution_overrides_config->get_description() ); ?></div>
</div>

<div>
	<p><?php $analytics_enable->print_label_text(); ?></p>
	<div class="toggleWrapper">
		<?php $analytics_enable->print_field(); ?>
		<label for="<?php echo esc_attr( $analytics_enable->get_label_name() ); ?>"></label>
	</div>
	<div><?php echo esc_html( $analytics_config->get_description() ); ?></div>
</div>
