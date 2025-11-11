document.addEventListener("DOMContentLoaded", () => {
  // Validate forbidden chars
  function hasForbiddenChars(value) {
    const forbiddenChars = ["/", "<", ">"];
    return forbiddenChars.some((char) => value.includes(char));
  }

  // Validate field function

  // WARN Code Duplication
  function validateField(field, minLength = 1) {
    const value = field.value.trim();
    const isValid = value.length >= minLength && !hasForbiddenChars(value);

    // Delete previous validation classes
    field.classList.remove("is-valid", "is-invalid");

    // Add specific class
    if (value === "") {
      return null;
    } else if (isValid) {
      field.classList.add("is-valid");
      return true;
    } else {
      field.classList.add("is-invalid");
      return false;
    }
  }

  // Validate edit form
  const editForm = document.forms.edit;
  if (editForm) {
    const editFields = {
      nickname: editForm.nickname,
      email: editForm.email,
      telegram_username: editForm.telegram_username,
      description: editForm.description,
    };

    // Add event listeners on inputs
    Object.entries(editFields).forEach(([name, field]) => {
      if (field) {
        field.addEventListener("input", function () {
          if (name === "email") {
            // Для email проверяем минимальную длину и валидность email
            const value = field.value.trim();
            const isValid =
              value.length >= 5 &&
              !hasForbiddenChars(value) &&
              /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);

            field.classList.remove("is-valid", "is-invalid");
            if (value === "") {
              return;
            } else if (isValid) {
              field.classList.add("is-valid");
            } else {
              field.classList.add("is-invalid");
            }
          } else if (name === "telegram_username") {
            const value = field.value.trim();
            const isValid = !hasForbiddenChars(value);

            field.classList.remove("is-valid", "is-invalid");
            if (value === "") {
              return;
            } else if (isValid) {
              field.classList.add("is-valid");
            } else {
              field.classList.add("is-invalid");
            }
          } else if (name === "description") {
            const value = field.value.trim();
            const isValid = !hasForbiddenChars(value);

            field.classList.remove("is-valid", "is-invalid");
            if (value === "") {
              return;
            } else if (isValid) {
              field.classList.add("is-valid");
            } else {
              field.classList.add("is-invalid");
            }
          } else {
            validateField(field, 5);
          }
        });

        if (field.value.trim() !== "") {
          field.dispatchEvent(new Event("input"));
        }
      }
    });
  }

  const securityForm = document.forms.security;
  if (securityForm) {
    const securityFields = {
      login: securityForm.querySelector('input[type="text"]'),
      new_password: securityForm.querySelector('input[name="new_password"]'),
    };

    Object.entries(securityFields).forEach(([name, field]) => {
      if (field) {
        field.addEventListener("input", function () {
          if (name === "login") {
            validateField(field, 5);
          } else if (name === "new_password") {
            validateField(field, 8);
          }
        });

        // Валидируем при загрузке страницы, если поле уже заполнено
        if (field.value.trim() !== "") {
          field.dispatchEvent(new Event("input"));
        }
      }
    });

    // Также валидируем поле текущего пароля (только на запрещенные символы)
    const currentPasswordField = securityForm.querySelector(
      'input[name="current_password"]'
    );
    if (currentPasswordField) {
      currentPasswordField.addEventListener("input", function () {
        const value = this.value.trim();
        const isValid = !hasForbiddenChars(value);

        this.classList.remove("is-valid", "is-invalid");
        if (value === "") {
          return;
        } else if (isValid) {
          this.classList.add("is-valid");
        } else {
          this.classList.add("is-invalid");
        }
      });
    }
  }

  // WARN Code Duplication

  // Handle hash after page load
  const hash = window.location.hash;
  if (hash) {
    const targetTab = document.querySelector(hash);
    if (targetTab) {
      const correspondingButton = document.querySelector(
        `button[data-bs-target="${hash}"]`
      );
      if (correspondingButton) {
        const bsTab = new bootstrap.Tab(correspondingButton);
        bsTab.show();
      }
    }
  }

  // Get button tabs
  const tabButtons = document.querySelectorAll(
    '#profileTabs button[data-bs-toggle="tab"]'
  );
  // Handle button tabs click
  tabButtons.forEach((button) => {
    button.addEventListener("click", function ()  {
      const target = this.getAttribute("data-bs-target");
      if (target) {
        window.location.hash = target;
      }
    });
  });
});
