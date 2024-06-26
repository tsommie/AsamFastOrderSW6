import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';

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
    this._form = this.el.querySelector(`#form-${this.options.formId}`);

    // Get the input container.
    this._inputContainer = this.el.querySelector(`#${this.options.formId}-input-container`);

    // Get the add block button.
    this._addBlockButton = this.el.querySelector(`#fastOrderAddBlockButton-${this.options.formId}`);

    // Get the alert box.
    this._alertBox = this.el.querySelector(`#fastOrderAlertBox-${this.options.formId}`);

    // Let's ensure that the required elements to enable the plugin work correctly are present.
    if (!this._validateRequiredElements()) {
      return;
    }

    // Call the _handleInputBlock function as many times as the minInputBlocks option
    for (let i = 0; i < this.options.minInputBlocks; i++) {
      this._client.get(this.options.urls.inputTemplate, this._setInputBlock.bind(this))
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

    const formData = new FormData(this._form);
    this._client.post(this._form.getAttribute('action'), formData, this._onFormSubmitted.bind(this));
  }

  _onFormSubmitted(response) {
    console.log(response);
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
   * @private
   */
  _setInputBlock(response) {
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
    this._client.get(this.options.urls.inputTemplate, this._setInputBlock.bind(this));
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
}