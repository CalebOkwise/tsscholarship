const form = document.querySelector(".application-form");
const message = document.querySelector(".form-message");
const params = new URLSearchParams(window.location.search);

if (form) {
  ["utm_source", "utm_medium", "utm_campaign", "utm_content", "utm_term"].forEach((key) => {
    const field = form.querySelector(`[name="${key}"]`);
    if (field && params.get(key)) {
      field.value = params.get(key);
    }
  });
}

if (message) {
  if (params.get("success") === "1") {
    message.textContent = "Application received. TeamSource will contact you with the next steps.";
    message.classList.add("is-success");
  }

  if (params.get("error")) {
    message.textContent = "Please check your details and try again.";
    message.classList.add("is-error");
  }
}

if (form) {
  form.addEventListener("submit", () => {
    const button = form.querySelector("button[type='submit']");
    if (button) {
      button.disabled = true;
      button.textContent = "Submitting...";
    }
  });
}
