<?php

use Ilabs\Inpost_Pay\Lib\helpers\HPOSHelper;

/** @var \WC_Order|null $order */
/** @var HPOSHelper|null $HPOSHelper */

if ( ! isset( $order ) || ! $order instanceof \WC_Order ) {
	$order = null;

	if ( isset( $_GET['id'] ) ) {
		$maybe_id = absint( $_GET['id'] );
		if ( $maybe_id ) {
			$order = wc_get_order( $maybe_id );
		}
	}

	if ( ! $order ) {
		global $post;
		if ( is_object( $post ) && ! empty( $post->ID ) ) {
			$order = wc_get_order( (int) $post->ID );
		}
	}
}

if ( ! $order ) {
	echo '<p>' . esc_html__( 'Order not found.', 'inpost-pay' ) . '</p>';

	return;
}

if ( ! isset( $HPOSHelper ) || ! $HPOSHelper instanceof HPOSHelper ) {
	$HPOSHelper = new HPOSHelper( $order );
}
?>
<style>
	.izi_row td {
		border-bottom: solid 1px #eee;
		padding-bottom: 13px;
	}
</style>

<table style="width:100%">
	<tbody>
	<tr class="izi_row">
		<td><?php _e( 'Shipment:', 'inpost-pay' ); ?></td>
		<td>
			<?php
			$send_method = (string) $HPOSHelper->get_meta( '_easypack_send_method', true );
			echo $send_method === 'parcel_machine'
				? esc_html__( 'Inpost Parcel locker', 'inpost-pay' )
				: esc_html__( 'Inpost Courier', 'inpost-pay' );
			?>
		</td>
	</tr>

	<?php if ( $send_method === 'parcel_machine' ) : ?>
		<tr class="izi_row">
			<td><?php _e( 'Parcel locker:', 'inpost-pay' ); ?></td>
			<td><?php echo esc_html( (string) $HPOSHelper->get_meta( 'delivery_point', true ) ); ?></td>
		</tr>
	<?php endif; ?>

	<?php if ( $HPOSHelper->get_meta( 'izi_payment_type' ) ) : ?>
		<tr class="izi_row">
			<td><?php _e( 'Payment type:', 'inpost-pay' ); ?></td>
			<td><?php echo esc_html( (string) $HPOSHelper->get_meta( 'izi_payment_type', true ) ); ?></td>
		</tr>
	<?php endif; ?>

	<?php if ( $HPOSHelper->get_meta( 'izi_payment_id' ) ) : ?>
		<tr class="izi_row">
			<td><?php _e( 'Payment ID:', 'inpost-pay' ); ?></td>
			<td><?php echo esc_html( (string) $HPOSHelper->get_meta( 'izi_payment_id', true ) ); ?></td>
		</tr>
	<?php endif; ?>

	<?php if ( $HPOSHelper->get_meta( 'izi_payment_reference' ) ) : ?>
		<tr class="izi_row">
			<td><?php _e( 'Payment Reference:', 'inpost-pay' ); ?></td>
			<td><?php echo esc_html( (string) $HPOSHelper->get_meta( 'izi_payment_reference', true ) ); ?></td>
		</tr>
	<?php endif; ?>

	<?php if ( $HPOSHelper->get_meta( 'inpost_pay_digital_delivery_email' ) ) : ?>
		<tr class="izi_row">
			<td><?php _e( 'Digital delivery email:', 'inpost-pay' ); ?></td>
			<td><?php echo esc_html( (string) $HPOSHelper->get_meta( 'inpost_pay_digital_delivery_email', true ) ); ?></td>
		</tr>
	<?php endif; ?>
	</tbody>
</table>
