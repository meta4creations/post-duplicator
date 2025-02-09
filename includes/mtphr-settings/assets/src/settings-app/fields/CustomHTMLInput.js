import { BaseControl, useBaseControlProps } from "@wordpress/components";

const CustomHTMLInput = ({ field }) => {
  const { class: className, std } = field;

  const { baseControlProps } = useBaseControlProps(field);

  return (
    <BaseControl {...baseControlProps}>
      <div dangerouslySetInnerHTML={{ __html: std }} />
    </BaseControl>
  );
};

export default CustomHTMLInput;
