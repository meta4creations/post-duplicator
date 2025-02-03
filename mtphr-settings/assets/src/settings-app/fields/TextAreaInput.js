import { TextareaControl } from "@wordpress/components";

const TextAreaInput = ({ field, value, onChange }) => {
  const {
    class: className,
    disabled,
    help,
    label,
    labelPosition,
    id,
    placeholder,
    prefix,
    rows,
    suffix,
  } = field;

  const onChangeHandler = (nextValue) => {
    onChange({ id: id, value: nextValue });
  };

  return (
    <TextareaControl
      className={className}
      disabled={disabled}
      help={help}
      label={label}
      labelPosition={labelPosition}
      onChange={onChangeHandler}
      placeholder={placeholder}
      prefix={prefix}
      rows={rows}
      suffix={suffix}
      value={value}
    />
  );
};

export default TextAreaInput;
