import {
  BaseControl,
  SelectControl,
  __experimentalHStack as HStack,
} from "@wordpress/components";
import { useState } from "@wordpress/element";

const MappingField = ({ field, value = {}, onChange }) => {
  const { label, id, help, map_source, map_options, disabled } = field;

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

    onChange({ id, value: newValues });
  };

  const availableOptions = (currentValue) =>
    map_options.map((option) => ({
      value: option.tag,
      label: option.label,
      disabled: mappedValues.some(
        (mapping) =>
          mapping.value === option.tag && mapping.value !== currentValue
      ),
    }));

  return (
    <BaseControl label={label} help={help} id={id}>
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
                ...availableOptions(item.value),
              ]}
              onChange={(value) => handleSelectChange(value, item.tag)}
              disabled={disabled}
            />
          </div>
        </HStack>
      ))}
    </BaseControl>
  );
};

export default MappingField;
