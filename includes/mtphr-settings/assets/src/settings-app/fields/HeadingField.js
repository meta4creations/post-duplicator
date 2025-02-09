import {
  BaseControl,
  useBaseControlProps,
  __experimentalHeading as Heading,
} from "@wordpress/components";

const HeadingField = ({ field }) => {
  const { level = 4, label } = field;

  const { baseControlProps } = useBaseControlProps({ ...field, label: null });

  return (
    <BaseControl {...baseControlProps} __nextHasNoMarginBottom>
      <Heading level={level}>{label}</Heading>
    </BaseControl>
  );
};

export default HeadingField;
