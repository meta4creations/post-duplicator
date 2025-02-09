import {
  BaseControl,
  useBaseControlProps,
  __experimentalHStack as HStack,
  __experimentalVStack as VStack,
} from "@wordpress/components";
import ButtonInput from "./ButtonInput";

const ButtonsField = ({ field, values, settingsOption, settingsId }) => {
  const {
    alignment,
    direction,
    justify,
    spacing,
    wrap,
    class: className = "",
    buttons,
  } = field;

  const { baseControlProps } = useBaseControlProps(field);

  return (
    <BaseControl {...baseControlProps} __nextHasNoMarginBottom>
      <HStack
        alignment={alignment}
        direction={direction}
        justify={justify}
        spacing={spacing}
        wrap={wrap}
        className={className}
      >
        {buttons.map((button, index) => {
          return (
            <ButtonInput
              key={button.id || index}
              field={button}
              values={values}
              settingsOption={settingsOption}
              settingsId={settingsId}
            />
          );
        })}
      </HStack>
    </BaseControl>
  );
};

export default ButtonsField;
