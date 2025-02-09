import classNames from "classnames";

import {
  Card,
  CardHeader,
  CardBody,
  Notice,
  __experimentalHeading as Heading,
} from "@wordpress/components";
import { getComponent } from "../utils/ComponentRegistry";

const Field = ({
  field,
  value,
  onChange,
  values,
  settingsOption,
  settingsId,
}) => {
  const { container, inline } = field;
  const { isBorderless, padding } = container || {};

  const Component = getComponent(field.type);

  if (!Component) {
    console.error(`No component registered for field type '${field.type}'`);
    return (
      <Notice status="error" isDismissible={false}>
        Unknown field type: {field.type}
      </Notice>
    );
  }

  const CardClassName = classNames(
    "mtphrSettings__field",
    `mtphrSettings__field--${field.type}`,
    field.class,
    { "mtphrSettings__field--inline": inline }
  );

  return (
    <Card
      className={CardClassName}
      isRounded={false}
      size="small"
      isBorderless={isBorderless}
      style={{ flex: 1 }}
    >
      {field.type === "group" && field.label && (
        <CardHeader className={`$mtphrSettings__field__heading`}>
          <Heading level={4}>{field.label}</Heading>
        </CardHeader>
      )}
      <CardBody
        className={`mtphrSettings__field__input-wrapper`}
        style={{ padding }}
      >
        <Component
          field={field}
          value={value}
          onChange={onChange}
          values={values}
          settingsOption={settingsOption}
          settingsId={settingsId}
        />
      </CardBody>
    </Card>
  );
};

export default Field;
