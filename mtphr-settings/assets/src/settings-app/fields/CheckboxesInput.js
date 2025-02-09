// CheckboxesInput.js
import {
  BaseControl,
  CheckboxControl,
  useBaseControlProps,
} from "@wordpress/components";

const CheckboxesInput = ({ field, value = [], settingsOption, onChange }) => {
  const { class: className, disabled, help, label, id, choices } = field;

  const onChangeHandler = (checked, option) => {
    const updatedValues = checked
      ? [...value, option]
      : value.filter((item) => item !== option);

    onChange({ id, value: updatedValues, settingsOption });
  };

  const { baseControlProps } = useBaseControlProps(field);

  return (
    <BaseControl {...baseControlProps}>
      <fieldset>
        {Object.entries(choices).map(([option, optionLabel]) => (
          <CheckboxControl
            key={option}
            label={optionLabel}
            checked={value.includes(option)}
            onChange={(checked) => onChangeHandler(checked, option)}
            disabled={disabled}
          />
        ))}
      </fieldset>
    </BaseControl>
  );
};

export default CheckboxesInput;
