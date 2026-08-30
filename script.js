/* ==========================================================================
   PUP MARAGONDON UNIVERSITY LIBRARY — SITE SCRIPT
   Behaviour only: navigation, disclosure, data rendering, state.
   Decorative motion is deliberately absent — one reveal pattern lives in CSS.
   ========================================================================== */

document.addEventListener("DOMContentLoaded", function () {
  const reduceMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)",
  ).matches;

  // ──────────────────────────────────────────────────────────────────────────
  // 1. STICKY HEADER
  // ──────────────────────────────────────────────────────────────────────────
  const header = document.getElementById("main-header");
  if (header) {
    window.addEventListener(
      "scroll",
      () => header.classList.toggle("sticky", window.scrollY > 24),
      { passive: true },
    );
  }

  // ──────────────────────────────────────────────────────────────────────────
  // 2. MOBILE DRAWER
  // ──────────────────────────────────────────────────────────────────────────
  const hamburger = document.querySelector(".hamburger");
  const drawer = document.getElementById("mobile-drawer");
  const drawerOverlay = document.getElementById("drawer-overlay");
  const drawerClose = document.querySelector(".drawer-close");

  const openDrawer = () => {
    drawer?.classList.add("open");
    drawerOverlay?.classList.add("open");
    document.body.style.overflow = "hidden";
  };
  const closeDrawer = () => {
    drawer?.classList.remove("open");
    drawerOverlay?.classList.remove("open");
    document.body.style.overflow = "";
  };

  hamburger?.addEventListener("click", openDrawer);
  drawerClose?.addEventListener("click", closeDrawer);
  drawerOverlay?.addEventListener("click", closeDrawer);
  drawer?.querySelectorAll("a").forEach((a) => {
    a.addEventListener("click", closeDrawer);
  });

  // ──────────────────────────────────────────────────────────────────────────
  // 3. SCROLL REVEAL — a single, restrained pattern
  // ──────────────────────────────────────────────────────────────────────────
  function revealNow(el) {
    el.classList.add("active");
  }

  const revealEls = document.querySelectorAll(
    ".reveal, .reveal-left, .reveal-right",
  );

  if (reduceMotion || !("IntersectionObserver" in window)) {
    revealEls.forEach(revealNow);
    document.querySelectorAll(".stagger-card").forEach(revealNow);
  } else {
    const revealObs = new IntersectionObserver(
      (entries, obs) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          revealNow(entry.target);
          obs.unobserve(entry.target);
        });
      },
      { threshold: 0.08, rootMargin: "0px 0px -40px 0px" },
    );
    revealEls.forEach((el) => revealObs.observe(el));

    // Grid children fade in together rather than one-by-one — the old
    // per-card cascade read as decoration rather than hierarchy.
    document
      .querySelectorAll(
        ".quick-grid, .arrivals-grid, .resources-grid-v2, .linkages-grid, .holdings-grid, .steps-grid, .personnel-big-grid",
      )
      .forEach((container) => {
        const children = container.querySelectorAll(":scope > *");
        children.forEach((child) => child.classList.add("stagger-card"));

        const obs = new IntersectionObserver(
          (entries, observer) => {
            entries.forEach((entry) => {
              if (!entry.isIntersecting) return;
              entry.target
                .querySelectorAll(".stagger-card")
                .forEach(revealNow);
              observer.unobserve(entry.target);
            });
          },
          { threshold: 0.05 },
        );
        obs.observe(container);
      });
  }

  // ──────────────────────────────────────────────────────────────────────────
  // 4. NEW ARRIVALS — rendered from CMS data
  // ──────────────────────────────────────────────────────────────────────────
  const arrivalsContainer = document.getElementById("arrivals-container");
  if (arrivalsContainer) {
    const arrivals = Array.isArray(window.cmsArrivals) ? window.cmsArrivals : [];
    if (arrivals.length) {
      const esc = (s) =>
        String(s ?? "").replace(
          /[&<>"']/g,
          (c) =>
            ({
              "&": "&amp;",
              "<": "&lt;",
              ">": "&gt;",
              '"': "&quot;",
              "'": "&#39;",
            })[c],
        );

      arrivalsContainer.innerHTML = "";
      arrivals.forEach((item) => {
        const card = document.createElement("article");
        card.className = "arrival-card stagger-card";

        const imgSrc = item.image || item.img || "";
        const imgTag = imgSrc
          ? `<img src="${esc(imgSrc)}" alt="${esc(item.title)}" loading="lazy">`
          : `<div class="arrival-img-empty"><i class="fa-solid fa-book"></i></div>`;

        card.innerHTML = `
          <div class="arrival-img">
            ${imgTag}
            ${item.type ? `<span class="arrival-badge">${esc(item.type)}</span>` : ""}
          </div>
          <div class="arrival-body">
            <h4>${esc(item.title)}</h4>
            ${item.author ? `<p class="arrival-author">${esc(item.author)}</p>` : ""}
            ${item.date ? `<p class="date"><i class="fa-regular fa-calendar"></i> ${esc(item.date)}</p>` : ""}
          </div>`;
        arrivalsContainer.appendChild(card);
        if (reduceMotion) card.classList.add("active");
      });

      if (!reduceMotion && "IntersectionObserver" in window) {
        const obs = new IntersectionObserver(
          (entries, observer) => {
            entries.forEach((entry) => {
              if (!entry.isIntersecting) return;
              entry.target
                .querySelectorAll(".stagger-card")
                .forEach((c) => c.classList.add("active"));
              observer.unobserve(entry.target);
            });
          },
          { threshold: 0.05 },
        );
        obs.observe(arrivalsContainer);
      }
    }
  }

  // ──────────────────────────────────────────────────────────────────────────
  // 5. VIDEO MODAL
  // ──────────────────────────────────────────────────────────────────────────
  const playThumb = document.getElementById("play-avp-thumb");
  const playBtn = document.getElementById("btn-watch-avp");
  const videoModal = document.getElementById("video-modal");
  const closeModalBtn = document.getElementById("close-modal");
  const avpIframe = document.getElementById("avp-iframe");
  const avpId = window.cmsSettings?.avp_youtube_id || "ScMzIvxBSi4";

  const openModal = () => {
    videoModal?.classList.add("show");
    avpIframe?.setAttribute(
      "src",
      "https://www.youtube.com/embed/" +
        encodeURIComponent(avpId) +
        "?autoplay=1",
    );
    document.body.style.overflow = "hidden";
  };
  const closeModal = () => {
    if (!videoModal?.classList.contains("show")) return;
    videoModal.classList.remove("show");
    avpIframe?.setAttribute("src", "");
    document.body.style.overflow = "";
  };

  playThumb?.addEventListener("click", openModal);
  playBtn?.addEventListener("click", openModal);
  closeModalBtn?.addEventListener("click", closeModal);
  videoModal?.addEventListener("click", (e) => {
    if (e.target === videoModal) closeModal();
  });
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeModal();
  });

  // ──────────────────────────────────────────────────────────────────────────
  // 6. ACCORDION
  // ──────────────────────────────────────────────────────────────────────────
  document.querySelectorAll(".accordion-header").forEach((hdr) => {
    hdr.setAttribute("tabindex", "0");
    hdr.setAttribute("role", "button");

    const toggle = () => {
      const item = hdr.closest(".accordion-item");
      const wasOpen = item.classList.contains("open");
      document
        .querySelectorAll(".accordion-item")
        .forEach((i) => i.classList.remove("open"));
      if (!wasOpen) item.classList.add("open");
      hdr.setAttribute("aria-expanded", String(!wasOpen));
    };

    hdr.addEventListener("click", toggle);
    hdr.addEventListener("keydown", (e) => {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        toggle();
      }
    });
  });

  // ──────────────────────────────────────────────────────────────────────────
  // 7. SERVICE TABS
  // ──────────────────────────────────────────────────────────────────────────
  document.querySelectorAll(".tab-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      const target = this.dataset.tab;
      document
        .querySelectorAll(".tab-btn")
        .forEach((b) => b.classList.remove("active"));
      document
        .querySelectorAll(".tab-panel")
        .forEach((p) => p.classList.remove("active"));
      this.classList.add("active");
      document.getElementById(target)?.classList.add("active");
    });
  });

  // ──────────────────────────────────────────────────────────────────────────
  // 8. BACK TO TOP
  // ──────────────────────────────────────────────────────────────────────────
  const backToTop = document.getElementById("back-to-top");
  if (backToTop) {
    window.addEventListener(
      "scroll",
      () => backToTop.classList.toggle("show", window.scrollY > 500),
      { passive: true },
    );
    backToTop.addEventListener("click", () =>
      window.scrollTo({
        top: 0,
        behavior: reduceMotion ? "auto" : "smooth",
      }),
    );
  }
});

/* ── Highlight today's row in the schedule card ─────────────────────────── */
(function () {
  const today = new Date().getDay();
  document.querySelectorAll(".hours-row-v2[data-day]").forEach((row) => {
    if (parseInt(row.dataset.day, 10) !== today) return;
    row.classList.add("is-today");
    const badge = row.querySelector(".today-badge");
    if (badge) badge.style.display = "inline-block";
  });
})();

/* ── Read more / show less ──────────────────────────────────────────────── */
(function () {
  const THRESHOLD_PX = 78; // ≈ 3 lines

  function initReadMore(el) {
    if (el.dataset.rmInit) return;
    el.dataset.rmInit = "1";
    if (el.scrollHeight <= THRESHOLD_PX + 4) return;

    el.classList.add("is-truncated");

    const btn = document.createElement("button");
    btn.className = "read-more-btn";
    btn.type = "button";
    btn.textContent = "Read more";
    btn.setAttribute("aria-expanded", "false");
    el.insertAdjacentElement("afterend", btn);

    btn.addEventListener("click", () => {
      const collapsed = el.classList.toggle("is-truncated");
      btn.textContent = collapsed ? "Read more" : "Show less";
      btn.setAttribute("aria-expanded", String(!collapsed));
    });
  }

  function scanAll() {
    document.querySelectorAll(".truncate-text").forEach(initReadMore);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", scanAll);
  } else {
    scanAll();
  }

  // Panels revealed by the services tabs need a second pass once visible.
  document.addEventListener("click", (e) => {
    if (e.target.closest(".tab-btn")) setTimeout(scanAll, 50);
  });
})();

/* ── Theme toggle ───────────────────────────────────────────────────────── */
(function () {
  const btn = document.getElementById("theme-toggle");
  const icon = document.getElementById("theme-icon");
  if (!btn) return;

  function applyTheme(dark) {
    document.documentElement.setAttribute(
      "data-theme",
      dark ? "dark" : "light",
    );
    if (icon) icon.className = dark ? "fa-solid fa-sun" : "fa-solid fa-moon";
    btn.setAttribute("aria-pressed", String(dark));
  }

  let saved = null;
  try {
    saved = localStorage.getItem("pup-theme");
  } catch (e) {
    /* storage unavailable — fall back to system preference */
  }
  const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
  applyTheme(saved ? saved === "dark" : prefersDark);

  btn.addEventListener("click", function () {
    const isDark =
      document.documentElement.getAttribute("data-theme") === "dark";
    applyTheme(!isDark);
    try {
      localStorage.setItem("pup-theme", !isDark ? "dark" : "light");
    } catch (e) {
      /* non-fatal */
    }
  });
})();
