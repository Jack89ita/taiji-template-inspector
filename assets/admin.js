jQuery(function ($) {
  function animateUsage() {
    $(".taiji-usage-number").each(function () {
      const el = $(this);
      const target = parseInt(el.data("target"), 10) || 0;

      if (el.data("animated")) {
        return;
      }

      el.data("animated", true);

      let current = 0;
      const steps = 30;
      const increment = target / steps;

      const timer = setInterval(function () {
        current += increment;

        if (current >= target) {
          current = target;
          clearInterval(timer);
        }

        el.text(Math.round(current));
      }, 22);
    });

    $(".taiji-progress-bar").each(function () {
      const bar = $(this);
      const width = parseInt(bar.data("width"), 10) || 0;

      if (bar.data("animated")) {
        return;
      }

      bar.data("animated", true);

      setTimeout(function () {
        bar.css("width", width + "%");
      }, 120);
    });
  }

  animateUsage();

  $(document).on("click", ".taiji-expand-arrow", function () {
    const row = $(this).closest("tr");
    const results = row.next(".taiji-results");
    const arrow = $(this).find(".taiji-arrow");

    if (results.hasClass("loaded")) {
      if (results.is(":visible")) {
        results.hide();
        arrow.removeClass("taiji-open");
        row.removeClass("taiji-row-open");
      } else {
        results.show();
        arrow.addClass("taiji-open");
        row.addClass("taiji-row-open");
      }

      return;
    }

    const template = row.data("template");
    const lang = row.data("lang") || "";

    results.show().find("td").html('<div class="taiji-loading">Loading…</div>');

    $.post(
      taiji_ajax.url,
      {
        action: "taiji_load_posts",
        template: template,
        lang: lang,
        nonce: taiji_ajax.nonce,
      },
      function (response) {
        results.find("td").html(response);
        results.addClass("loaded");
        results.show();

        arrow.addClass("taiji-open");
        row.addClass("taiji-row-open");
      }
    );
  });

  $("#taiji-search").on("keyup", function () {
    const value = $(this).val().toLowerCase();

    $(".taiji-template-row").each(function () {
      const row = $(this);
      const text = row.text().toLowerCase();
      const match = text.indexOf(value) > -1;

      row.toggle(match);

      if (!match) {
        row.next(".taiji-results").hide();
        row.removeClass("taiji-row-open");
        row.find(".taiji-arrow").removeClass("taiji-open");
      }
    });
  });

  $(document).on("click", ".taiji-open-all-front, .taiji-open-all-back", function () {

    const urlsData = $(this).data("urls");

    if (!urlsData) {
      return;
    }

    const urls = urlsData.split("|").filter(Boolean);

    if (!urls.length) {
      return;
    }

    if (urls.length > 1) {
      if (!confirm("Open " + urls.length + " tabs?")) {
        return;
      }
    }

    urls.forEach(function (url) {
      window.open(url, "_blank", "noopener");
    });

  });
});
