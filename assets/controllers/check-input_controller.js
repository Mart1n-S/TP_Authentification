// controllers/check-input_controller.js
import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = [
    "emailInput",
    "error",
    "criteria",
    "length",
    "uppercase",
    "lowercase",
    "number",
    "special",
    "toggleIcon",
    "passwordInput",
    "confirmPasswordInput",
    "confirmError",
  ];

  connect() {
    this.setupListeners();
  }

  setupListeners() {
    // Affiche les erreurs existantes au chargement de la page
    this.errorTargets.forEach((error) => {
      if (error.textContent.trim() !== "") {
        error.classList.remove("d-none");
      }
    });

    // Validation de l'email si le champ est présent
    if (this.hasEmailInputTarget) {
      this.emailInputTarget.addEventListener("input", (event) =>
        this.validateEmail(event)
      );
    }

    // Écouteurs pour le mot de passe
    if (this.hasPasswordInputTarget) {
      this.passwordInputTarget.addEventListener("input", (event) =>
        this.validatePasswordCriteria(event)
      );

      this.passwordInputTarget.addEventListener("focus", () => {
        this.criteriaTarget.classList.remove("d-none");
      });

      this.passwordInputTarget.addEventListener("blur", () => {
        if (this.passwordInputTarget.value === "") {
          this.criteriaTarget.classList.add("d-none");
        }
      });
    }

    if (this.hasConfirmPasswordInputTarget) {
      this.confirmPasswordInputTarget.addEventListener("input", (event) =>
        this.validatePasswordMatch(event)
      );
    }
  }

  validateEmail(event) {
    const email = event.target.value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    const errorMessage = "L'email n'est pas valide.";

    if (!emailRegex.test(email)) {
      this.showError("error", errorMessage);
      this.setInvalid(event.target);
    } else {
      this.hideError("error");
      this.setValid(event.target);
    }
  }

  validatePasswordCriteria(event) {
    const password = event.target.value;

    // Validation des critères individuels
    const isLengthValid = password.length >= 16;
    const isUppercaseValid = /[A-Z]/.test(password);
    const isLowercaseValid = /[a-z]/.test(password);
    const isNumberValid = /[0-9]/.test(password);
    const isSpecialValid = /[\W_]/.test(password);

    // Validation des critères individuels
    this.toggleCriteria(this.lengthTarget, password.length >= 16);
    this.toggleCriteria(this.uppercaseTarget, /[A-Z]/.test(password));
    this.toggleCriteria(this.lowercaseTarget, /[a-z]/.test(password));
    this.toggleCriteria(this.numberTarget, /[0-9]/.test(password));
    this.toggleCriteria(this.specialTarget, /[\W_]/.test(password));

    // Vérifie si tous les critères sont valides
    if (
      isLengthValid &&
      isUppercaseValid &&
      isLowercaseValid &&
      isNumberValid &&
      isSpecialValid
    ) {
      this.setValid(event.target);
      this.hideError("error");
    } else {
      this.setInvalid(event.target);
    }
  }

  validatePasswordMatch(event) {
    const password = this.passwordInputTarget.value;
    const confirmPassword = event.target.value;
    const errorMessage = "Les mots de passe doivent correspondre.";

    if (password !== confirmPassword) {
      this.showError("confirmError", errorMessage);
      this.setInvalid(event.target);
    } else {
      this.hideError("confirmError");
      this.setValid(event.target);
    }
  }

  toggleCriteria(target, isValid) {
    target.classList.toggle("text-success", isValid);
    target.classList.toggle("text-danger", !isValid);
  }

  showError(name, message) {
    // Cherche l'élément d'erreur correspondant à la cible spécifiée
    let errorElement;
    if (name === "confirmError") {
      errorElement = this.confirmErrorTarget;
    } else {
      errorElement = this.errorTargets.find(
        (error) => error.dataset.checkInputTarget === name
      );
    }

    if (errorElement) {
      errorElement.textContent = message;
      errorElement.classList.remove("d-none");
    }
  }

  hideError(name) {
    // Cherche l'élément d'erreur correspondant à la cible spécifiée
    let errorElement;
    if (name === "confirmError") {
      errorElement = this.confirmErrorTarget;
    } else {
      errorElement = this.errorTargets.find(
        (error) => error.dataset.checkInputTarget === name
      );
    }

    if (errorElement) {
      errorElement.classList.add("d-none");
      errorElement.textContent = "";
    }
  }

  setValid(target) {
    target.classList.add("is-valid");
    target.classList.remove("is-invalid");
  }

  setInvalid(target) {
    target.classList.add("is-invalid");
    target.classList.remove("is-valid");
  }

  togglePasswordVisibility() {
    // Recherche tous les inputs de type password parmi les inputTargets
    const passwordFields = this.element.querySelectorAll("input.password");
    const icon = this.toggleIconTarget;

    // Alterne entre "text" et "password" pour chaque champ de mot de passe
    passwordFields.forEach((passwordField) => {
      if (passwordField.type === "password") {
        passwordField.type = "text";
        icon.classList.replace("bi-eye", "bi-eye-slash");
      } else {
        passwordField.type = "password";
        icon.classList.replace("bi-eye-slash", "bi-eye");
      }
    });
  }
}
