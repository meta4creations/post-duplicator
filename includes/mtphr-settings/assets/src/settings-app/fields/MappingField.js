import {
  BaseControl,
  SelectControl,
  __experimentalHStack as HStack,
  __experimentalVStack as VStack,
} from "@wordpress/components";
import { useState } from "@wordpress/element";

const MappingField = ({ field, value = {}, settingsOption, onChange }) => {
  const { label, id, help, map_source, map_choices, disabled } = field;

  // Initialize state to track mapped values
  const [mappedValues, setMappedValues] = useState(() => {
    return map_source.map((item) => ({
      tag: item.tag,
      label: item.label,
      value: value[item.tag] || "",
    }));
  });

  const handleSelectChange = (selectedValue, tag) => {
    const updatedMappedValues = mappedValues.map((mapping) =>
      mapping.tag === tag
        ? {
            ...mapping,
            value: selectedValue,
          }
        : mapping
    );

    setMappedValues(updatedMappedValues);

    // Create the updated value object, excluding empty selections
    const newValues = updatedMappedValues.reduce((acc, mapping) => {
      if (mapping.value) {
        acc[mapping.tag] = mapping.value;
      }
      return acc;
    }, {});

    onChange({ id, value: newValues, settingsOption });
  };

  const availableChoices = (currentValue) =>
    map_choices.map((choice) => ({
      value: choice.tag,
      label: choice.label,
      disabled: mappedValues.some(
        (mapping) =>
          mapping.value === choice.tag && mapping.value !== currentValue
      ),
    }));

  return (
    <BaseControl label={label} help={help} id={id} __nextHasNoMarginBottom>
      <VStack>
        {mappedValues.map((item) => (
          <HStack
            key={item.tag}
            spacing="10px"
            className="mapping-field-row"
            alignment="left"
          >
            <div className="mapping-field-label" style={{ flex: 1 }}>
              {item.label}
            </div>
            <div className="mapping-field-select" style={{ flex: 2 }}>
              <SelectControl
                value={item.value}
                options={[
                  { value: "", label: "-- Select --", disabled: false },
                  ...availableChoices(item.value),
                ]}
                onChange={(value) => handleSelectChange(value, item.tag)}
                disabled={disabled}
                __nextHasNoMarginBottom
              />
            </div>
          </HStack>
        ))}
      </VStack>
    </BaseControl>
  );
};

export default MappingField;
