{**
 * Cornelius - Core PrestaShop module
 * @author    tivuno.com <hi@tivuno.com>
 * @copyright 2018 - 2024 © tivuno.com
 * @license   https://tivuno.com/blog/bp/business-news/2-basic-license
 *}
<div class='bd-example bg-dark bd-example-toasts p-0'>
    <div aria-live='polite' aria-atomic='true' class='position-relative'>
        <div class='toast-container top-0 end-0 p-3'></div>
    </div>
</div>
<div class="loader_container hidden">
    <span class="loader"></span>
    <div class="loader_title title">{l s='This is a random title...' mod='tvcore'}</div>
</div>
<div class='toast toast_template fade hidden' role='alert' aria-live='assertive' aria-atomic='true'>
    <div class='toast-body float-start'>Toast body</div>
    <button type='button' class='toast-close float-end' data-bs-dismiss='toast' aria-label='Close'>
        <i class='fa-regular fa-circle-xmark'></i>
    </button>
</div>
<div id='request_message'
     data-prepare0="{l s='Hold on, we are checking your XPath validity and filtering out the unwanted data' mod='tvcore'}..."
     data-status0="{l s='No credentials' mod='tvcore'}"
     data-status1="{l s='Incorrect credentials' mod='tvcore'}"
     data-status200="{l s='Your files have been updated' mod='tvcore'}"
></div>
