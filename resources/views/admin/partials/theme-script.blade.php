<script>
    (function () {
        const storageKey = "adminDashboardTheme";
        const container = document.getElementById("adminDashboard");
        const toggleButton = document.getElementById("themeToggleBtn");
        const toggleIcon = document.getElementById("themeToggleIcon");
        const confirmModal = document.getElementById("confirmActionModal");
        if (!container) return;

        const applyTheme = (theme) => {
            const selected = theme === "light" ? "light" : "dark";
            container.classList.toggle("theme-light", selected === "light");
            if (confirmModal) {
                confirmModal.classList.toggle("theme-light", selected === "light");
            }
            if (toggleIcon) {
                toggleIcon.textContent = selected === "light" ? "◑" : "◐";
            }
            if (toggleButton) {
                toggleButton.title = selected === "light" ? "Switch to dark" : "Switch to light";
            }
            localStorage.setItem(storageKey, selected);
        };

        applyTheme(localStorage.getItem(storageKey) || "dark");
        if (toggleButton) {
            toggleButton.addEventListener("click", function () {
                applyTheme(container.classList.contains("theme-light") ? "dark" : "light");
            });
        }

        document.querySelectorAll("form").forEach((form) => {
            form.addEventListener("submit", (event) => {
                if (form.hasAttribute("data-confirm") && form.dataset.confirmed !== "true") {
                    // If the form needs confirmation, do not disable buttons until confirm is accepted.
                    return;
                }

                const submitButtons = form.querySelectorAll("button[type='submit']");
                submitButtons.forEach((button) => {
                    button.disabled = true;
                    button.dataset.originalText = button.textContent;
                    button.textContent = button.dataset.loadingText || "Saving...";
                });
            });
        });

        const confirmMessage = document.getElementById("confirmActionMessage");
        const confirmSubmitBtn = document.getElementById("confirmActionSubmit");
        const confirmCancelBtn = document.getElementById("confirmActionCancel");
        let pendingForm = null;

        if (confirmModal && confirmMessage && confirmSubmitBtn && confirmCancelBtn) {
            const openModal = (message) => {
                confirmMessage.textContent = message || "Are you sure to continue this action?";
                confirmModal.classList.add("is-open");
                confirmModal.setAttribute("aria-hidden", "false");
                document.body.style.overflow = "hidden";
            };

            const closeModal = () => {
                confirmModal.classList.remove("is-open");
                confirmModal.setAttribute("aria-hidden", "true");
                document.body.style.overflow = "";
                pendingForm = null;
            };

            document.querySelectorAll("form[data-confirm]").forEach((form) => {
                form.addEventListener("submit", (event) => {
                    if (form.dataset.confirmed === "true") {
                        form.dataset.confirmed = "false";
                        return;
                    }

                    event.preventDefault();
                    pendingForm = form;
                    openModal(form.dataset.confirmMessage);
                });
            });

            confirmSubmitBtn.addEventListener("click", () => {
                if (!pendingForm) {
                    closeModal();
                    return;
                }

                pendingForm.dataset.confirmed = "true";
                pendingForm.requestSubmit();
                closeModal();
            });

            confirmCancelBtn.addEventListener("click", closeModal);

            confirmModal.addEventListener("click", (event) => {
                if (event.target === confirmModal) {
                    closeModal();
                }
            });

            document.addEventListener("keydown", (event) => {
                if (event.key === "Escape" && confirmModal.classList.contains("is-open")) {
                    closeModal();
                }
            });
        }
    })();
</script>
