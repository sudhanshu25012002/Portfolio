/**
 * ============================================================================
 * SUDHANSHU RANJAN — PORTFOLIO 2026
 * Retro-Artisan Workstation Engine
 * Controls: Taskbar Clock, Direct Connect Modal, Form Validation & AJAX Dispatch
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', () => {

  // ==========================================================================
  // 1. Live Real-Time Taskbar Clock
  // ==========================================================================
  const taskbarClock = document.getElementById('taskbarClock');

  function updateClocks() {
    try {
      const now = new Date();
      const options = {
        timeZone: 'Asia/Kolkata',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
      };
      const formatted = new Intl.DateTimeFormat('en-US', options).format(now);
      if (taskbarClock) taskbarClock.textContent = formatted;
    } catch (e) {
      const d = new Date();
      const timeStr = d.toTimeString().split(' ')[0];
      if (taskbarClock) taskbarClock.textContent = timeStr;
    }
  }
  updateClocks();
  setInterval(updateClocks, 1000);

  // ==========================================================================
  // 2. Retro Toast Notification Helper
  // ==========================================================================
  const toastBox = document.getElementById('retroToast');
  const toastMessage = document.getElementById('toastMessage');
  let toastTimer = null;

  function showToast(message, duration = 3000) {
    if (!toastBox || !toastMessage) return;
    toastMessage.textContent = message;
    toastBox.classList.add('show');

    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
      toastBox.classList.remove('show');
    }, duration);
  }

  // ==========================================================================
  // 3. Direct Connect Modal Logic (Open / Close)
  // ==========================================================================
  const modalBackdrop = document.getElementById('connectModal');
  const openModalBtn = document.getElementById('openConnectModalBtn');
  const taskbarStartBtn = document.getElementById('taskbarStartBtn');
  const closeModalXBtn = document.getElementById('modalCloseXBtn');
  const cancelModalBtn = document.getElementById('btnCancelModal');
  const directForm = document.getElementById('directConnectForm');
  const modalAlertBox = document.getElementById('modalAlertBox');

  function openModal() {
    if (!modalBackdrop) return;
    modalBackdrop.classList.add('open');
    modalBackdrop.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    // Security: Record open timestamp for time-lock protection
    const tsInput = document.getElementById('formTimestamp');
    if (tsInput && (!tsInput.value || tsInput.value === '')) {
      tsInput.value = Date.now();
    }

    // Focus the first input
    const firstInput = document.getElementById('inputName');
    if (firstInput) {
      setTimeout(() => firstInput.focus(), 150);
    }
  }

  function closeModal() {
    if (!modalBackdrop) return;
    modalBackdrop.classList.remove('open');
    modalBackdrop.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    clearValidationErrors();
  }

  // Open triggers
  if (openModalBtn) openModalBtn.addEventListener('click', openModal);
  if (taskbarStartBtn) taskbarStartBtn.addEventListener('click', openModal);

  // Close triggers
  if (closeModalXBtn) closeModalXBtn.addEventListener('click', closeModal);
  if (cancelModalBtn) cancelModalBtn.addEventListener('click', closeModal);

  // Close on outside backdrop click
  if (modalBackdrop) {
    modalBackdrop.addEventListener('click', (e) => {
      if (e.target === modalBackdrop) {
        closeModal();
      }
    });
  }

  // Close on Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modalBackdrop && modalBackdrop.classList.contains('open')) {
      closeModal();
    }
  });

  // ==========================================================================
  // 4. Form Validation & AJAX Transmission to contact.php
  // ==========================================================================
  const nameInput = document.getElementById('inputName');
  const emailInput = document.getElementById('inputEmail');
  const phoneInput = document.getElementById('inputPhone');
  const briefInput = document.getElementById('inputBrief');

  const nameError = document.getElementById('nameError');
  const emailError = document.getElementById('emailError');
  const phoneError = document.getElementById('phoneError');
  const briefError = document.getElementById('briefError');

  const submitBtn = document.getElementById('btnSubmitModal');
  const submitText = document.getElementById('btnSubmitText');

  function clearValidationErrors() {
    if (nameError) nameError.textContent = '';
    if (emailError) emailError.textContent = '';
    if (phoneError) phoneError.textContent = '';
    if (briefError) briefError.textContent = '';
    if (modalAlertBox) {
      modalAlertBox.style.display = 'none';
      modalAlertBox.className = 'retro-alert-box';
      modalAlertBox.textContent = '';
    }
  }

  // Real-time error clearing when user types
  [nameInput, emailInput, phoneInput, briefInput].forEach(input => {
    if (input) {
      input.addEventListener('input', () => {
        const errSpan = document.getElementById(`${input.name}Error`);
        if (errSpan) errSpan.textContent = '';
      });
    }
  });

  function validateForm() {
    let isValid = true;
    clearValidationErrors();

    const nameVal = nameInput ? nameInput.value.trim() : '';
    const emailVal = emailInput ? emailInput.value.trim() : '';
    const phoneVal = phoneInput ? phoneInput.value.trim() : '';
    const briefVal = briefInput ? briefInput.value.trim() : '';

    // Name Validation
    if (!nameVal) {
      if (nameError) nameError.textContent = 'Full name is required.';
      isValid = false;
    } else if (nameVal.length < 2) {
      if (nameError) nameError.textContent = 'Name must be at least 2 characters.';
      isValid = false;
    }

    // Email Validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailVal) {
      if (emailError) emailError.textContent = 'Email address is required.';
      isValid = false;
    } else if (!emailRegex.test(emailVal)) {
      if (emailError) emailError.textContent = 'Enter a valid email address.';
      isValid = false;
    }

    // Phone Validation
    const phoneRegex = /^[0-9+\s\-()]{7,20}$/;
    if (!phoneVal) {
      if (phoneError) phoneError.textContent = 'Phone number is required.';
      isValid = false;
    } else if (!phoneRegex.test(phoneVal)) {
      if (phoneError) phoneError.textContent = 'Enter a valid phone number.';
      isValid = false;
    }

    // Brief / Message Validation
    if (!briefVal) {
      if (briefError) briefError.textContent = 'Project brief or message is required.';
      isValid = false;
    } else if (briefVal.length < 5) {
      if (briefError) briefError.textContent = 'Please provide at least 5 characters.';
      isValid = false;
    }

    return isValid;
  }

  function setModalAlert(message, type = 'error') {
    if (!modalAlertBox) return;
    modalAlertBox.textContent = message;
    modalAlertBox.className = `retro-alert-box ${type}`;
    modalAlertBox.style.display = 'block';
  }

  if (directForm) {
    directForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      if (!validateForm()) {
        setModalAlert('Please fill in the required fields correctly.', 'error');
        return;
      }

      // Prepare UI for sending
      submitBtn.disabled = true;
      const originalText = submitText ? submitText.textContent : 'Connect';
      if (submitText) submitText.textContent = 'Connecting...';

      try {
        const formData = new FormData(directForm);

        const response = await fetch('contact.php', {
          method: 'POST',
          body: formData
        });

        const data = await response.json();

        if (response.ok && data.status === 'success') {
          setModalAlert('✓ Transmission successful! Sudhanshu will get in touch shortly.', 'success');
          showToast('MESSAGE DISPATCHED');
          directForm.reset();

          setTimeout(() => {
            closeModal();
          }, 2200);

        } else {
          const errMsg = data.message || 'Transmission failed. Please try again.';
          setModalAlert(errMsg, 'error');
        }

      } catch (err) {
        setModalAlert('Network transmission error. Please check your connection.', 'error');
      } finally {
        submitBtn.disabled = false;
        if (submitText) submitText.textContent = originalText;
      }
    });
  }

  // ==========================================================================
  // 5. Smooth Scrolling for Page Anchors
  // ==========================================================================
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      const targetId = this.getAttribute('href');
      if (targetId === '#' || targetId === '') return;
      const targetEl = document.querySelector(targetId);
      if (targetEl) {
        e.preventDefault();
        targetEl.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });

});
