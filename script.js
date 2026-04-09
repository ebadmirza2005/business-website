const loader = document.getElementById("pageLoader");
let aosInitialized = false;
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
const userChip = document.getElementById("userChip");
const navUserChip = document.getElementById("navUserChip");
const userName = document.getElementById("userName");
const navUserName = document.getElementById("navUserName");
const userAvatar = document.getElementById("userAvatar");
const navUserAvatar = document.getElementById("navUserAvatar");
const logoutBtn = document.getElementById("logoutBtn");
const navLogoutBtn = document.getElementById("navLogoutBtn");
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

const initAos = () => {
  if (aosInitialized || !window.AOS) {
    return;
  }

  window.AOS.init({
    duration: 700,
    easing: "ease-out-cubic",
    once: true,
    offset: 84,
  });
  aosInitialized = true;
};

if (loader) {
  document.body.classList.add("is-loading");
  let loaderHidden = false;

  const hideLoader = () => {
    if (loaderHidden) {
      return;
    }
    loaderHidden = true;
    loader.classList.add("is-hidden");
    document.body.classList.remove("is-loading");
    initAos();
    window.AOS?.refreshHard();
  };

  window.addEventListener("load", () => {
    window.setTimeout(hideLoader, 1000);
  });

  window.setTimeout(hideLoader, 5000);
} else {
  initAos();
}

if (year) {
  year.textContent = new Date().getFullYear();
}

syncHeaderOnScroll();
window.addEventListener("scroll", syncHeaderOnScroll, { passive: true });
window.addEventListener("load", syncHeaderOnScroll);
window.addEventListener("resize", syncHeaderOnScroll);

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

document.addEventListener("click", (event) => {
  const anchor = event.target instanceof Element ? event.target.closest('a[href^="#"]') : null;

  if (!anchor) {
    return;
  }

  const targetId = anchor.getAttribute("href")?.slice(1);
  if (!targetId) {
    return;
  }

  const targetElement = document.getElementById(targetId);
  if (!targetElement) {
    return;
  }

  event.preventDefault();
  targetElement.scrollIntoView({ behavior: "smooth", block: "start" });
  history.replaceState(null, "", `#${targetId}`);
});

const setSessionUser = (name) => {
  localStorage.setItem(SESSION_KEY, name);
};

const clearSessionUser = () => {
  localStorage.removeItem(SESSION_KEY);
};

const setUserPresentation = (name) => {
  const safeName = (name || "User").trim();
  const firstLetter = safeName.charAt(0).toUpperCase() || "U";

  if (userName) {
    userName.textContent = safeName;
  }
  if (navUserName) {
    navUserName.textContent = safeName;
  }
  if (userAvatar) {
    userAvatar.textContent = firstLetter;
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

  if (userChip) {
    userChip.hidden = !hasValidUser;
  }

  if (navUserChip) {
    navUserChip.hidden = !hasValidUser;
  }

  if (hasValidUser) {
    setUserPresentation(currentUser);
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

if (logoutBtn) {
  logoutBtn.addEventListener("click", () => {
    clearSessionUser();
    updateAuthUI();
    setMessage("You have been logged out.");
  });
}

if (navLogoutBtn) {
  navLogoutBtn.addEventListener("click", () => {
    clearSessionUser();
    updateAuthUI();
    nav?.classList.remove("is-open");
    menuToggle?.classList.remove("is-active");
    menuToggle?.setAttribute("aria-expanded", "false");
    setMessage("You have been logged out.");
  });
}

updateAuthUI();

const canvas = document.getElementById("web");

if (canvas && !reducedMotionQuery.matches) {
  const ctx = canvas.getContext("2d");
  const particles = [];
  const viewportArea = window.innerWidth * window.innerHeight;
  const particleCount = Math.min(44, Math.max(24, Math.floor(viewportArea / 36000)));
  const linkDistance = 110;

  if (ctx) {
    let rafId = 0;
    let canvasVisible = true;

    const clampToCanvas = (value, max) => Math.min(Math.max(value, 0), max);

    const setCanvasSize = () => {
      const ratio = window.devicePixelRatio || 1;
      const rect = canvas.getBoundingClientRect();
      const width = Math.max(Math.floor(rect.width), 1);
      const height = Math.max(Math.floor(rect.height), 1);

      canvas.width = Math.floor(width * ratio);
      canvas.height = Math.floor(height * ratio);
      ctx.setTransform(ratio, 0, 0, ratio, 0, 0);

      particles.forEach((particle) => {
        particle.x = clampToCanvas(particle.x, width);
        particle.y = clampToCanvas(particle.y, height);
      });
    };

    class Particle {
      constructor() {
        this.reset();
      }

      reset() {
        this.x = Math.random() * canvas.clientWidth;
        this.y = Math.random() * canvas.clientHeight;
        this.vx = Math.random() * 0.8 - 0.4;
        this.vy = Math.random() * 0.8 - 0.4;
      }

      move() {
        const width = canvas.clientWidth;
        const height = canvas.clientHeight;

        this.x += this.vx;
        this.y += this.vy;

        if (this.x <= 0 || this.x >= width) {
          this.vx *= -1;
          this.x = clampToCanvas(this.x, width);
        }

        if (this.y <= 0 || this.y >= height) {
          this.vy *= -1;
          this.y = clampToCanvas(this.y, height);
        }
      }

      draw() {
        ctx.beginPath();
        ctx.arc(this.x, this.y, 1.8, 0, Math.PI * 2);
        ctx.fillStyle = "rgba(255, 240, 214, 0.95)";
        ctx.fill();
      }
    }

    for (let i = 0; i < particleCount; i += 1) {
      particles.push(new Particle());
    }

    const drawConnections = () => {
      for (let i = 0; i < particles.length; i += 1) {
        for (let j = i + 1; j < particles.length; j += 1) {
          const dx = particles[i].x - particles[j].x;
          const dy = particles[i].y - particles[j].y;
          const distance = Math.sqrt(dx * dx + dy * dy);

          if (distance < linkDistance) {
            const alpha = 1 - distance / linkDistance;
            ctx.beginPath();
            ctx.moveTo(particles[i].x, particles[i].y);
            ctx.lineTo(particles[j].x, particles[j].y);
            ctx.strokeStyle = `rgba(164, 220, 255, ${0.42 * alpha})`;
            ctx.lineWidth = 0.9;
            ctx.stroke();
          }
        }
      }
    };

    const animate = () => {
      ctx.clearRect(0, 0, canvas.clientWidth, canvas.clientHeight);

      particles.forEach((particle) => {
        particle.move();
        particle.draw();
      });

      drawConnections();
      rafId = requestAnimationFrame(animate);
    };

    const startAnimation = () => {
      if (!rafId && canvasVisible) {
        animate();
      }
    };

    const stopAnimation = () => {
      if (rafId) {
        cancelAnimationFrame(rafId);
        rafId = 0;
      }
    };

    setCanvasSize();
    startAnimation();

    if (window.IntersectionObserver) {
      const heroObserver = new IntersectionObserver(
        (entries) => {
          const [entry] = entries;
          canvasVisible = Boolean(entry?.isIntersecting);

          if (canvasVisible) {
            startAnimation();
          } else {
            stopAnimation();
          }
        },
        {
          threshold: 0.08,
        },
      );

      const heroSection = document.getElementById("home");
      if (heroSection) {
        heroObserver.observe(heroSection);
      }
    }

    let resizeFrame = 0;
    const resizeHandler = () => {
      if (resizeFrame) {
        return;
      }

      resizeFrame = requestAnimationFrame(() => {
        resizeFrame = 0;
        setCanvasSize();
      });
    };
    window.addEventListener("resize", resizeHandler);

    document.addEventListener("visibilitychange", () => {
      if (document.hidden) {
        stopAnimation();
      } else {
        startAnimation();
      }
    });

    if (window.ResizeObserver) {
      const observer = new ResizeObserver(() => setCanvasSize());
      observer.observe(canvas);
    }
  }
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

const digitalCards = document.querySelectorAll(".section-service-digital .card");

if (digitalCards.length) {
  digitalCards.forEach((card) => {
    card.addEventListener("click", () => {
      const shouldFlip = !card.classList.contains("is-flipped");

      digitalCards.forEach((item) => item.classList.remove("is-flipped"));

      if (shouldFlip) {
        card.classList.add("is-flipped");
      }
    });
  });
}

const packageMatrix = {
  web: {
    starter: [
      "Starter - $299: 5-page responsive website",
      "Mobile-friendly layout with contact form",
      "Basic on-page SEO setup",
    ],
    business: [
      "Business - $599: 10-page site + booking form",
      "Speed optimization and lead capture sections",
      "Google indexing plus analytics setup",
    ],
    pro: [
      "Pro - $999: Custom CMS + complete SEO setup",
      "Advanced animations and conversion-focused pages",
      "Priority support and monthly performance report",
    ],
  },
  app: {
    starter: [
      "Starter - $699: Single-platform app (Android or iOS)",
      "Clean UI with 4 to 6 screens",
      "Play Store/App Store basic guidance",
    ],
    business: [
      "Business - $1,199: Cross-platform app + API integration",
      "Auth, dashboard and push notification setup",
      "Performance tuning and QA testing",
    ],
    pro: [
      "Pro - $1,999: Full app + admin panel + deployment",
      "Scalable architecture with advanced analytics",
      "3 months priority maintenance support",
    ],
  },
  logo: {
    starter: [
      "Starter - $79: 2 logo concepts + PNG/JPG files",
      "Brand color suggestions and typography pair",
      "2 revision rounds included",
    ],
    business: [
      "Business - $149: 4 concepts + 3 revisions",
      "Social profile kit and transparent assets",
      "Source file in AI/EPS formats",
    ],
    pro: [
      "Pro - $249: Complete logo kit + brand guide",
      "Mockups for web, print and packaging",
      "Priority delivery and strategy call",
    ],
  },
  graphic: {
    starter: [
      "Starter - $129: 10 social media posts",
      "Branded layouts for FB and Instagram",
      "Delivery in editable and export formats",
    ],
    business: [
      "Business - $249: Brochure + banner + post set",
      "Campaign-focused creative direction",
      "3 revision rounds for final polish",
    ],
    pro: [
      "Pro - $399: Monthly creative design support",
      "Ad creatives, carousel kits and promo visuals",
      "Dedicated designer with quick turnaround",
    ],
  },
  marketing: {
    starter: [
      "Starter - $199: Social media setup + weekly posts",
      "Profile optimization and content calendar",
      "Basic growth tracking report",
    ],
    business: [
      "Business - $399: Ads management + lead tracking",
      "Audience targeting and conversion creatives",
      "Weekly optimization with KPI reports",
    ],
    pro: [
      "Pro - $799: Full funnel marketing + monthly reports",
      "Performance campaigns across multiple channels",
      "Strategy, automation and remarketing setup",
    ],
  },
};

const highlightPriceInItem = (item) => {
  const priceMatch = item.match(/\$[\d,]+/);
  if (!priceMatch) {
    return item;
  }

  const [price] = priceMatch;
  return item.replace(
    price,
    `<span class="package-price" aria-label="Price ${price}">${price}</span>`,
  );
};

const renderPackageForPanel = (panel, tier) => {
  if (!panel) {
    return;
  }

  const service = panel.dataset.service;
  const list = panel.querySelector(".package-list");
  const packageItems = packageMatrix[service]?.[tier];

  if (!list || !packageItems) {
    return;
  }

  list.innerHTML = packageItems
    .map((item) => `<li>${highlightPriceInItem(item)}</li>`)
    .join("");
};

const packagePanels = document.querySelectorAll(".section-service-digital .package-panel[data-service]");

if (packagePanels.length) {
  packagePanels.forEach((panel) => {
    const buttons = panel.querySelectorAll("[data-tier]");

    buttons.forEach((button) => {
      button.addEventListener("click", (event) => {
        event.preventDefault();
        event.stopPropagation();

        const tier = button.dataset.tier;
        if (!tier) {
          return;
        }

        buttons.forEach((item) => item.classList.remove("is-active"));
        button.classList.add("is-active");
        renderPackageForPanel(panel, tier);
      });
    });

    renderPackageForPanel(panel, "starter");
  });
}
