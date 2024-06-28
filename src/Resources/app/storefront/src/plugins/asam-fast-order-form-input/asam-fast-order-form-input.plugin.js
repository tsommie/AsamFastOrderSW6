import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import Debouncer from 'src/helper/debouncer.helper';

export default class AsamFastOrderFormInput extends Plugin {
  static options = {
    hashId: '',
    minInputBeforeSearch: 3,
    searchDelay: 300,
    urls: {
      search: ''
    }
  };

  init() {
    // initialize the HttpClient
    this._client = new HttpClient();

    this._productNumberInput = this.el.querySelector(`#fastOrderProductNumber-${this.options.hashId}`);
    this._productOptionsContainer = this.el.querySelector(`#fastOrderProductOptions-${this.options.hashId}`);

    if (!this._validateRequiredElements()) {
      return;
    }

    this._registerEvents();
  }

  /**
   * Register the events for the plugin.
   *
   * @private
   */
  _registerEvents() {
    // On input, search for the product number
    this._productNumberInput.addEventListener('input', Debouncer.debounce(
      this._handleProductNumberInput.bind(this),
      this.options.searchDelay
    ));

    // On un-focus, validate the input
    this._productNumberInput.addEventListener('blur', this._handleProductNumberInputValidation.bind(this));
  }

  _handleProductNumberInput(event) {
    const productNumber = event.target.value.trim();

    if (productNumber.length < this.options.minInputBeforeSearch) {
      return;
    }

    this._client.get(`${this.options.urls.search}?productNumber=${productNumber}`, this._setInputOptions.bind(this));
  }

  /**
   * Validate the product number input
   *
   * @param event
   * @private
   */
  _handleProductNumberInputValidation(event) {
    const productNumber = event.target.value;

    // If the product number is empty, we don't need to validate it.
    if (productNumber.length < 1) {
      return;
    }

    // We treat the request differently if validationMode is set to 1.
    this._client.get(
      `${this.options.urls.search}?productNumber=${productNumber}&validationMode=1`,
      this._handleValidationResponse.bind(this)
    );
  }

  _handleValidationResponse(response) {
    /**
     * @var {{ valid: boolean, productId: string, message: string, product: {
     *   id: string,
     *   productNumber: string,
     *   minPurchase: string,
     *   maxPurchase: string,
     *   purchaseSteps: string,
     *   stock: string
     * } }} data
     * */
    let data = JSON.parse(response);

    //  Unset number attributes...
    this._unsetNumberInputAttributes();

    // Get .invalid-feedback element next to the input field
    let invalidFeedback = this._productNumberInput.nextElementSibling;

    // If the response is valid, set the product id and reference id
    if (data.valid) {
      this._productNumberInput.classList.add('is-valid');
      this._productNumberInput.classList.remove('is-invalid');

      // Set number input field attributes.
      this._setNumberInputAttributes(data.product);

      if (invalidFeedback && invalidFeedback.classList.contains('invalid-feedback')) {
        // Add the d-none class to hide the error message if it was shown
        invalidFeedback.classList.add('d-none');
      }
    } else {
      this._productNumberInput.classList.add('is-invalid');
      this._productNumberInput.classList.remove('is-valid');

      if (invalidFeedback && invalidFeedback.classList.contains('invalid-feedback')) {
        // Remove the d-none class to show the error message if it was hidden
        invalidFeedback.classList.remove('d-none');
      }
    }
  }

  /**
   * Set number constraint attributes to better aid the customer make better decision.
   *
   * @param {{
   *   id: string,
   *   productNumber: string,
   *   minPurchase: string,
   *   maxPurchase: string,
   *   purchaseSteps: string,
   *   stock: string
   * }} product
   * @private
   */
  _setNumberInputAttributes(product) {
    const input = this.el.querySelector(`#fastOrderQuantity-${this.options.hashId}`);

    if (!input) {
      return;
    }

    input.value = product.minPurchase;
    input.setAttribute('min', product.minPurchase);
    input.setAttribute('max', product.maxPurchase);
    input.setAttribute('step', product.purchaseSteps);
  }

  /**
   * Unset the number input attributes.
   *
   * @private
   */
  _unsetNumberInputAttributes() {
    const input = this.el.querySelector(`#fastOrderQuantity-${this.options.hashId}`);

    if (!input) {
      return;
    }

    input.removeAttribute('min');
    input.removeAttribute('max');
    input.removeAttribute('step');
  }

  /**
   * Set the input options returned from the response.
   *
   * @param response
   * @private
   */
  _setInputOptions(response) {
    this._productOptionsContainer.innerHTML = response;
  }

  /**
   * Validate the required elements for the plugin.
   *
   * @returns {boolean}
   * @private
   */
  _validateRequiredElements() {
    let isValid = true;

    if (!this._productNumberInput) {
      // For debug purposes only.
      console.error('Product number input not found', `#fastOrderProductNumber-${this.options.hashId}`);
      isValid = false;
    }

    if (!this._productOptionsContainer) {
      // For debug purposes only.
      console.error('Product options container not found', `#fastOrderProductOptions-${this.options.hashId}`);
      isValid = false;
    }

    return isValid;
  }
}