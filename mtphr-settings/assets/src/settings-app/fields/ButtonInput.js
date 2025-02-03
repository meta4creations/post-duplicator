import {
  BaseControl,
  Button,
  createSlotFill,
  Notice,
  useBaseControlProps,
} from "@wordpress/components";
const { useState } = wp.element;

const ButtonInput = ({ field, settings, settingsId }) => {
  const [isSaving, setIsSaving] = useState(false);
  const [notice, setNotice] = useState(null);

  const {
    action,
    class: className,
    description,
    disabled,
    icon,
    iconPosition,
    iconSize,
    isDestructive,
    isLink,
    size,
    text,
    target,
    variant = "secondary",
  } = field;

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
            <div dangerouslySetInnerHTML={{ __html: notice.message }} />
          </Notice>
        </Fill>
      )
    );
  };

  const onClickHandler = () => {
    // If confirm exists, show confirmation dialog
    if (action?.confirm) {
      const confirmMessage = action.confirm;
      if (!window.confirm(confirmMessage)) {
        return; // Exit if user cancels
      }
    }

    if (action && action.type === "api") {
      const apiUrl = action.url;
      const settingVars = window[`${settingsId}Vars`];
      setNotice(null);

      setIsSaving(true); // Start saving state
      fetch(apiUrl, {
        method: "POST",
        headers: {
          "X-WP-Nonce": settingVars.nonce,
          "Content-Type": "application/json",
        },
        body: JSON.stringify(settings), // Pass the current settings
      })
        .then((response) => response.json())
        .then((data) => {
          setIsSaving(false); // Stop saving state
          if (action.response) {
            if (typeof data === "object" && data !== null) {
              setNotice(data);
            } else {
              setNotice({
                status: "success",
                message: data,
              });
            }
          }
        })
        .catch((error) => {
          setIsSaving(false);
          console.error("Error:", error);
        });
    } else if (!action || action.type === "default") {
      // For 'default' type or if action does not exist, treat as a link
      window.location.href = action.url;
    }
  };

  const { baseControlProps } = useBaseControlProps(field);

  return (
    <BaseControl {...baseControlProps} __nextHasNoMarginBottom>
      <Button
        className={className}
        description={description}
        disabled={isSaving && action?.type === "api"}
        href={action?.type === "api" ? null : action.url}
        icon={icon}
        iconPosition={iconPosition}
        iconSize={iconSize}
        isBusy={isSaving}
        isDestructive={isDestructive}
        isLink={isLink}
        onClick={onClickHandler}
        size={size}
        target={target}
        text={text}
        variant={variant}
      />
      <Notification />
    </BaseControl>
  );
};

export default ButtonInput;
