// Edit form validation
const editForm = document.forms.edit;
const editFormAlert = document.querySelector("#editFormAlert");
editForm.nickname.addEventListener("input", function() {
  InputValidation.validateField(this, 5);
});
editForm.email.addEventListener("input", function() {
  InputValidation.validateField(this, 5, true);
});
editForm.telegram_username.addEventListener("input", function() {
  InputValidation.validateField(this, 1);
});
editForm.description.addEventListener("input", function() {
  InputValidation.validateField(this, 10);
});
editForm.addEventListener("change", () => {
  editFormAlert.classList.remove("d-none");
});

// Security form validation
const securityForm = document.forms.security;
const securityFormAlert = document.querySelector("#securityFormAlert");
securityForm.login.addEventListener("input", function()  {
  InputValidation.validateField(this, 5);
});
securityForm.new_password.addEventListener("input", function()  {
  InputValidation.validateField(this, 8);
});
securityForm.current_password.addEventListener("input", function()  {
  InputValidation.validateField(this, 8);
});
securityForm.addEventListener("change", () => {
  isProfileFormEdited = true;
  securityFormAlert.classList.remove("d-none");
});

// Tabs handler
tabOptions = {
  titleBase: "D&D Finder",
  defaultTab: "edit",
  tabs: {
    edit: {
      target: "edit",
      tab: "edit-tab",
      hash: "edit",
      title: "Личный кабинет - Редактировать профиль",
    },
    security: {
      target: "security",
      tab: "security-tab",
      hash: "security",
      title: "Личный кабинет - Редактировать профиль",
    },
    apps: {
      target: "apps",
      tab: "apps-tab",
      hash: "apps",
      title: "Личный кабинет - Заявки",
    },
    favorites: {
      target: "favorites",
      tab: "favorites-tab",
      hash: "favorites",
      title: "Личный кабинет - Редактировать профиль",
    },
  },
};
new BootstrapTabsManager(tabOptions);
