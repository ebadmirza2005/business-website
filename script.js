const pageHeader = document.querySelector("header");
const menuToggle = document.getElementById("menuToggle");
const nav = document.getElementById("primaryNav");
const navLinks = document.querySelectorAll(".nav-menu a");
const year = document.getElementById("year");
const authModal = document.getElementById("authModal");
const openAuth = document.getElementById("openAuth");
const openAuthMenu = document.getElementById("openAuthMenu");
const closeAuth = document.getElementById("closeAuth");
const loginTab = document.getElementById("loginTab");
const signupTab = document.getElementById("signupTab");
const loginForm = document.getElementById("loginForm");
const signupForm = document.getElementById("signupForm");
const authMessage = document.getElementById("authMessage");
const navUserChip = document.getElementById("navUserChip");
const navUserName = document.getElementById("navUserName");
const navUserAvatar = document.getElementById("navUserAvatar");
const navLogoutBtn = document.getElementById("navLogoutBtn");
const modalLogoutBtn = document.getElementById("modalLogoutBtn");
const contactForm = document.getElementById("contactForm");
const contactMessage = document.getElementById("contactMessage");
const paymentModal = document.getElementById("paymentModal");
const closePayment = document.getElementById("closePayment");
const paymentForm = document.getElementById("paymentForm");
const paymentMessage = document.getElementById("paymentMessage");
const reducedMotionQuery = window.matchMedia("(prefers-reduced-motion: reduce)");

const SESSION_KEY = "fastprotech_current_user";

const getScrollTop = () =>
  window.scrollY ||
  window.pageYOffset ||
  document.documentElement.scrollTop ||
  document.body.scrollTop ||
  0;

const syncHeaderOnScroll = () => {
  if (!pageHeader) {
    return;
  }

  pageHeader.classList.toggle("is-scrolled", getScrollTop() > 2);
};

const setupScrollReveal = () => {
  const revealTargets = document.querySelectorAll(
    ".detail-section > span, .section-service-bpo h2, .section-service-bpo .card, .section-service-digital h2, .section-service-digital .card, .section-packages h2, .packages-tabs, .packages-grid .package-card, .section-about .about-card, .section-contact .contact-box, .footer-details > *",
  );

  if (!revealTargets.length || reducedMotionQuery.matches) {
    return;
  }

  revealTargets.forEach((target, index) => {
    target.classList.add("reveal-item");
    target.style.setProperty("--reveal-delay", `${Math.min(index * 70, 350)}ms`);
  });

  const observer = new IntersectionObserver(
    (entries, observerInstance) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-visible");
          observerInstance.unobserve(entry.target);
        }
      });
    },
    {
      threshold: 0.14,
      rootMargin: "0px 0px -8% 0px",
    },
  );

  revealTargets.forEach((target) => observer.observe(target));
};

if (year) {
  year.textContent = new Date().getFullYear();
}

setupScrollReveal();

syncHeaderOnScroll();
window.addEventListener("scroll", syncHeaderOnScroll, { passive: true });

if (menuToggle && nav) {
  menuToggle.addEventListener("click", () => {
    const isOpen = nav.classList.toggle("is-open");
    menuToggle.classList.toggle("is-active", isOpen);
    menuToggle.setAttribute("aria-expanded", String(isOpen));
  });

  navLinks.forEach((link) => {
    link.addEventListener("click", () => {
      nav.classList.remove("is-open");
      menuToggle.classList.remove("is-active");
      menuToggle.setAttribute("aria-expanded", "false");
    });
  });
}

const setSessionUser = (name) => {
  localStorage.setItem(SESSION_KEY, name);
};

const clearSessionUser = () => {
  localStorage.removeItem(SESSION_KEY);
};

const setUserPresentation = (name) => {
  const safeName = (name || "User").trim();
  const firstLetter = safeName.charAt(0).toUpperCase() || "U";

  if (navUserName) {
    navUserName.textContent = safeName;
  }

  if (navUserAvatar) {
    navUserAvatar.textContent = firstLetter;
  }
};

const postForm = async (url, payload) => {
  const body = new URLSearchParams(payload).toString();
  const response = await fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
      Accept: "application/json",
    },
    body,
  });

  const raw = await response.text();
  let data = null;

  try {
    data = JSON.parse(raw);
  } catch {
    const cleanMessage = raw.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
    const fallback = `HTTP ${response.status}${response.statusText ? ` ${response.statusText}` : ""}`;
    const detail = cleanMessage || raw.trim().slice(0, 220) || fallback;
    throw new Error(
      `Server error from ${url}: ${detail}`,
    );
  }

  if (!response.ok || !data.success) {
    const messageParts = [data.message || "Request failed"];
    if (data.error) {
      messageParts.push(String(data.error));
    }
    throw new Error(messageParts.join(": "));
  }

  return data;
};

const setMessage = (message, isError = false) => {
  if (!authMessage) {
    return;
  }
  authMessage.textContent = message;
  authMessage.style.color = isError ? "#ffc7c7" : "#d7ffdf";
};

const updateAuthUI = () => {
  const currentUser = localStorage.getItem(SESSION_KEY) || "";
  const normalizedUser = currentUser.trim();
  const hasValidUser = Boolean(normalizedUser && normalizedUser !== "Guest");

  if (normalizedUser === "Guest") {
    clearSessionUser();
  }

  if (openAuth) {
    openAuth.hidden = hasValidUser;
  }

  if (openAuthMenu) {
    openAuthMenu.hidden = hasValidUser;
  }

  if (navUserChip) {
    navUserChip.hidden = !hasValidUser;
  }

  if (modalLogoutBtn) {
    modalLogoutBtn.hidden = !hasValidUser;
  }

  if (hasValidUser) {
    setUserPresentation(normalizedUser);
  }

  document.body.classList.toggle("is-authenticated", hasValidUser);
};

const switchTab = (target) => {
  const showLogin = target === "login";

  if (loginTab) {
    loginTab.classList.toggle("is-active", showLogin);
    loginTab.setAttribute("aria-selected", String(showLogin));
  }

  if (signupTab) {
    signupTab.classList.toggle("is-active", !showLogin);
    signupTab.setAttribute("aria-selected", String(!showLogin));
  }

  if (loginForm) {
    loginForm.classList.toggle("is-active", showLogin);
  }

  if (signupForm) {
    signupForm.classList.toggle("is-active", !showLogin);
  }

  setMessage("");
};

const openModal = (targetTab = "login") => {
  if (!authModal) {
    return;
  }

  authModal.classList.add("is-open");
  authModal.setAttribute("aria-hidden", "false");
  document.body.classList.add("is-loading");
  switchTab(targetTab);
};

const closeModal = () => {
  if (!authModal) {
    return;
  }

  authModal.classList.remove("is-open");
  authModal.setAttribute("aria-hidden", "true");
  document.body.classList.remove("is-loading");
  setMessage("");
};

if (openAuth) {
  openAuth.addEventListener("click", () => openModal("login"));
}

if (openAuthMenu) {
  openAuthMenu.addEventListener("click", () => {
    openModal("login");
    nav?.classList.remove("is-open");
    menuToggle?.classList.remove("is-active");
    menuToggle?.setAttribute("aria-expanded", "false");
  });
}

if (closeAuth) {
  closeAuth.addEventListener("click", closeModal);
}

if (authModal) {
  authModal.addEventListener("click", (event) => {
    if (event.target === authModal) {
      closeModal();
    }
  });
}

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && authModal?.classList.contains("is-open")) {
    closeModal();
  }
});

if (loginTab) {
  loginTab.addEventListener("click", () => switchTab("login"));
}

if (signupTab) {
  signupTab.addEventListener("click", () => switchTab("signup"));
}

if (signupForm) {
  signupForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const name = document.getElementById("signupName")?.value.trim();
    const email = document
      .getElementById("signupEmail")
      ?.value.trim()
      .toLowerCase();
    const password = document.getElementById("signupPassword")?.value || "";

    if (!name || !email || password.length < 6) {
      setMessage(
        "Please fill all fields with a valid password (min 6 chars).",
        true,
      );
      return;
    }

    try {
      const result = await postForm("signup.php", {
        username: name,
        email,
        password,
      });

      setSessionUser(result.username || name);
      updateAuthUI();
      signupForm.reset();
      setMessage(result.message || "Account created successfully.");
      window.setTimeout(closeModal, 700);
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Signup failed. Please try again.";
      if (message.toLowerCase().includes("already")) {
        switchTab("signup");
        setMessage("This email is already registered. Please log in.", true);
        return;
      }

      setMessage(message, true);
    }
  });
}

if (loginForm) {
  loginForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const email = document
      .getElementById("loginEmail")
      ?.value.trim()
      .toLowerCase();
    const password = document.getElementById("loginPassword")?.value || "";
    try {
      const result = await postForm("login.php", { email, password });
      setSessionUser(result.username || "User");
      updateAuthUI();
      loginForm.reset();
      setMessage(result.message || "Login successful. Welcome back.");
      window.setTimeout(closeModal, 650);
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Invalid email or password.";
      setMessage(message, true);
    }
  });
}

const setContactMessage = (message, isError = false) => {
  if (!contactMessage) {
    return;
  }

  contactMessage.textContent = message;
  contactMessage.style.color = isError ? "#ffc7c7" : "#d7ffdf";
};

if (contactForm) {
  contactForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const submitBtn = contactForm.querySelector("button[type='submit']");
    const name = (contactForm.elements.namedItem("name")?.value || "").trim();
    const email = (contactForm.elements.namedItem("email")?.value || "").trim().toLowerCase();
    const message = (contactForm.elements.namedItem("message")?.value || "").trim();

    if (!name || !email || !message) {
      setContactMessage("Please fill in all fields before sending.", true);
      return;
    }

    try {
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = "Sending...";
      }

      const result = await postForm("send_request.php", {
        name,
        email,
        message,
      });

      setContactMessage(result.message || "Your request has been sent successfully.");
      contactForm.reset();
    } catch (error) {
      const errorMessage =
        error instanceof Error ? error.message : "Could not send your request. Please try again.";
      setContactMessage(errorMessage, true);
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = "Send Request";
      }
    }
  });
}

const performLogout = () => {
  clearSessionUser();
  if (navUserChip) {
    navUserChip.hidden = true;
  }
  if (modalLogoutBtn) {
    modalLogoutBtn.hidden = true;
  }
  if (openAuthMenu) {
    openAuthMenu.hidden = false;
  }
  updateAuthUI();
  nav?.classList.remove("is-open");
  menuToggle?.classList.remove("is-active");
  menuToggle?.setAttribute("aria-expanded", "false");
  setMessage("You have been logged out.");
};

if (navLogoutBtn) {
  navLogoutBtn.addEventListener("click", performLogout);
}

if (modalLogoutBtn) {
  modalLogoutBtn.addEventListener("click", () => {
    performLogout();
    closeModal();
  });
}

updateAuthUI();

const canvas = document.getElementById("web");
if (canvas) {
  canvas.style.display = "none";
}

const btn = document.getElementById("seeMoreBtn");
let expanded = false;

if (btn) {
  btn.addEventListener("click", () => {
    const cards = document.querySelectorAll(".section-service-bpo .service-card > .card");

    if (!expanded) {
      cards.forEach((card) => {
        if (card.classList.contains("hidden")) {
          card.classList.remove("hidden");
          card.classList.add("show");
        }
      });

      btn.innerText = "See Less";
      expanded = true;
    } else {
      cards.forEach((card, index) => {
        if (index >= 6) {
          card.classList.remove("show");
          card.classList.add("hidden");
        }
      });

      btn.innerText = "See More";
      expanded = false;

      const targetY = Math.max(
        btn.getBoundingClientRect().top + window.scrollY - 180,
        0,
      );
      window.scrollTo({ top: targetY, behavior: "smooth" });
    }
  });
}

// Package Tab Switching
const initPackageTabs = () => {
  const packageTabButtons = document.querySelectorAll(".package-tab-btn");
  const packageGridWrappers = document.querySelectorAll(".packages-grid-wrapper");

  if (packageTabButtons.length === 0 || packageGridWrappers.length === 0) {
    console.warn("Package tabs or wrappers not found");
    return;
  }

  packageTabButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      const targetTab = btn.getAttribute("data-tab");

      // Remove active class from all buttons and wrappers
      packageTabButtons.forEach((button) => button.classList.remove("is-active"));
      packageGridWrappers.forEach((wrapper) => wrapper.classList.remove("is-active"));

      // Add active class to clicked button and corresponding wrapper
      btn.classList.add("is-active");
      const targetWrapper = document.getElementById(targetTab);
      if (targetWrapper) {
        targetWrapper.classList.add("is-active");
      }
    });
  });
};

// Initialize package tabs when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initPackageTabs);
} else {
  initPackageTabs();
}

// Payment Integration
let stripe;
let elements;
let cardElement;

// Initialize Stripe Elements (call this after Stripe is loaded)
const initStripeElements = () => {
  if (!stripe) {
    console.error('Stripe not initialized. Please check your public key.');
    return false;
  }

  try {
    elements = stripe.elements();
    cardElement = elements.create('card', {
      style: {
        base: {
          color: '#e8f6ff',
          fontFamily: '"Manrope", sans-serif',
          fontSize: '16px',
          '::placeholder': { color: '#9fb8c8' }
        }
      }
    });
    cardElement.mount('#card-element');

    cardElement.addEventListener('change', (event) => {
      const displayError = document.getElementById('card-errors');
      if (event.error) {
        displayError.textContent = event.error.message;
      } else {
        displayError.textContent = '';
      }
    });

    console.log('✓ Stripe card element mounted successfully');
    return true;
  } catch (error) {
    console.error('Failed to mount card element:', error);
    return false;
  }
};

// Initialize Stripe with public key from backend
const initializeStripe = async () => {
  try {
    const response = await fetch('config-frontend.php');
    const config = await response.json();
    
    if (!config.stripePublicKey || config.stripePublicKey.includes('your_public_key')) {
      console.error('❌ Stripe public key not configured. Please update .env file with STRIPE_PUBLIC_KEY');
      alert('Payment system not configured. Please contact admin.');
      return false;
    }
    
    if (typeof Stripe === 'undefined') {
      console.error('❌ Stripe.js library not loaded. Check if script tag is present.');
      return false;
    }

    stripe = Stripe(config.stripePublicKey);
    console.log('✓ Stripe initialized with public key: ' + config.stripePublicKey.substring(0, 10) + '...');

    // Now initialize the card element
    initStripeElements();
    return true;
  } catch (error) {
    console.error('Failed to initialize Stripe:', error);
    return false;
  }
};

// Call initialization when DOM is ready
const setupPaymentSystem = () => {
  console.log('Setting up payment system...');
  initializeStripe();
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', setupPaymentSystem);
} else {
  setupPaymentSystem();
}

const openPaymentModal = (packageName, packageAmount, packageType) => {
  if (!paymentModal) return;

  document.getElementById('paymentPackage').textContent = packageName;
  document.getElementById('paymentAmount').textContent = '$' + (packageAmount / 100).toFixed(2);
  document.getElementById('paymentType').textContent = packageType.charAt(0).toUpperCase() + packageType.slice(1);
  
  paymentModal.classList.add('is-open');
  paymentModal.setAttribute('aria-hidden', 'false');
  document.body.classList.add('is-loading');

  // Card element should be ready (initialized on page load)
  if (!cardElement) {
    console.warn('Card element not initialized. Attempting to initialize now...');
    initStripeElements();
  }
};

const closePaymentModal = () => {
  if (!paymentModal) return;

  paymentModal.classList.remove('is-open');
  paymentModal.setAttribute('aria-hidden', 'true');
  document.body.classList.remove('is-loading');
  
  if (paymentMessage) {
    paymentMessage.textContent = '';
  }
};

if (closePayment) {
  closePayment.addEventListener('click', closePaymentModal);
}

if (paymentModal) {
  paymentModal.addEventListener('click', (event) => {
    if (event.target === paymentModal) {
      closePaymentModal();
    }
  });
}

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && paymentModal?.classList.contains('is-open')) {
    closePaymentModal();
  }
});

if (paymentForm) {
  paymentForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    const packageName = document.getElementById('paymentPackage').textContent;
    const packageAmount = parseFloat(document.getElementById('paymentAmount').textContent.replace('$', '')) * 100;
    const packageType = document.getElementById('paymentType').textContent.toLowerCase();
    const email = document.getElementById('paymentEmail').value.trim();

    if (!email) {
      setPaymentMessage('Please enter your email address', true);
      return;
    }

    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      setPaymentMessage('Please enter a valid email address', true);
      return;
    }

    const submitBtn = paymentForm.querySelector('button[type="submit"]');
    if (submitBtn) {
      submitBtn.disabled = true;
      document.getElementById('paymentSubmitText').textContent = 'Processing...';
    }

    try {
      // Validate card element exists and has data
      if (!cardElement) {
        throw new Error('Payment form not initialized. Please refresh and try again.');
      }

      // Send payment to backend
      const response = await fetch('payment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          packageName,
          amount: Math.round(packageAmount),
          email,
          packageType
        })
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Payment failed');
      }

      if (!data.success) {
        throw new Error(data.message || 'Unable to create checkout session');
      }

      if (!data.sessionUrl) {
        throw new Error('Invalid checkout session');
      }

      // Redirect to Stripe Checkout
      window.location.href = data.sessionUrl;

    } catch (error) {
      console.error('Payment Error:', error);
      
      let errorMessage = 'Payment processing failed. Please try again.';
      
      if (error instanceof TypeError) {
        errorMessage = 'Network error. Please check your connection and try again.';
      } else if (error instanceof Error) {
        errorMessage = error.message;
      }

      // Special error handling for common issues
      if (errorMessage.toLowerCase().includes('insufficient')) {
        errorMessage = 'Insufficient funds. Please use a different payment method.';
      } else if (errorMessage.toLowerCase().includes('declined')) {
        errorMessage = 'Your card was declined. Please use a different card or contact your bank.';
      } else if (errorMessage.toLowerCase().includes('expired')) {
        errorMessage = 'Your card has expired. Please use a different card.';
      }

      setPaymentMessage(errorMessage, true);
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
        document.getElementById('paymentSubmitText').textContent = 'Pay Now';
      }
    }
  });
}

const setPaymentMessage = (message, isError = false) => {
  if (!paymentMessage) return;
  paymentMessage.textContent = message;
  paymentMessage.style.color = isError ? '#ffc7c7' : '#d7ffdf';
};

// Handle "Get Started" buttons
const handleGetStarted = () => {
  const getStartedBtns = document.querySelectorAll('.package-btn');
  
  getStartedBtns.forEach((btn) => {
    btn.addEventListener('click', (event) => {
      event.preventDefault();
      
      const card = btn.closest('.package-card');
      if (!card) return;

      const packageName = card.querySelector('.package-header h3').textContent;
      const priceText = card.querySelector('.price').textContent;
      const packageAmount = Math.round(parseFloat(priceText.replace('$', '')) * 100);
      const activeTab = document.querySelector('.package-tab-btn.is-active');
      const packageType = activeTab ? activeTab.getAttribute('data-tab').replace('-', ' ') : 'unknown';

      // Check if it's a contact sales button
      if (btn.textContent.includes('Contact Sales')) {
        document.getElementById('contactForm').scrollIntoView({ behavior: 'smooth' });
        return;
      }

      openPaymentModal(packageName, packageAmount, packageType);
    });
  });
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', handleGetStarted);
} else {
  handleGetStarted();
}

// Test Payment Button (500 Rs)
const testPaymentBtn = document.getElementById('testPaymentBtn');
const testPaymentSection = document.getElementById('testPaymentSection');

// Show test payment button only in development/localhost
if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
  if (testPaymentSection) {
    testPaymentSection.style.display = 'block';
  }
}

if (testPaymentBtn) {
  testPaymentBtn.addEventListener('click', () => {
    // 5000 in smallest currency unit (50 rupees in paise or $50 USD)
    const testAmount = 5000; // 50 rupees/dollars in smallest unit
    openPaymentModal('Test Payment 50 Rs', testAmount, 'test');
  });
}


