// Global Component Registry
const componentRegistry = {};

/**
 * Register a component with a specific field type.
 * @param {string} type - The field type.
 * @param {React.Component} component - The React component to render.
 */
export const registerComponent = (type, component) => {
  componentRegistry[type] = component;
};

/**
 * Get the component associated with a field type.
 * @param {string} type - The field type.
 * @returns {React.Component} - The registered component.
 */
export const getComponent = (type) => {
  return componentRegistry[type] || null;
};

// Expose globally
window.mtphrSettingsRegistry = {
  registerComponent,
  getComponent,
};
