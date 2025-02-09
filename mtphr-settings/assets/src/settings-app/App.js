import { __ } from "@wordpress/i18n";
import { useState, useEffect } from "@wordpress/element";
import {
  Button,
  Card,
  CardHeader,
  CardBody,
  CardFooter,
  Notice,
  SlotFillProvider,
  TabPanel,
  __experimentalHeading as Heading,
  createSlotFill,
} from "@wordpress/components";
import Field from "./fields/Field";
import { shouldRenderField } from "./utils/fieldVisibility";

export default ({ settingsId }) => {
  const settingVars = window[`${settingsId}Vars`];
  const [values, setValues] = useState(settingVars.values);
  const [updatedValueKeys, setUpdatedValueKeys] = useState({});
  const [isSaving, setIsSaving] = useState(false);
  const [notice, setNotice] = useState(null); // State for managing the notice
  const fieldSections = settingVars.field_sections;
  const fields = settingVars.fields;

  const { Fill, Slot } = createSlotFill(`${settingsId}Notices`);
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

  //console.log("values", values["mtphr_post_duplicator_settings"]);

  // Build a map of section ids to section data for easy lookup
  const fieldSectionsMap = fieldSections.reduce((map, section) => {
    map[section.id] = section;
    return map;
  }, {});

  // Group fields by their 'section' property, filtering out sections that don't exist in fieldSections
  const sections = fields.reduce((acc, field) => {
    const sectionName = field.section;
    const sectionData = fieldSectionsMap[sectionName];

    if (!sectionData) {
      return acc; // Skip if the section doesn't exist in fieldSections
    }

    let section = acc.find((s) => s.id === sectionName);

    if (!section) {
      section = {
        id: sectionName,
        slug: sectionData.slug,
        label: sectionData.label,
        order:
          typeof sectionData.order !== "undefined" ? sectionData.order : 10,
        // isIntegration:
        //   typeof sectionData.is_integration !== "undefined"
        //     ? sectionData.is_integration
        //     : false,
        fields: [],
      };
      acc.push(section);
    }

    section.fields.push(field);
    return acc;
  }, []);

  // Sort sections by their order
  sections.sort((a, b) => a.order - b.order);

  // Filter sections by enabled integrations and always-visible sections
  const enabledSections = sections.filter((section) => {
    return true;
    // return (
    //   section.id === "general" ||
    //   section.id === "advanced" ||
    //   (section.isIntegration
    //     ? values.integrations?.includes(section.id)
    //     : true)
    // );
  });

  // Prepare tabs for the TabPanel component
  const tabs = enabledSections.map((section) => ({
    id: section.id,
    name: section.slug,
    title: section.label,
  }));

  // Determine initial active tab from URL
  const params = new URLSearchParams(window.location.search);
  const initialSection = params.get("section")
    ? params.get("section")
    : enabledSections.length
    ? enabledSections[0].slug
    : false;
  //const validTabNames = tabs.map((tab) => tab.name);
  const initialTab = initialSection;
  // const initialTab = validTabNames.includes(initialSection)
  //   ? initialSection
  //   : "general";

  const [activeTab, setActiveTab] = useState(initialTab);

  useEffect(() => {
    // Update the URL when activeTab changes
    const params = new URLSearchParams(window.location.search);
    if (!enabledSections.length || activeTab === enabledSections[0].slug) {
      params.delete("section");
    } else {
      params.set("section", activeTab);
    }
    const newUrl = `${window.location.pathname}?${params.toString()}`;
    window.history.replaceState(null, "", newUrl);
  }, [activeTab]);

  // useEffect(() => {
  //   // Update menu visibility whenever values change
  //   const integrations = values.integrations || [];
  //   const alwaysVisible = ["general", "advanced"];

  //   // Get all submenu links
  //   const menuItems = document.querySelectorAll(
  //     ".wp-submenu a[href*='edit.php?post_type=mtphr_email_template&page=settings&section=']"
  //   );

  //   menuItems.forEach((menuItem) => {
  //     const href = menuItem.getAttribute("href");

  //     // Extract the section id from the URL
  //     const urlParams = new URLSearchParams(href.split("?")[1]);
  //     const section = urlParams.get("section");

  //     // Show if it's always visible or enabled in integrations
  //     if (alwaysVisible.includes(section) || integrations.includes(section)) {
  //       menuItem.closest("li").style.display = "";
  //     } else {
  //       menuItem.closest("li").style.display = "none";
  //     }
  //   });
  // }, [values]);

  const handleInputChange = (data) => {
    const { id, value, settingsOption } = data;
    setValues((prevValues) => ({
      ...prevValues,
      [settingsOption]: {
        ...prevValues[settingsOption],
        [id]: value,
      },
    }));

    setUpdatedValueKeys((prevKeys) => ({
      ...prevKeys,
      [settingsOption]: prevKeys[settingsOption]?.includes(id)
        ? prevKeys[settingsOption]
        : [...(prevKeys[settingsOption] || []), id],
    }));
  };

  const handleSave = () => {
    setIsSaving(true);
    fetch(`${settingVars.restUrl}settings`, {
      method: "POST",
      headers: {
        "X-WP-Nonce": settingVars.nonce,
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        valueKeys: updatedValueKeys,
        values: values,
      }),
    })
      .then((response) => {
        if (!response.ok) {
          // Attempt to extract error message from response JSON, if available
          return response
            .json()
            .then((errData) => {
              const errorMessage =
                errData?.message || `HTTP Error ${response.status}`;
              throw new Error(errorMessage);
            })
            .catch(() => {
              throw new Error(`HTTP Error ${response.status}`);
            });
        }
        return response.json(); // Parse response only if it's OK
      })
      .then((data) => {
        // Update the values with sanitized results
        setValues(data);
        setIsSaving(false);
        setNotice({
          status: "success",
          message: __("Settings saved successfully!", "mtphr-emailcustomizer"),
        });
      })
      .catch((error) => {
        setIsSaving(false);
        setNotice({
          status: "error",
          message: `${__("Error saving settings.", "mtphr-emailcustomizer")} ${
            error.message
          }`,
        });
      });
  };

  return (
    <SlotFillProvider>
      <Card className={`mtphrSettings ${settingsId}`}>
        <CardHeader>
          <Heading level={1}>{__("Settings", "mtphr-emailcustomizer")}</Heading>
        </CardHeader>
        <div className="toolbar">
          <Slot />
        </div>
        <CardBody className={`mtphrSettings__form`}>
          <TabPanel
            className={`mtphrSettings__tabs`}
            activeClass="is-active"
            tabs={tabs}
            initialTabName={activeTab}
            onSelect={(tabName) => {
              setActiveTab(tabName);
            }}
          >
            {(tab) => {
              const currentSection = enabledSections.find(
                (section) => section.id === tab.id
              );
              return (
                <div className={`mtphrSettings__section`}>
                  {currentSection.fields.map((field) => {
                    const settingsOption = field.option;
                    const fieldId = field.id;
                    if (!shouldRenderField(field, values[settingsOption]))
                      return null; // Don't render if conditions fail

                    return (
                      <Field
                        key={fieldId}
                        field={field}
                        value={values[settingsOption][fieldId] || ""}
                        onChange={handleInputChange}
                        values={values[settingsOption]}
                        settingsOption={settingsOption}
                        settingsId={settingsId}
                      />
                    );
                  })}
                </div>
              );
            }}
          </TabPanel>
        </CardBody>
        <CardFooter className={`mtphrSettings__footer`}>
          <Button
            onClick={handleSave}
            disabled={isSaving}
            variant="primary"
            isBusy={isSaving}
          >
            {isSaving ? "Saving..." : "Save Settings"}
          </Button>
        </CardFooter>
        <Notification />
      </Card>
    </SlotFillProvider>
  );
};
