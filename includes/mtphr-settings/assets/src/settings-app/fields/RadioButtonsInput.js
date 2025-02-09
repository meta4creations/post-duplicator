// RadioButtonsInput.js
import { RadioControl } from "@wordpress/components";

const RadioButtonsInput = ({ field, value, settingsOption, onChange }) => {
  const { class: className, disabled, help, label, id, options } = field;

  const onChangeHandler = (nextValue) => {
    onChange({ id, value: nextValue, settingsOption });
  };

  /**
   * Format the options
   */
  const formattedOptions = () => {
    // If it's already an array, return it as is
    if (Array.isArray(options)) {
      return options;
    }

    // If it's an object, convert it to an array of objects
    if (typeof options === "object" && options !== null) {
      return Object.entries(options).map(([value, label]) => ({
        value,
        label,
      }));
    }

    // Return an empty array or handle unexpected cases
    return [];
  };

  return (
    <RadioControl
      className={className}
      label={label}
      help={help}
      selected={value}
      options={formattedOptions()}
      onChange={onChangeHandler}
      disabled={disabled}
    />
  );
};

export default RadioButtonsInput;
