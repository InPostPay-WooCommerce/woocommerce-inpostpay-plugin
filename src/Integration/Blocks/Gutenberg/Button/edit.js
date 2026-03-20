import React, { useEffect, useState, useRef, useCallback } from "react";
import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, SelectControl } from "@wordpress/components";

const Edit = ({ attributes, setAttributes }) => {
  const {
    variant = "primary",
    background = "bright",
    bindingPlace = "PRODUCT_CARD",
    frameStyle = "none",
    size = "size-s",
  } = attributes;

  const language = window.inpostPayAdmin?.language || "en";

  const blockProps = useBlockProps({
    className: "inpost-pay-button-editor",
  });

  const previewRef = useRef(null);
  const buttonRef = useRef(null);
  const [widgetLoaded, setWidgetLoaded] = useState(false);
  const [error, setError] = useState(null);
  const [scriptLoaded, setScriptLoaded] = useState(false);

  const bindingPlaceOptions = [
    { label: __("Product Card", "inpost-pay"), value: "PRODUCT_CARD" },
    { label: __("Basket Summary", "inpost-pay"), value: "BASKET_SUMMARY" },
    { label: __("Order Create", "inpost-pay"), value: "ORDER_CREATE" },
    { label: __("Checkout Page", "inpost-pay"), value: "CHECKOUT_PAGE" },
    { label: __("Login Page", "inpost-pay"), value: "LOGIN_PAGE" },
    { label: __("Basket Popup", "inpost-pay"), value: "BASKET_POPUP" },
    { label: __("Thank You Page", "inpost-pay"), value: "THANK_YOU_PAGE" },
    { label: __("Minicart Page", "inpost-pay"), value: "MINICART_PAGE" },
  ];

  const variantsOptions = [
    { label: __("Yellow", "inpost-pay"), value: "primary" },
    { label: __("Black", "inpost-pay"), value: "secondary" },
  ];

  const backgroundOptions = [
    { label: __("Bright", "inpost-pay"), value: "bright" },
    { label: __("Dark", "inpost-pay"), value: "dark" },
  ];

  const frameStyleOptions = [
    { label: __("No round", "inpost-pay"), value: "none" },
    { label: __("Big round", "inpost-pay"), value: "round" },
    { label: __("Small round", "inpost-pay"), value: "rounded" },
  ];

  const sizeOptions = [
    { label: __("Extra Small", "inpost-pay"), value: "size-xs" },
    { label: __("Small", "inpost-pay"), value: "size-sm" },
    { label: __("Medium", "inpost-pay"), value: "size-md" },
    { label: __("Large", "inpost-pay"), value: "size-lg" },
    { label: __("Extra Large", "inpost-pay"), value: "size-xl" },
  ];

  const updateButtonStyles = useCallback(() => {
    if (!buttonRef.current || !buttonRef.current.shadowRoot) {
      return;
    }

    const iziButtonShadowRoot =
      buttonRef.current.shadowRoot.querySelector(".inpostpay-widget");
    if (!iziButtonShadowRoot) {
      return;
    }

    const sizeClasses = Array.from(iziButtonShadowRoot.classList).filter(
      (className) => className.startsWith("size-"),
    );
    sizeClasses.forEach((className) =>
      iziButtonShadowRoot.classList.remove(className),
    );

    [
      "rounded",
      "round",
      "none",
      "dark",
      "bright",
      "primary",
      "secondary",
    ].forEach((cls) => {
      iziButtonShadowRoot.classList.remove(cls);
    });

    iziButtonShadowRoot.classList.add(frameStyle);
    iziButtonShadowRoot.classList.add(size);

    if (background === "dark") {
      iziButtonShadowRoot.classList.add("dark");
    } else {
      iziButtonShadowRoot.classList.add("bright");
    }

    if (variant === "primary") {
      iziButtonShadowRoot.classList.add("primary");
    } else {
      iziButtonShadowRoot.classList.add("secondary");
    }
  }, [variant, background, frameStyle, size]);

  const createButton = useCallback(() => {
    if (!previewRef.current || !scriptLoaded) return;

    if (buttonRef.current) {
      buttonRef.current.setAttribute("binding_place", bindingPlace);
      buttonRef.current.setAttribute(
        "variation",
        `${size} ${variant} ${background} ${frameStyle}`,
      );

      setTimeout(updateButtonStyles, 100);
      return;
    }

    try {
      const buttonElement = document.createElement("inpost-izi-button");
      buttonElement.setAttribute("binding_place", bindingPlace);
      buttonElement.setAttribute(
        "variation",
        `${size} ${variant} ${background} ${frameStyle}`,
      );

      previewRef.current.innerHTML = "";
      previewRef.current.appendChild(buttonElement);

      buttonRef.current = buttonElement;

      setWidgetLoaded(true);
      setError(null);

      setTimeout(updateButtonStyles, 100);
    } catch (err) {
      console.error("Error creating InPost button:", err);
      setError(err.message);
    }
  }, [
    bindingPlace,
    size,
    variant,
    background,
    frameStyle,
    scriptLoaded,
    updateButtonStyles,
  ]);

  useEffect(() => {
    const loadInPostWidget = () => {
      const existingScript = document.getElementById("InpostpayWidgetV2-js");
      if (existingScript) {
        if (window.InPostPayWidget) {
          try {
            const IPPwidget = window.InPostPayWidget.init({
              merchantClientId: window.inpostPayAdmin?.merchantId,
              language: language,
              basketBindingApiKey: "",
            });

            if (IPPwidget && typeof IPPwidget.refresh === "function") {
              IPPwidget.refresh();
            }

            setScriptLoaded(true);
          } catch (err) {
            console.error("Error reinitializing InPost widget:", err);
            setError(__("Failed to reinitialize InPost widget", "inpost-pay"));
          }
        }
        return;
      }

      const script = document.createElement("script");
      const timestamp = new Date().getTime();
      script.id = "InpostpayWidgetV2-js";
      script.src = `${window.inpostPayAdmin?.jsUrl}?a=${timestamp}`;
      script.onload = () => {
        if (window.InPostPayWidget) {
          const IPPWidgetOptions = {
            merchantClientId: window.inpostPayAdmin?.merchantId,
            language: language,
            basketBindingApiKey: "",
          };
          try {
            window.InPostPayWidget.init(IPPWidgetOptions);
            setScriptLoaded(true);
          } catch (err) {
            console.error("Error initializing InPost widget:", err);
            setError(__("Failed to initialize InPost widget", "inpost-pay"));
          }
        }
      };
      script.onerror = () => {
        setError(__("Failed to load InPost widget script", "inpost-pay"));
      };

      document.head.appendChild(script);
    };

    loadInPostWidget();
  }, [language]);

  useEffect(() => {
    if (scriptLoaded) {
      createButton();
    }
  }, [scriptLoaded, createButton]);

  useEffect(() => {
    if (widgetLoaded && buttonRef.current) {
      if (buttonRef.current.setAttribute) {
        buttonRef.current.setAttribute("binding_place", bindingPlace);
        buttonRef.current.setAttribute(
          "variation",
          `${size} ${variant} ${background} ${frameStyle}`,
        );
      }

      setTimeout(updateButtonStyles, 50);
    }
  }, [
    variant,
    background,
    frameStyle,
    size,
    bindingPlace,
    widgetLoaded,
    updateButtonStyles,
  ]);

  useEffect(() => {
    return () => {
      buttonRef.current = null;
    };
  }, []);

  return (
    <>
      <InspectorControls>
        <PanelBody
          title={__("Button Settings", "inpost-pay")}
          initialOpen={true}
        >
          <SelectControl
            label={__("Binding Place", "inpost-pay")}
            value={bindingPlace}
            options={bindingPlaceOptions}
            onChange={(value) => setAttributes({ bindingPlace: value })}
          />
        </PanelBody>

        <PanelBody
          title={__("Style Settings", "inpost-pay")}
          initialOpen={false}
        >
          <SelectControl
            label={__("Variant", "inpost-pay")}
            value={variant}
            options={variantsOptions}
            onChange={(value) => setAttributes({ variant: value })}
            help={__("Determines the variant of button", "inpost-pay")}
          />

          <SelectControl
            label={__("Background", "inpost-pay")}
            value={background}
            options={backgroundOptions}
            onChange={(value) => setAttributes({ background: value })}
            help={__("Determines the background theme", "inpost-pay")}
          />

          <SelectControl
            label={__("Round style", "inpost-pay")}
            value={frameStyle}
            options={frameStyleOptions}
            onChange={(value) => setAttributes({ frameStyle: value })}
            help={__("Determines the button frame style", "inpost-pay")}
          />

          <SelectControl
            label={__("Size", "inpost-pay")}
            value={size}
            options={sizeOptions}
            onChange={(value) => setAttributes({ size: value })}
            help={__("Determines the button size", "inpost-pay")}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        {!scriptLoaded && !error && (
          <div className="inpost-pay-preview-placeholder">
            {__("Loading InPost Pay script...", "inpost-pay")}
          </div>
        )}

        {!widgetLoaded && scriptLoaded && !error && (
          <div className="inpost-pay-preview-placeholder">
            {__("Initializing InPost Pay button...", "inpost-pay")}
          </div>
        )}

        {error && (
          <div className="inpost-pay-preview-placeholder inpost-pay-preview-placeholder--error">
            {__("Error loading InPost Pay button:", "inpost-pay")} {error}
          </div>
        )}

        {(widgetLoaded || scriptLoaded) && (
          <div className="inpost-pay-button-preview-wrapper">
            <div className="inpost-pay-button-preview" ref={previewRef}>
              {/* Przycisk będzie wstawiony tutaj przez JavaScript */}
            </div>
            <div className="inpost-pay-preview-info">
              <small>
                {__(
                  "Preview mode - button is not functional in editor",
                  "inpost-pay",
                )}
              </small>
            </div>
            <div className="inpost-pay-preview-info">
              <small>
                {__(
                  "If you want to use this button, we recommend you to remove other InpostPay buttons from your page",
                  "inpost-pay",
                )}
              </small>
            </div>
          </div>
        )}
      </div>
    </>
  );
};

export default Edit;
