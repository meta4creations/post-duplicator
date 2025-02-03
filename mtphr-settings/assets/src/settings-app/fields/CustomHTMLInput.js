import {
  BaseControl,
  ColorPalette,
  ColorPicker,
  useBaseControlProps,
} from "@wordpress/components";

const CustomHTMLInput = ({ field, value, onChange }) => {
  const { class: className, std } = field;

  const { baseControlProps, controlProps } = useBaseControlProps(field);

  return (
    <BaseControl {...baseControlProps}>
      <div dangerouslySetInnerHTML={{ __html: std }} />
    </BaseControl>
  );
};

export default CustomHTMLInput;
