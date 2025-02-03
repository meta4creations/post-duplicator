const { createRoot, render } = wp.element; // We're using wp.element here!
import App from "./App";
import { exposeRegistry } from "./utils/ComponentRegistry";
import "./utils/RegisterComponents";
import "./css/app.scss";

// Get the root element
const rootElement = document.getElementById("mtphr-settings-app");

// Check if the root element exists
if (rootElement) {
  // Get the value of the "data-fields" attribute from the root element
  const namespace = rootElement.getAttribute("namespace")
    ? rootElement.getAttribute("namespace")
    : "mtphr";

  const settingsId = namespace;

  console.log("settingsId", settingsId);

  exposeRegistry(settingsId);

  if (createRoot) {
    createRoot(rootElement).render(<App settingsId={settingsId} />);
  } else {
    render(<App settingsId={settingsId} />, rootElement);
  }
}
