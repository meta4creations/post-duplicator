import {
  __experimentalHStack as HStack,
  __experimentalVStack as VStack,
} from "@wordpress/components";
import Field from "./Field";
import { shouldRenderField } from "../utils/fieldVisibility";

const GroupField = ({
  field,
  value,
  onChange,
  values,
  settingsOption,
  settingsId,
}) => {
  const {
    alignment,
    direction,
    justify,
    spacing,
    wrap,
    class: className = "",
    id,
    fields,
  } = field;

  const groupValue = id || value ? value || {} : values;
  const Container = "column" === direction ? VStack : HStack;

  return (
    <Container
      alignment={alignment}
      //direction={direction}
      justify={justify}
      spacing={spacing}
      wrap={wrap}
      className={className}
    >
      {fields.map((subField, index) => {
        const fieldValue = subField.id ? groupValue[subField.id] : groupValue;

        if (!shouldRenderField(subField, values)) return null; // Don't render if conditions fail

        return (
          <Field
            key={subField.id || index}
            field={subField}
            value={fieldValue}
            onChange={(data) => {
              const { id: subFieldId, value: newValue } = data;
              const updatedValue = { ...value, [subFieldId]: newValue };
              onChange(id ? { id, value: updatedValue, settingsOption } : data);
            }}
            values={values}
            settingsOption={settingsOption}
            settingsId={settingsId}
          />
        );
      })}
    </Container>
  );
};

export default GroupField;
