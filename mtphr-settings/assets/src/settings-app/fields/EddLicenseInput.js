const { __ } = wp.i18n;
import {
  BaseControl,
  Button,
  ButtonGroup,
  Notice,
  Tooltip,
  useBaseControlProps,
  createSlotFill,
  __experimentalHStack as HStack,
  __experimentalInputControl as InputControl,
  __experimentalSpacer as Spacer,
  __experimentalText as Text,
  __experimentalVStack as VStack,
} from "@wordpress/components";
import { Icon, check, closeSmall, rotateRight } from "@wordpress/icons";

const { useState } = wp.element;

const EddLicenseInput = ({ field, value, onChange, settingsId }) => {
  const {
    class: className,
    id,
    license_data = {},
    activate_url,
    deactivate_url,
    refresh_url,
  } = field;

  const [isUpdating, setIsUpdating] = useState(null);
  const [licenseData, setLicenseData] = useState(license_data);
  const [notice, setNotice] = useState(null); // State for managing the notice

  const { Fill } = createSlotFill(`${settingsId}Notices`);
  const Notification = () => {
    return (
      notice && (
        <Fill>
          <Notice
            status={notice.status}
            onRemove={() => setNotice(null)}
            isDismissible
          >
            {notice.message}
          </Notice>
        </Fill>
      )
    );
  };

  const handleUpdate = (type) => {
    const settingVars = window[`${settingsId}Vars`];
    let apiUrl = false;
    switch (type) {
      case "activate":
        apiUrl = activate_url ? activate_url : false;
        break;
      case "deactivate":
        apiUrl = deactivate_url ? deactivate_url : false;
        break;
      case "refresh":
        apiUrl = refresh_url ? refresh_url : false;
        break;
    }
    if (!apiUrl) {
      return false;
    }

    setIsUpdating(type);

    fetch(apiUrl, {
      method: "POST",
      headers: {
        "X-WP-Nonce": settingVars.nonce,
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ license: value }),
    })
      .then((response) => response.json())
      .then((data) => {
        setLicenseData(data);
        setIsUpdating(false);
        let status = "success";
        let message = __(
          "License key saved successfully!",
          "mtphr-emailcustomizer"
        );
        switch (data.license) {
          case "valid":
            status = "success";
            message = __(
              "License key has been activated!",
              "mtphr-emailcustomizer"
            );
            break;
          case "deactivated":
            status = "warning";
            message = __(
              "License key has been deactivated.",
              "mtphr-emailcustomizer"
            );
            break;
          default:
            break;
        }
        setNotice({
          status: status,
          message: message,
        });
      })
      .catch((error) => {
        setIsUpdating(false);
        setNotice({
          status: "error",
          message: __(
            "Error saving license key updates.",
            "mtphr-emailcustomizer"
          ),
        });
        console.error("Error:", error);
      });
  };

  const onChangeHandler = (nextValue) => {
    onChange({
      id: id,
      value: nextValue,
    });
  };

  const maskedLicense = () => {
    // Check if the string has at least 15 characters
    if (!value || value.length <= 15) {
      return value; // Return the original string if it's shorter than 15 characters
    }

    // Extract the first 15 characters
    const first15Chars = value.slice(0, 15);

    // Calculate the number of characters to replace with '*'
    const numCharsToReplace = value.length - 15;

    // Create a string of '*' with the required length
    const maskedChars = "*".repeat(numCharsToReplace);

    // Concatenate the first 15 characters with the masked characters
    const maskedString = first15Chars + maskedChars;

    return maskedString;
  };

  const { baseControlProps, controlProps } = useBaseControlProps(field);

  return (
    <BaseControl {...baseControlProps}>
      <VStack>
        <HStack alignment="left">
          <Text>{`Status: ${licenseData.license}`}</Text>
          {"valid" == licenseData.license && (
            <Text>{`Expires: ${licenseData.expires}`}</Text>
          )}
        </HStack>
        <HStack wrap={false}>
          <Spacer>
            <InputControl
              style={{ height: "50px" }}
              value={maskedLicense()}
              onChange={onChangeHandler}
              __nextHasNoMarginBottom
            />
          </Spacer>
          <ButtonGroup>
            {licenseData.license && "valid" == licenseData.license && (
              <Tooltip text={__("Refresh License", "mtphr-emailcustomizer")}>
                <Button
                  style={{ height: "50px", width: "50px" }}
                  variant="secondary"
                  disabled={isUpdating}
                  isBusy={"refresh" == isUpdating}
                  onClick={() => handleUpdate("refresh")}
                >
                  <Icon icon={rotateRight} />
                </Button>
              </Tooltip>
            )}
            {licenseData.license && "valid" == licenseData.license ? (
              <Tooltip text={__("Deactivate License", "mtphr-emailcustomizer")}>
                <Button
                  style={{ height: "50px", width: "50px" }}
                  variant="primary"
                  isDestructive={true}
                  disabled={isUpdating}
                  isBusy={"deactivate" == isUpdating}
                  onClick={() => handleUpdate("deactivate")}
                >
                  <Icon icon={closeSmall} />
                </Button>
              </Tooltip>
            ) : (
              <Tooltip text={__("Activate License", "mtphr-emailcustomizer")}>
                <Button
                  style={{ height: "50px", width: "50px" }}
                  variant="primary"
                  disabled={isUpdating}
                  isBusy={"activate" == isUpdating}
                  onClick={() => handleUpdate("activate")}
                >
                  <Icon icon={check} />
                </Button>
              </Tooltip>
            )}
          </ButtonGroup>
        </HStack>
      </VStack>
      <Notification />
    </BaseControl>
  );
};

export default EddLicenseInput;
