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
const contactForm = document.getElementById("contactForm");
const contactMessage = document.getElementById("contactMessage");
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
    ".detail-section > span, .section-service-bpo h2, .section-service-bpo .card, .section-service-digital h2, .section-service-digital .card, .section-about .about-card, .section-contact .contact-box, .footer-details > *",
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
    throw new Error(
      cleanMessage || "Server returned an invalid response. Please try again.",
    );
  }

  if (!response.ok || !data.success) {
    throw new Error(data.message || "Request failed");
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
  const currentUser = localStorage.getItem(SESSION_KEY);
  const hasValidUser = Boolean(currentUser && currentUser !== "Guest");

  if (currentUser === "Guest") {
    clearSessionUser();
  }

  if (openAuth) {
    openAuth.hidden = hasValidUser;
  }

  if (openAuthMenu) {
    openAuthMenu.hidden = hasValidUser;
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

