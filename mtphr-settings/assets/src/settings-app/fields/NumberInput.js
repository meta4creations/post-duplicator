import he from "he";
import { __experimentalNumberControl as NumberControl } from "@wordpress/components";

const NumberInput = ({ field, value, settingsOption, onChange }) => {
  const { help, label, id, min, max } = field;

  const onChangeHandler = (nextValue) => {
    onChange({ id, value: parseInt(nextValue), settingsOption });
  };

  return (
    <NumberControl
      label={label}
      help={help ? he.decode(help) : false}
      min={min}
      max={max}
      onChange={onChangeHandler}
      value={value}
      __next40pxDefaultSize
    />
  );
};

export default NumberInput;
