// BAPS e-Library - interactive behaviour
document.addEventListener("DOMContentLoaded", function () {
  var base = document.body.dataset.base || "";

  // Sidebar toggle (mobile)
  var sidebar = document.getElementById("sidebar"),
    overlay = document.getElementById("overlay"),
    menuBtn = document.getElementById("menuBtn");
  function toggleMenu() {
    sidebar.classList.toggle("open");
    overlay.classList.toggle("open");
  }
  if (menuBtn) {
    menuBtn.addEventListener("click", toggleMenu);
    overlay.addEventListener("click", toggleMenu);
  }

  // Notification dropdown
  var bell = document.getElementById("bellBtn"),
    bellMenu = document.getElementById("bellMenu");
  if (bell) {
    bell.addEventListener("click", function (e) {
      e.stopPropagation();
      bellMenu.classList.toggle("open");
      var c = document.getElementById("bellCount");
      if (c) {
        fetch(base + "mark_read.php", { method: "POST" });
        c.remove();
      }
    });
    document.addEventListener("click", function (e) {
      if (!bellMenu.contains(e.target)) bellMenu.classList.remove("open");
    });
  }

  // Show / hide password
  document.querySelectorAll(".pw button").forEach(function (b) {
    b.addEventListener("click", function () {
      var i = b.parentElement.querySelector("input");
      var show = i.type === "password";
      i.type = show ? "text" : "password";
      b.innerHTML = show
        ? '<i class="fa-regular fa-eye-slash"></i>'
        : '<i class="fa-regular fa-eye"></i>';
    });
  });

  // Generic modals
  function openModal(m) {
    m.classList.remove("closing");
    m.classList.add("open");
    m.setAttribute("aria-hidden", "false");
  }
  function closeModal(m) {
    if (!m.classList.contains("open") || m.classList.contains("closing")) return;
    m.classList.add("closing");
    setTimeout(function () {
      m.classList.remove("open", "closing");
      m.setAttribute("aria-hidden", "true");
    }, 190);
  }
  document.querySelectorAll("[data-open]").forEach(function (b) {
    b.addEventListener("click", function () {
      var m = document.getElementById(b.dataset.open);
      if (m) {
        if (b.dataset.fill) fillForm(m, JSON.parse(b.dataset.fill));
        openModal(m);
      }
    });
  });
  document.querySelectorAll(".modal").forEach(function (m) {
    m.addEventListener("click", function (e) {
      if (e.target === m || e.target.closest("[data-close]")) closeModal(m);
    });
  });
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape")
      document.querySelectorAll(".modal.open").forEach(closeModal);
  });

  // Fill edit forms from data-fill JSON
  function fillForm(modal, data) {
    Object.keys(data).forEach(function (k) {
      var f = modal.querySelector('[name="' + k + '"]');
      if (f && f.type !== "file") f.value = data[k];
    });
    var t = modal.querySelector("[data-title]");
    if (t && data._title) t.textContent = data._title;
    // Add Book: cover picture is required (when editing, the old cover is kept if none is chosen)
    var cv = modal.querySelector("[data-cover]");
    if (cv) {
      cv.required = data.action === "add";
      if (data.action === "add") cv.value = "";
    }
  }

  // Book preview modal
  var bm = document.getElementById("bookModal");
  function stars(a) {
    var s = "";
    for (var i = 1; i <= 5; i++)
      s +=
        a >= i
          ? '<i class="fa-solid fa-star"></i>'
          : a >= i - 0.5
            ? '<i class="fa-solid fa-star-half-stroke"></i>'
            : '<i class="fa-regular fa-star"></i>';
    return '<span class="stars">' + s + "</span>";
  }
  document.querySelectorAll("[data-book]").forEach(function (card) {
    card.addEventListener("click", function () {
      var b = JSON.parse(card.dataset.book),
        $ = function (id) {
          return document.getElementById(id);
        };
      $("bmCover").src = b.cover;
      $("bmCover").alt = "Cover of " + b.title;
      $("bmTitle").textContent = b.title;
      $("bmAuthor").textContent = "by " + b.author;
      $("bmCat").textContent = b.category;
      $("bmPages").textContent = b.pages;
      $("bmBorrowed").textContent = b.borrowed;
      $("bmIsbn").textContent = b.isbn;
      $("bmAbstract").textContent =
        b.abstract || "No abstract available for this book yet.";
      $("bmStars").innerHTML =
        b.rating > 0
          ? stars(b.rating) + " <small>(" + b.rating + "/5)</small>"
          : "<small>No ratings yet</small>";
      $("bmStock").innerHTML =
        b.available > 0
          ? '<i class="fa-solid fa-check"></i> ' +
            b.available +
            " of " +
            b.total +
            " available"
          : '<i class="fa-solid fa-ban"></i> Out of stock';
      if ($("bmBorrowId")) {
        $("bmBorrowId").value = b.id;
        $("bmBorrowBtn").disabled = b.available < 1;
        $("bmBorrowBtn").style.opacity = b.available < 1 ? 0.5 : 1;
      }
      if ($("bmFavId")) {
        $("bmFavId").value = b.id;
        $("bmFavBtn").innerHTML = b.fav
          ? '<i class="fa-solid fa-heart"></i> Remove Favorite'
          : '<i class="fa-regular fa-heart"></i> Favorite';
      }
      if ($("bmEditLink"))
        $("bmEditLink").href =
          base + "librarian/inventory.php?q=" + encodeURIComponent(b.isbn);
      openModal(bm);
    });
  });

  // Carousels (scroll buttons)
  document.querySelectorAll("[data-scroll]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var row = document.getElementById(btn.dataset.scroll);
      row.scrollBy({
        left: parseInt(btn.dataset.dir, 10) * 360,
        behavior: "smooth",
      });
    });
  });

  // Confirm dangerous actions
  document.querySelectorAll("form[data-confirm]").forEach(function (f) {
    f.addEventListener("submit", function (e) {
      if (!confirm(f.dataset.confirm)) e.preventDefault();
    });
  });

  // Student ID auto hyphen (2026-00001)
  document.querySelectorAll("[data-studentid]").forEach(function (inp) {
    inp.addEventListener("input", function (e) {
      var v = inp.value,
        auto = inp.dataset.studentid === "auto";
      if (auto && !/^[\d-]*$/.test(v)) return; // login: letters = it is a username, leave it
      var d = v.replace(/\D/g, "").slice(0, 9);
      if (d.length > 4) d = d.slice(0, 4) + "-" + d.slice(4);
      else if (d.length === 4 && e.inputType !== "deleteContentBackward")
        d += "-";
      inp.value = d;
    });
  });

  // Number limits (stock max 100, pages max 1,200): block e/+/-/. and clamp while typing
  document.querySelectorAll("input[data-max]").forEach(function (inp) {
    var max = parseInt(inp.dataset.max, 10);
    inp.addEventListener("keydown", function (e) {
      if (["e", "E", "+", "-", ".", ","].indexOf(e.key) !== -1)
        e.preventDefault();
    });
    inp.addEventListener("input", function () {
      var d = inp.value.replace(/\D/g, "");
      if (d !== "") d = String(Math.min(parseInt(d, 10), max)); // also drops leading zeros
      if (d !== inp.value) inp.value = d;
    });
  });

  // ISBN-13 auto hyphen: 978-0-00-000000-0 (hyphen appears as soon as a group is complete)
  document.querySelectorAll("input[data-isbn]").forEach(function (inp) {
    inp.addEventListener("input", function (e) {
      var d = inp.value.replace(/\D/g, "").slice(0, 13);
      var cuts = [3, 4, 6, 12],
        v = "",
        last = 0;
      cuts.forEach(function (c) {
        if (d.length >= c) {
          v += d.slice(last, c) + (d.length > c ? "-" : "");
          last = c;
        }
      });
      v += d.slice(last);
      var deleting = e.inputType && e.inputType.indexOf("delete") === 0;
      if (!deleting && cuts.indexOf(d.length) !== -1) v += "-";
      inp.value = v;
    });
  });

  // Course & Year auto hyphen: typing "BSIT" then Space -> "BSIT - "
  document
    .querySelectorAll('input[name="course_year"]')
    .forEach(function (inp) {
      inp.addEventListener("input", function (e) {
        if (e.inputType && e.inputType.indexOf("delete") === 0) return; // don't fight backspace
        if (/^[A-Za-z]+\s$/.test(inp.value))
          inp.value = inp.value.trim() + " - ";
      });
    });

  // Topbar scroll state for glassmorphic elevation
  var topbar = document.querySelector(".topbar");
  if (topbar) {
    var checkScroll = function () {
      topbar.classList.toggle("scrolled", window.scrollY > 15);
    };
    window.addEventListener("scroll", checkScroll, { passive: true });
    checkScroll();
  }

  // Smooth entrance reveals for panels & cards
  if ("IntersectionObserver" in window) {
    var revealObserver = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("revealed");
            revealObserver.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.08, rootMargin: "0px 0px -20px 0px" }
    );
    document
      .querySelectorAll(".panel, .stat, .contact-card")
      .forEach(function (el) {
        el.classList.add("will-reveal");
        revealObserver.observe(el);
      });
  }
});
