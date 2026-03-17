(function() {
    const { addFilter } = wp.hooks;
    const { __ } = wp.i18n;

    const QPayTabComponent = {
        name: 'qpay',
        props: {
            incoming: {
                type: Object,
                default: () => ({})
            }
        },
        data() {
            return {
                storage: JSON.parse(JSON.stringify(this.incoming))
            };
        },
        methods: {
            getRequestOnSave() {
                return {
                    data: { ...this.storage }
                };
            }
        },
        template: `
            <section>
                <cx-vui-input
                    label="${__('API Username', 'jet-form-builder-qpay')}"
                    :wrapper-css="['equalwidth']"
                    size="fullwidth"
                    v-model="storage.username"
                ></cx-vui-input>
                <cx-vui-input
                    label="${__('API Password', 'jet-form-builder-qpay')}"
                    :wrapper-css="['equalwidth']"
                    size="fullwidth"
                    type="password"
                    v-model="storage.password"
                ></cx-vui-input>
                <cx-vui-input
                    label="${__('Invoice Code', 'jet-form-builder-qpay')}"
                    :wrapper-css="['equalwidth']"
                    size="fullwidth"
                    v-model="storage.invoice_code"
                ></cx-vui-input>
            </section>
        `
    };

    addFilter('jet.fb.register.gateways', 'jet-form-builder-qpay', function(tabs) {
        tabs.push({
            title: __('QPay Gateway API', 'jet-form-builder-qpay'),
            slug: 'qpay', // Added slug matching the PHP handler
            component: QPayTabComponent
        });
        return tabs;
    });
})();
