jQuery(function ($) {
  function animateUsage() {
    $(".tui-usage-number").each(function () {
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

    $(".tui-progress-bar").each(function () {
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

  $(document).on("click", ".tui-expand-arrow", function () {
    const row = $(this).closest("tr");
    const results = row.next(".tui-results");
    const arrow = $(this).find(".tui-arrow");

    if (results.hasClass("loaded")) {
      if (results.is(":visible")) {
        results.hide();
        arrow.removeClass("tui-open");
        row.removeClass("tui-row-open");
      } else {
        results.show();
        arrow.addClass("tui-open");
        row.addClass("tui-row-open");
      }

      return;
    }

    const template = row.data("template");
    const lang = row.data("lang") || "";

    results.show().find("td").html('<div class="tui-loading">Loading…</div>');

    $.post(
      tui_ajax.url,
      {
        action: "tui_load_posts",
        template: template,
        lang: lang,
        nonce: tui_ajax.nonce,
      },
      function (response) {
        results.find("td").html(response);
        results.addClass("loaded");
        results.show();

        arrow.addClass("tui-open");
        row.addClass("tui-row-open");
      }
    );
  });

  $("#tui-search").on("keyup", function () {
    const value = $(this).val().toLowerCase();

    $(".tui-template-row").each(function () {
      const row = $(this);
      const text = row.text().toLowerCase();
      const match = text.indexOf(value) > -1;

      row.toggle(match);

      if (!match) {
        row.next(".tui-results").hide();
        row.removeClass("tui-row-open");
        row.find(".tui-arrow").removeClass("tui-open");
      }
    });
  });

  $(document).on("click", ".tui-open-all-front, .tui-open-all-back", function () {

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
