jQuery(document).ready(function () {
  /**
   * Duplicate post listener.
   *
   * Creates an ajax request that creates a new post,
   * duplicating all the data and custom meta.
   *
   * @since 2.25
   */

  jQuery("body").on("click", ".m4c-duplicate-post", function (e) {
    e.preventDefault();
    var $spinner = jQuery(this).next(".spinner");
    $spinner.css("visibility", "visible");

    // Create the data to pass
    var data = {
      action: "m4c_duplicate_post",
      original_id: jQuery(this).data("postid"),
      security: jQuery(this).attr("rel"),
    };

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(
      ajaxurl,
      data,
      function (response) {
        if (response.duplicate_id) {
          var location = window.location.href;
          if (location.split("?").length > 1) {
            location = location + "&post-duplicated=" + response.duplicate_id;
          } else {
            location = location + "?post-duplicated=" + response.duplicate_id;
          }
          window.location.href = location;
        }
      },
      "json"
    );
  });
});
