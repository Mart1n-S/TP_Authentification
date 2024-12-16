import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static values = {
    siteKey: String,
    action: String,
  };

  connect() {
    this.loadRecaptcha();
  }

  loadRecaptcha() {
    const { siteKeyValue: siteKey, actionValue: action } = this;

    if (window.grecaptcha) {
      grecaptcha.ready(() => {
        grecaptcha.execute(siteKey, { action: action }).then((token) => {
          document.getElementById("form_captcha").value = token;
        });
      });
    } else {
      console.error("reCAPTCHA is not loaded.");
    }
  }
}
