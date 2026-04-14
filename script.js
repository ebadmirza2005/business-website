const pageHeader = document.querySelector("header");
const menuToggle = document.getElementById("menuToggle");
const nav = document.getElementById("primaryNav");
const navLinks = document.querySelectorAll(".nav-menu a");
const year = document.getElementById("year");
const contactForm = document.getElementById("contactForm");
const contactMessage = document.getElementById("contactMessage");
const reducedMotionQuery = window.matchMedia("(prefers-reduced-motion: reduce)");
let revealObserver;

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
    ".detail-section > p > span, .detail-section > ul li, .detail-section > ul li span, .detail-section > span, .hero-actions > span, .section-service-bpo h2, .section-service-bpo .card, .section-service-digital h2, .section-service-digital .card, .section-packages h2, .packages-tabs, .packages-grid .package-card, .section-about .about-card, .section-contact .contact-box, .footer-details > *",
  );

  if (!revealTargets.length || reducedMotionQuery.matches) {
    return;
  }

  if (!revealObserver) {
    revealObserver = new IntersectionObserver(
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
  }

  revealTargets.forEach((target, index) => {
    if (!target.classList.contains("reveal-item")) {
      target.classList.add("reveal-item");
      target.style.setProperty("--reveal-delay", `${Math.min(index * 70, 350)}ms`);
    }

    if (!target.classList.contains("is-visible")) {
      revealObserver.observe(target);
    }
  });
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
    throw new Error(`Server error from ${url}: ${detail}`);
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

const setContactMessage = (message, isError = false) => {
  if (!contactMessage) {
    return;
  }

  contactMessage.textContent = message;
  contactMessage.style.color = isError ? "#ffc7c7" : "#d7ffdf";
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

if (contactForm) {
  contactForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const submitBtn = contactForm.querySelector("button[type='submit']");
    const name = (contactForm.elements.namedItem("name")?.value || "").trim();
    const email = (contactForm.elements.namedItem("email")?.value || "")
      .trim()
      .toLowerCase();
    const service = (contactForm.elements.namedItem("service")?.value || "").trim();
    const message = (contactForm.elements.namedItem("message")?.value || "").trim();

    if (!name || !email || !service || !message) {
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
        service,
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
      setupScrollReveal();
    } else {
      cards.forEach((card, index) => {
        if (index >= 6) {
          card.classList.remove("show");
          card.classList.add("hidden");
        }
      });

      btn.innerText = "See More";
      expanded = false;

      const targetY = Math.max(btn.getBoundingClientRect().top + window.scrollY - 180, 0);
      window.scrollTo({ top: targetY, behavior: "smooth" });
    }
  });
}

const initPackageTabs = () => {
  const packageTabButtons = document.querySelectorAll(".package-tab-btn");
  const packageGridWrappers = document.querySelectorAll(".packages-grid-wrapper");

  if (packageTabButtons.length === 0 || packageGridWrappers.length === 0) {
    return;
  }

  packageTabButtons.forEach((tabButton) => {
    tabButton.addEventListener("click", () => {
      const targetTab = tabButton.getAttribute("data-tab");
      packageTabButtons.forEach((button) => button.classList.remove("is-active"));
      packageGridWrappers.forEach((wrapper) => wrapper.classList.remove("is-active"));

      tabButton.classList.add("is-active");
      const targetWrapper = document.getElementById(targetTab);
      if (targetWrapper) {
        targetWrapper.classList.add("is-active");
        setupScrollReveal();
      }
    });
  });
};

const initPackageButtons = () => {
  const packageButtons = document.querySelectorAll(".package-btn");
  const contactSection = document.getElementById("contact");

  if (!packageButtons.length || !contactSection) {
    return;
  }

  packageButtons.forEach((packageButton) => {
    packageButton.addEventListener("click", (event) => {
      event.preventDefault();
      contactSection.scrollIntoView({ behavior: "smooth", block: "start" });
    });
  });
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    initPackageTabs();
    initPackageButtons();
  });
} else {
  initPackageTabs();
  initPackageButtons();
}


