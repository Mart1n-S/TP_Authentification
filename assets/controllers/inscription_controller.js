import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = [
    "input",
    "error",
    "criteria",
    "length",
    "uppercase",
    "lowercase",
    "number",
    "special",
    "toggleIcon",
  ];

  connect() {
    // Affiche les erreurs existantes au chargement de la page
    this.errorTargets.forEach((error) => {
      if (error.textContent.trim() !== "") {
        error.classList.remove("d-none");
      }
    });

    // Gère l'affichage des critères de mot de passe
    const passwordInput = this.inputTargets.find(
      (input) => input.name === "registration_form[plainPassword][first]"
    );
    const confirmPasswordInput = this.inputTargets.find(
      (input) => input.name === "registration_form[plainPassword][second]"
    );

    if (passwordInput) {
      passwordInput.addEventListener("focus", () => {
        this.criteriaTarget.classList.remove("d-none");
      });
      passwordInput.addEventListener("blur", () => {
        if (passwordInput.value === "") {
          this.criteriaTarget.classList.add("d-none");
        }
      });
      passwordInput.addEventListener("input", (event) =>
        this.validatePasswordCriteria(event)
      );
    }

    if (confirmPasswordInput) {
      confirmPasswordInput.addEventListener("input", (event) =>
        this.validatePasswordMatch(event)
      );
    }
  }

  validate(event) {
    const target = event.target;
    const value = target.value;
    const name = target.name;
    const errorMessage = this.getErrorMessage(name, value);
    const label = target.labels[0];
    if (errorMessage || errorMessage === "") {
      this.showError(name, errorMessage);
      this.setInvalid(target, label);
    } else {
      this.hideError(name);
      this.setValid(target, label);
    }

    // Appelle la validation de correspondance des mots de passe
    if (name === "registration_form[plainPassword][second]") {
      this.validatePasswordMatch(event);
    }
  }

  validatePasswordCriteria(event) {
    const password = event.target.value;

    // Validation des critères individuels
    this.toggleCriteria(this.lengthTarget, password.length >= 16);
    this.toggleCriteria(this.uppercaseTarget, /[A-Z]/.test(password));
    this.toggleCriteria(this.lowercaseTarget, /[a-z]/.test(password));
    this.toggleCriteria(this.numberTarget, /[0-9]/.test(password));
    this.toggleCriteria(this.specialTarget, /[\W_]/.test(password));
  }

  validatePasswordMatch(event) {
    const password = this.inputTargets.find(
      (input) => input.name === "registration_form[plainPassword][first]"
    ).value;
    const confirmPassword = event.target.value;
    const errorMessage = "Les mots de passe doivent correspondre.";
    if (password !== confirmPassword) {
      this.showError("registration_form[plainPassword][second]", errorMessage);
      this.setInvalid(event.target, event.target.labels[0]);
    } else {
      this.hideError("registration_form[plainPassword][second]");
      this.setValid(event.target, event.target.labels[0]);
    }
  }

  toggleCriteria(target, isValid) {
    target.classList.toggle("text-valid", isValid);
    target.classList.toggle("text-error", !isValid);
  }

  getErrorMessage(name, value) {
    switch (name) {
      case "registration_form[name]":
        const nameRegex = /^[A-Za-zÀ-ÿ\s-]{2,50}$/;
        return !nameRegex.test(value) ? "Le nom n'est pas valide." : null;
      case "registration_form[email]":
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
        return !emailRegex.test(value) ? "L'email n'est pas valide." : null;
      case "registration_form[plainPassword][first]":
        const passwordRegex =
          /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{16,}$/;
        return !passwordRegex.test(value) ? "" : null;
      case "registration_form[agreeTerms]":
        return !this.inputTargets[4].checked
          ? "Veuillez accepter les CGU."
          : null;
      default:
        return null;
    }
  }

  showError(name, message) {
    const index = this.inputTargets.findIndex((input) => input.name === name);
    if (index >= 0) {
      this.errorTargets[index].textContent = message;
      this.errorTargets[index].classList.remove("d-none");
    }
  }

  hideError(name) {
    const index = this.inputTargets.findIndex((input) => input.name === name);
    if (index >= 0) {
      this.errorTargets[index].classList.add("d-none");
      this.errorTargets[index].textContent = "";
    }
  }

  setValid(target, label) {
    target.classList.add("is-valid");
    label.classList.add("text-valid");
    target.classList.remove("is-invalid");
    label.classList.remove("text-error");
  }

  setInvalid(target, label) {
    target.classList.add("is-invalid");
    label.classList.add("text-error");
    target.classList.remove("is-valid");
    label.classList.remove("text-valid");
  }

  togglePasswordVisibility() {
    // Recherche tous les inputs de type password parmi les inputTargets
    const passwordFields = this.inputTargets.filter((input) =>
      input.classList.contains("password")
    );
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
