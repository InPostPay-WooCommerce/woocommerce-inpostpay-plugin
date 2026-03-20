document.addEventListener( 'DOMContentLoaded', function () {
  const { registerCheckoutFilters } = window.wc.blocksCheckout;

  const modifyAdditionalInnerBlockTypes = (
    defaultValue,
    extensions,
    args,
    validation
  ) => {
    defaultValue.push( 'inpost-pay/gutenberg-button' );

    // if ( args?.block === 'woocommerce/checkout-shipping-address-block' ) {
    //   defaultValue.push( 'inpost-pay/gutenberg-button' );
    // }

    return defaultValue;
  };

  registerCheckoutFilters( 'inpost-pay-gutenberg-block', {
    additionalCartCheckoutInnerBlockTypes: modifyAdditionalInnerBlockTypes,
  } );
} );
