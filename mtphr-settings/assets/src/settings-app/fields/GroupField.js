import {
  __experimentalHStack as HStack,
  __experimentalVStack as VStack,
} from "@wordpress/components";
import Field from "./Field";

const GroupField = ({ field, value, onChange, settings, settingsId }) => {
  const {
    alignment,
    direction,
    justify,
    spacing,
    wrap,
    class: className = "",
    id,
    label,
    tooltip,
    fields,
  } = field;

  return (
    <HStack
      alignment={alignment}
      direction={direction}
      justify={justify}
      spacing={spacing}
      wrap={wrap}
      className={className}
    >
      {fields.map((subField, index) => {
        return (
          <Field
            key={subField.id}
            field={subField}
            value={value[index] || subField.default_value || ""}
            onChange={(data) => {
              onChange(data, id, index);
            }}
            settings={settings}
            settingsId={settingsId}
          />
        );
      })}
    </HStack>
  );
};

export default GroupField;
