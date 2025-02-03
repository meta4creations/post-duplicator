import { SelectControl } from "@wordpress/components";

const SelectInput = ({ field, value, onChange }) => {
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
    onChange({ id: id, value: nextValue });
  };

  return (
    <SelectControl
      className={className}
      disabled={disabled}
      help={help}
      label={label}
      labelPosition={labelPosition}
      onChange={onChangeHandler}
      multiple={multiple}
      name={id}
      options={options}
      value={value}
      variant={variant}
      __nextHasNoMarginBottom
    />
  );
};

export default SelectInput;
