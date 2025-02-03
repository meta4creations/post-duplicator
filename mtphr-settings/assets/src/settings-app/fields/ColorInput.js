import {
  BaseControl,
  ColorPalette,
  ColorPicker,
  useBaseControlProps,
} from "@wordpress/components";

import { useState } from "react";

const ColorInput = ({ field, value = [], onChange }) => {
  // State to keep track of the currently selected color index
  const [colorIndex, setColorIndex] = useState(null);

  const { class: className, min = 1, max, id } = field;

  // Get the currently selected color based on colorIndex
  const getCurrentColor = () => {
    if (colorIndex !== null && colorIndex !== undefined) {
      return value[colorIndex];
    }
    return undefined;
  };

  // Handle color changes from the ColorPalette
  const handleColorChange = (color) => {
    // Create a new array to avoid mutating the original 'value' prop
    const updatedValues = [...value];
    updatedValues[colorIndex] = color;

    // Call the onChange prop with the updated values
    onChange({ id, value: updatedValues });
  };

  // Function to add a new color to the list
  const addColor = () => {
    const newColor = "#000000"; // Default new color (black)
    const updatedValues = [...value, newColor];
    onChange({ id, value: updatedValues });
    setColorIndex(updatedValues.length - 1); // Set the new color as selected
  };

  // Function to remove a color from the list
  const removeColor = (index) => {
    const updatedValues = value.filter((_, i) => i !== index);
    onChange({ id, value: updatedValues });

    // Adjust the colorIndex if necessary
    if (colorIndex === index) {
      setColorIndex(null);
    } else if (colorIndex > index) {
      setColorIndex(colorIndex - 1);
    }
  };

  const { baseControlProps, controlProps } = useBaseControlProps(field);

  return (
    <BaseControl {...baseControlProps}>
      {/* Display the list of color swatches */}
      <div
        style={{
          display: "flex",
          gap: "8px",
          flexWrap: "wrap",
        }}
      >
        {value.map((color, index) => (
          <div key={index} style={{ position: "relative" }}>
            <div
              onClick={() => {
                if (colorIndex === index) {
                  setColorIndex(null);
                } else {
                  setColorIndex(index);
                }
              }}
              style={{
                width: "32px",
                height: "32px",
                backgroundColor: color,
                border:
                  colorIndex === index ? "2px solid blue" : "1px solid gray",
                borderRadius: "50%",
                cursor: "pointer",
              }}
              title={`Color ${index + 1}`}
            />

            {/* Remove color button */}
            {index >= min && (
              <button
                onClick={() => removeColor(index)}
                style={{
                  position: "absolute",
                  top: "-4px",
                  right: "-4px",
                  backgroundColor: "white",
                  border: "1px solid gray",
                  borderRadius: "50%",
                  width: "16px",
                  height: "16px",
                  cursor: "pointer",
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center",
                  padding: 0,
                }}
                aria-label={`Remove color ${index + 1}`}
              >
                &times;
              </button>
            )}
          </div>
        ))}
        {
          /* Add color button */
          value.length < max && (
            <button
              onClick={addColor}
              style={{
                width: "32px",
                height: "32px",
                backgroundColor: "#f0f0f0",
                border: "1px dashed gray",
                borderRadius: "50%",
                cursor: "pointer",
                display: "flex",
                alignItems: "center",
                justifyContent: "center",
              }}
              aria-label="Add color"
            >
              +
            </button>
          )
        }
      </div>

      {/* Display the ColorPalette when a colorIndex is selected */}
      {colorIndex !== null && (
        <ColorPalette
          value={getCurrentColor()}
          onChange={handleColorChange}
          asButtons={true}
          style={{ marginTop: "16px" }}
          // By not passing a 'colors' prop, the default swatches are used
        />
      )}
    </BaseControl>
  );
};

export default ColorInput;
