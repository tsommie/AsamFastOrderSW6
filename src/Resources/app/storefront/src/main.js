import AsamFastOrderForm from "./plugins/asam-fast-order-form/asam-fast-order-form.plugin";
import AsamFastOrderFormInput from "./plugins/asam-fast-order-form-input/asam-fast-order-form-input.plugin";

const PluginManager = window.PluginManager;

PluginManager.register('AsamFastOrderForm', AsamFastOrderForm, '[data-asam-fast-order-form]');
PluginManager.register('AsamFastOrderFormInput', AsamFastOrderFormInput, '[data-asam-fast-order-form-input]');
