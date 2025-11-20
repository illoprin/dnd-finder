class InputValidation {
  // Invalid chars check function
  static hasInvalidCharacters(value) {
    const invalidChars = ["/", "<", ">"];
    return invalidChars.some((char) => value.includes(char));
  }

  /**
   * Validate email by regex
   * @param {string} email
   * @returns {boolean} is email valid
   */
  static isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  /**
   * Validates HTML Input field by:
   * - length
   * - invalid characters (/, <, >)
   * - email validation
   * @param {HTMLInputElement} field
   * @param {number} minLength
   * @param {boolean} isEmail
   * @returns {boolean}
   */
  static validateField(field, minLength, isEmail = false) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = "";

    // Проверка минимальной длины
    if (value.length < minLength) {
      isValid = false;
      errorMessage = `Минимум ${minLength} символов`;
    }

    // Check invalid chars
    if (this.hasInvalidCharacters(value)) {
      isValid = false;
      errorMessage = "Запрещенные символы: /, <, >";
    }

    // Check email if it needed
    if (isEmail && !this.isValidEmail(value)) {
      isValid = false;
      errorMessage = "Некорректный формат email";
    }

    // Add validation styles
    field.classList.remove("is-valid", "is-invalid");
    field.classList.add(isValid ? "is-valid" : "is-invalid");

    return {
      isValid,
      errorMessage,
    };
  }
}