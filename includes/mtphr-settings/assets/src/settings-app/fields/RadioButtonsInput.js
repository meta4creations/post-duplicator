// RadioButtonsInput.js
import { RadioControl } from "@wordpress/components";

const RadioButtonsInput = ({ field, value, settingsOption, onChange }) => {
  const { class: className, disabled, help, label, id, choices } = field;

  const onChangeHandler = (nextValue) => {
    onChange({ id, value: nextValue, settingsOption });
  };

  /**
   * Format the choices
   */
  const formattedChoices = () => {
    // If it's already an array, return it as is
    if (Array.isArray(choices)) {
      return choices;
    }

    // If it's an object, convert it to an array of objects
    if (typeof choices === "object" && choices !== null) {
      return Object.entries(choices).map(([value, label]) => ({
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
      options={formattedChoices()}
      onChange={onChangeHandler}
      disabled={disabled}
    />
  );
};

export default RadioButtonsInput;
