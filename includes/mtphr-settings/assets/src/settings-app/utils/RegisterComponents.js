const { registerComponent } = window.mtphrSettingsRegistry || {};

import ButtonInput from "../fields/ButtonInput";
import ButtonsField from "../fields/ButtonsField";
import CheckboxInput from "../fields/CheckboxInput";
import CheckboxesInput from "../fields/CheckboxesInput";
import ColorInput from "../fields/ColorInput";
import CustomHTMLInput from "../fields/CustomHTMLInput";
import EddLicenseInput from "../fields/EddLicenseInput";
import GroupField from "../fields/GroupField";
import HeadingField from "../fields/HeadingField";
import MappingField from "../fields/MappingField";
import NumberInput from "../fields/NumberInput";
import RadioButtonsInput from "../fields/RadioButtonsInput";
import SelectInput from "../fields/SelectInput";
import SpacerField from "../fields/SpacerField";
import TabsField from "../fields/TabsField";
import TextAreaInput from "../fields/TextAreaInput";
import TextInput from "../fields/TextInput";

// Register built-in components
if (registerComponent) {
  registerComponent("button", ButtonInput);
  registerComponent("buttons", ButtonsField);
  registerComponent("color", ColorInput);
  registerComponent("checkbox", CheckboxInput);
  registerComponent("checkboxes", CheckboxesInput);
  registerComponent("edd_license", EddLicenseInput);
  registerComponent("group", GroupField);
  registerComponent("heading", HeadingField);
  registerComponent("mapping", MappingField);
  registerComponent("number", NumberInput);
  registerComponent("radio_buttons", RadioButtonsInput);
  registerComponent("select", SelectInput);
  registerComponent("spacer", SpacerField);
  registerComponent("html", CustomHTMLInput);
  registerComponent("tabs", TabsField);
  registerComponent("text", TextInput);
  registerComponent("textarea", TextAreaInput);
}
