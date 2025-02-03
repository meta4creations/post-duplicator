import { __experimentalInputControl as InputControl } from "@wordpress/components";

const TextInput = ({ field, value, onChange }) => {
  const {
    class: className,
    disabled,
    help,
    label,
    labelPosition,
    id,
    placeholder,
    prefix,
    suffix,
    type = "text",
  } = field;

  const onChangeHandler = (nextValue) => {
    onChange({ id: id, value: nextValue });
  };

  return (
    <InputControl
      className={className}
      disabled={disabled}
      help={help}
      label={label}
      labelPosition={labelPosition}
      onChange={onChangeHandler}
      placeholder={placeholder}
      prefix={prefix}
      suffix={suffix}
      type={type}
      value={value}
    />
  );
};

export default TextInput;
