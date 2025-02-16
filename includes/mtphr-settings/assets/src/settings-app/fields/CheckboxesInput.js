// CheckboxesInput.js
import {
  BaseControl,
  CheckboxControl,
  useBaseControlProps,
} from "@wordpress/components";

const CheckboxesInput = ({ field, value, settingsOption, onChange }) => {
  const { class: className, disabled, help, label, id, choices } = field;

  const onChangeHandler = (checked, choice) => {
    if (!Array.isArray(value)) {
      value = [];
    }

    const updatedValues = checked
      ? [...value, choice]
      : value.filter((item) => item !== choice);

    onChange({ id, value: updatedValues, settingsOption });
  };

  const { baseControlProps } = useBaseControlProps(field);

  return (
    <BaseControl {...baseControlProps} __nextHasNoMarginBottom>
      <fieldset>
        {Object.entries(choices).map(([choice, choiceLabel]) => (
          <CheckboxControl
            key={choice}
            label={choiceLabel}
            checked={value && value.includes(choice)}
            onChange={(checked) => onChangeHandler(checked, choice)}
            disabled={disabled}
          />
        ))}
      </fieldset>
    </BaseControl>
  );
};

export default CheckboxesInput;
