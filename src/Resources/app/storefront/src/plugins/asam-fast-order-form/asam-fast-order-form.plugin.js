import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import LoadingIndicatorUtil from 'src/utility/loading-indicator/loading-indicator.util';

export default class AsamFastOrderForm extends Plugin {
  /**
   * Plugin options.
   * @type {{formId: string, urls: {inputTemplate: string}, minInputBlocks: number}}
   */
  static options = {
    formId: '',
    minInputBlocks: 4,
    urls: {
      inputTemplate: ''
    }
  };

  init() {
    // initialize the HttpClient
    this._client = new HttpClient();

    // Get the form element.
    this._form = this._getElementChildById(`form-${this.options.formId}`);
    this._submitButton = this._getElementChildById(`fastOrderSubmitButton-${this.options.formId}`)

    // Get the input container.
    this._inputContainer = this._getElementChildById(`${this.options.formId}-input-container`)

    // Get the add block button.
    this._addBlockButton = this._getElementChildById(`fastOrderAddBlockButton-${this.options.formId}`);

    // Get the alert box.
    this._alertBox = this._getElementChildById(`fastOrderAlertBox-${this.options.formId}`);

    // Let's ensure that the required elements to enable the plugin work correctly are present.
    if (!this._validateRequiredElements()) {
      return;
    }

    // Call the _handleInputBlock function as many times as the minInputBlocks option
    for (let i = 0; i < this.options.minInputBlocks; i++) {
      this._client.get(this.options.urls.inputTemplate, async (response) => {
        await this._setInputBlock(response);
      })
    }

    this._registerEvents();
  }

  /**
   * Register the events for the plugin.
   *
   * @private
   */
  _registerEvents() {
    this._addBlockButton.addEventListener('click', this._handleAddBlock.bind(this));
    this._form.addEventListener('submit', this._handleSubmit.bind(this));
  }

  _handleSubmit(event) {
    event.preventDefault();

    // Get load indicator for the submit button.
    const loadIndicator = this.getLoadingIndicator(this._submitButton);

    const formData = new FormData(this._form);
    this._client.post(this._form.getAttribute('action'), formData, (response) => {
      this._onFormSubmitted(response, loadIndicator);
    });
  }

  _onFormSubmitted(response, loadIndicator) {
    loadIndicator.remove();

    /** @type {{ success: boolean, redirectTo: string, errors: array }} */
    let data = JSON.parse(response);

    if (data.success) {
      window.location = data.redirectTo;
    } else {
      this._alertBox.innerHTML = data.errors.join('<br>');
      this._alertBox.classList.remove('d-none');
    }
  }

  /**
   * Handle input block insertion into the DOM.
   *
   * @param response
   * @param loadingIndicator
   * @private
   */
  _setInputBlock(response, loadingIndicator = null) {
    if (loadingIndicator) {
      loadingIndicator.remove();
    }

    // Add the response to the end of the element
    this._inputContainer.insertAdjacentHTML('beforeend', response);

    // Initialize the AsamFastOrderFormInput plugin.
    const inputBlock = this._inputContainer.lastElementChild;

    // Get the plugin options from the input data-asam-fast-order-form-input-options attribute.
    const options = JSON.parse(inputBlock.getAttribute('data-asam-fast-order-form-input-options'));

    // Initialize the AsamFastOrderFormInput plugin.
    window.PluginManager.initializePlugin('AsamFastOrderFormInput', inputBlock, options)
  }

  /**
   * Handle the add block button click event.
   *
   * @private
   */
  _handleAddBlock() {
    const loadingIndicator = this.getLoadingIndicator(this._addBlockButton)

    this._client.get(this.options.urls.inputTemplate, (response) => {
      this._setInputBlock(response, loadingIndicator);
    });
  }

  /**
   * Validate the required elements for the plugin.
   *
   * @returns {boolean}
   * @private
   */
  _validateRequiredElements() {
    let isValid = true;

    // If the input container is not found, log an error and return.
    if (!this._inputContainer) {
      // For debug purposes only.
      console.error(`The input container with the ID ${this.options.formId}-input-container was not found.`);
      isValid = false;
    }

    if (!this._addBlockButton) {
      // For debug purposes only.
      console.error(`The add block button with the ID ${this.options.formId}-add-block-button was not found.`);
      isValid = false;
    }

    if (!this._form) {
      // For debug purposes only.
      console.error(`The form with the ID ${this.options.formId} was not found.`);
      isValid = false;
    }

    if (!this._alertBox) {
      // For debug purposes only.
      console.error(`The alert box with the ID ${this.options.formId}-alert-box was not found.`);
      isValid = false;
    }

    return isValid;
  }

  /**
   * Get the loading indicator for the given element.
   *
   * @param element
   * @returns {*}
   */
  getLoadingIndicator(element) {
    let loadIndicator = new LoadingIndicatorUtil(element);
    loadIndicator.create();

    return loadIndicator;
  }

  _getElementChildById(id) {
    return this.el.querySelector(`#${id}`);
  }
}