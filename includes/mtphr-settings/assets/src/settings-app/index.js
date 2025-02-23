import { __ } from "@wordpress/i18n";
const { createRoot, render } = wp.element; // We're using wp.element here!
import App from "./App";
import "./utils/RegisterComponents"; // Ensure components are registered
import "./css/app.scss";

// Get the root element
const rootElement = document.getElementById("mtphr-settings-app");

// Check if the root element exists
if (rootElement) {
  const settingsId = rootElement.dataset.id ? rootElement.dataset.id : "mtphr";
  const settingsTitle = rootElement.dataset.title
    ? rootElement.dataset.title
    : __("Settings", "mtphr-settings");

  if (createRoot) {
    createRoot(rootElement).render(
      <App settingsId={settingsId} settingsTitle={settingsTitle} />
    );
  } else {
    render(<App settingsId={settingsId} />, rootElement);
  }
}
