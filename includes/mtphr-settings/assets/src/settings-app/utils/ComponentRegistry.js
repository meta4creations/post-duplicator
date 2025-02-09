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
  return componentRegistry[type];
};

/**
 * Expose the registerComponent function globally with a given namespace.
 * @param {string} namespace - The unique global namespace to use.
 */
export const exposeRegistry = (namespace) => {
  if (!namespace || typeof namespace !== "string") {
    throw new Error("A valid namespace string must be provided.");
  }
  window[namespace] = window[namespace] || {};
  window[namespace].registerComponent = registerComponent;
};
