const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
const { getSetting } = window.wc.wcSettings;
const { createElement } = window.wp.element;
const { __ } = window.wp.i18n;

const settings = getSetting( 'qpay_data', {} );

const Content = () => {
    return createElement( 'div', null, 
        createElement( 'div', null, settings.description || __( 'Pay via QPay QR code.', 'woocommerce-qpay' ) ),
        createElement( 'div', { style: { marginTop: '8px', fontSize: '13px', color: '#666', fontStyle: 'italic' } }, __( 'A secure QPay QR code will be generated on the next screen after you place your order.', 'woocommerce-qpay' ) )
    );
};

const Icon = () => {
    return settings.icon 
        ? createElement( 'img', { src: settings.icon, style: { float: 'right', marginRight: '20px', height: '24px' } } ) 
        : null;
};

const Label = ( props ) => {
    const { PaymentMethodLabel } = props.components;
    // We render the default label component and our custom QPay icon.
    return createElement( 'span', { style: { width: '100%' } },
        createElement( PaymentMethodLabel, { text: settings.title || 'QPay' } ),
        createElement( Icon, null )
    );
};

registerPaymentMethod( {
    name: 'qpay',
    label: createElement( Label, null ),
    content: createElement( Content, null ),
    edit: createElement( Content, null ),
    canMakePayment: () => true,
    ariaLabel: settings.title || 'QPay',
    supports: {
        features: settings.supports || [],
    }
} );
