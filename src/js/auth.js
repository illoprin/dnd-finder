document.addEventListener("DOMContentLoaded", function () {
  // Validation

  // Elements for validation
  const registrationForm = document.forms.registration;
  const loginForm = document.forms.login;
  const validationAlert = document.getElementById("validationAlert");
  const alertMessage = document.getElementById("alertMessage");

  function showAlert(message, type = "danger") {
    alertMessage.textContent = message;
    validationAlert.className = `alert alert-${type} alert-dismissible fade show`;

    // Hide alert automatically
    setTimeout(() => {
      hideAlert();
    }, 5000);
  }

  function hideAlert() {
    validationAlert.classList.add = "d-none";
  }

  // Invalid chars check function
  function hasInvalidCharacters(value) {
    const invalidChars = ["/", "<", ">"];
    return invalidChars.some((char) => value.includes(char));
  }

  // Validate email function
  function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  // Validate field function
  function validateField(field, minLength, isEmail = false) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = "";

    // Проверка минимальной длины
    if (value.length < minLength) {
      isValid = false;
      errorMessage = `Минимум ${minLength} символов`;
    }

    // Check invalid chars
    if (hasInvalidCharacters(value)) {
      isValid = false;
      errorMessage = "Запрещенные символы: /, <, >";
    }

    // Check email if it needed
    if (isEmail && !isValidEmail(value)) {
      isValid = false;
      errorMessage = "Некорректный формат email";
    }

    // Add validation styles
    field.classList.remove("is-valid", "is-invalid");
    field.classList.add(isValid ? "is-valid" : "is-invalid");

    return isValid;
  }

  function validatePasswords() {
    const password = document.getElementById("regPassword");
    const passwordRepeat = document.getElementById("regPasswordRepeat");

    const isPasswordValid = validateField(password, 8);
    const passwordsMatch = password.value === passwordRepeat.value;

    // Валидация повторного пароля
    passwordRepeat.classList.remove("is-valid", "is-invalid");
    if (passwordRepeat.value) {
      passwordRepeat.classList.add(passwordsMatch ? "is-valid" : "is-invalid");
    }

    return isPasswordValid && passwordsMatch;
  }

  // Реальная-время валидация при вводе
  document.getElementById("regLogin").addEventListener("input", function () {
    validateField(this, 5);
  });

  document.getElementById("regNickname").addEventListener("input", function () {
    validateField(this, 5);
  });

  document.getElementById("regEmail").addEventListener("input", function () {
    validateField(this, 5, true);
  });

  document.getElementById("regPassword").addEventListener("input", function () {
    validateField(this, 8);
    validatePasswords();
  });

  document.getElementById("regPasswordRepeat")
    .addEventListener("input", function () {
      validatePasswords();
    });

  // Tab controller

  // Get all tabs
  const registerTab = document.getElementById("pills-register-tab");
  const loginTab = document.getElementById("pills-login-tab");
  const registerPane = document.getElementById("pills-register");
  const loginPane = document.getElementById("pills-login");

  // Create Bootstrap Tab Object
  const tab = new bootstrap.Tab(document.getElementById("pills-register-tab"));

  // Activate tab by ID function
  function activateTab(tabId) {
    const targetTab = document.querySelector(`[data-bs-target="${tabId}"]`);
    if (targetTab) {
      const bsTab = new bootstrap.Tab(targetTab);
      bsTab.show();
    }
  }

  // Update hash function
  function updateWindow(tabName) {
    window.location.hash = `#${tabName}`;
    const baseTitle = "D&D Finder — ";
    if ((tabName = "register")) {
      document.title = baseTitle + "Регистрация";
    } else {
      document.title = baseTitle + "Вход";
    }
  }

  // Tab button handler
  registerTab.addEventListener("click", function () {
    updateWindow("register");
  });

  loginTab.addEventListener("click", function () {
    updateWindow("login");
  });

  // Обработчик события показа таба (для дополнительной безопасности)
  document.addEventListener("shown.bs.tab", function (event) {
    const activeTab = event.target;
    if (activeTab.id === "pills-register-tab") {
      updateWindow("register");
    } else if (activeTab.id === "pills-login-tab") {
      updateWindow("login");
    }
  });

  // Handle current hash
  function handleHashChange() {
    const hash = window.location.hash.substring(1); // Убираем #

    switch (hash) {
      case "login":
        activateTab("#pills-login");
        break;
      case "register":
      default:
        activateTab("#pills-register");
        break;
    }
  }

  // Listen hash change
  window.addEventListener("hashchange", handleHashChange);

  // Check hash after page load
  handleHashChange();
});
