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
    options,
    variant,
  } = field;

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
    <SelectControl
      className={className}
      label={label}
      labelPosition={labelPosition}
      help={help}
      onChange={onChangeHandler}
      multiple={multiple}
      name={id}
      options={formattedOptions()}
      value={value}
      variant={variant}
      disabled={disabled}
      __nextHasNoMarginBottom
    />
  );
};

export default SelectInput;
