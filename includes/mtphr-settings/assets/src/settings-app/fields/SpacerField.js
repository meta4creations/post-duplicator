const SpacerField = ({ field }) => {
  const { height = "20px" } = field;
  return <div style={{ height: height }}></div>;
};

export default SpacerField;
