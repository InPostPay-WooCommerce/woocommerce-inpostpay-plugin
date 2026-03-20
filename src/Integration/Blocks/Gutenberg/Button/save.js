import { useBlockProps } from "@wordpress/block-editor";

const Save = ({ attributes }) => {
  const blockProps = useBlockProps.save();

  const getBindingPlaceClass = () => {
    switch (attributes.bindingPlace) {
      case "PRODUCT_CARD":
        return "izi-widget-product";
      case "BASKET_SUMMARY":
        return "izi-widget-cart";
      case "ORDER_CREATE":
        return "izi-widget-order";
      case "CHECKOUT_PAGE":
        return "izi-widget-checkout";
      case "LOGIN_PAGE":
        return "izi-widget-login";
      case "BASKET_POPUP":
        return "izi-widget-basket-popup";
      case "THANK_YOU_PAGE":
        return "izi-widget-thank-you";
      case "MINICART_PAGE":
        return "izi-widget-minicart";
    }
  };

  return (
    <div {...blockProps}>
      <div
        className={`izi-widget-placeholder ${getBindingPlaceClass()} izi-widget-gutenberg`}
        data-attributes={JSON.stringify(attributes)}
      ></div>
    </div>
  );
};

export default Save;
