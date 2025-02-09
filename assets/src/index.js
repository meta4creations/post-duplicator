import "./css/index.scss";

function showSnackbar(message, type = "error") {
  // Create the container
  const snackbar = document.createElement("div");
  snackbar.classList.add("my-snackbar", `my-snackbar--${type}`);
  snackbar.textContent = message;

  // Append to body
  document.body.appendChild(snackbar);

  // Auto-remove after 3 seconds
  setTimeout(() => {
    snackbar.classList.add("my-snackbar--hide");
    snackbar.addEventListener("transitionend", () => snackbar.remove());
  }, 3000);
}

document.addEventListener("DOMContentLoaded", function () {
  // Event delegation for .m4c-duplicate-post clicks
  document.body.addEventListener("click", function (e) {
    // Only run if the clicked element has .m4c-duplicate-post
    if (e.target.classList.contains("m4c-duplicate-post")) {
      e.preventDefault();

      // Show spinner if present
      const spinner = e.target.nextElementSibling;
      if (spinner && spinner.classList.contains("spinner")) {
        spinner.style.visibility = "visible";
      }

      // Build the POST data
      const data = {
        original_id: e.target.getAttribute("data-postid"),
      };

      // Send request to our custom REST endpoint
      fetch(`${postDuplicatorVars.restUrl}duplicate-post`, {
        method: "POST",
        headers: {
          "X-WP-Nonce": postDuplicatorVars.nonce,
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
      })
        .then((response) => {
          // If HTTP status not in the 200-299 range, parse JSON and throw it
          if (!response.ok) {
            return response.json().then((errorJSON) => {
              throw errorJSON;
            });
          }
          return response.json(); // Otherwise parse the successful response
        })
        .then((response) => {
          //showSnackbar("Post duplicated successfully!", "success");

          // If the server returned a new duplicate post ID, redirect with ?post-duplicated=...
          if (response.duplicate_id) {
            let loc = window.location.href;
            if (loc.includes("?")) {
              loc += `&post-duplicated=${response.duplicate_id}`;
            } else {
              loc += `?post-duplicated=${response.duplicate_id}`;
            }
            window.location.href = loc;
          }
        })
        .catch((error) => {
          console.error("Error duplicating post:", error.message);
          showSnackbar(`Error duplicating post: ${error.message}`, "error");
        });
    }
  });
});
