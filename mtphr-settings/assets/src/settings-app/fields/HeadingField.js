import { __experimentalHeading as Heading } from "@wordpress/components";

const HeadingField = ({ field }) => {
  const { level = 4, label } = field;
  return <Heading level={level}>{label}</Heading>;
};

export default HeadingField;
