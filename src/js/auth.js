function validatePasswords() {
  const password = document.getElementById("regPassword");
  const passwordRepeat = document.getElementById("regPasswordRepeat");

  const isPasswordValid = InputValidation.validateField(password, 8);
  const passwordsMatch = password.value === passwordRepeat.value;

  // Валидация повторного пароля
  passwordRepeat.classList.remove("is-valid", "is-invalid");
  if (passwordRepeat.value) {
    passwordRepeat.classList.add(passwordsMatch ? "is-valid" : "is-invalid");
  }

  return isPasswordValid && passwordsMatch;
}

// Registration form validation
const registrationForm = document.forms.registration;
// Real-time validation
registrationForm.login.addEventListener("input", function () {
  InputValidation.validateField(this, 5);
});
registrationForm.nickname.addEventListener("input", function () {
  InputValidation.validateField(this, 5);
});
registrationForm.email.addEventListener("input", function () {
  InputValidation.validateField(this, 5, true);
});
registrationForm.password.addEventListener("input", function () {
  InputValidation.validateField(this, 8);
  validatePasswords();
});
registrationForm.password_repeat.addEventListener("input", function () {
  validatePasswords();
});

// Create tab manager instance
const tabsOptions = {
  titleBase: "D&D Finder",
  defaultTab: "register", // default tab
  tabs: {
    login: {
      target: "login", // contents ID
      tab: "login-tab", // button ID
      hash: "login", // URL hash
      title: "Вход", // Title
    },
    register: {
      target: "register",
      tab: "register-tab",
      hash: "reg",
      title: "Регистрация",
    },
  },
};
new BootstrapTabsManager(tabsOptions);