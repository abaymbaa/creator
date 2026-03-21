(function() {
    const { addFilter } = wp.hooks;

    const QPayTab = {
        title: jfbQpayAdmin.labels.title,
        component: {
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
            render(h) {
                const _c = this._self._c || h;
                return _c('section', [
                    _c('cx-vui-input', {
                        attrs: {
                            label: jfbQpayAdmin.labels.client_id,
                            'wrapper-css': ['equalwidth'],
                            size: 'fullwidth',
                        },
                        model: {
                            value: this.storage.client_id,
                            callback: (v) => { this.$set(this.storage, 'client_id', v) },
                            expression: 'storage.client_id'
                        }
                    }),
                    _c('cx-vui-input', {
                        attrs: {
                            label: jfbQpayAdmin.labels.client_secret,
                            'wrapper-css': ['equalwidth'],
                            size: 'fullwidth',
                        },
                        model: {
                            value: this.storage.client_secret,
                            callback: (v) => { this.$set(this.storage, 'client_secret', v) },
                            expression: 'storage.client_secret'
                        }
                    }),
                    _c('cx-vui-input', {
                        attrs: {
                            label: jfbQpayAdmin.labels.invoice_code,
                            'wrapper-css': ['equalwidth'],
                            size: 'fullwidth',
                        },
                        model: {
                            value: this.storage.invoice_code,
                            callback: (v) => { this.$set(this.storage, 'invoice_code', v) },
                            expression: 'storage.invoice_code'
                        }
                    }),
                    _c('cx-vui-input', {
                        attrs: {
                            label: jfbQpayAdmin.labels.invoice_receiver_code,
                            'wrapper-css': ['equalwidth'],
                            size: 'fullwidth',
                        },
                        model: {
                            value: this.storage.invoice_receiver_code,
                            callback: (v) => { this.$set(this.storage, 'invoice_receiver_code', v) },
                            expression: 'storage.invoice_receiver_code'
                        }
                    }),
                    _c('cx-vui-input', {
                        attrs: {
                            label: jfbQpayAdmin.labels.price_field,
                            'wrapper-css': ['equalwidth'],
                            size: 'fullwidth',
                            help: jfbQpayAdmin.labels.price_field_help
                        },
                        model: {
                            value: this.storage.price_field,
                            callback: (v) => { this.$set(this.storage, 'price_field', v) },
                            expression: 'storage.price_field'
                        }
                    }),
                    _c('cx-vui-switcher', {
                        attrs: {
                            label: jfbQpayAdmin.labels.is_sandbox,
                            'wrapper-css': ['equalwidth'],
                        },
                        model: {
                            value: this.storage.is_sandbox,
                            callback: (v) => { this.$set(this.storage, 'is_sandbox', v) },
                            expression: 'storage.is_sandbox'
                        }
                    })
                ]);
            }
        }
    };

    addFilter('jet.fb.register.gateways', 'jfb-qpay', function(tabs) {
        tabs.push(QPayTab);
        return tabs;
    });
})();
