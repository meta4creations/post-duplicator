import { SelectControl } from "@wordpress/components";

const SelectInput = ({ field, value, settingsOption, onChange }) => {
  const {
    class: className,
    disabled,
    help,
    label,
    labelPosition,
    multiple,
    id,
    choices,
    variant,
  } = field;

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
    <SelectControl
      className={className}
      label={label}
      labelPosition={labelPosition}
      help={help}
      onChange={onChangeHandler}
      multiple={multiple}
      name={id}
      options={formattedChoices()}
      value={value}
      variant={variant}
      disabled={disabled}
      __nextHasNoMarginBottom
    />
  );
};

export default SelectInput;
